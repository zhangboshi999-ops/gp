<?php

namespace app\common\library;

use think\Config;
use think\Db;

/**
 * 外部行情同步服务
 *
 * 数据源：
 *  - Twelve Data：东京/美股报价、日线、基本面统计、指数
 *  - CoinGecko ：加密货币价格、24h 涨跌、市值排名、OHLC
 *
 * 落库表：fa_stock（最新快照）、fa_stock_kline（历史 K 线）
 */
class MarketSync
{
    /** @var array 同步日志 */
    protected static $messages = [];

    /** @var int 已消耗的接口额度 */
    protected static $credits = 0;

    public static function messages()
    {
        return self::$messages;
    }

    public static function credits()
    {
        return self::$credits;
    }

    protected static function log($message)
    {
        self::$messages[] = $message;
    }

    /**
     * 读取配置
     */
    protected static function conf($key = '')
    {
        $config = Config::get('market');
        if ($key === '') {
            return $config;
        }
        $keys = explode('.', $key);
        $value = $config;
        foreach ($keys as $k) {
            $value = isset($value[$k]) ? $value[$k] : null;
        }
        return $value;
    }

    /**
     * HTTP GET 请求（返回数组）
     */
    protected static function http($url, $params = [], $headers = [])
    {
        if ($params) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
        }
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::conf('timeout') ?: 20,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_HTTPHEADER     => array_merge(
                ['Accept: application/json', 'Accept-Language: en-US,en;q=0.9'],
                $headers
            ),
            // 部分数据源（如纳斯达克官方接口）会拒绝非浏览器 UA
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36',
        ]);
        $body = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($body === false) {
            throw new \Exception('HTTP 请求失败：' . $error);
        }
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \Exception('返回内容非 JSON：' . substr($body, 0, 120));
        }
        return $data;
    }

    /**
     * 检查 Twelve Data 返回是否出错
     */
    protected static function assertTwelve($data)
    {
        if (isset($data['status']) && $data['status'] === 'error') {
            $code = isset($data['code']) ? $data['code'] : 0;
            $message = isset($data['message']) ? $data['message'] : '未知错误';
            if ($code == 429) {
                $message .= '（免费额度用尽或请求过快，请稍后再试）';
            }
            throw new \Exception('[TwelveData ' . $code . '] ' . $message);
        }
    }

    protected static function apiKey()
    {
        $key = self::conf('twelvedata.apikey');
        if (!$key) {
            throw new \Exception('未配置 Twelve Data API Key：请在 application/extra/market.php 中填写 twelvedata.apikey（https://twelvedata.com 免费申请）');
        }
        return $key;
    }

    /* ============================ 股票/指数行情 ============================ */

    /**
     * 同步标的快照行情（Twelve Data /quote）
     *
     * @param array $codes 留空则同步全部股票
     * @return int 更新数量
     */
    public static function syncQuote($codes = [], $market = '')
    {
        $query = Db::name('stock')->where('type', 'in', ['stock'])->where('status', 'normal');
        $markets = self::conf('sync_markets');
        if ($market) {
            // 指定市场（用于按市场省额度地刷新）
            $query->where('market', $market);
        } elseif ($markets && !$codes) {
            // 只同步配置范围内的市场（默认日本 + 美国股票）
            $query->where('market', 'in', $markets);
        }
        if ($codes) {
            $query->where('code', 'in', $codes);
        }
        $stocks = $query->select();
        if (!$stocks) {
            self::log('没有需要同步的标的');
            return 0;
        }

        // iTick 配置了令牌时，日本 + 美国股票统一走 iTick
        if (self::conf('itick.token')) {
            $updated = self::quoteByItick($stocks);
            self::log('行情同步完成，更新 ' . $updated . ' 个标的');
            return $updated;
        }

        // 日本股票与美股走不同数据源（Twelve Data 免费计划不含 TSE）
        $jpSource = self::conf('japan.source');
        $hasTdKey = (bool)self::conf('twelvedata.apikey');
        $jp = [];
        $nasdaq = [];
        $others = [];
        foreach ($stocks as $stock) {
            if ($stock['market'] == 'TSE' && $jpSource && $jpSource != 'twelvedata') {
                $jp[] = $stock;
            } elseif (!$hasTdKey && $stock['market'] == 'NASDAQ') {
                // 未配置 Twelve Data key 时，纳斯达克股票走官方免 key 接口
                $nasdaq[] = $stock;
            } else {
                $others[] = $stock;
            }
        }

        $updated = 0;
        if ($jp) {
            $updated += self::quoteJapan($jp, $jpSource);
        }
        if ($nasdaq) {
            $updated += self::quoteByNasdaq($nasdaq);
        }
        if ($others) {
            $updated += self::quoteByTwelve($others);
        }
        self::log('行情同步完成，更新 ' . $updated . ' 个标的');
        return $updated;
    }

    /**
     * 美股行情（Twelve Data /quote，按交易所分组批量请求）
     */
    protected static function quoteByTwelve($stocks)
    {
        $groups = [];
        foreach ($stocks as $stock) {
            $exchange = isset(self::conf('exchange')[$stock['market']]) ? self::conf('exchange')[$stock['market']] : '';
            $groups[$exchange][] = $stock;
        }

        $updated = 0;
        foreach ($groups as $exchange => $rows) {
            foreach (array_chunk($rows, 5) as $chunk) {
                $symbols = array_map(function ($row) {
                    return $row['code'];
                }, $chunk);
                try {
                    $params = [
                        'symbol' => implode(',', $symbols),
                        'apikey' => self::apiKey(),
                    ];
                    if ($exchange) {
                        $params['exchange'] = $exchange;
                    }
                    $data = self::http(self::conf('twelvedata.endpoint') . '/quote', $params);
                    self::assertTwelve($data);
                    self::$credits += count($symbols);

                    // 单代码返回对象，多代码返回数组
                    $list = isset($data[0]) ? $data : [$data];
                    foreach ($list as $item) {
                        $updated += self::saveQuote($item, $exchange);
                    }
                } catch (\Exception $e) {
                    self::log('行情同步失败 ' . implode(',', $symbols) . '：' . $e->getMessage());
                }
                $interval = (int)self::conf('twelvedata.interval');
                if ($interval > 0) {
                    sleep($interval);
                }
            }
        }
        return $updated;
    }

    /**
     * 免 key 兜底：纳斯达克官方接口（仅限 NASDAQ 上市的美股）
     *
     * /info    实时价格、涨跌额、涨跌幅、成交量
     * /summary 昨收、52周高低、市值、股息率、行业
     */
    protected static function quoteByNasdaq($rows)
    {
        $updated = 0;
        foreach ($rows as $row) {
            try {
                $info = self::http(self::conf('nasdaq.endpoint') . '/' . $row['code'] . '/info', ['assetclass' => 'stocks']);
                $primary = isset($info['data']['primaryData']) ? $info['data']['primaryData'] : [];
                if (!isset($primary['lastSalePrice'])) {
                    self::log('无行情数据：' . $row['code']);
                    continue;
                }
                $summary = [];
                try {
                    $sum = self::http(self::conf('nasdaq.endpoint') . '/' . $row['code'] . '/summary', ['assetclass' => 'stocks']);
                    $summary = isset($sum['data']['summaryData']) ? $sum['data']['summaryData'] : [];
                } catch (\Exception $e) {
                    // 基本面取不到不影响行情更新
                }

                $price = self::toNumber($primary['lastSalePrice']);
                $change = self::toNumber($primary['netChange']);
                $prev = isset($summary['PreviousClose']) ? self::toNumber($summary['PreviousClose']) : 0;
                if (!$prev) {
                    $prev = $price - $change;
                }
                $volume = (int)self::toNumber($primary['volume']);

                $update = [
                    'price'         => $price,
                    'prev_close'    => $prev,
                    'open'          => $price,
                    'high'          => $price,
                    'low'           => $price,
                    'change_amount' => $change,
                    'change_rate'   => self::toNumber($primary['percentageChange']),
                    'volume'        => $volume,
                    'turnover'      => round($volume * $price, 2),
                    'source'        => 'nasdaq',
                    'symbol'        => $row['code'],
                    'updatetime'    => time(),
                ];
                foreach (['Sector' => 'sector', 'MarketCap' => 'market_cap', 'Yield' => 'dividend_yield'] as $key => $field) {
                    if (isset($summary[$key])) {
                        $raw = is_array($summary[$key]) ? $summary[$key]['value'] : $summary[$key];
                        $raw = trim(strip_tags((string)$raw));
                        $update[$field] = $field == 'sector' ? $raw : self::toNumber($raw);
                    }
                }
                if (isset($summary['FiftTwoWeekHighLow'])) {
                    $parts = explode('/', trim(strip_tags((string)$summary['FiftTwoWeekHighLow']['value'])));
                    if (count($parts) == 2) {
                        $update['high_52w'] = self::toNumber($parts[0]);
                        $update['low_52w'] = self::toNumber($parts[1]);
                    }
                }
                Db::name('stock')->where('id', $row['id'])->update($update);
                $updated++;
            } catch (\Exception $e) {
                self::log('纳斯达克行情同步失败 ' . $row['code'] . '：' . $e->getMessage());
            }
            usleep(300000);
        }
        self::log('纳斯达克行情同步完成，更新 ' . $updated . ' 个标的');
        return $updated;
    }

    /**
     * 把 "$345.39" / "-0.49%" / "1,616,994" 转成数字
     */
    protected static function toNumber($value)
    {
        if (is_array($value)) {
            $value = isset($value['value']) ? $value['value'] : '';
        }
        $value = trim(strip_tags((string)$value));
        $value = preg_replace('/[^0-9\.\-]/', '', $value);
        return is_numeric($value) ? (float)$value : 0;
    }

    /**
     * iTick 行情：日本 + 美国股票统一数据源
     *
     * 接口 GET /stock/quote?region={region}&code={code}，鉴权用 token 头。
     * 返回 data: { s 代码, ld 最新价, o 开, p 前收, h 高, l 低,
     *              t 时间戳(ms), v 成交量, tu 成交额, ch 涨跌额, chp 涨跌幅% }
     * 免费体验版速率约 1 次/秒且不支持批量，因此逐只请求 + 间隔限速 + 限流退避重试。
     */
    protected static function quoteByItick($rows)
    {
        $updated = 0;
        $interval = (int)self::conf('itick.interval') ?: 1200000;
        $maxRetry = (int)self::conf('itick.retry') ?: 2;
        foreach ($rows as $row) {
            $region = self::itickRegion($row['market']);
            if (!$region) {
                self::log('iTick 暂不支持该市场：' . $row['market'] . ' / ' . $row['code']);
                continue;
            }
            $data = null;
            for ($try = 0; $try <= $maxRetry; $try++) {
                $res = null;
                try {
                    $res = self::http(self::conf('itick.endpoint') . '/stock/quote', [
                        'region' => $region,
                        'code'   => $row['code'],
                    ], ['token: ' . self::conf('itick.token')]);
                } catch (\Exception $e) {
                    self::log('iTick 请求失败 ' . $row['code'] . '：' . $e->getMessage());
                    usleep($interval * 2);
                    continue;
                }
                if (self::itickRateLimited($res)) {
                    // 触发限流：等更久再重试
                    usleep($interval * (3 + $try * 3));
                    continue;
                }
                if (!empty($res['data']['ld'])) {
                    $data = $res['data'];
                }
                break;
            }
            if (!$data) {
                self::log('iTick 无可用数据或被限流：' . $row['code'] . '（' . $region . '）');
                usleep($interval);
                continue;
            }
            self::saveItickQuote($row, $data);
            $updated++;
            usleep($interval);
        }
        return $updated;
    }

    /**
     * 判断 iTick 是否返回限流（免费体验版常见）
     */
    protected static function itickRateLimited($res)
    {
        if (!is_array($res)) {
            return false;
        }
        if (isset($res['error_msg']) && strpos($res['error_msg'], '429') !== false) {
            return true;
        }
        $msg = isset($res['msg']) ? strtolower((string)$res['msg']) : '';
        return strpos($msg, 'too much') !== false || strpos($msg, 'limit exceeded') !== false;
    }

    /**
     * fa_stock.market => iTick region
     */
    protected static function itickRegion($market)
    {
        $map = self::conf('itick.region');
        return isset($map[$market]) ? $map[$market] : '';
    }

    /**
     * 写入 iTick 行情快照（美日统一）
     */
    protected static function saveItickQuote($row, $data)
    {
        $price = (float)$data['ld'];
        $prev = isset($data['p']) ? (float)$data['p'] : $price;
        $high = isset($data['h']) ? (float)$data['h'] : $price;
        $low = isset($data['l']) ? (float)$data['l'] : $price;
        $open = isset($data['o']) ? (float)$data['o'] : $price;
        $volume = (int)(float)(isset($data['v']) ? $data['v'] : 0);
        $update = [
            'price'         => $price,
            'prev_close'    => $prev,
            'open'          => $open,
            'high'          => $high,
            'low'           => $low,
            'change_amount' => isset($data['ch']) ? (float)$data['ch'] : round($price - $prev, 4),
            'change_rate'   => isset($data['chp']) ? (float)$data['chp'] : 0,
            'volume'        => $volume,
            'turnover'      => isset($data['tu']) ? (float)$data['tu'] : 0,
            'amplitude'     => $prev ? round(($high - $low) / $prev * 100, 4) : 0,
            'source'        => 'itick',
            'symbol'        => isset($data['s']) ? $data['s'] : $row['code'],
            'updatetime'    => time(),
        ];
        Db::name('stock')->where('id', $row['id'])->update($update);
        return 1;
    }

    /**
     * 日本股票行情（FMP 或 Alpha Vantage）
     */
    protected static function quoteJapan($rows, $source)
    {
        $updated = 0;
        foreach ($rows as $row) {
            try {
                if ($source == 'alphavantage') {
                    $data = self::http(self::conf('japan.alphavantage.endpoint'), [
                        'function' => 'GLOBAL_QUOTE',
                        'symbol'   => $row['code'] . '.TYO',
                        'apikey'   => self::jpApiKey('alphavantage'),
                    ]);
                    if (isset($data['Note']) || isset($data['Information'])) {
                        throw new \Exception('免费额度用尽或被限流（25 次/天）');
                    }
                    $quote = isset($data['Global Quote']) ? $data['Global Quote'] : [];
                    if (!isset($quote['05. price'])) {
                        throw new \Exception('无行情数据');
                    }
                    $updated += self::saveJpQuote($row, [
                        'price'      => $quote['05. price'],
                        'prev_close' => $quote['08. previous close'],
                        'open'       => isset($quote['02. open']) ? $quote['02. open'] : $quote['05. price'],
                        'high'       => isset($quote['03. high']) ? $quote['03. high'] : $quote['05. price'],
                        'low'        => isset($quote['04. low']) ? $quote['04. low'] : $quote['05. price'],
                        'change'     => isset($quote['09. change']) ? $quote['09. change'] : 0,
                        'percent'    => isset($quote['10. change percent']) ? rtrim($quote['10. change percent'], '%') : 0,
                        'volume'     => isset($quote['06. volume']) ? $quote['06. volume'] : 0,
                        'symbol'     => $row['code'] . '.TYO',
                        'source'     => 'alphavantage',
                    ]);
                } elseif ($source == 'hayinsights') {
                    $data = self::http(
                        self::conf('japan.hayinsights.endpoint') . '/market/jp/equities/' . $row['code'],
                        [],
                        ['X-API-Key: ' . self::jpApiKey('hayinsights')]
                    );
                    if (empty($data['success']) || empty($data['data'])) {
                        $message = isset($data['error']['message']) ? $data['error']['message'] : '接口返回异常';
                        throw new \Exception($message);
                    }
                    $item = $data['data'];
                    $quote = isset($item['quote']) ? $item['quote'] : [];
                    if (!isset($quote['price'])) {
                        self::log('无行情数据：' . $row['code']);
                        continue;
                    }
                    $price = (float)$quote['price'];
                    $prev = isset($quote['previousClose']) ? (float)$quote['previousClose'] : $price;
                    $volume = (int)(isset($item['tradingActivity']['volume']) ? $item['tradingActivity']['volume'] : 0);
                    $value = (float)(isset($item['tradingActivity']['tradingValue']) ? $item['tradingActivity']['tradingValue'] : 0);
                    $shares = isset($item['overview']['sharesOutstanding']) ? (int)$item['overview']['sharesOutstanding'] : 0;
                    $treasury = isset($item['overview']['treasuryShares']) ? (int)$item['overview']['treasuryShares'] : 0;
                    $floating = max($shares - $treasury, 0);
                    $update = [
                        'price'         => $price,
                        'prev_close'    => $prev,
                        'open'          => $price,
                        'high'          => $price,
                        'low'           => $price,
                        'change_amount' => isset($quote['change']) ? (float)$quote['change'] : round($price - $prev, 4),
                        'change_rate'   => isset($quote['changePercent']) ? (float)$quote['changePercent'] : 0,
                        'volume'        => $volume,
                        'turnover'      => $value,
                        'turnover_rate' => $floating ? round($volume / $floating * 100, 4) : 0,
                        'source'        => 'hayinsights',
                        'symbol'        => isset($item['symbol']) ? $item['symbol'] : $row['code'],
                        'updatetime'    => time(),
                    ];
                    if (!empty($item['nameLocal']) && $row['source'] != 'manual') {
                        $update['name'] = $item['nameLocal'];
                    } elseif (!empty($item['name']) && $row['source'] != 'manual') {
                        $update['name'] = $item['name'];
                    }
                    if (isset($item['sector']['name'])) {
                        $update['sector'] = $item['sector']['name'];
                    }
                    if (isset($item['overview']['marketCap'])) {
                        $update['market_cap'] = (float)$item['overview']['marketCap'];
                    }
                    if (isset($item['overview']['tradingUnit'])) {
                        $update['lot_size'] = (int)$item['overview']['tradingUnit'];
                    }
                    if ($shares) {
                        $update['total_shares'] = $shares;
                    }
                    if (isset($item['ratios']['peRatio'])) {
                        $update['pe_ratio'] = (float)$item['ratios']['peRatio'];
                    }
                    if (isset($item['ratios']['pbRatio'])) {
                        $update['pb_ratio'] = (float)$item['ratios']['pbRatio'];
                    }
                    Db::name('stock')->where('id', $row['id'])->update($update);
                    $updated++;
                    continue;
                } else {
                    $data = self::http(self::conf('japan.fmp.endpoint') . '/quote/' . $row['code'] . '.TSE', [
                        'apikey' => self::jpApiKey('fmp'),
                    ]);
                    if (isset($data['Error Message'])) {
                        throw new \Exception($data['Error Message']);
                    }
                    $item = isset($data[0]) ? $data[0] : null;
                    if (!$item || !isset($item['price'])) {
                        self::log('无行情数据：' . $row['code']);
                        continue;
                    }
                    $updated += self::saveJpQuote($row, [
                        'price'      => $item['price'],
                        'prev_close' => isset($item['previousClose']) ? $item['previousClose'] : $item['price'],
                        'open'       => isset($item['open']) ? $item['open'] : $item['price'],
                        'high'       => isset($item['dayHigh']) ? $item['dayHigh'] : $item['price'],
                        'low'        => isset($item['dayLow']) ? $item['dayLow'] : $item['price'],
                        'change'     => isset($item['change']) ? $item['change'] : 0,
                        'percent'    => isset($item['changesPercentage']) ? rtrim($item['changesPercentage'], '%') : 0,
                        'volume'     => isset($item['volume']) ? $item['volume'] : 0,
                        'market_cap' => isset($item['marketCap']) ? $item['marketCap'] : 0,
                        'pe_ratio'   => isset($item['pe']) ? $item['pe'] : 0,
                        'high_52w'   => isset($item['yearHigh']) ? $item['yearHigh'] : 0,
                        'low_52w'    => isset($item['yearLow']) ? $item['yearLow'] : 0,
                        'name'       => isset($item['name']) ? $item['name'] : '',
                        'symbol'     => $row['code'] . '.TSE',
                        'source'     => 'fmp',
                    ]);
                }
            } catch (\Exception $e) {
                self::log('日股行情同步失败 ' . $row['code'] . '：' . $e->getMessage());
            }
            // Alpha Vantage 免费 25 次/天，FMP 250 次/天，依次请求即可
            sleep(1);
        }
        return $updated;
    }

    /**
     * 写入日本股票行情
     */
    protected static function saveJpQuote($row, $data)
    {
        $price = (float)$data['price'];
        $prevClose = (float)$data['prev_close'];
        $volume = (int)$data['volume'];
        $update = [
            'price'        => $price,
            'prev_close'   => $prevClose,
            'open'         => (float)$data['open'],
            'high'         => (float)$data['high'],
            'low'          => (float)$data['low'],
            'change_amount'=> (float)$data['change'],
            'change_rate'  => (float)$data['percent'],
            'volume'       => $volume,
            'turnover'     => round($volume * $price, 2),
            'amplitude'    => $prevClose ? round(((float)$data['high'] - (float)$data['low']) / $prevClose * 100, 4) : 0,
            'symbol'       => $data['symbol'],
            'source'       => $data['source'],
            'updatetime'   => time(),
        ];
        if (!empty($data['name']) && $row['source'] != 'manual') {
            $update['name'] = $data['name'];
        }
        foreach (['market_cap', 'pe_ratio', 'high_52w', 'low_52w'] as $key) {
            if (isset($data[$key])) {
                $update[$key] = (float)$data[$key];
            }
        }
        Db::name('stock')->where('id', $row['id'])->update($update);
        return 1;
    }

    protected static function jpApiKey($source)
    {
        $key = self::conf('japan.' . $source . '.apikey');
        $names = [
            'fmp'          => 'Financial Modeling Prep（https://site.financialmodelingprep.com/register 免费 250 次/天）',
            'alphavantage' => 'Alpha Vantage（https://www.alphavantage.co/support/#api-key 免费 25 次/天）',
            'hayinsights'  => 'HayInsights（https://app.hayinsights.com → Account → API keys）',
        ];
        if (!$key) {
            throw new \Exception('未配置日本行情 API Key：请在 application/extra/market.php 填 japan.' . $source . '.apikey → ' . (isset($names[$source]) ? $names[$source] : $source));
        }
        return $key;
    }

    /**
     * 把 Twelve Data 的 quote 写入 fa_stock
     */
    protected static function saveQuote($item, $exchange = '')
    {
        $code = isset($item['symbol']) ? $item['symbol'] : '';
        if (!$code || !isset($item['close'])) {
            return 0;
        }
        // 兼容多种代码格式：7203.TSE / BTC/USD / 7203
        $code = preg_replace('/\.(TSE|TYO|NYSE|NASDAQ)$/', '', $code);
        $code = preg_replace('#/.+$#', '', $code);
        $row = Db::name('stock')->where('code', $code)->find();
        if (!$row) {
            return 0;
        }
        $close = (float)$item['close'];
        $prevClose = isset($item['previous_close']) ? (float)$item['previous_close'] : $row['prev_close'];
        $high = isset($item['high']) ? (float)$item['high'] : $row['high'];
        $low = isset($item['low']) ? (float)$item['low'] : $row['low'];
        $update = [
            'price'        => $close,
            'prev_close'   => $prevClose,
            'open'         => isset($item['open']) ? (float)$item['open'] : $row['open'],
            'high'         => $high,
            'low'          => $low,
            'change_amount'=> isset($item['change']) ? (float)$item['change'] : round($close - $prevClose, 4),
            'change_rate'  => isset($item['percent_change']) ? (float)$item['percent_change'] : ($prevClose ? round(($close - $prevClose) / $prevClose * 100, 4) : 0),
            'volume'       => isset($item['volume']) ? (int)$item['volume'] : $row['volume'],
            'turnover'     => isset($item['volume']) ? round((int)$item['volume'] * $close, 2) : $row['turnover'],
            'amplitude'    => $prevClose ? round(($high - $low) / $prevClose * 100, 4) : 0,
            'source'       => 'twelvedata',
            'symbol'       => $exchange ? $code . '.' . $exchange : $code,
            'updatetime'   => time(),
        ];
        Db::name('stock')->where('id', $row['id'])->update($update);
        return 1;
    }

    /**
     * 同步日线（Twelve Data /time_series）
     */
    public static function syncKline($code, $limit = 0)
    {
        $limit = $limit ?: (int)self::conf('kline_limit') ?: 90;
        $row = Db::name('stock')->where('code', $code)->find();
        if (!$row) {
            self::log('标的不存在：' . $code);
            return 0;
        }
        // iTick 已配置令牌时，日线也走 iTick（日股、美股统一）
        if (self::conf('itick.token')) {
            return self::klineByItick($row, $limit);
        }

        // 日本股票走独立数据源
        $jpSource = self::conf('japan.source');
        if ($row['market'] == 'TSE' && $jpSource && $jpSource != 'twelvedata') {
            return self::klineJapan($row, $limit);
        }

        $exchange = isset(self::conf('exchange')[$row['market']]) ? self::conf('exchange')[$row['market']] : '';
        try {
            $params = [
                'symbol'     => $code,
                'interval'   => '1day',
                'outputsize' => $limit,
                'apikey'     => self::apiKey(),
            ];
            if ($exchange && $row['type'] != 'index') {
                $params['exchange'] = $exchange;
            }
            $data = self::http(self::conf('twelvedata.endpoint') . '/time_series', $params);
            self::assertTwelve($data);
            self::$credits++;
        } catch (\Exception $e) {
            self::log('K线同步失败 ' . $code . '：' . $e->getMessage());
            return 0;
        }

        if (empty($data['values'])) {
            self::log('K线为空：' . $code);
            return 0;
        }
        return self::saveKline($row, $data['values']);
    }

    /**
     * 日本股票日线（FMP / Alpha Vantage）
     */
    protected static function klineJapan($row, $limit)
    {
        $source = self::conf('japan.source');
        $values = [];
        try {
            if ($source == 'alphavantage') {
                $data = self::http(self::conf('japan.alphavantage.endpoint'), [
                    'function'   => 'TIME_SERIES_DAILY',
                    'symbol'     => $row['code'] . '.TYO',
                    'outputsize' => $limit > 100 ? 'full' : 'compact',
                    'apikey'     => self::jpApiKey('alphavantage'),
                ]);
                $series = isset($data['Time Series (Daily)']) ? $data['Time Series (Daily)'] : [];
                foreach ($series as $date => $item) {
                    $values[] = [
                        'datetime' => $date,
                        'open'     => $item['1. open'],
                        'high'     => $item['2. high'],
                        'low'      => $item['3. low'],
                        'close'    => $item['4. close'],
                        'volume'   => isset($item['5. volume']) ? $item['5. volume'] : 0,
                    ];
                }
            } elseif ($source == 'hayinsights') {
                $data = self::http(
                    self::conf('japan.hayinsights.endpoint') . '/market/jp/equities/' . $row['code'] . '/prices',
                    [],
                    ['X-API-Key: ' . self::jpApiKey('hayinsights')]
                );
                if (empty($data['success']) || empty($data['data'])) {
                    $message = isset($data['error']['message']) ? $data['error']['message'] : '接口返回异常';
                    throw new \Exception($message);
                }
                $series = isset($data['data']['prices']) ? $data['data']['prices'] : $data['data'];
                foreach ((array)$series as $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    $date = isset($item['date']) ? $item['date'] : (isset($item['tradingDate']) ? $item['tradingDate'] : '');
                    $close = isset($item['adjustedClose']) ? $item['adjustedClose'] : (isset($item['close']) ? $item['close'] : null);
                    if (!$date || $close === null) {
                        continue;
                    }
                    $values[] = [
                        'datetime' => $date,
                        'open'     => isset($item['open']) ? $item['open'] : $close,
                        'high'     => isset($item['high']) ? $item['high'] : $close,
                        'low'      => isset($item['low']) ? $item['low'] : $close,
                        'close'    => $close,
                        'volume'   => isset($item['adjustedVolume']) ? $item['adjustedVolume'] : (isset($item['volume']) ? $item['volume'] : 0),
                    ];
                }
            } else {
                $data = self::http(self::conf('japan.fmp.endpoint') . '/historical-price-full/' . $row['code'] . '.TSE', [
                    'apikey' => self::jpApiKey('fmp'),
                ]);
                $historical = isset($data['historical']) ? $data['historical'] : [];
                foreach ($historical as $item) {
                    $values[] = [
                        'datetime' => $item['date'],
                        'open'     => $item['open'],
                        'high'     => $item['high'],
                        'low'      => $item['low'],
                        'close'    => $item['close'],
                        'volume'   => isset($item['volume']) ? $item['volume'] : 0,
                    ];
                }
            }
        } catch (\Exception $e) {
            self::log('日股K线同步失败 ' . $row['code'] . '：' . $e->getMessage());
            return 0;
        }
        if (!$values) {
            self::log('日股K线为空：' . $row['code']);
            return 0;
        }
        // 接口按日期倒序返回，截取最新 N 根
        $values = array_slice($values, 0, $limit);
        return self::saveKline($row, $values);
    }

    /**
     * iTick 日线：GET /stock/kline?region={region}&code={code}&kType={kType}&limit={limit}
     *
     * kType：1=1分 2=5分 3=15分 4=30分 5=1时 6=2时 7=4时 8=日K 9=周K 10=月K
     * 返回 data: [{ t 时间戳(ms), o, h, l, c, v, tu }]
     */
    protected static function klineByItick($row, $limit)
    {
        $interval = (int)self::conf('itick.interval') ?: 1200000;
        $region = self::itickRegion($row['market']);
        if (!$region) {
            self::log('iTick 暂不支持该市场：' . $row['market']);
            return 0;
        }
        try {
            $res = self::http(self::conf('itick.endpoint') . '/stock/kline', [
                'region' => $region,
                'code'   => $row['code'],
                'kType'  => 8,
                'limit'  => $limit,
            ], ['token: ' . self::conf('itick.token')]);
        } catch (\Exception $e) {
            self::log('iTick K线请求失败 ' . $row['code'] . '：' . $e->getMessage());
            return 0;
        }
        if (empty($res['data']) || !is_array($res['data'])) {
            self::log('iTick K线为空：' . $row['code']);
            return 0;
        }
        // 时间戳是 UTC：东京 00:00 = UTC 前日 15:00，需 +9h 才落在正确的交易日；美股用 UTC 日期即可
        $offset = $region == 'JP' ? 9 * 3600 : 0;
        $values = [];
        foreach ($res['data'] as $item) {
            if (!isset($item['t'], $item['c'])) {
                continue;
            }
            $values[] = [
                'datetime' => gmdate('Y-m-d', (int)($item['t'] / 1000) + $offset),
                'open'     => (float)$item['o'],
                'high'     => (float)$item['h'],
                'low'      => (float)$item['l'],
                'close'    => (float)$item['c'],
                'volume'   => (int)(float)$item['v'],
            ];
        }
        usleep($interval);
        if (!$values) {
            self::log('iTick K线解析为空：' . $row['code']);
            return 0;
        }
        return self::saveKline($row, $values);
    }

    /**
     * 写入 K 线（先清理同周期旧数据再批量插入）
     */
    protected static function saveKline($row, $values)
    {
        $rows = [];
        foreach ($values as $item) {
            $time = strtotime($item['datetime']);
            if (!$time) {
                continue;
            }
            $rows[] = [
                'stock_id'   => $row['id'],
                'code'       => $row['code'],
                'period'     => '1d',
                'time'       => $time,
                'date'       => date('Y-m-d', $time),
                'open'       => (float)$item['open'],
                'high'       => (float)$item['high'],
                'low'        => (float)$item['low'],
                'close'      => (float)$item['close'],
                'volume'     => isset($item['volume']) ? (int)$item['volume'] : 0,
                'createtime' => time(),
            ];
        }
        if (!$rows) {
            return 0;
        }
        // 时间升序
        usort($rows, function ($a, $b) {
            return $a['time'] - $b['time'];
        });
        Db::name('stock_kline')->where('code', $row['code'])->where('period', '1d')->delete();
        Db::name('stock_kline')->insertAll($rows);
        self::log('K线已更新 ' . $row['code'] . '（' . count($rows) . '根）');
        return count($rows);
    }

    /**
     * 同步基本面（市值 / PE / PB / 股息率 / 52周高低）
     */
    public static function syncProfile($codes = [])
    {
        $query = Db::name('stock')->where('type', 'stock')->where('status', 'normal');
        if ($codes) {
            $query->where('code', 'in', $codes);
        }
        $stocks = $query->select();
        $updated = 0;
        $interval = (int)self::conf('twelvedata.interval');
        foreach ($stocks as $stock) {
            $exchange = isset(self::conf('exchange')[$stock['market']]) ? self::conf('exchange')[$stock['market']] : '';
            try {
                $params = ['symbol' => $stock['code'], 'apikey' => self::apiKey()];
                if ($exchange) {
                    $params['exchange'] = $exchange;
                }
                $data = self::http(self::conf('twelvedata.endpoint') . '/statistics', $params);
                self::assertTwelve($data);
                self::$credits++;
                $update = [];
                $valuation = isset($data['valuation']) ? $data['valuation'] : [];
                $share = isset($data['share_stats']) ? $data['share_stats'] : [];
                $dividend = isset($data['dividends']) ? $data['dividends'] : [];
                $range = isset($data['fifty_two_week']) ? $data['fifty_two_week'] : [];
                if (isset($valuation['market_capitalization'])) {
                    $update['market_cap'] = (float)$valuation['market_capitalization'];
                }
                if (isset($valuation['pe_ratio'])) {
                    $update['pe_ratio'] = (float)$valuation['pe_ratio'];
                }
                if (isset($valuation['pb_ratio'])) {
                    $update['pb_ratio'] = (float)$valuation['pb_ratio'];
                }
                if (isset($share['shares_outstanding'])) {
                    $update['total_shares'] = (int)$share['shares_outstanding'];
                }
                if (isset($dividend['dividend_yield'])) {
                    $update['dividend_yield'] = (float)$dividend['dividend_yield'];
                }
                if (isset($range['high'])) {
                    $update['high_52w'] = (float)$range['high'];
                }
                if (isset($range['low'])) {
                    $update['low_52w'] = (float)$range['low'];
                }
                if (isset($data['company']['sector'])) {
                    $update['sector'] = $data['company']['sector'];
                }
                if ($update) {
                    $update['updatetime'] = time();
                    Db::name('stock')->where('id', $stock['id'])->update($update);
                    $updated++;
                }
            } catch (\Exception $e) {
                self::log('基本面同步失败 ' . $stock['code'] . '：' . $e->getMessage());
            }
            if ($interval > 0) {
                sleep($interval);
            }
        }
        self::log('基本面同步完成，更新 ' . $updated . ' 个标的');
        return $updated;
    }

    /**
     * 同步指数（首页大盘区）
     */
    public static function syncIndex()
    {
        $indexes = self::conf('indexes');
        if (!$indexes) {
            self::log('未配置指数');
            return 0;
        }
        $updated = 0;
        foreach ($indexes as $index) {
            try {
                $data = self::http(self::conf('twelvedata.endpoint') . '/quote', [
                    'symbol' => $index['symbol'],
                    'apikey' => self::apiKey(),
                ]);
                self::assertTwelve($data);
                self::$credits++;
                $quote = isset($data[0]) ? $data[0] : $data;
                if (!isset($quote['close'])) {
                    continue;
                }
                $close = (float)$quote['close'];
                $prevClose = isset($quote['previous_close']) ? (float)$quote['previous_close'] : $close;
                $row = Db::name('stock')->where('code', $index['code'])->find();
                $update = [
                    'name'         => $index['name'],
                    'market'       => $index['market'],
                    'currency'     => $index['currency'],
                    'type'         => 'index',
                    'price'        => $close,
                    'prev_close'   => $prevClose,
                    'open'         => isset($quote['open']) ? (float)$quote['open'] : $close,
                    'high'         => isset($quote['high']) ? (float)$quote['high'] : $close,
                    'low'          => isset($quote['low']) ? (float)$quote['low'] : $close,
                    'change_amount'=> isset($quote['change']) ? (float)$quote['change'] : round($close - $prevClose, 4),
                    'change_rate'  => isset($quote['percent_change']) ? (float)$quote['percent_change'] : 0,
                    'volume'       => isset($quote['volume']) ? (int)$quote['volume'] : 0,
                    'source'       => 'twelvedata',
                    'symbol'       => $index['symbol'],
                    'updatetime'   => time(),
                ];
                if ($row) {
                    Db::name('stock')->where('id', $row['id'])->update($update);
                } else {
                    $update['code'] = $index['code'];
                    $update['lot_size'] = 1;
                    $update['weigh'] = 100;
                    $update['createtime'] = time();
                    Db::name('stock')->insert($update);
                }
                $updated++;
            } catch (\Exception $e) {
                self::log('指数同步失败 ' . $index['code'] . '：' . $e->getMessage());
            }
            $interval = (int)self::conf('twelvedata.interval');
            if ($interval > 0) {
                sleep($interval);
            }
        }
        self::log('指数同步完成，更新 ' . $updated . ' 个指数');
        return $updated;
    }

    /* ============================ 加密货币 ============================ */

    /**
     * 同步加密货币行情（Twelve Data /quote，代码形如 BTC/USD）
     */
    public static function syncCrypto()
    {
        $map = self::conf('crypto');
        if (!$map) {
            self::log('未配置加密货币');
            return 0;
        }
        $updated = 0;
        foreach ($map as $code => $item) {
            try {
                $data = self::http(self::conf('twelvedata.endpoint') . '/quote', [
                    'symbol' => $item['td'],
                    'apikey' => self::apiKey(),
                ]);
                self::assertTwelve($data);
                self::$credits++;
                $quote = isset($data[0]) ? $data[0] : $data;
                if (isset($quote['close'])) {
                    $quote['symbol'] = $item['td'];
                    $updated += self::saveQuote($quote);
                }
            } catch (\Exception $e) {
                self::log('加密行情同步失败 ' . $code . '：' . $e->getMessage());
            }
            $interval = (int)self::conf('twelvedata.interval');
            if ($interval > 0) {
                sleep($interval);
            }
        }
        self::log('加密行情同步完成，更新 ' . $updated . ' 个币种');
        return $updated;
    }

    /**
     * 备用源：CoinGecko 市值/排名/24h高低（网络可达时可选使用）
     */
    public static function syncCryptoMarket()
    {
        $map = self::conf('crypto');
        if (!$map) {
            self::log('未配置加密货币');
            return 0;
        }
        try {
            $data = self::http(self::conf('coingecko.endpoint') . '/coins/markets', [
                'vs_currency'              => 'usd',
                'ids'                      => implode(',', array_column($map, 'cg')),
                'price_change_percentage'  => '24h',
            ]);
        } catch (\Exception $e) {
            self::log('加密行情同步失败：' . $e->getMessage());
            return 0;
        }
        $updated = 0;
        foreach ($data as $item) {
            $code = strtoupper($item['symbol']);
            $row = Db::name('stock')->where('code', $code)->where('type', 'crypto')->find();
            $price = (float)$item['current_price'];
            $rate = isset($item['price_change_percentage_24h']) ? (float)$item['price_change_percentage_24h'] : 0;
            $prevClose = $rate != -100 ? round($price / (1 + $rate / 100), 4) : $price;
            $update = [
                'name'          => $item['name'] . ' (' . $code . ')',
                'price'         => $price,
                'prev_close'    => $prevClose,
                'high'          => isset($item['high_24h']) ? (float)$item['high_24h'] : $price,
                'low'           => isset($item['low_24h']) ? (float)$item['low_24h'] : $price,
                'change_amount' => round($price - $prevClose, 4),
                'change_rate'   => $rate,
                'volume'        => isset($item['total_volume']) && $price ? (int)($item['total_volume'] / $price) : 0,
                'turnover'      => isset($item['total_volume']) ? (float)$item['total_volume'] : 0,
                'market_cap'    => isset($item['market_cap']) ? (float)$item['market_cap'] : 0,
                'turnover_rate' => isset($item['market_cap']) && $item['market_cap'] ? round($item['total_volume'] / $item['market_cap'] * 100, 4) : 0,
                'amplitude'     => $prevClose ? round(((float)$item['high_24h'] - (float)$item['low_24h']) / $prevClose * 100, 4) : 0,
                'high_52w'      => isset($item['ath']) ? (float)$item['ath'] : 0,
                'source'        => 'coingecko',
                'symbol'        => $item['id'],
                'currency'      => 'USD',
                'updatetime'    => time(),
            ];
            if ($row) {
                Db::name('stock')->where('id', $row['id'])->update($update);
            } else {
                $update['code'] = $code;
                $update['market'] = 'CRYPTO';
                $update['type'] = 'crypto';
                $update['lot_size'] = 1;
                $update['createtime'] = time();
                Db::name('stock')->insert($update);
            }
            $updated++;
        }
        self::log('加密行情同步完成，更新 ' . $updated . ' 个币种');
        return $updated;
    }

    /**
     * 同步加密货币日线（Twelve Data /time_series）
     */
    public static function syncCryptoKline($code)
    {
        $map = self::conf('crypto');
        $code = strtoupper($code);
        if (!isset($map[$code])) {
            self::log('未配置该币种：' . $code);
            return 0;
        }
        $row = Db::name('stock')->where('code', $code)->where('type', 'crypto')->find();
        if (!$row) {
            self::log('标的不存在：' . $code);
            return 0;
        }
        try {
            $data = self::http(self::conf('twelvedata.endpoint') . '/time_series', [
                'symbol'     => $map[$code]['td'],
                'interval'   => '1day',
                'outputsize' => (int)self::conf('kline_limit') ?: 90,
                'apikey'     => self::apiKey(),
            ]);
            self::assertTwelve($data);
            self::$credits++;
        } catch (\Exception $e) {
            self::log('加密K线同步失败 ' . $code . '：' . $e->getMessage());
            return 0;
        }
        if (empty($data['values'])) {
            self::log('加密K线为空：' . $code);
            return 0;
        }
        return self::saveKline($row, $data['values']);
    }

    /* ============================ 组合任务 ============================ */

    /**
     * 全部同步：行情 + 指数 + 加密 + K线
     */
    public static function syncAll($withKline = true)
    {
        self::syncQuote();
        self::syncIndex();
        self::syncCrypto();
        if ($withKline) {
            $codes = Db::name('stock')->where('type', 'crypto')->column('code');
            foreach ($codes as $code) {
                self::syncCryptoKline($code);
            }
            // 股票K线按额度逐个拉取
            $stocks = Db::name('stock')->where('type', 'stock')->where('status', 'normal')->column('code');
            foreach ($stocks as $code) {
                self::syncKline($code);
                $interval = (int)self::conf('twelvedata.interval');
                if ($interval > 0) {
                    sleep($interval);
                }
            }
        }
        return true;
    }
}

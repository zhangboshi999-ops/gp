<?php

namespace app\admin\command;

use app\common\library\QuoteStream;
use think\Config;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\Db;

/**
 * 实时行情 tick 推送
 *
 * 从 iTick 拉取活跃标的（有 WebSocket 客户端订阅的）最新报价，
 * 聚合成 1 分钟 K 线写入 Redis，并 PUBLISH 到 stock:stream 供
 * socket/server.js 转发给浏览器。iTick 免费版限速约 1 次/秒，
 * 因此多标的时按轮询逐个拉取；接口失败时回退为模拟波动（source=demo）。
 *
 *   php think quote:tick                 常驻运行
 *   php think quote:tick --codes=7203    只推送指定标的（调试）
 *   php think quote:tick --once=1        只跑一轮
 *   php think quote:tick --demo=1        不请求外部接口，纯模拟
 */
class QuoteTick extends Command
{
    protected function configure()
    {
        $this->setName('quote:tick')
            ->addOption('interval', null, Option::VALUE_OPTIONAL, '每只标的拉取间隔(秒)', 1)
            ->addOption('once', null, Option::VALUE_OPTIONAL, '只执行一轮(1/0)', 0)
            ->addOption('demo', null, Option::VALUE_OPTIONAL, '强制模拟行情(1/0)', 0)
            ->addOption('codes', null, Option::VALUE_OPTIONAL, '指定标的代码，逗号分隔')
            ->setDescription('实时行情 tick 推送（Redis + WebSocket 数据源）');
    }

    protected function execute(Input $input, Output $output)
    {
        $interval = max((int)$input->getOption('interval'), 1);
        $once = (int)$input->getOption('once') === 1;
        $forceDemo = (int)$input->getOption('demo') === 1;
        $fixCodes = trim((string)$input->getOption('codes'));

        $output->writeln('== 行情 tick 服务启动 ' . date('Y-m-d H:i:s') . ' ==');
        try {
            QuoteStream::redis()->ping();
        } catch (\Exception $e) {
            $output->error($e->getMessage());
            return;
        }
        $output->writeln('Redis 连接正常，轮询间隔 ' . $interval . 's' . ($forceDemo ? '（模拟模式）' : '（iTick 实时）'));

        $stocks = [];
        $stocksAt = 0;
        $persistAt = [];
        $lastPrice = [];

        while (true) {
            $codes = $fixCodes ? explode(',', $fixCodes) : QuoteStream::activeCodes();
            if (!$codes) {
                if ($once) {
                    $output->writeln('暂无订阅标的，退出');
                    break;
                }
                usleep(500000);
                continue;
            }

            // 标的元信息缓存 30 秒
            if (time() - $stocksAt > 30) {
                $stocks = [];
                $rows = Db::name('stock')->whereIn('code', $codes)->select();
                foreach ($rows as $row) {
                    $stocks[$row['code']] = $row;
                }
                $stocksAt = time();
            }

            foreach ($codes as $code) {
                $code = trim($code);
                if ($code === '' || !isset($stocks[$code])) {
                    continue;
                }
                try {
                    $tick = $forceDemo ? null : $this->fetchItick($stocks[$code]);
                    if ($tick) {
                        $source = 'itick';
                        $quote = $this->buildQuote($stocks[$code], $tick, $source);
                    } else {
                        $source = 'demo';
                        $quote = $this->buildQuote($stocks[$code], $this->fakeTick($stocks[$code], $lastPrice[$code] ?? null), $source);
                    }
                    $lastPrice[$code] = (float)$quote['price'];

                    $volumeDelta = 0;
                    if ($source === 'itick' && isset($tick['v'])) {
                        $totalVolume = (int)$tick['v'];
                        $volumeDelta = max($totalVolume - (int)$stocks[$code]['volume'], 0);
                    } else {
                        $volumeDelta = random_int(100, 5000);
                    }

                    $bar = QuoteStream::pushTick($quote, $volumeDelta);
                    $output->writeln(sprintf(
                        '[%s] %s %s %.4f (%+.2f%%) 1m bar H%.4f L%.4f V%d [%s]',
                        date('H:i:s'),
                        $code,
                        $quote['currency'],
                        $quote['price'],
                        $quote['change_rate'],
                        $bar['high'],
                        $bar['low'],
                        $bar['volume'],
                        $source
                    ));

                    // 报价落库节流：同一标的 10 秒最多一次
                    if (time() - ($persistAt[$code] ?? 0) >= 10) {
                        Db::name('stock')->where('id', $stocks[$code]['id'])->update([
                            'price'         => $quote['price'],
                            'high'          => $quote['high'],
                            'low'           => $quote['low'],
                            'volume'        => $quote['volume'],
                            'change_amount' => $quote['change_amount'],
                            'change_rate'   => $quote['change_rate'],
                            'updatetime'    => time(),
                        ]);
                        $persistAt[$code] = time();
                    }
                } catch (\Exception $e) {
                    $output->warning($code . ' tick 失败：' . $e->getMessage());
                }

                if ($once) {
                    break 2;
                }
                usleep($interval * 1000000);
            }
        }

        return;
    }

    /**
     * 请求 iTick 实时报价
     * @return array|null
     */
    protected function fetchItick(array $stock)
    {
        $conf = Config::get('market');
        $regionMap = isset($conf['itick']['region']) ? $conf['itick']['region'] : [];
        $region = isset($regionMap[$stock['market']]) ? $regionMap[$stock['market']] : '';
        if (!$region) {
            return null;
        }
        $endpoint = rtrim($conf['itick']['endpoint'], '/');
        $url = $endpoint . '/stock/quote?' . http_build_query(['region' => $region, 'code' => $stock['code']]);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_HTTPHEADER     => ['token: ' . $conf['itick']['token']],
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
        if (!$body) {
            return null;
        }
        $res = json_decode($body, true);
        if (!is_array($res) || empty($res['data']['ld'])) {
            return null;
        }
        return $res['data'];
    }

    /**
     * 组装统一报价结构
     */
    protected function buildQuote(array $stock, array $data, $source)
    {
        $price = (float)$data['ld'];
        $prev = isset($data['p']) && $data['p'] > 0 ? (float)$data['p'] : (float)$stock['prev_close'];
        $open = isset($data['o']) && $data['o'] > 0 ? (float)$data['o'] : $price;
        $high = isset($data['h']) && $data['h'] > 0 ? (float)$data['h'] : $price;
        $low = isset($data['l']) && $data['l'] > 0 ? (float)$data['l'] : $price;
        $change = isset($data['ch']) ? (float)$data['ch'] : round($price - $prev, 4);
        $rate = isset($data['chp']) ? (float)$data['chp'] : ($prev ? round($change / $prev * 100, 2) : 0);

        return [
            'code'          => $stock['code'],
            'name'          => $stock['name'],
            'market'        => $stock['market'],
            'currency'      => $stock['currency'],
            'price'         => round($price, 4),
            'prev_close'    => round($prev, 4),
            'open'          => round($open, 4),
            'high'          => round($high, 4),
            'low'           => round($low, 4),
            'change_amount' => round($change, 4),
            'change_rate'   => round($rate, 2),
            'volume'        => isset($data['v']) ? (int)$data['v'] : (int)$stock['volume'],
            'turnover'      => isset($data['tu']) ? (float)$data['tu'] : (float)$stock['turnover'],
            'source'        => $source,
            'time'          => time(),
        ];
    }

    /**
     * 模拟一笔 tick（iTick 不可用时兜底，保证图表持续更新）
     */
    protected function fakeTick(array $stock, $lastPrice = null)
    {
        $base = $lastPrice ?: ((float)$stock['price'] ?: (float)$stock['prev_close']);
        if ($base <= 0) {
            $base = 100;
        }
        // ±0.08% 随机游走，涨跌幅限制在 ±5%
        $price = $base * (1 + (random_int(-80, 80) / 100000));
        $prev = (float)$stock['prev_close'] ?: $base;
        if ($price > $prev * 1.05) {
            $price = $prev * 1.05;
        }
        if ($price < $prev * 0.95) {
            $price = $prev * 0.95;
        }
        $high = max((float)$stock['high'], $price);
        $low = (float)$stock['low'] > 0 ? min((float)$stock['low'], $price) : $price;

        return [
            'ld'  => $price,
            'p'   => $prev,
            'o'   => (float)$stock['open'] ?: $prev,
            'h'   => $high,
            'l'   => $low,
            'v'   => (int)$stock['volume'] + random_int(100, 5000),
            'tu'  => (float)$stock['turnover'],
            'ch'  => round($price - $prev, 4),
            'chp' => $prev ? round(($price - $prev) / $prev * 100, 2) : 0,
        ];
    }
}

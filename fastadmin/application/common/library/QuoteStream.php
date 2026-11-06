<?php

namespace app\common\library;

use think\Config;
use think\Db;
use think\Log;

/**
 * 行情实时流（Redis）
 *
 * 数据结构：
 *   stock:quote:{code}  hash  最新报价 JSON
 *   stock:bar:{code}    hash  当前未完成的 1 分钟 K 线 JSON
 *   stock:bars:{code}   list  已完成的 1 分钟 K 线（最新在前，最多 300 根）
 *   stock:active        set   当前有客户端订阅的标的
 *   stock:stream        频道  发布 {"type":"bar","code":"..","quote":{..},"bar":{..}}
 */
class QuoteStream
{
    const CHANNEL = 'stock:stream';
    const ACTIVE_KEY = 'stock:active';
    const QUOTE_PREFIX = 'stock:quote:';
    const BAR_PREFIX = 'stock:bar:';
    const BARS_PREFIX = 'stock:bars:';
    const MAX_BARS = 300;

    /** @var \Redis */
    protected static $redis = null;

    /**
     * Redis 连接（phpredis 扩展）
     * @return \Redis
     */
    public static function redis()
    {
        if (self::$redis instanceof \Redis) {
            return self::$redis;
        }
        if (!extension_loaded('redis')) {
            throw new \RuntimeException('缺少 php redis 扩展');
        }
        $conf = Config::get('redis');
        $redis = new \Redis();
        $ok = @$redis->connect($conf['host'], (int)$conf['port'], (float)$conf['timeout']);
        if (!$ok) {
            throw new \RuntimeException('Redis 连接失败，请先启动 Redis：' . $conf['host'] . ':' . $conf['port']);
        }
        if (!empty($conf['password'])) {
            $redis->auth($conf['password']);
        }
        if (!empty($conf['select'])) {
            $redis->select((int)$conf['select']);
        }
        self::$redis = $redis;
        return self::$redis;
    }

    /**
     * 当前被订阅的标的代码
     * @return array
     */
    public static function activeCodes()
    {
        $codes = self::redis()->sMembers(self::ACTIVE_KEY);
        return $codes ?: [];
    }

    /**
     * 标记/取消标的为活跃（有客户端订阅）
     */
    public static function setActive($code, $active = true)
    {
        $code = (string)$code;
        if ($active) {
            self::redis()->sAdd(self::ACTIVE_KEY, $code);
        } else {
            self::redis()->sRem(self::ACTIVE_KEY, $code);
        }
    }

    /**
     * 读取最新报价
     */
    public static function quote($code)
    {
        $json = self::redis()->get(self::QUOTE_PREFIX . $code);
        return $json ? json_decode($json, true) : null;
    }

    /**
     * 写入最新报价
     */
    public static function saveQuote(array $quote)
    {
        $key = self::QUOTE_PREFIX . $quote['code'];
        self::redis()->set($key, json_encode($quote, JSON_UNESCAPED_UNICODE), 60);
    }

    /**
     * 已完成的历史 1 分钟 K 线（按时间升序）
     */
    public static function historyBars($code, $limit = 240)
    {
        $list = self::redis()->lRange(self::BARS_PREFIX . $code, 0, max($limit - 1, 0));
        $rows = [];
        foreach (($list ?: []) as $json) {
            $row = json_decode($json, true);
            if ($row) {
                $rows[] = $row;
            }
        }
        return array_reverse($rows);
    }

    /**
     * 当前分钟未完成的 K 线
     */
    public static function currentBar($code)
    {
        $json = self::redis()->get(self::BAR_PREFIX . $code);
        return $json ? json_decode($json, true) : null;
    }

    /**
     * 订阅时的全量快照：报价 + 当前分钟 K 线 + 历史 1 分钟 K 线
     */
    public static function snapshot($code, $limit = 240)
    {
        return [
            'type'  => 'snapshot',
            'code'  => $code,
            'quote' => self::quote($code),
            'bar'   => self::currentBar($code),
            'bars'  => self::historyBars($code, $limit),
        ];
    }

    /**
     * 用一次 tick 合并出最新 K 线并发布
     *
     * @param array $quote 报价：code/price/open/high/low/prev_close/volume/turnover/change_amount/change_rate/currency/source/time
     * @param int   $volumeDelta 本次新增成交量
     */
    public static function pushTick(array $quote, $volumeDelta = 0)
    {
        $redis = self::redis();
        $code = $quote['code'];
        $price = (float)$quote['price'];
        $now = (int)$quote['time'] ?: time();
        $minuteStart = $now - $now % 60;

        $bar = self::currentBar($code);

        // 跨分钟：归档旧 K 线，开启新 K 线
        if ($bar && (int)$bar['time'] !== $minuteStart) {
            self::archiveBar($code, $bar);
            $bar = null;
        }

        if (!$bar) {
            $bar = [
                'time'   => $minuteStart,
                'date'   => date('Y-m-d H:i', $minuteStart),
                'open'   => $price,
                'high'   => $price,
                'low'    => $price,
                'close'  => $price,
                'volume' => max($volumeDelta, 0),
            ];
        } else {
            $bar['high'] = max((float)$bar['high'], $price);
            $bar['low'] = (float)$bar['low'] > 0 ? min((float)$bar['low'], $price) : $price;
            $bar['close'] = $price;
            $bar['volume'] = (int)$bar['volume'] + max($volumeDelta, 0);
        }

        $redis->set(self::BAR_PREFIX . $code, json_encode($bar, JSON_UNESCAPED_UNICODE), 3600);
        self::saveQuote($quote);

        $payload = [
            'type'  => 'bar',
            'code'  => $code,
            'quote' => $quote,
            'bar'   => $bar,
        ];
        $redis->publish(self::CHANNEL, json_encode($payload, JSON_UNESCAPED_UNICODE));
        return $bar;
    }

    /**
     * 归档已完成的 K 线：写内存列表（供快照回放）+ 落库
     */
    public static function archiveBar($code, array $bar)
    {
        $redis = self::redis();
        $key = self::BARS_PREFIX . $code;
        $redis->lPush($key, json_encode($bar, JSON_UNESCAPED_UNICODE));
        $redis->lTrim($key, 0, self::MAX_BARS - 1);
        try {
            self::persistBar($code, $bar);
        } catch (\Exception $e) {
            Log::write('K线落库失败：' . $code . ' ' . $e->getMessage(), 'error');
        }
    }

    /**
     * 1 分钟 K 线写入 fa_stock_kline（同一分钟已存在则更新）
     */
    public static function persistBar($code, array $bar)
    {
        $stock = Db::name('stock')->where('code', $code)->find();
        if (!$stock) {
            return;
        }
        $data = [
            'stock_id'   => $stock['id'],
            'code'       => $code,
            'period'     => '1m',
            'time'       => (int)$bar['time'],
            'open'       => $bar['open'],
            'high'       => $bar['high'],
            'low'        => $bar['low'],
            'close'      => $bar['close'],
            'volume'     => (int)$bar['volume'],
            'date'       => date('Y-m-d', (int)$bar['time']),
            'createtime' => time(),
        ];
        $exists = Db::name('stock_kline')
            ->where('code', $code)
            ->where('period', '1m')
            ->where('time', (int)$bar['time'])
            ->find();
        if ($exists) {
            Db::name('stock_kline')->where('id', $exists['id'])->update([
                'high'   => $data['high'],
                'low'    => $data['low'],
                'close'  => $data['close'],
                'volume' => $data['volume'],
            ]);
        } else {
            Db::name('stock_kline')->insert($data);
        }
    }
}

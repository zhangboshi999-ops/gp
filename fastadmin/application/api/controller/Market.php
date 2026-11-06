<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Stock;
use app\common\model\StockFavorite;
use app\common\model\StockKline;

/**
 * 行情接口
 */
class Market extends Api
{
    protected $noNeedLogin = ['lists', 'detail', 'kline', 'hot', 'groups', 'indexes'];
    protected $noNeedRight = '*';

    /**
     * 行情列表
     *
     * @ApiMethod (GET)
     * @ApiParams (name="market", type="string", required=false, description="市场:TSE/NASDAQ/CRYPTO")
     * @ApiParams (name="type", type="string", required=false, description="类型:stock/crypto/index")
     * @ApiParams (name="keyword", type="string", required=false, description="搜索关键字(代码或名称)")
     * @ApiParams (name="sort", type="string", required=false, description="排序:gainers=涨幅榜,losers=跌幅榜,turnover=成交额榜")
     * @ApiParams (name="page", type="integer", required=false, description="页码")
     * @ApiParams (name="limit", type="integer", required=false, description="每页数量")
     */
    public function lists()
    {
        $market = $this->request->get('market');
        $type = $this->request->get('type');
        $keyword = $this->request->get('keyword');
        $sort = $this->request->get('sort');
        $page = max((int)$this->request->get('page'), 1);
        $limit = min(max((int)$this->request->get('limit'), 1), 100);

        // 注意：Query 对象执行 count() 后条件会被重置，因此统计与查询分开构造
        $map = ['status' => 'normal'];
        if ($market) {
            // US = 纳斯达克 + 纽交所（美股分两个交易所，页面上的「美股」要都包含）
            if (strtoupper($market) === 'US') {
                $map['market'] = ['in', ['NASDAQ', 'NYSE']];
            } else {
                $map['market'] = $market;
            }
        }
        if ($type) {
            $map['type'] = $type;
        }
        if ($sort == 'gainers') {
            $order = ['change_rate' => 'desc'];
        } elseif ($sort == 'losers') {
            $order = ['change_rate' => 'asc'];
        } elseif ($sort == 'turnover') {
            $order = ['turnover' => 'desc'];
        } else {
            $order = ['weigh' => 'desc', 'id' => 'asc'];
        }

        $countQuery = Stock::where($map);
        $listQuery = Stock::where($map);
        if ($keyword) {
            $countQuery->where('code|name', 'like', "%{$keyword}%");
            $listQuery->where('code|name', 'like', "%{$keyword}%");
        }

        $total = $countQuery->count();
        $list = $listQuery->order($order)->page($page, $limit)->select();
        $rows = [];
        foreach ($list as $item) {
            $rows[] = $this->format($item);
        }
        $this->success('', ['total' => $total, 'page' => $page, 'limit' => $limit, 'list' => $rows]);
    }

    /**
     * 热门标的
     */
    public function hot()
    {
        $list = Stock::where('status', 'normal')->where('is_hot', 1)->order('weigh', 'desc')->limit(10)->select();
        $rows = [];
        foreach ($list as $item) {
            $rows[] = $this->format($item);
        }
        $this->success('', $rows);
    }

    /**
     * 市场分组(前端 Tab)
     */
    public function groups()
    {
        // 按库里实际存在的市场动态生成，避免新增交易所后页面漏掉（NYSE 曾被漏掉过）
        $exists = Stock::where('status', 'normal')
            ->where('type', 'stock')
            ->column('market');
        $markets = array_values(array_unique(array_filter($exists)));
        sort($markets);
        $usMarkets = array_values(array_intersect($markets, ['NASDAQ', 'NYSE']));

        $groups = [['key' => 'all', 'name' => 'すべて']];
        if (in_array('TSE', $markets)) {
            $groups[] = ['key' => 'TSE', 'name' => '東証'];
        }
        if ($usMarkets) {
            // 美股合并成一个 tab，key=US 由 lists() 展开成 NASDAQ+NYSE
            $groups[] = ['key' => 'US', 'name' => '米国株'];
        }
        // 加密币单独查（type=crypto，不在上面的股票市场列表里）
        $hasCrypto = Stock::where('status', 'normal')->where('market', 'CRYPTO')->count() > 0;
        if ($hasCrypto) {
            $groups[] = ['key' => 'CRYPTO', 'name' => '暗号資産'];
        }
        $this->success('', $groups);
    }

    /**
     * 大盘指数(首页指数区)
     */
    public function indexes()
    {
        $list = Stock::where('status', 'normal')->where('type', 'index')->order('weigh', 'desc')->select();
        $rows = [];
        foreach ($list as $item) {
            $rows[] = $this->format($item);
        }
        $this->success('', $rows);
    }

    /**
     * 标的详情
     *
     * @ApiMethod (GET)
     * @ApiParams (name="code", type="string", required=true, description="证券代码")
     */
    public function detail()
    {
        $code = $this->request->get('code');
        $stock = Stock::get(['code' => $code, 'status' => 'normal']);
        if (!$stock) {
            $this->error('标的不存在');
        }
        $data = $this->format($stock);
        $data['holding'] = 0;
        if ($this->auth->isLogin()) {
            $data['favorite'] = StockFavorite::where([
                'user_id'  => $this->auth->id,
                'stock_id' => $stock['id'],
            ])->count() > 0 ? 1 : 0;
            $position = \app\common\model\Position::get(['user_id' => $this->auth->id, 'stock_id' => $stock['id']]);
            $data['holding'] = $position ? $position['quantity'] : 0;
        } else {
            $data['favorite'] = 0;
        }
        $this->success('', $data);
    }

    /**
     * K线数据
     *
     * @ApiMethod (GET)
     * @ApiParams (name="code", type="string", required=true, description="证券代码")
     * @ApiParams (name="period", type="string", required=false, description="周期:1d/1h/15m")
     */
    public function kline()
    {
        $code = $this->request->get('code');
        $period = $this->request->get('period', '1d');
        $limit = min(max((int)$this->request->get('limit'), 10), 500);
        // 取最近 N 根：先按时间倒序截取，再翻转回升序供图表从左到右绘制
        $list = StockKline::where('code', $code)
            ->where('period', $period)
            ->order('time', 'desc')
            ->limit($limit)
            ->select();
        $rows = [];
        foreach ($list as $item) {
            $rows[] = [
                'time'   => (int)$item['time'],
                'date'   => date('Y-m-d', $item['time']),
                'open'   => (float)$item['open'],
                'high'   => (float)$item['high'],
                'low'    => (float)$item['low'],
                'close'  => (float)$item['close'],
                'volume' => (int)$item['volume'],
            ];
        }
        $rows = array_reverse($rows);
        $this->success('', $rows);
    }

    /**
     * 格式化行情数据
     */
    protected function format($stock)
    {
        $favorite = 0;
        if ($this->auth->isLogin()) {
            $favorite = StockFavorite::where([
                'user_id'  => $this->auth->id,
                'stock_id' => $stock['id'],
            ])->count() > 0 ? 1 : 0;
        }
        return [
            'id'            => (int)$stock['id'],
            'code'          => $stock['code'],
            'name'          => $stock['name'],
            'market'        => $stock['market'],
            'type'          => $stock['type'],
            'currency'      => $stock['currency'],
            'price'         => (float)$stock['price'],
            'prev_close'    => (float)$stock['prev_close'],
            'open'          => (float)$stock['open'],
            'high'          => (float)$stock['high'],
            'low'           => (float)$stock['low'],
            'change_amount' => (float)$stock['change_amount'],
            'change_rate'   => (float)$stock['change_rate'],
            'volume'        => (int)$stock['volume'],
            'turnover'      => (float)$stock['turnover'],
            'lot_size'      => (int)$stock['lot_size'],
            'is_hot'        => (int)$stock['is_hot'],
            'favorite'      => $favorite,
            // 基本面与统计指标（来自 Twelve Data / CoinGecko）
            'sector'         => $stock['sector'] ?: '',
            'source'         => $stock['source'] ?: '',
            'symbol'         => $stock['symbol'] ?: '',
            'market_cap'     => (float)$stock['market_cap'],
            'pe_ratio'       => (float)$stock['pe_ratio'],
            'pb_ratio'       => (float)$stock['pb_ratio'],
            'dividend_yield' => (float)$stock['dividend_yield'],
            'turnover_rate'  => (float)$stock['turnover_rate'],
            'amplitude'      => (float)$stock['amplitude'],
            'high_52w'       => (float)$stock['high_52w'],
            'low_52w'        => (float)$stock['low_52w'],
        ];
    }
}

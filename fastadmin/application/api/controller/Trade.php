<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Trade as TradeService;
use app\common\model\Order;
use app\common\model\Position;
use app\common\model\Stock;

/**
 * 交易接口
 */
class Trade extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = '*';

    /**
     * 下单
     *
     * @ApiMethod (POST)
     * @ApiParams (name="code", type="string", required=true, description="证券代码")
     * @ApiParams (name="side", type="string", required=true, description="方向:buy/sell")
     * @ApiParams (name="type", type="string", required=true, description="类型:market/limit")
     * @ApiParams (name="price", type="string", required=false, description="委托价(限价必填)")
     * @ApiParams (name="quantity", type="integer", required=true, description="数量")
     * @ApiParams (name="pay_password", type="string", required=true, description="资金密码")
     */
    public function create()
    {
        $code = $this->request->post('code');
        $side = $this->request->post('side', 'buy');
        $type = $this->request->post('type', 'limit');
        $price = (float)$this->request->post('price', 0);
        $quantity = (int)$this->request->post('quantity', 0);
        $payPassword = $this->request->post('pay_password');

        if (!in_array($side, ['buy', 'sell'])) {
            $this->error('交易方向不正确');
        }
        if (!in_array($type, ['market', 'limit'])) {
            $this->error('委托类型不正确');
        }
        try {
            \app\common\library\Fund::checkPayPassword($this->auth->id, $payPassword);
            $stock = Stock::get(['code' => $code]);
            if (!$stock) {
                $this->error('标的不存在');
            }
            $order = TradeService::createOrder($this->auth->id, $stock, $side, $type, $price, $quantity);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success($order['status'] == 'filled' ? '委托已成交' : '委托已提交', [
            'order_no' => $order['order_no'],
            'status'   => $order['status'],
        ]);
    }

    /**
     * 撤单
     *
     * @ApiMethod (POST)
     * @ApiParams (name="id", type="integer", required=true, description="委托ID")
     */
    public function cancel()
    {
        $id = (int)$this->request->post('id');
        try {
            TradeService::cancel($this->auth->id, $id);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('委托已撤销');
    }

    /**
     * 委托列表
     *
     * @ApiMethod (GET)
     * @ApiParams (name="status", type="string", required=false, description="状态:pending/filled/canceled")
     */
    public function orders()
    {
        $status = $this->request->get('status');
        $page = max((int)$this->request->get('page'), 1);
        $limit = min(max((int)$this->request->get('limit'), 1), 100);
        $query = Order::where('user_id', $this->auth->id);
        if ($status) {
            $query->where('status', $status);
        }
        $total = $query->count();
        $list = $query->order('id', 'desc')->page($page, $limit)->select();
        $rows = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['price'] = (float)$row['price'];
            $row['amount'] = (float)$row['amount'];
            $row['fee'] = (float)$row['fee'];
            $rows[] = $row;
        }
        $this->success('', ['total' => $total, 'list' => $rows]);
    }

    /**
     * 持仓列表
     */
    public function positions()
    {
        $this->success('', TradeService::positions($this->auth->id));
    }

    /**
     * 卖出持仓(快捷卖单)
     *
     * @ApiMethod (POST)
     * @ApiParams (name="stock_id", type="integer", required=true, description="证券ID")
     * @ApiParams (name="quantity", type="integer", required=true, description="数量")
     * @ApiParams (name="pay_password", type="string", required=true, description="资金密码")
     */
    public function sell()
    {
        $stockId = (int)$this->request->post('stock_id');
        $quantity = (int)$this->request->post('quantity');
        $payPassword = $this->request->post('pay_password');
        try {
            \app\common\library\Fund::checkPayPassword($this->auth->id, $payPassword);
            $stock = Stock::get($stockId);
            if (!$stock) {
                $this->error('标的不存在');
            }
            $position = Position::get(['user_id' => $this->auth->id, 'stock_id' => $stockId]);
            if (!$position || $position['available'] < $quantity) {
                $this->error('可卖持仓不足');
            }
            $order = TradeService::createOrder($this->auth->id, $stock, 'sell', 'market', 0, $quantity);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('卖出已成交', ['order_no' => $order['order_no']]);
    }
}

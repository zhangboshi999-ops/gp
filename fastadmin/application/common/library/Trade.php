<?php

namespace app\common\library;

use app\common\model\Account;
use app\common\model\Order;
use app\common\model\Position;
use app\common\model\Stock;
use think\Db;

/**
 * 交易服务：委托下单、撮合成交、撤单、持仓与账户资金变动
 */
class Trade
{
    /**
     * 手续费率 0.05%
     */
    const FEE_RATE = 0.0005;

    /**
     * 生成委托单号
     */
    public static function orderNo()
    {
        return 'T' . date('YmdHis') . str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
    }

    /**
     * 获取会员资金账户(不存在则创建)
     *
     * @param int $userId
     * @param string $currency
     * @return Account
     */
    public static function account($userId, $currency = 'JPY')
    {
        $account = Account::get(['user_id' => $userId, 'currency' => $currency]);
        if (!$account) {
            $account = Account::create([
                'user_id'    => $userId,
                'currency'   => $currency,
                'balance'    => 0,
                'frozen'     => 0,
                'updatetime' => time(),
            ]);
        }
        return $account;
    }

    /**
     * 计算手续费
     */
    public static function fee($amount)
    {
        return round($amount * self::FEE_RATE, 2);
    }

    /**
     * 下单
     *
     * @param int $userId 会员ID
     * @param Stock $stock 标的
     * @param string $side buy=买入,sell=卖出
     * @param string $type market=市价,limit=限价
     * @param float $price 委托价(市价单传0)
     * @param int $quantity 数量
     * @return Order
     * @throws \Exception
     */
    public static function createOrder($userId, $stock, $side, $type, $price, $quantity)
    {
        $quantity = (int)$quantity;
        if ($quantity <= 0) {
            throw new \Exception('委托数量必须大于0');
        }
        if ($stock['status'] != 'normal') {
            throw new \Exception('该标的当前不可交易');
        }
        $marketPrice = (float)$stock['price'];
        // 市价单直接以最新价成交；限价单判断是否可立即成交
        if ($type == 'market') {
            $dealPrice = $marketPrice;
            $canDeal = true;
        } else {
            if ($price <= 0) {
                throw new \Exception('请输入委托价格');
            }
            $dealPrice = $price;
            // 买单价 >= 市价 或 卖单价 <= 市价 时立即成交
            $canDeal = $side == 'buy' ? $price >= $marketPrice : $price <= $marketPrice;
        }

        $amount = round($dealPrice * $quantity, 2);
        $fee = self::fee($amount);

        $order = new Order([
            'order_no'        => self::orderNo(),
            'user_id'         => $userId,
            'stock_id'        => $stock['id'],
            'code'            => $stock['code'],
            'name'            => $stock['name'],
            'currency'        => $stock['currency'],
            'side'            => $side,
            'type'            => $type,
            'price'           => $dealPrice,
            'quantity'        => $quantity,
            'amount'          => $amount,
            'fee'             => $fee,
            'status'          => 'pending',
            'filled_quantity' => 0,
            'filled_amount'   => 0,
        ]);

        Db::startTrans();
        try {
            // 预冻结资金或持仓
            if ($side == 'buy') {
                $account = self::account($userId, $stock['currency']);
                $need = $amount + $fee;
                if ($account['balance'] < $need) {
                    throw new \Exception('可用余额不足');
                }
                $account->balance = $account['balance'] - $need;
                $account->frozen = $account['frozen'] + $need;
                $account->save();
            } else {
                $position = Position::get(['user_id' => $userId, 'stock_id' => $stock['id']]);
                if (!$position || $position['available'] < $quantity) {
                    throw new \Exception('可卖持仓不足');
                }
                $position->available = $position['available'] - $quantity;
                $position->save();
            }
            $order->save();
            if ($canDeal) {
                self::fill($order, $dealPrice);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
        return $order;
    }

    /**
     * 成交处理：写入持仓、结算资金
     *
     * @param Order $order
     * @param float $price 成交价
     */
    public static function fill($order, $price)
    {
        $quantity = $order['quantity'] - $order['filled_quantity'];
        if ($quantity <= 0) {
            return;
        }
        $amount = round($price * $quantity, 2);
        $fee = self::fee($amount);
        $currency = $order['currency'];
        $account = self::account($order['user_id'], $currency);

        if ($order['side'] == 'buy') {
            // 解冻资金 -> 扣除实际金额与手续费，多冻结部分退回
            $frozenTotal = $order['amount'] + $order['fee'];
            $used = $amount + $fee;
            $account->frozen = max($account['frozen'] - $frozenTotal, 0);
            $account->balance = $account['balance'] + ($frozenTotal - $used);

            // 更新持仓(加权成本)
            $position = Position::get(['user_id' => $order['user_id'], 'stock_id' => $order['stock_id']]);
            if (!$position) {
                $position = new Position([
                    'user_id'    => $order['user_id'],
                    'stock_id'   => $order['stock_id'],
                    'code'       => $order['code'],
                    'name'       => $order['name'],
                    'currency'   => $currency,
                    'quantity'   => 0,
                    'available'  => 0,
                    'cost_price' => 0,
                ]);
            }
            $newQty = $position['quantity'] + $quantity;
            $costTotal = $position['quantity'] * $position['cost_price'] + $amount;
            $position->quantity = $newQty;
            $position->available = $position['available'] + $quantity;
            $position->cost_price = $newQty > 0 ? round($costTotal / $newQty, 4) : 0;
            $position->save();
        } else {
            // 卖出：减少持仓，增加可用资金
            $position = Position::get(['user_id' => $order['user_id'], 'stock_id' => $order['stock_id']]);
            if ($position) {
                $position->quantity = max($position['quantity'] - $quantity, 0);
                $position->available = max($position['available'] - $quantity, 0);
                if ($position['quantity'] <= 0) {
                    $position->cost_price = 0;
                }
                $position->save();
            }
            $account->balance = $account['balance'] + $amount - $fee;
        }

        $account->updatetime = time();
        $account->save();

        $order->filled_quantity = $order['quantity'];
        $order->filled_amount = $amount;
        $order->fee = $fee;
        $order->status = 'filled';
        $order->finishtime = time();
        $order->save();
    }

    /**
     * 撤单：解冻资金或持仓
     *
     * @param int $userId
     * @param int $orderId
     * @return bool
     * @throws \Exception
     */
    public static function cancel($userId, $orderId)
    {
        $order = Order::get(['id' => $orderId, 'user_id' => $userId]);
        if (!$order) {
            throw new \Exception('委托单不存在');
        }
        if (!in_array($order['status'], ['pending', 'partial'])) {
            throw new \Exception('该委托单当前状态不可撤销');
        }
        Db::startTrans();
        try {
            $remain = $order['quantity'] - $order['filled_quantity'];
            if ($order['side'] == 'buy') {
                $account = self::account($userId, $order['currency']);
                $back = round($order['price'] * $remain, 2) + self::fee($order['price'] * $remain);
                $account->frozen = max($account['frozen'] - $back, 0);
                $account->balance = $account['balance'] + $back;
                $account->save();
            } else {
                $position = Position::get(['user_id' => $userId, 'stock_id' => $order['stock_id']]);
                if ($position) {
                    $position->available = $position['available'] + $remain;
                    $position->save();
                }
            }
            $order->status = 'canceled';
            $order->finishtime = time();
            $order->save();
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
        return true;
    }

    /**
     * 持仓列表(含现价与浮动盈亏)
     */
    public static function positions($userId)
    {
        $list = Position::where('user_id', $userId)->where('quantity', '>', 0)->select();
        $stocks = Stock::column('price,currency,name', 'id');
        $result = [];
        foreach ($list as $row) {
            $row = $row->toArray();
            $price = isset($stocks[$row['stock_id']]) ? (float)$stocks[$row['stock_id']]['price'] : (float)$row['cost_price'];
            $row['current_price'] = $price;
            $row['market_value'] = round($price * $row['quantity'], 2);
            $row['cost_amount'] = round($row['cost_price'] * $row['quantity'], 2);
            $row['profit'] = round($row['market_value'] - $row['cost_amount'], 2);
            $row['profit_rate'] = $row['cost_amount'] > 0 ? round($row['profit'] / $row['cost_amount'] * 100, 2) : 0;
            $result[] = $row;
        }
        return $result;
    }
}

<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Follow as FollowModel;
use app\common\model\Stock;
use app\common\model\StockFavorite;
use app\common\model\Trader;

/**
 * 跟单与自选接口
 */
class Follow extends Api
{
    protected $noNeedLogin = ['traders'];
    protected $noNeedRight = '*';

    /**
     * 交易员列表
     */
    public function traders()
    {
        $list = Trader::where('status', 'normal')->order('weigh', 'desc')->select();
        $rows = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['roi_rate'] = (float)$row['roi_rate'];
            $row['win_rate'] = (float)$row['win_rate'];
            $row['total_profit'] = (float)$row['total_profit'];
            $row['min_amount'] = (float)$row['min_amount'];
            $rows[] = $row;
        }
        $this->success('', $rows);
    }

    /**
     * 跟单
     *
     * @ApiMethod (POST)
     * @ApiParams (name="trader_id", type="integer", required=true, description="交易员ID")
     * @ApiParams (name="amount", type="string", required=true, description="跟单金额")
     */
    public function follow()
    {
        $traderId = (int)$this->request->post('trader_id');
        $amount = (float)$this->request->post('amount');
        $trader = Trader::get($traderId);
        if (!$trader) {
            $this->error('交易员不存在');
        }
        if ($amount < $trader['min_amount']) {
            $this->error('跟单金额不得低于最低金额');
        }
        $exists = FollowModel::get(['user_id' => $this->auth->id, 'trader_id' => $traderId]);
        if ($exists) {
            $exists->status = 'following';
            $exists->amount = $amount;
            $exists->save();
        } else {
            FollowModel::create([
                'user_id'    => $this->auth->id,
                'trader_id'  => $traderId,
                'amount'     => $amount,
                'profit'     => 0,
                'status'     => 'following',
                'createtime' => time(),
                'updatetime' => time(),
            ]);
        }
        $trader->followers = $trader['followers'] + 1;
        $trader->save();
        $this->success('跟单成功');
    }

    /**
     * 取消跟单
     *
     * @ApiMethod (POST)
     * @ApiParams (name="trader_id", type="integer", required=true, description="交易员ID")
     */
    public function unfollow()
    {
        $traderId = (int)$this->request->post('trader_id');
        $exists = FollowModel::get(['user_id' => $this->auth->id, 'trader_id' => $traderId]);
        if (!$exists) {
            $this->error('尚未跟单该交易员');
        }
        $exists->status = 'stopped';
        $exists->save();
        $this->success('已取消跟单');
    }

    /**
     * 我的跟单
     */
    public function myfollows()
    {
        $list = FollowModel::where('user_id', $this->auth->id)->select();
        $traders = Trader::column('name,avatar,roi_rate,win_rate', 'id');
        $rows = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['trader_name'] = isset($traders[$row['trader_id']]) ? $traders[$row['trader_id']]['name'] : '';
            $row['trader_avatar'] = isset($traders[$row['trader_id']]) ? $traders[$row['trader_id']]['avatar'] : '';
            $row['trader_roi'] = isset($traders[$row['trader_id']]) ? (float)$traders[$row['trader_id']]['roi_rate'] : 0;
            $row['amount'] = (float)$row['amount'];
            $row['profit'] = (float)$row['profit'];
            $rows[] = $row;
        }
        $this->success('', $rows);
    }

    /**
     * 自选列表
     */
    public function favorites()
    {
        $ids = StockFavorite::where('user_id', $this->auth->id)->column('stock_id');
        if (!$ids) {
            $this->success('', []);
        }
        $list = Stock::where('id', 'in', $ids)->select();
        $rows = [];
        foreach ($list as $item) {
            $rows[] = [
                'id'            => (int)$item['id'],
                'code'          => $item['code'],
                'name'          => $item['name'],
                'market'        => $item['market'],
                'currency'      => $item['currency'],
                'price'         => (float)$item['price'],
                'change_amount' => (float)$item['change_amount'],
                'change_rate'   => (float)$item['change_rate'],
            ];
        }
        $this->success('', $rows);
    }

    /**
     * 添加/取消自选
     *
     * @ApiMethod (POST)
     * @ApiParams (name="code", type="string", required=true, description="证券代码")
     */
    public function toggle()
    {
        $code = $this->request->post('code');
        $stock = Stock::get(['code' => $code]);
        if (!$stock) {
            $this->error('标的不存在');
        }
        $exists = StockFavorite::get(['user_id' => $this->auth->id, 'stock_id' => $stock['id']]);
        if ($exists) {
            $exists->delete();
            $this->success('已取消自选', ['favorite' => 0]);
        }
        StockFavorite::create([
            'user_id'    => $this->auth->id,
            'stock_id'   => $stock['id'],
            'createtime' => time(),
        ]);
        $this->success('已加入自选', ['favorite' => 1]);
    }
}

<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Ipo as IpoModel;
use app\common\model\IpoApply;

/**
 * 新股认购接口
 */
class Ipo extends Api
{
    protected $noNeedLogin = ['lists', 'detail'];
    protected $noNeedRight = '*';

    /**
     * 新股列表
     */
    public function lists()
    {
        $list = IpoModel::order('weigh', 'desc')->order('id', 'desc')->select();
        $rows = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['issue_price'] = (float)$row['issue_price'];
            $rows[] = $row;
        }
        $this->success('', $rows);
    }

    /**
     * 新股详情
     *
     * @ApiParams (name="id", type="integer", required=true, description="新股ID")
     */
    public function detail()
    {
        $id = (int)$this->request->param('id');
        $ipo = IpoModel::get($id);
        if (!$ipo) {
            $this->error('新股不存在');
        }
        $data = $ipo->toArray();
        $data['issue_price'] = (float)$data['issue_price'];
        $this->success('', $data);
    }

    /**
     * 认购新股
     *
     * @ApiMethod (POST)
     * @ApiParams (name="ipo_id", type="integer", required=true, description="新股ID")
     * @ApiParams (name="quantity", type="integer", required=true, description="认购数量")
     * @ApiParams (name="pay_password", type="string", required=true, description="资金密码")
     */
    public function apply()
    {
        $ipoId = (int)$this->request->post('ipo_id');
        $quantity = (int)$this->request->post('quantity');
        $payPassword = $this->request->post('pay_password');
        $ipo = IpoModel::get($ipoId);
        if (!$ipo) {
            $this->error('新股不存在');
        }
        if ($ipo['status'] != 'ongoing') {
            $this->error('该新股当前不在认购期');
        }
        if ($quantity < $ipo['min_quantity']) {
            $this->error('认购数量不得低于最小认购数量');
        }
        if ($ipo['max_quantity'] > 0 && $quantity > $ipo['max_quantity']) {
            $this->error('认购数量超出上限');
        }
        $amount = round($ipo['issue_price'] * $quantity, 2);
        try {
            // 冻结认购资金
            $account = \app\common\library\Fund::account($this->auth->id, 'JPY');
            \app\common\library\Fund::checkPayPassword($this->auth->id, $payPassword);
            if ($account['balance'] < $amount) {
                $this->error('可用余额不足');
            }
            $account->balance = $account['balance'] - $amount;
            $account->frozen = $account['frozen'] + $amount;
            $account->save();
            IpoApply::create([
                'apply_no'   => 'I' . date('YmdHis') . str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT),
                'user_id'    => $this->auth->id,
                'ipo_id'     => $ipoId,
                'code'       => $ipo['code'],
                'name'       => $ipo['name'],
                'quantity'   => $quantity,
                'price'      => $ipo['issue_price'],
                'amount'     => $amount,
                'status'     => 'pending',
                'createtime' => time(),
                'updatetime' => time(),
            ]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('认购申请已提交');
    }

    /**
     * 我的认购记录
     */
    public function myapplies()
    {
        $list = IpoApply::where('user_id', $this->auth->id)->order('id', 'desc')->select();
        $rows = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['price'] = (float)$row['price'];
            $row['amount'] = (float)$row['amount'];
            $rows[] = $row;
        }
        $this->success('', $rows);
    }
}

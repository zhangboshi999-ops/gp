<?php

namespace app\admin\model;

use think\Model;

/**
 * 资金流水
 */
class Transaction extends Model
{
    // 表名
    protected $name = 'transaction';

    public function getTypeList()
    {
        return ['deposit' => '充值', 'withdraw' => '提现', 'transfer' => '划转'];
    }

    public function getStatusList()
    {
        return ['pending' => '待审核', 'success' => '已通过', 'rejected' => '已驳回'];
    }
}

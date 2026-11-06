<?php

namespace app\admin\model\loan;

/**
 * 还款记录(后台)
 *
 * 直接复用公共模型的字段/状态/文本获取器,这里只补充后台需要的关联与写入处理
 */
class Repayment extends \app\common\model\LoanRepayment
{
    public function user()
    {
        return $this->belongsTo('app\common\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(1);
    }

    /**
     * 还款日期支持日期字符串
     */
    public function setDueTimeAttr($value, $data)
    {
        if (is_numeric($value)) {
            return $value;
        }
        return strtotime((string)$value) ?: 0;
    }
}

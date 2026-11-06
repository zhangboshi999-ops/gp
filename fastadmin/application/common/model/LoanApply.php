<?php

namespace app\common\model;

use think\Model;

/**
 * 资金申请
 */
class LoanApply extends Model
{

    // 状态:申请中
    const STATUS_PENDING = '0';
    // 状态:申请已通过
    const STATUS_PASSED = '1';
    // 状态:已拒绝
    const STATUS_REJECTED = '2';
    // 状态:提款处理中
    const STATUS_WITHDRAWING = '3';
    // 状态:提款完成
    const STATUS_DONE = '4';

    // 表名
    protected $name = 'loan_apply';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text',
        'status_class',
        'amount_text',
        'withdrawable_amount',
        'withdrawable_amount_text',
        'withdraw_amount_text',
        'payment_text',
        'interest_text',
        'total_text',
        'createtime_text',
    ];

    /**
     * 进行中的状态(存在这些状态时不允许再次申请)
     */
    public static function getPendingStatusList()
    {
        return [self::STATUS_PENDING, self::STATUS_PASSED, self::STATUS_WITHDRAWING];
    }

    /**
     * 状态列表
     */
    public function getStatusList()
    {
        return [
            self::STATUS_PENDING     => 'Ariza ko\'rib chiqilmoqda',
            self::STATUS_PASSED      => 'Ariza tasdiqlandi',
            self::STATUS_REJECTED    => 'Rad etildi',
            self::STATUS_WITHDRAWING => 'Yechib olish jarayonida',
            self::STATUS_DONE        => 'Yechib olish yakunlandi',
        ];
    }

    /**
     * 状态对应的样式
     */
    public function getStatusClassList()
    {
        return [
            self::STATUS_PENDING     => 'pending',
            self::STATUS_PASSED      => 'passed',
            self::STATUS_REJECTED    => 'rejected',
            self::STATUS_WITHDRAWING => 'withdrawing',
            self::STATUS_DONE        => 'done',
        ];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getStatusClassAttr($value, $data)
    {
        $value = $value ?: (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusClassList();
        return isset($list[$value]) ? $list[$value] : 'pending';
    }

    /**
     * 申请金额(日元格式)
     */
    public function getAmountTextAttr($value, $data)
    {
        return self::formatYen(isset($data['amount']) ? $data['amount'] : 0);
    }

    /**
     * 已提款金额(日元格式)
     */
    public function getWithdrawAmountTextAttr($value, $data)
    {
        return self::formatYen(isset($data['withdraw_amount']) ? $data['withdraw_amount'] : 0);
    }

    /**
     * 可提款金额
     */
    public function getWithdrawableAmountAttr($value, $data)
    {
        $amount = isset($data['amount']) ? (float)$data['amount'] : 0;
        $withdraw = isset($data['withdraw_amount']) ? (float)$data['withdraw_amount'] : 0;
        //仅"申请已通过"状态才可提款
        if (!isset($data['status']) || $data['status'] != self::STATUS_PASSED) {
            return 0;
        }
        return max(0, $amount - $withdraw);
    }

    public function getWithdrawableAmountTextAttr($value, $data)
    {
        $amount = isset($data['amount']) ? (float)$data['amount'] : 0;
        $withdraw = isset($data['withdraw_amount']) ? (float)$data['withdraw_amount'] : 0;
        $withdrawable = (isset($data['status']) && $data['status'] == self::STATUS_PASSED) ? max(0, $amount - $withdraw) : 0;
        return self::formatYen($withdrawable);
    }

    public function getPaymentTextAttr($value, $data)
    {
        return '¥' . number_format((float)(isset($data['payment']) ? $data['payment'] : 0), 2);
    }

    public function getInterestTextAttr($value, $data)
    {
        return '¥' . number_format((float)(isset($data['interest']) ? $data['interest'] : 0), 2);
    }

    public function getTotalTextAttr($value, $data)
    {
        return '¥' . number_format((float)(isset($data['total']) ? $data['total'] : 0), 2);
    }

    public function getCreatetimeTextAttr($value, $data)
    {
        $value = isset($data['createtime']) ? $data['createtime'] : '';
        return $value ? date('Y-m-d H:i', $value) : '';
    }

    /**
     * 计算还款计划(等额本息)
     *
     * @param float $amount 金额
     * @param int   $term   期数
     * @param float $rate   年利率(%)
     * @return array
     */
    public static function buildPlan($amount, $term, $rate)
    {
        $amount = (float)$amount;
        $term = max(1, (int)$term);
        $rate = (float)$rate;
        $monthRate = $rate / 100 / 12;
        if ($monthRate > 0) {
            $pow = pow(1 + $monthRate, $term);
            $payment = $amount * $monthRate * $pow / ($pow - 1);
        } else {
            $payment = $amount / $term;
        }
        //每期付款四舍五入取整，保证"共计 = 每期付款 × 期数"
        $payment = round($payment);
        $total = $payment * $term;
        $interest = $total - $amount;
        return [
            'amount'   => $amount,
            'term'     => $term,
            'rate'     => $rate,
            'payment'  => $payment,
            'interest' => $interest,
            'total'    => $total,
        ];
    }

    /**
     * 金额格式化为"¥xx.xx"(日元)
     */
    public static function formatYen($number)
    {
        return '¥' . number_format((float)$number, 2);
    }
}

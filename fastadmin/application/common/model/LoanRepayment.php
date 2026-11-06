<?php

namespace app\common\model;

use think\Model;

/**
 * 还款记录(每一期的还款单)
 */
class LoanRepayment extends Model
{
    // 状态:待还款
    const STATUS_UNPAID = '0';
    // 状态:客户已上传凭证待后台确认
    const STATUS_PAID = '1';
    // 状态:后台已确认收款
    const STATUS_CONFIRMED = '2';
    // 状态:后台已驳回
    const STATUS_REJECTED = '3';

    // 表名
    protected $name = 'loan_repayment';

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
        'due_time_text',
        'paytime_text',
        'checktime_text',
        'createtime_text',
        'images_list',
        'payee_text',
    ];

    /**
     * 状态列表
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_UNPAID    => 'To\'lov kutilmoqda',
            self::STATUS_PAID      => 'O\'tkazildi, tasdiqlash kutilmoqda',
            self::STATUS_CONFIRMED => 'Qabul qilindi',
            self::STATUS_REJECTED  => 'Rad etildi',
        ];
    }

    /**
     * 未结清的状态(需要客户操作的)
     */
    public static function getUnpaidStatusList()
    {
        return [self::STATUS_UNPAID, self::STATUS_REJECTED];
    }

    /**
     * 状态对应的样式
     */
    public static function getStatusClassList()
    {
        return [
            self::STATUS_UNPAID    => 'pending',
            self::STATUS_PAID      => 'withdrawing',
            self::STATUS_CONFIRMED => 'done',
            self::STATUS_REJECTED  => 'rejected',
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
        $list = self::getStatusClassList();
        return isset($list[$value]) ? $list[$value] : 'pending';
    }

    public function getAmountTextAttr($value, $data)
    {
        return '¥' . number_format((float)(isset($data['amount']) ? $data['amount'] : 0), 2);
    }

    public function getDueTimeTextAttr($value, $data)
    {
        $value = isset($data['due_time']) ? $data['due_time'] : 0;
        return $value ? date('Y-m-d', $value) : '';
    }

    public function getPaytimeTextAttr($value, $data)
    {
        $value = isset($data['paytime']) ? $data['paytime'] : 0;
        return $value ? date('Y-m-d H:i', $value) : '';
    }

    public function getChecktimeTextAttr($value, $data)
    {
        $value = isset($data['checktime']) ? $data['checktime'] : 0;
        return $value ? date('Y-m-d H:i', $value) : '';
    }

    public function getCreatetimeTextAttr($value, $data)
    {
        $value = isset($data['createtime']) ? $data['createtime'] : 0;
        return $value ? date('Y-m-d H:i', $value) : '';
    }

    /**
     * 凭证图片列表
     */
    public function getImagesListAttr($value, $data)
    {
        $images = isset($data['images']) ? $data['images'] : '';
        $images = array_filter(explode(',', trim((string)$images)));
        return array_values($images);
    }

    /**
     * 收款账户(一行文本)
     */
    public function getPayeeTextAttr($value, $data)
    {
        $bank = isset($data['payee_bank_name']) ? $data['payee_bank_name'] : '';
        $branch = isset($data['payee_branch_name']) ? $data['payee_branch_name'] : '';
        $type = isset($data['payee_account_type']) ? $data['payee_account_type'] : '';
        $no = isset($data['payee_account_no']) ? $data['payee_account_no'] : '';
        $name = isset($data['payee_account_name']) ? $data['payee_account_name'] : '';
        return trim($bank . ' ' . $branch . ' ' . $type . ' ' . $no . ' ' . $name);
    }
}

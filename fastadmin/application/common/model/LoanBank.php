<?php

namespace app\common\model;

use think\Model;

/**
 * 银行卡
 */
class LoanBank extends Model
{

    // 表名
    protected $name = 'loan_bank';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'account_type_text',
        'account_no_text',
    ];

    /**
     * 账户类型列表
     */
    public static function getAccountTypeList()
    {
        return [
            '通常' => 'Oddiy',
            '当座' => 'Joriy',
            '储蓄' => 'Jamg\'arma',
        ];
    }

    public function getAccountTypeTextAttr($value, $data)
    {
        $list = self::getAccountTypeList();
        $accountType = isset($data['account_type']) && $data['account_type'] ? $data['account_type'] : '通常';
        return isset($list[$accountType]) ? $list[$accountType] : $accountType;
    }

    /**
     * 账号脱敏显示
     */
    public function getAccountNoTextAttr($value, $data)
    {
        $no = isset($data['account_no']) ? (string)$data['account_no'] : '';
        if ($no === '') {
            return '';
        }
        $len = mb_strlen($no);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }
        return str_repeat('*', 4) . mb_substr($no, -4);
    }
}

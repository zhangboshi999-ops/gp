<?php

namespace app\admin\model\loan;

use think\Model;


class Apply extends Model
{

    

    

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
        'checktime_text',
        'withdrawtime_text'
    ];
    

    
    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3'), '4' => __('Status 4')];
    }

    /**
     * 会员状态(进行中的申请)
     */
    public function getPendingStatusList()
    {
        return ['0', '1', '3'];
    }

    /**
     * 关联会员
     * 注意:使用独立查询(setEagerlyType 1),避免 JOIN 造成 id/status 等字段歧义
     */
    public function user()
    {
        return $this->belongsTo('app\common\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(1);
    }

    /**
     * 关联银行卡
     */
    public function bank()
    {
        return $this->belongsTo('app\admin\model\loan\Bank', 'bank_id', 'id', [], 'LEFT')->setEagerlyType(1);
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }


    public function getChecktimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['checktime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getWithdrawtimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['withdrawtime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setChecktimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setWithdrawtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}

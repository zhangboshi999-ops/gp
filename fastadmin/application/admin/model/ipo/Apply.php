<?php

namespace app\admin\model\ipo;

use think\Model;


class Apply extends Model
{

    

    

    // 表名
    protected $name = 'ipo_apply';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text',
        'checktime_text'
    ];
    

    
    public function getStatusList()
    {
        return ['pending' => __('Status pending'), 'won' => __('Status won'), 'partial' => __('Status partial'), 'lost' => __('Status lost'), 'canceled' => __('Status canceled')];
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

    protected function setChecktimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}

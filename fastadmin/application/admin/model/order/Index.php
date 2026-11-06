<?php

namespace app\admin\model\order;

use think\Model;


class Index extends Model
{

    

    

    // 表名
    protected $name = 'order';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'side_text',
        'type_text',
        'status_text',
        'finishtime_text'
    ];
    

    
    public function getSideList()
    {
        return ['buy' => __('Side buy'), 'sell' => __('Side sell')];
    }

    public function getTypeList()
    {
        return ['market' => __('Type market'), 'limit' => __('Type limit')];
    }

    public function getStatusList()
    {
        return ['pending' => __('Status pending'), 'partial' => __('Status partial'), 'filled' => __('Status filled'), 'canceled' => __('Status canceled'), 'rejected' => __('Status rejected')];
    }


    public function getSideTextAttr($value, $data)
    {
        $value = $value ?: ($data['side'] ?? '');
        $list = $this->getSideList();
        return $list[$value] ?? '';
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
        return $list[$value] ?? '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }


    public function getFinishtimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['finishtime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setFinishtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}

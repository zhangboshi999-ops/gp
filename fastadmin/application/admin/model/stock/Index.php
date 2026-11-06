<?php

namespace app\admin\model\stock;

use think\Model;


class Index extends Model
{

    

    

    // 表名
    protected $name = 'stock';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'is_hot_text',
        'status_text'
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            if (!$row['weigh']) {
                $pk = $row->getPk();
                $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
            }
        });
    }

    
    public function getTypeList()
    {
        return ['stock' => __('Type stock'), 'crypto' => __('Type crypto'), 'index' => __('Type index'), 'sector' => __('Type sector')];
    }

    public function getIsHotList()
    {
        return ['0' => __('Is_hot 0'), '1' => __('Is_hot 1')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Status normal'), 'suspended' => __('Status suspended'), 'hidden' => __('Status hidden')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
        return $list[$value] ?? '';
    }


    public function getIsHotTextAttr($value, $data)
    {
        $value = $value ?: ($data['is_hot'] ?? '');
        $list = $this->getIsHotList();
        return $list[$value] ?? '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }




}

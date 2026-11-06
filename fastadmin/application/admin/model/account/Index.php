<?php

namespace app\admin\model\account;

use think\Model;


class Index extends Model
{

    

    

    // 表名
    protected $name = 'account';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







}

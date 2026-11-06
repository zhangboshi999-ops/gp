<?php

namespace app\admin\model\loan;

use think\Model;


class Bank extends Model
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

    ];

    /**
     * 关联会员
     * 注意:使用独立查询(setEagerlyType 1),避免 JOIN 造成字段歧义
     */
    public function user()
    {
        return $this->belongsTo('app\common\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(1);
    }
    

    







}

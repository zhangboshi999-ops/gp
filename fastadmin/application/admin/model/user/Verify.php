<?php

namespace app\admin\model\user;

use think\Model;

class Verify extends Model
{
    // 表名
    protected $name = 'user_verify';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function getStatusList()
    {
        return ['pending' => '待审核', 'pass' => '已通过', 'reject' => '已驳回'];
    }

    /**
     * 证件类型（与 App 端提交的值保持一致）
     */
    public function getIdTypeList()
    {
        return [
            'idcard'   => '身份证',
            'passport' => '护照',
            'license'  => '驾驶证',
        ];
    }
}

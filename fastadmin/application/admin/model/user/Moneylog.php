<?php

namespace app\admin\model\user;

/**
 * 会员余额变动记录(后台)
 *
 * 复用公共模型的表定义与时间戳,这里只补充后台需要的关联与展示属性
 */
class Moneylog extends \app\common\model\MoneyLog
{
    // 追加属性
    protected $append = [
        'type_text',
    ];

    public function user()
    {
        return $this->belongsTo('app\common\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(1);
    }

    public function admin()
    {
        return $this->belongsTo('app\admin\model\Admin', 'admin_id', 'id', [], 'LEFT')->setEagerlyType(1);
    }

    /**
     * 变动方向:大于0为增加,小于0为扣减
     */
    public function getTypeTextAttr($value, $data)
    {
        $money = isset($data['money']) ? (float)$data['money'] : 0;
        return $money > 0 ? '增加' : '扣减';
    }
}

<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Config;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }

    /**
     * 可用语言列表
     *
     * 语言由 application/extra/lang.php 配置，关闭的语言不会返回。
     * 前端据此渲染语言切换项，并把当前语言通过 X-Lang 请求头回传。
     *
     * @ApiMethod (GET)
     * @ApiReturnParams (name="current", type="string", required=true, description="本次请求命中的语言包")
     * @ApiReturnParams (name="list", type="array", required=true, description="语言列表(code/name)")
     */
    public function langs()
    {
        $conf = Config::get('lang') ?: [];
        $list = [];
        foreach ((isset($conf['languages']) ? $conf['languages'] : []) as $item) {
            if (empty($item['enable'])) {
                continue;
            }
            $list[] = [
                'code' => $item['code'],
                'name' => isset($item['name']) ? $item['name'] : $item['code'],
            ];
        }
        $this->success('', [
            'default' => isset($conf['default']) ? $conf['default'] : 'zh-cn',
            'current' => $this->lang,
            'list'    => $list,
        ]);
    }
}

<?php

namespace app\index\controller;

use app\common\controller\Frontend;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function index()
    {
        // 首页正文内容走 home.html，套用公共头尾布局 layout/ondeck
        $this->view->assign('title', 'Small Business Lending That\'s Fast & Easy');
        $this->view->engine->layout('layout/ondeck');
        return $this->view->fetch('home');
    }

    /**
     * 复刻学习页面(参考 OnDeck 落地页结构)
     */
    public function ondeck()
    {
        return $this->view->fetch('ondeck');
    }

}

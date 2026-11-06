<?php

namespace app\admin\controller\stock;

use app\common\controller\Backend;
use think\Db;

/**
 * 证券标的管理
 *
 * @icon fa fa-circle-o
 */
class Index extends Backend
{

    /**
     * Index模型对象
     * @var \app\admin\model\stock\Index
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\stock\Index;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("isHotList", $this->model->getIsHotList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("marketList", $this->getMarketList());
    }

    /**
     * 市场列表（按库里实际数据生成，带中文名与数量，供列表页 Tab 使用）
     * 新增交易所时无需改代码，Tab 会自动出现
     */
    protected function getMarketList()
    {
        $names = [
            'TSE'    => '东京证券',
            'NASDAQ' => '纳斯达克',
            'NYSE'   => '纽约证券',
            'CRYPTO' => '加密货币',
        ];
        $rows = Db::name('stock')->field('market, count(*) AS total')->group('market')->select();
        $list = [];
        foreach ($rows as $row) {
            $market = $row['market'];
            if ($market === '' || $market === null) {
                continue;
            }
            $label = isset($names[$market]) ? $names[$market] : $market;
            $list[$market] = $label . '（' . $row['total'] . '）';
        }
        return $list;
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}

<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\library\Fund;

/**
 * 资金流水管理
 *
 * @icon fa fa-circle-o
 */
class Transaction extends Backend
{

    /**
     * Transaction模型对象
     * @var \app\admin\model\Transaction
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Transaction;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }



    /**
     * 审核充值/提现申请
     *
     * 审核通过后按类型结算资金：充值入账、提现扣除冻结金额；
     * 驳回提现时把冻结金额退回可用余额。
     *
     * @param null $ids
     * @return string
     */
    public function audit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            $status = isset($params['status']) ? $params['status'] : '';
            if (!in_array($status, ['success', 'rejected'])) {
                $this->error('请选择审核结果');
            }
            try {
                Fund::audit($ids, $status, isset($params['checkremark']) ? $params['checkremark'] : '');
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success();
        }
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}

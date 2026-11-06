<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\common\model\User;
use think\Db;

/**
 * 实名认证管理
 *
 * @icon fa fa-circle-o
 */
class Verify extends Backend
{

    /**
     * Verify模型对象
     * @var \app\admin\model\user\Verify
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\user\Verify;
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("idTypeList", $this->model->getIdTypeList());
    }



    /**
     * 审核实名认证：同步更新会员表的认证状态(2=已认证,3=已驳回)
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
            if (!in_array($status, ['pass', 'reject'])) {
                $this->error('请选择审核结果');
            }
            Db::startTrans();
            try {
                $row->save([
                    'status'      => $status,
                    'remark'      => isset($params['remark']) ? $params['remark'] : '',
                    'checktime'   => time(),
                    'updatetime'  => time(),
                ]);
                $user = User::get($row['user_id']);
                if ($user) {
                    $user->kyc_status = $status == 'pass' ? 2 : 3;
                    $user->save();
                }
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
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

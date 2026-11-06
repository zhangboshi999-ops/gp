<?php

namespace app\admin\controller\loan;

use app\admin\model\loan\Bank;
use app\common\controller\Backend;
use think\Db;

/**
 * 资金申请
 *
 * @icon fa fa-circle-o
 */
class Apply extends Backend
{

    /**
     * Apply模型对象
     * @var \app\admin\model\loan\Apply
     */
    protected $model = null;

    /**
     * 快速搜索字段
     */
    protected $searchFields = 'name,mobile,email';

    /**
     * Selectpage下拉选择的返回字段(关联申请下拉展示姓名/手机号/状态)
     */
    protected $selectpageFields = 'id,name,mobile,status_text';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\loan\Apply;
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是Selectpage，则转发到Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $list = $this->model
            ->with([
                'user' => function ($query) {
                    // 独立关联查询,只取需要的字段,避免 JOIN 歧义与返回敏感信息
                    $query->field('id,username,nickname,email,mobile');
                },
                'bank' => function ($query) {
                    // 列表"收款账户"列需要 account_type,补充查询避免显示 undefined
                    $query->field('id,account_name,account_no,account_type,bank_name');
                },
            ])
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    /**
     * 编辑
     *
     * 银行卡ID按会员自动关联(每个会员唯一一张银行卡),编辑时无需手动选择
     *
     * @param null $ids
     * @return string
     * @throws \think\exception\DbException
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            $this->view->assign('bank', $row['bank_id'] ? Bank::get($row['bank_id']) : null);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        // 银行卡跟随会员自动关联,即便表单未提交 bank_id 也会写入
        $userId = isset($params['user_id']) && $params['user_id'] !== '' ? $params['user_id'] : $row['user_id'];
        $params['bank_id'] = Bank::where('user_id', $userId)->value('id') ?: 0;
        // 时间字段允许留空，并兼容日期字符串
        foreach (['checktime', 'withdrawtime'] as $timeField) {
            if (isset($params[$timeField])) {
                $params[$timeField] = (string)$params[$timeField] === ''
                    ? 0
                    : (is_numeric($params[$timeField]) ? $params[$timeField] : strtotime($params[$timeField]));
            }
        }
        $result = false;
        Db::startTrans();
        try {
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    /**
     * 审核通过
     */
    public function pass()
    {
        $ids = $this->request->param('ids');
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if ($row['status'] != '0') {
                $this->error('该申请已处理，无法再次审核');
            }
            $amount = isset($params['amount']) && $params['amount'] !== '' ? $params['amount'] : $row['amount'];
            if (!is_numeric($amount) || $amount <= 0) {
                $this->error('批准金额必须大于0');
            }
            Db::startTrans();
            try {
                $row->save([
                    'amount'      => $amount,
                    'status'      => '1',
                    'checkremark' => isset($params['checkremark']) ? $params['checkremark'] : '',
                    'admin_id'    => $this->auth->id,
                    'checktime'   => time(),
                ]);
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
     * 驳回申请
     */
    public function reject()
    {
        $ids = $this->request->param('ids');
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if ($row['status'] != '0') {
                $this->error('该申请已处理，无法再次审核');
            }
            $remark = isset($params['checkremark']) ? $params['checkremark'] : '';
            if (trim($remark) === '') {
                $this->error('请填写驳回原因');
            }
            $row->save([
                'status'      => '2',
                'checkremark' => $remark,
                'admin_id'    => $this->auth->id,
                'checktime'   => time(),
            ]);
            $this->success();
        }
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }

    /**
     * 确认放款(提款完成)
     */
    public function pay()
    {
        $ids = $this->request->param('ids');
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if ($row['status'] != '3') {
                $this->error('该申请当前状态不可放款');
            }
            if (!$row['bank_id']) {
                $this->error('会员尚未添加银行卡，无法放款');
            }
            Db::startTrans();
            try {
                $row->save([
                    'status'          => '4',
                    'withdraw_amount' => $row['amount'],
                    'withdrawtime'    => time(),
                    'checkremark'     => isset($params['checkremark']) && $params['checkremark'] !== '' ? $params['checkremark'] : $row['checkremark'],
                ]);
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

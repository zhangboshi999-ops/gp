<?php

namespace app\admin\controller\loan;

use app\admin\model\loan\Repayment as RepaymentModel;
use app\common\controller\Backend;
use app\common\model\LoanApply;
use app\common\model\LoanRepayment;
use think\Config;
use think\Db;

/**
 * 还款记录
 *
 * @icon fa fa-jpy
 */
class Repayment extends Backend
{

    /**
     * Repayment模型对象
     * @var \app\admin\model\loan\Repayment
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\loan\Repayment;
        $this->view->assign("statusList", LoanRepayment::getStatusList());
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
                    $query->field('id,username,nickname');
                },
            ])
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    /**
     * 生成一期还款单
     *
     * 会员身份按所选申请自动带入,收款账户默认取系统配置,可临时修改
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            $this->view->assign('payee', Config::get('loan.payee'));
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $applyId = isset($params['apply_id']) ? (int)$params['apply_id'] : 0;
        $apply = $applyId ? LoanApply::get($applyId) : null;
        if (!$apply) {
            $this->error('请选择对应的资金申请');
        }
        $amount = isset($params['amount']) ? (float)$params['amount'] : 0;
        if ($amount <= 0) {
            $this->error('本期还款金额必须大于0');
        }
        $dueTime = !empty($params['due_time']) ? strtotime($params['due_time']) : 0;
        if (!$dueTime) {
            $this->error('请选择正确的还款日期');
        }
        //同一申请同一期次只能有一条记录
        $periodNo = isset($params['period_no']) && $params['period_no'] !== '' ? trim((string)$params['period_no']) : '1';
        $exists = RepaymentModel::where('apply_id', $apply['id'])->where('period_no', $periodNo)->find();
        if ($exists) {
            $this->error('第 ' . $periodNo . ' 期还款单已存在，请勿重复生成');
        }

        $payee = Config::get('loan.payee');
        $pickPayee = function ($key) use ($params, $payee) {
            $value = isset($params[$key]) ? trim((string)$params[$key]) : '';
            return $value !== '' ? $value : (isset($payee[str_replace('payee_', '', $key)]) ? $payee[str_replace('payee_', '', $key)] : '');
        };

        $data = [
            'apply_id'           => $apply['id'],
            'user_id'            => $apply['user_id'],
            'period_no'          => $periodNo,
            'amount'             => $amount,
            'due_time'           => $dueTime,
            'payee_bank_name'    => $pickPayee('payee_bank_name'),
            'payee_branch_name'  => $pickPayee('payee_branch_name'),
            'payee_account_type' => $pickPayee('payee_account_type'),
            'payee_account_no'   => $pickPayee('payee_account_no'),
            'payee_account_name' => $pickPayee('payee_account_name'),
            'remark'             => isset($params['remark']) ? trim((string)$params['remark']) : '',
            'status'             => LoanRepayment::STATUS_UNPAID,
        ];
        $result = false;
        Db::startTrans();
        try {
            $result = $this->model->allowField(true)->save($data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }

    /**
     * 批复客户上传的还款凭证
     *
     * @param null $ids
     * @return string
     */
    public function check($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            $action = $this->request->post('action');
            $remark = isset($params['checkremark']) ? trim((string)$params['checkremark']) : '';
            if ($row['status'] == LoanRepayment::STATUS_CONFIRMED) {
                $this->error('该笔还款已确认收款，无需再次批复');
            }
            if ($action === 'pass') {
                if ($row['status'] != LoanRepayment::STATUS_PAID) {
                    $this->error('客户尚未确认还款并上传凭证，无法批复通过');
                }
                $data = [
                    'status'      => LoanRepayment::STATUS_CONFIRMED,
                    'checkremark' => $remark,
                    'admin_id'    => $this->auth->id,
                    'checktime'   => time(),
                ];
            } else {
                if ($remark === '') {
                    $this->error('请填写驳回原因');
                }
                $data = [
                    'status'      => LoanRepayment::STATUS_REJECTED,
                    'checkremark' => $remark,
                    'admin_id'    => $this->auth->id,
                    'checktime'   => time(),
                ];
            }
            $row->save($data);
            $this->success();
        }
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }

    /**
     * 删除
     *
     * @param null $ids
     */
    public function del($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ?: $this->request->post("ids");
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $list = $this->model->where($this->model->getPk(), 'in', $ids)->select();
        $count = 0;
        Db::startTrans();
        try {
            foreach ($list as $item) {
                $count += $item->delete();
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (!$count) {
            $this->error(__('No rows were deleted'));
        }
        $this->success();
    }
}

<?php

namespace app\admin\controller\user;

use app\admin\model\user\Moneylog as MoneylogModel;
use app\common\controller\Backend;
use app\common\model\User;
use think\Db;

/**
 * 会员余额记录
 *
 * @icon fa fa-money
 */
class Moneylog extends Backend
{

    /**
     * Moneylog模型对象
     * @var \app\admin\model\user\Moneylog
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\user\Moneylog;
        $this->view->assign("typeList", $this->getTypeList());
    }

    /**
     * 调整类型:增加/扣减
     */
    protected function getTypeList()
    {
        return ['inc' => '增加余额', 'dec' => '扣减余额'];
    }

    /**
     * 查看
     *
     * 支持 ?user_id={会员ID} 只查看某一会员的余额明细(会员列表里的"余额明细"按钮)
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            $userId = (int)$this->request->request('user_id');
            $this->view->assign('userId', $userId);
            $this->view->assign('user', $userId ? User::get($userId) : null);
            return $this->view->fetch();
        }
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $query = $this->model
            ->with([
                'user'  => function ($q) {
                    $q->field('id,username,nickname');
                },
                'admin' => function ($q) {
                    $q->field('id,username,nickname');
                },
            ]);
        //会员明细入口:锁定到指定会员
        if ($userId = $this->request->request('user_id')) {
            $query->where('user_id', (int)$userId);
        }
        $list = $query
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    /**
     * 调整会员余额
     *
     * 增加/扣减均写入 fa_user_money_log,同时更新 fa_user.money
     *
     * @param null $ids 会员ID(会员列表按钮以 ?user_id={id} 方式传入)
     * @return string
     */
    public function add($ids = null)
    {
        if (false === $this->request->isPost()) {
            $userId = (int)($this->request->request('user_id') ?: $ids);
            $this->view->assign('userId', $userId);
            $this->view->assign('user', $userId ? User::get($userId) : null);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $userId = isset($params['user_id']) ? (int)$params['user_id'] : 0;
        $amount = isset($params['money']) ? round((float)$params['money'], 2) : 0;
        $type   = isset($params['type']) && $params['type'] === 'dec' ? 'dec' : 'inc';
        $memo   = isset($params['memo']) ? trim((string)$params['memo']) : '';
        if (!$userId) {
            $this->error('请选择要调整的会员');
        }
        if ($amount <= 0) {
            $this->error('调整金额必须大于0');
        }
        $user = User::get($userId);
        if (!$user) {
            $this->error('会员不存在');
        }
        $before = (float)$user['money'];
        $delta  = $type === 'inc' ? $amount : -$amount;
        $after  = function_exists('bcadd') ? (float)bcadd((string)$before, (string)$delta, 2) : $before + $delta;
        if ($after < 0) {
            $this->error('扣减后余额不能小于0，当前余额为 ' . number_format($before, 2));
        }
        if ($memo === '') {
            $memo = $type === 'inc' ? '后台增加余额' : '后台扣减余额';
        }
        $result = false;
        Db::startTrans();
        try {
            $user->save(['money' => $after]);
            MoneylogModel::create([
                'user_id'  => $userId,
                'money'    => $delta,
                'before'   => $before,
                'after'    => $after,
                'memo'     => $memo,
                'admin_id' => $this->auth->id,
            ]);
            Db::commit();
            $result = true;
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (!$result) {
            $this->error(__('No rows were inserted'));
        }
        $this->success('调整成功，当前余额 ' . number_format($after, 2));
    }

    /**
     * 删除(余额历史记录不应修改历史金额,提供删除便于清理误操作)
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

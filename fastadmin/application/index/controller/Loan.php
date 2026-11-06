<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\LoanApply;
use app\common\model\LoanBank;
use app\common\model\LoanRepayment;
use think\Config;
use think\Validate;

/**
 * 会员中心 - 申请财政支持
 */
class Loan extends Frontend
{

    protected $layout = 'loan';
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'), '/');
        }
    }

    /**
     * 会员中心(申请/详情)
     */
    public function index()
    {
        $userId = $this->auth->id;
        $current = LoanApply::where('user_id', $userId)->order('id', 'desc')->find();
        $bank = LoanBank::where('user_id', $userId)->order('id', 'desc')->find();
        $history = LoanApply::where('user_id', $userId)->order('id', 'desc')->limit(20)->select();
        //还款账单(按到期日正序)
        $repayments = LoanRepayment::where('user_id', $userId)->order('due_time', 'asc')->select();

        $active = $current && in_array($current['status'], LoanApply::getPendingStatusList());
        $status = $current ? $current['status'] : null;
        $loanConfig = Config::get('loan');

        //协议内容按段落输出
        $agreementHtml = '';
        $paragraphs = array_filter(array_map('trim', explode("\n\n", (string)$loanConfig['agreement'])));
        foreach ($paragraphs as $paragraph) {
            $agreementHtml .= '<p>' . nl2br(htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8')) . '</p>';
        }

        $this->view->assign([
            'title'        => 'Moliyaviy qo\'llab-quvvatlash',
            'current'      => $current,
            'bank'         => $bank,
            'history'      => $history,
            'repayments'   => $repayments,
            'canApply'     => !$active,
            'showWithdraw' => in_array($status, [LoanApply::STATUS_PASSED, LoanApply::STATUS_WITHDRAWING]),
            'showBank'     => in_array($status, [LoanApply::STATUS_PASSED, LoanApply::STATUS_WITHDRAWING, LoanApply::STATUS_DONE]),
            'terms'        => (array)$loanConfig['terms'],
            'defaultTerm'  => (int)$loanConfig['default_term'],
            'selectedTerm' => $current && $current['term'] ? (int)$current['term'] : (int)$loanConfig['default_term'],
            'interestRate' => $loanConfig['interest_rate'],
            'agreementTitle' => $loanConfig['agreement_title'],
            'agreementHtml' => $agreementHtml,
            'accountTypes' => LoanBank::getAccountTypeList(),
        ]);
        return $this->view->fetch();
    }

    /**
     * 提交申请
     */
    public function apply()
    {
        if (!$this->request->isPost()) {
            $this->error('So\'rov xato');
        }
        $userId = $this->auth->id;
        $pending = LoanApply::where('user_id', $userId)
            ->where('status', 'in', LoanApply::getPendingStatusList())
            ->find();
        if ($pending) {
            $this->error('Sizda ko\'rib chiqilayotgan ariza bor, takroran yubormang');
        }

        $params = $this->request->post();
        $data = [
            'name'      => isset($params['name']) ? trim($params['name']) : '',
            'mobile'    => isset($params['mobile']) ? trim($params['mobile']) : '',
            'email'     => isset($params['email']) ? trim($params['email']) : '',
            'amount'    => isset($params['amount']) ? $params['amount'] : '',
            'purpose'   => isset($params['purpose']) ? trim($params['purpose']) : '',
            '__token__' => $this->request->post('__token__'),
        ];
        $rule = [
            'name'      => 'require|length:1,50',
            'mobile'    => 'require|length:5,20',
            'email'     => 'require|email',
            'amount'    => 'require|float|gt:0',
            'purpose'   => 'require|length:1,200',
            '__token__' => 'require|token',
        ];
        $msg = [
            'name.require'    => 'Ismingizni kiriting',
            'mobile.require'  => 'Telefon raqamini kiriting',
            'email.require'   => 'Elektron pochtani kiriting',
            'email.email'     => 'Elektron pochta formati noto\'g\'ri',
            'amount.require'  => 'So\'ralgan summani kiriting',
            'amount.float'    => 'So\'ralgan summa raqam bo\'lishi kerak',
            'amount.gt'       => 'So\'ralgan summa 0 dan katta bo\'lishi kerak',
            'purpose.require' => 'Maqsadni kiriting',
        ];
        $validate = new Validate($rule, $msg);
        if (!$validate->check($data)) {
            $this->error($validate->getError(), null, ['token' => $this->request->token()]);
        }

        $loanConfig = Config::get('loan');
        $apply = LoanApply::create([
            'user_id'       => $userId,
            'name'          => $data['name'],
            'mobile'        => $data['mobile'],
            'email'         => $data['email'],
            'amount'        => $data['amount'],
            'purpose'       => $data['purpose'],
            'status'        => LoanApply::STATUS_PENDING,
            'interest_rate' => $loanConfig['interest_rate'],
        ]);
        if (!$apply) {
            $this->error('Arizani yuborishda xato, keyinroq qayta urinib ko\'ring');
        }
        $this->success('Ariza muvaffaqiyatli yuborildi, ko\'rib chiqilishini kuting', url('loan/index'));
    }

    /**
     * 还款计划试算
     */
    public function plan()
    {
        $userId = $this->auth->id;
        $term = (int)$this->request->param('term');
        $apply = LoanApply::where('user_id', $userId)
            ->where('status', LoanApply::STATUS_PASSED)
            ->order('id', 'desc')
            ->find();
        if (!$apply) {
            $this->error('Hozirda yechib olish mumkin bo\'lgan ariza yo\'q');
        }
        $loanConfig = Config::get('loan');
        if (!in_array($term, (array)$loanConfig['terms'])) {
            $this->error('To\'g\'ri to\'lov muddatini tanlang');
        }
        $plan = LoanApply::buildPlan($apply['amount'], $term, $apply['interest_rate']);
        $this->success('', null, [
            'term'            => $plan['term'],
            'payment'         => '¥' . number_format($plan['payment'], 2),
            'interest'        => '¥' . number_format($plan['interest'], 2),
            'total'           => '¥' . number_format($plan['total'], 2),
            'payment_raw'     => $plan['payment'],
            'interest_raw'    => $plan['interest'],
            'total_raw'       => $plan['total'],
        ]);
    }

    /**
     * 保存银行卡(注册后不可修改)
     */
    public function bank()
    {
        if (!$this->request->isPost()) {
            $this->error('So\'rov xato');
        }
        $userId = $this->auth->id;
        if (LoanBank::where('user_id', $userId)->find()) {
            $this->error('Hisobingiz ro\'yxatdan o\'tgan, uni o\'zgartirib bo\'lmaydi');
        }
        $apply = LoanApply::where('user_id', $userId)
            ->where('status', 'in', [LoanApply::STATUS_PASSED, LoanApply::STATUS_WITHDRAWING])
            ->order('id', 'desc')
            ->find();
        if (!$apply) {
            $this->error('Bank kartasini faqat ariza tasdiqlangandan keyin qo\'shish mumkin');
        }

        $params = $this->request->post();
        $data = [
            'account_name' => isset($params['account_name']) ? trim($params['account_name']) : '',
            'account_no'   => isset($params['account_no']) ? trim($params['account_no']) : '',
            'bank_name'    => isset($params['bank_name']) ? trim($params['bank_name']) : '',
            'account_type' => isset($params['account_type']) ? trim($params['account_type']) : '通常',
            '__token__'    => $this->request->post('__token__'),
        ];
        $rule = [
            'account_name' => 'require|length:1,50',
            'account_no'   => 'require|length:4,30',
            'bank_name'    => 'require|length:1,100',
            'account_type' => 'require|in:通常,当座,储蓄',
            '__token__'    => 'require|token',
        ];
        $msg = [
            'account_name.require' => 'Hisob egasining ismini kiriting',
            'account_no.require'   => 'Hisob raqamingizni kiriting',
            'bank_name.require'    => 'Bank nomini kiriting',
            'account_type.require' => 'Hisob turini tanlang',
        ];
        $validate = new Validate($rule, $msg);
        if (!$validate->check($data)) {
            $this->error($validate->getError(), null, ['token' => $this->request->token()]);
        }
        unset($data['__token__']);
        $data['user_id'] = $userId;
        if (!LoanBank::create($data)) {
            $this->error('Bank kartasini saqlashda xato, keyinroq qayta urinib ko\'ring');
        }
        $this->success('Bank karta ma\'lumotlari ro\'yxatdan o\'tgan, o\'zgartirib bo\'lmaydi', url('loan/index'));
    }

    /**
     * 申请提款(仅支持全额提款)
     */
    public function withdraw()
    {
        if (!$this->request->isPost()) {
            $this->error('So\'rov xato');
        }
        $userId = $this->auth->id;
        $apply = LoanApply::where('user_id', $userId)
            ->where('status', LoanApply::STATUS_PASSED)
            ->order('id', 'desc')
            ->find();
        if (!$apply) {
            $this->error('Hozirda yechib olish mumkin bo\'lgan ariza yo\'q');
        }
        $bank = LoanBank::where('user_id', $userId)->find();
        if (!$bank) {
            $this->error('Avval bank kartasini qo\'shing');
        }
        $loanConfig = Config::get('loan');
        $term = (int)$this->request->post('term');
        $validate = new Validate([
            'term'      => 'require|in:' . implode(',', (array)$loanConfig['terms']),
            '__token__' => 'require|token',
        ], [
            'term.require' => 'To\'lov muddatini tanlang',
            'term.in'      => 'To\'g\'ri to\'lov muddatini tanlang',
        ]);
        if (!$validate->check(['term' => $term, '__token__' => $this->request->post('__token__')])) {
            $this->error($validate->getError(), null, ['token' => $this->request->token()]);
        }

        $plan = LoanApply::buildPlan($apply['amount'], $term, $apply['interest_rate']);
        $apply->save([
            'status'   => LoanApply::STATUS_WITHDRAWING,
            'term'     => $plan['term'],
            'payment'  => $plan['payment'],
            'interest' => $plan['interest'],
            'total'    => $plan['total'],
            'bank_id'  => $bank['id'],
        ]);
        $this->success('Yechib olish arizasi yuborildi, pul o\'tkazilgach hisobingizga tushadi', url('loan/index'));
    }

    /**
     * 还款详情(展示后台下发的还款金额/日期与收款账户)
     *
     * @return mixed
     */
    public function repayment()
    {
        $ids = $this->request->param('ids');
        $row = LoanRepayment::where('user_id', $this->auth->id)->where('id', $ids)->find();
        if (!$row) {
            $this->error('Ushbu to\'lov hisob-kitobi topilmadi');
        }
        if ($this->request->isPost()) {
            if ($row['status'] == LoanRepayment::STATUS_CONFIRMED) {
                $this->error('Ushbu to\'lov qabul qilindi, takroran yuborish shart emas');
            }
            $images = trim((string)$this->request->post('images'));
            $remark = trim((string)$this->request->post('remark'));
            if (!$images) {
                $this->error('Avval o\'tkazma kvitansiyasini yuklang');
            }
            $row->save([
                'images'  => $images,
                'remark'  => $remark,
                'status'  => LoanRepayment::STATUS_PAID,
                'paytime' => time(),
            ]);
            $this->success('To\'lov ma\'lumotlari yuborildi, qabul qilinishini kuting', url('loan/index'));
        }
        $this->view->assign([
            'title' => 'To\'lov',
            'row'   => $row,
        ]);
        return $this->view->fetch();
    }
}

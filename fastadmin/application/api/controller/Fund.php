<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Fund as FundService;
use app\common\model\BankCard;
use app\common\model\CryptoWallet;
use app\common\model\Transaction;
use think\Config;

/**
 * 资金接口
 */
class Fund extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = '*';

    /**
     * 充值收款信息（来自 application/extra/payee.php）
     *
     * @ApiMethod (GET)
     * @ApiReturnParams (name="bank", type="object", required=true, description="银行收款账户")
     * @ApiReturnParams (name="crypto", type="array", required=true, description="加密货币收款地址")
     * @ApiReturnParams (name="min_amount", type="int", required=true, description="单笔最小金额")
     * @ApiReturnParams (name="max_amount", type="int", required=true, description="单笔最大金额")
     */
    public function payee()
    {
        $conf = Config::get('payee') ?: [];
        $crypto = [];
        foreach ((isset($conf['crypto']) ? $conf['crypto'] : []) as $key => $item) {
            $item['method'] = $key;
            $crypto[] = $item;
        }
        $this->success('', [
            'bank'       => isset($conf['bank']) ? $conf['bank'] : [],
            'crypto'     => $crypto,
            'min_amount' => (float)(isset($conf['min_amount']) ? $conf['min_amount'] : 0),
            'max_amount' => (float)(isset($conf['max_amount']) ? $conf['max_amount'] : 0),
        ]);
    }

    /**
     * 账户列表
     */
    public function accounts()
    {
        $list = FundService::accounts($this->auth->id);
        $rows = [];
        foreach ($list as $item) {
            $rows[] = [
                'currency'      => $item['currency'],
                'balance'       => (float)$item['balance'],
                'frozen'        => (float)$item['frozen'],
                'total'         => (float)($item['balance'] + $item['frozen']),
                'total_deposit' => (float)$item['total_deposit'],
                'total_withdraw' => (float)$item['total_withdraw'],
            ];
        }
        $this->success('', $rows);
    }

    /**
     * 充值
     *
     * @ApiMethod (POST)
     * @ApiParams (name="currency", type="string", required=true, description="币种:JPY/USD/USDT")
     * @ApiParams (name="amount", type="string", required=true, description="金额")
     * @ApiParams (name="method", type="string", required=true, description="渠道:bank/btc/eth/usdt")
     */
    public function deposit()
    {
        $currency = $this->request->post('currency', 'JPY');
        $amount = (float)$this->request->post('amount');
        $method = $this->request->post('method', 'bank');
        $extra = [
            'bank_name'    => $this->request->post('bank_name', ''),
            'branch_name'  => $this->request->post('branch_name', ''),
            'account_no'   => $this->request->post('account_no', ''),
            'account_name' => $this->request->post('account_name', ''),
            'address'      => $this->request->post('address', ''),
            'txid'         => $this->request->post('txid', ''),
            'receipt'      => $this->request->post('receipt', ''),
            'remark'       => $this->request->post('remark', ''),
        ];
        try {
            $transaction = FundService::deposit($this->auth->id, $currency, $amount, $method, $extra);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('充值申请已提交,等待审核', ['sn' => $transaction['sn']]);
    }

    /**
     * 提现
     *
     * @ApiMethod (POST)
     * @ApiParams (name="currency", type="string", required=true, description="币种")
     * @ApiParams (name="amount", type="string", required=true, description="金额")
     * @ApiParams (name="method", type="string", required=true, description="渠道:bank/btc/eth/usdt")
     * @ApiParams (name="pay_password", type="string", required=true, description="资金密码")
     */
    public function withdraw()
    {
        $currency = $this->request->post('currency', 'JPY');
        $amount = (float)$this->request->post('amount');
        $method = $this->request->post('method', 'bank');
        $payPassword = $this->request->post('pay_password');
        $extra = [
            'bank_name'    => $this->request->post('bank_name', ''),
            'branch_name'  => $this->request->post('branch_name', ''),
            'account_no'   => $this->request->post('account_no', ''),
            'account_name' => $this->request->post('account_name', ''),
            'address'      => $this->request->post('address', ''),
            'remark'       => $this->request->post('remark', ''),
        ];
        try {
            FundService::checkPayPassword($this->auth->id, $payPassword);
            $transaction = FundService::withdraw($this->auth->id, $currency, $amount, $method, $extra);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('提现申请已提交,等待审核', ['sn' => $transaction['sn']]);
    }

    /**
     * 站内划转
     *
     * @ApiMethod (POST)
     * @ApiParams (name="to_account", type="string", required=true, description="收款账号(用户名/手机号/邮箱)")
     * @ApiParams (name="currency", type="string", required=true, description="币种")
     * @ApiParams (name="amount", type="string", required=true, description="金额")
     * @ApiParams (name="pay_password", type="string", required=true, description="资金密码")
     */
    public function transfer()
    {
        $toAccount = $this->request->post('to_account');
        $currency = $this->request->post('currency', 'JPY');
        $amount = (float)$this->request->post('amount');
        $payPassword = $this->request->post('pay_password');
        $remark = $this->request->post('remark', '');
        if (!$toAccount) {
            $this->error('请输入收款账号');
        }
        $toUser = \app\common\model\User::getByUsername($toAccount);
        if (!$toUser) {
            $toUser = \app\common\model\User::getByMobile($toAccount);
        }
        if (!$toUser) {
            $toUser = \app\common\model\User::getByEmail($toAccount);
        }
        if (!$toUser) {
            $this->error('收款会员不存在');
        }
        try {
            FundService::checkPayPassword($this->auth->id, $payPassword);
            FundService::transfer($this->auth->id, $toUser['id'], $currency, $amount, $remark);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('划转成功');
    }

    /**
     * 货币划转汇率（以 JPY 为基准）
     *
     * @ApiMethod (GET)
     */
    public function rate()
    {
        $this->success('', config('rate.rates'));
    }

    /**
     * 货币划转（同一账户内两种货币互换，如 USD -> JPY）
     *
     * @ApiMethod (POST)
     * @ApiParams (name="from_currency", type="string", required=true, description="转出币种")
     * @ApiParams (name="to_currency", type="string", required=true, description="转入币种")
     * @ApiParams (name="amount", type="float", required=true, description="转出金额")
     * @ApiParams (name="pay_password", type="string", required=true, description="支付密码")
     */
    public function exchange()
    {
        $from = strtoupper($this->request->post('from_currency', ''));
        $to = strtoupper($this->request->post('to_currency', ''));
        $amount = (float)$this->request->post('amount');
        $payPassword = $this->request->post('pay_password');
        try {
            FundService::checkPayPassword($this->auth->id, $payPassword);
            $toAmount = FundService::exchange($this->auth->id, $from, $to, $amount);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('划转成功', ['to_amount' => $toAmount]);
    }

    /**
     * 资金流水
     *
     * @ApiMethod (GET)
     * @ApiParams (name="type", type="string", required=false, description="deposit/withdraw/transfer")
     */
    public function records()
    {
        $type = $this->request->get('type');
        $page = max((int)$this->request->get('page'), 1);
        $limit = min(max((int)$this->request->get('limit'), 1), 100);
        $query = Transaction::where('user_id', $this->auth->id);
        if ($type) {
            $query->where('type', $type);
        }
        $total = $query->count();
        $list = $query->order('id', 'desc')->page($page, $limit)->select();
        $rows = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['amount'] = (float)$row['amount'];
            $row['fee'] = (float)$row['fee'];
            $rows[] = $row;
        }
        $this->success('', ['total' => $total, 'list' => $rows]);
    }

    /**
     * 银行卡列表
     */
    public function banks()
    {
        $list = BankCard::where('user_id', $this->auth->id)->order('is_default', 'desc')->select();
        $this->success('', $list);
    }

    /**
     * 添加银行卡
     *
     * @ApiMethod (POST)
     */
    public function addbank()
    {
        $params = $this->request->post();
        if (empty($params['bank_name']) || empty($params['account_no']) || empty($params['account_name'])) {
            $this->error('请填写完整的银行信息');
        }
        BankCard::create([
            'user_id'      => $this->auth->id,
            'bank_name'    => $params['bank_name'],
            'branch_name'  => isset($params['branch_name']) ? $params['branch_name'] : '',
            'account_type' => isset($params['account_type']) ? $params['account_type'] : '普通',
            'account_no'   => $params['account_no'],
            'account_name' => $params['account_name'],
            'is_default'   => BankCard::where('user_id', $this->auth->id)->count() > 0 ? 0 : 1,
            'createtime'   => time(),
        ]);
        $this->success('银行卡已添加');
    }

    /**
     * 删除银行卡
     *
     * @ApiMethod (POST)
     * @ApiParams (name="id", type="integer", required=true, description="银行卡ID")
     */
    public function delbank()
    {
        $id = (int)$this->request->post('id');
        BankCard::where(['id' => $id, 'user_id' => $this->auth->id])->delete();
        $this->success('已删除');
    }

    /**
     * 钱包地址列表
     */
    public function wallets()
    {
        $list = CryptoWallet::where('user_id', $this->auth->id)->order('id', 'desc')->select();
        $this->success('', $list);
    }

    /**
     * 添加钱包地址
     *
     * @ApiMethod (POST)
     * @ApiParams (name="chain", type="string", required=true, description="链:BTC/ETH/USDT")
     * @ApiParams (name="address", type="string", required=true, description="地址")
     */
    public function addwallet()
    {
        $chain = $this->request->post('chain', 'BTC');
        $address = $this->request->post('address');
        if (!$address) {
            $this->error('请输入钱包地址');
        }
        CryptoWallet::create([
            'user_id'    => $this->auth->id,
            'chain'      => $chain,
            'address'    => $address,
            'label'      => $this->request->post('label', ''),
            'createtime' => time(),
        ]);
        $this->success('钱包地址已添加');
    }

    /**
     * 设置资金密码
     *
     * @ApiMethod (POST)
     * @ApiParams (name="pay_password", type="string", required=true, description="6位数字资金密码")
     */
    public function paypwd()
    {
        $password = $this->request->post('pay_password');
        if (!preg_match('/^\d{6}$/', $password)) {
            $this->error('资金密码必须为6位数字');
        }
        try {
            FundService::setPayPassword($this->auth->id, $password);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('资金密码设置成功');
    }
}

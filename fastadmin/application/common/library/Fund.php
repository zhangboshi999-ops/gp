<?php

namespace app\common\library;

use app\common\model\Account;
use app\common\model\Transaction;
use app\common\model\User;
use think\Db;

/**
 * 资金服务：账户、充值、提现、站内划转、审核
 */
class Fund
{
    /**
     * 生成流水单号
     */
    public static function sn($prefix = 'F')
    {
        return $prefix . date('YmdHis') . str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
    }

    /**
     * 资金密码加密(与 FastAdmin 会员密码加密方式一致)
     */
    public static function encryptPayPassword($password, $salt)
    {
        return md5(md5($password) . $salt);
    }

    /**
     * 设置资金密码
     */
    public static function setPayPassword($userId, $password)
    {
        $user = User::get($userId);
        if (!$user) {
            throw new \Exception('会员不存在');
        }
        $salt = \fast\Random::alnum(6);
        $user->pay_salt = $salt;
        $user->pay_password = self::encryptPayPassword($password, $salt);
        $user->save();
        return true;
    }

    /**
     * 校验资金密码
     */
    public static function checkPayPassword($userId, $password)
    {
        $user = User::get($userId);
        if (!$user || empty($user['pay_password'])) {
            throw new \Exception('请先设置资金密码');
        }
        if ($user['pay_password'] !== self::encryptPayPassword($password, $user['pay_salt'])) {
            throw new \Exception('资金密码不正确');
        }
        return true;
    }

    /**
     * 账户列表(自动初始化三个币种账户)
     */
    public static function accounts($userId)
    {
        $exists = Account::where('user_id', $userId)->column('currency');
        foreach (['JPY', 'USD', 'USDT'] as $currency) {
            if (!in_array($currency, $exists)) {
                Account::create([
                    'user_id'    => $userId,
                    'currency'   => $currency,
                    'balance'    => 0,
                    'frozen'     => 0,
                    'updatetime' => time(),
                ]);
            }
        }
        return Account::where('user_id', $userId)->select();
    }

    /**
     * 单个账户
     */
    public static function account($userId, $currency)
    {
        $account = Account::get(['user_id' => $userId, 'currency' => $currency]);
        if (!$account) {
            $account = Account::create([
                'user_id'    => $userId,
                'currency'   => $currency,
                'balance'    => 0,
                'frozen'     => 0,
                'updatetime' => time(),
            ]);
        }
        return $account;
    }

    /**
     * 充值申请(待后台审核后入账)
     */
    public static function deposit($userId, $currency, $amount, $method = 'bank', $extra = [])
    {
        if ($amount <= 0) {
            throw new \Exception('充值金额必须大于0');
        }
        return Transaction::create(self::buildData('D', 'deposit', $userId, $currency, $amount, $method, $extra));
    }

    /**
     * 提现申请(冻结资金,待后台审核)
     */
    public static function withdraw($userId, $currency, $amount, $method = 'bank', $extra = [])
    {
        if ($amount <= 0) {
            throw new \Exception('提现金额必须大于0');
        }
        $account = self::account($userId, $currency);
        if ($account['balance'] < $amount) {
            throw new \Exception('可用余额不足');
        }
        Db::startTrans();
        try {
            $account->balance = $account['balance'] - $amount;
            $account->frozen = $account['frozen'] + $amount;
            $account->save();
            $transaction = Transaction::create(self::buildData('W', 'withdraw', $userId, $currency, $amount, $method, $extra));
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
        return $transaction;
    }

    /**
     * 站内划转(会员之间,实时到账)
     */
    public static function transfer($userId, $toUserId, $currency, $amount, $remark = '')
    {
        if ($amount <= 0) {
            throw new \Exception('划转金额必须大于0');
        }
        if ($toUserId == $userId) {
            throw new \Exception('不能向自己划转');
        }
        $toUser = User::get($toUserId);
        if (!$toUser) {
            throw new \Exception('收款会员不存在');
        }
        $from = self::account($userId, $currency);
        if ($from['balance'] < $amount) {
            throw new \Exception('可用余额不足');
        }
        $to = self::account($toUserId, $currency);
        Db::startTrans();
        try {
            $from->balance = $from['balance'] - $amount;
            $from->save();
            $to->balance = $to['balance'] + $amount;
            $to->save();
            Transaction::create([
                'sn'           => self::sn('R'),
                'user_id'      => $userId,
                'type'         => 'transfer',
                'method'       => 'internal',
                'currency'     => $currency,
                'amount'       => $amount,
                'fee'          => 0,
                'to_user_id'   => $toUserId,
                'status'       => 'success',
                'remark'       => $remark,
                'checktime'    => time(),
                'createtime'   => time(),
                'updatetime'   => time(),
            ]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
        return true;
    }

    /**
     * 货币划转（同一账户内两种货币按汇率互换，如 USD -> JPY）
     *
     * @param int    $userId       会员ID
     * @param string $fromCurrency 转出币种
     * @param string $toCurrency   转入币种
     * @param float  $amount       转出金额
     * @return float 实际到账金额
     * @throws \Exception
     */
    public static function exchange($userId, $fromCurrency, $toCurrency, $amount)
    {
        $rates = config('rate.rates');
        if (!$rates || !isset($rates[$fromCurrency]) || !isset($rates[$toCurrency])) {
            throw new \Exception('不支持的币种');
        }
        if ($fromCurrency == $toCurrency) {
            throw new \Exception('转出转入币种不能相同');
        }
        if ($amount <= 0) {
            throw new \Exception('划转金额必须大于0');
        }
        $from = self::account($userId, $fromCurrency);
        if ($from['balance'] < $amount) {
            throw new \Exception('可用余额不足');
        }
        // 以 JPY 为基准换算：toAmount = amount * rate(from) / rate(to)
        $toAmount = round($amount * $rates[$fromCurrency] / $rates[$toCurrency], 2);
        if ($toAmount <= 0) {
            throw new \Exception('换算后金额过小');
        }

        $to = self::account($userId, $toCurrency);
        Db::startTrans();
        try {
            $from->balance = $from['balance'] - $amount;
            $from->save();
            $to->balance = $to['balance'] + $toAmount;
            $to->save();
            // 一出、一入两条流水，type=exchange，remark 互相注明对方币种与金额
            Transaction::create([
                'sn'         => self::sn('E'),
                'user_id'    => $userId,
                'type'       => 'exchange',
                'method'     => 'internal',
                'currency'   => $fromCurrency,
                'amount'     => $amount,
                'fee'        => 0,
                'status'     => 'success',
                'remark'     => '兑换为 ' . $toCurrency . ' ' . $toAmount,
                'checktime'  => time(),
                'createtime' => time(),
                'updatetime' => time(),
            ]);
            Transaction::create([
                'sn'         => self::sn('E'),
                'user_id'    => $userId,
                'type'       => 'exchange',
                'method'     => 'internal',
                'currency'   => $toCurrency,
                'amount'     => $toAmount,
                'fee'        => 0,
                'status'     => 'success',
                'remark'     => '来自 ' . $fromCurrency . ' ' . $amount . ' 兑换',
                'checktime'  => time(),
                'createtime' => time(),
                'updatetime' => time(),
            ]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
        return $toAmount;
    }

    /**
     * 审核充值/提现
     *
     * @param int $id 流水ID
     * @param string $status success|rejected
     * @param string $remark 审核备注
     */
    public static function audit($id, $status, $remark = '')
    {
        $transaction = Transaction::get($id);
        if (!$transaction) {
            throw new \Exception('流水不存在');
        }
        if ($transaction['status'] != 'pending') {
            throw new \Exception('该流水已处理');
        }
        $account = self::account($transaction['user_id'], $transaction['currency']);
        Db::startTrans();
        try {
            if ($status == 'success') {
                if ($transaction['type'] == 'deposit') {
                    $account->balance = $account['balance'] + $transaction['amount'];
                    $account->total_deposit = $account['total_deposit'] + $transaction['amount'];
                } else {
                    // 提现已冻结,审核通过直接扣除冻结金额
                    $account->frozen = max($account['frozen'] - $transaction['amount'], 0);
                    $account->total_withdraw = $account['total_withdraw'] + $transaction['amount'];
                }
            } elseif ($transaction['type'] == 'withdraw') {
                // 驳回提现:解冻退回
                $account->balance = $account['balance'] + $transaction['amount'];
                $account->frozen = max($account['frozen'] - $transaction['amount'], 0);
            }
            $account->updatetime = time();
            $account->save();
            $transaction->status = $status;
            $transaction->checkremark = $remark;
            $transaction->checktime = time();
            $transaction->updatetime = time();
            $transaction->save();
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
        return true;
    }

    /**
     * 组装流水数据
     */
    protected static function buildData($prefix, $type, $userId, $currency, $amount, $method, $extra)
    {
        return [
            'sn'           => self::sn($prefix),
            'user_id'      => $userId,
            'type'         => $type,
            'method'       => $method,
            'currency'     => $currency,
            'amount'       => $amount,
            'fee'          => isset($extra['fee']) ? $extra['fee'] : 0,
            'bank_name'    => isset($extra['bank_name']) ? $extra['bank_name'] : '',
            'branch_name'  => isset($extra['branch_name']) ? $extra['branch_name'] : '',
            'account_no'   => isset($extra['account_no']) ? $extra['account_no'] : '',
            'account_name' => isset($extra['account_name']) ? $extra['account_name'] : '',
            'address'      => isset($extra['address']) ? $extra['address'] : '',
            'txid'         => isset($extra['txid']) ? $extra['txid'] : '',
            'remark'       => isset($extra['remark']) ? $extra['remark'] : '',
            'status'       => 'pending',
            'createtime'   => time(),
            'updatetime'   => time(),
        ];
    }
}

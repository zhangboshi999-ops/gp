<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use fast\Random;
use think\Config;
use think\Validate;

/**
 * 会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['login', 'mobilelogin', 'register', 'resetpwd', 'changeemail', 'changemobile', 'third'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }

    }

    /**
     * 会员中心
     */
    public function index()
    {
        $this->success('', ['welcome' => $this->auth->nickname]);
    }

    /**
     * 会员登录
     *
     * @ApiMethod (POST)
     * @ApiParams (name="account", type="string", required=true, description="账号")
     * @ApiParams (name="password", type="string", required=true, description="密码")
     */
    public function login()
    {
        $account = $this->request->post('account');
        $password = $this->request->post('password');
        if (!$account || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($account, $password);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 手机验证码登录
     *
     * @ApiMethod (POST)
     * @ApiParams (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams (name="captcha", type="string", required=true, description="验证码")
     */
    public function mobilelogin()
    {
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, 'mobilelogin', true)) {
            $this->error(__('Captcha is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            if ($user->status != 'normal') {
                $this->error(__('Account is locked'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注册会员
     *
     * @ApiMethod (POST)
     * @ApiParams (name="username", type="string", required=true, description="用户名")
     * @ApiParams (name="password", type="string", required=true, description="密码")
     * @ApiParams (name="email", type="string", required=false, description="邮箱(选填)")
     * @ApiParams (name="mobile", type="string", required=false, description="手机号(选填)")
     */
    public function register()
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        $email = $this->request->post('email', '');
        $mobile = $this->request->post('mobile', '');
        if (!$username || !$password) {
            $this->error(__('Invalid parameters'));
        }
        if ($email && !Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        $ret = $this->auth->register($username, $password, $email, $mobile, []);
        if ($ret) {
            $this->grantDemoFunds($this->auth->id);
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 退出登录
     * @ApiMethod (POST)
     */
    public function logout()
    {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息
     *
     * @ApiMethod (POST)
     * @ApiParams (name="avatar", type="string", required=true, description="头像地址")
     * @ApiParams (name="username", type="string", required=true, description="用户名")
     * @ApiParams (name="nickname", type="string", required=true, description="昵称")
     * @ApiParams (name="bio", type="string", required=true, description="个人简介")
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $username = $this->request->post('username');
        $nickname = $this->request->post('nickname');
        $bio = $this->request->post('bio');
        $avatar = $this->request->post('avatar', '', 'trim,strip_tags,htmlspecialchars');
        if ($username) {
            $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Username already exists'));
            }
            $user->username = $username;
        }
        if ($nickname) {
            $exists = \app\common\model\User::where('nickname', $nickname)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Nickname already exists'));
            }
            $user->nickname = $nickname;
        }
        if ($avatar) {
            //判断是否匹配config('upload.cdnurl')开头以及当前$SERVER['HTTP_HOST']开头。
            if (preg_match('/^' . preg_quote(config('upload.cdnurl') . '/', '/') . '/i', $avatar)
                || preg_match('/^' . preg_quote(substr(config('upload.savekey'), 0, strpos(config('upload.savekey'), '{')), '/') . '/i', $avatar)
                || preg_match('/^' . preg_quote($_SERVER['HTTP_HOST'] . '/', '/') . '/i', $avatar)) {
                $user->avatar = $avatar;
            }
        }
        $user->bio = $bio;
        $user->save();
        $this->success();
    }

    /**
     * 修改邮箱
     *
     * @ApiMethod (POST)
     * @ApiParams (name="email", type="string", required=true, description="邮箱")
     * @ApiParams (name="captcha", type="string", required=true, description="验证码")
     */
    public function changeemail()
    {
        $user = $this->auth->getUser();
        $email = $this->request->post('email');
        $captcha = $this->request->post('captcha');
        if (!$email || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if (\app\common\model\User::where('email', $email)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail', true);
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->email = 1;
        $user->verification = $verification;
        $user->email = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /**
     * 修改手机号
     *
     * @ApiMethod (POST)
     * @ApiParams (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams (name="captcha", type="string", required=true, description="验证码")
     */
    public function changemobile()
    {
        $user = $this->auth->getUser();
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exists'));
        }
        $result = Sms::check($mobile, $captcha, 'changemobile', true);
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success();
    }

    /**
     * 第三方登录
     *
     * @ApiMethod (POST)
     * @ApiParams (name="platform", type="string", required=true, description="平台名称")
     * @ApiParams (name="code", type="string", required=true, description="Code码")
     */
    public function third()
    {
        $url = url('user/index');
        $platform = $this->request->post("platform");
        $code = $this->request->post("code");
        $config = get_addon_config('third');
        if (!$config || !isset($config[$platform])) {
            $this->error(__('Invalid parameters'));
        }
        $app = new \addons\third\library\Application($config);
        //通过code换access_token和绑定会员
        $result = $app->{$platform}->getUserInfo(['code' => $code]);
        if ($result) {
            $loginret = \addons\third\library\Service::connect($platform, $result);
            if ($loginret) {
                $data = [
                    'userinfo'  => $this->auth->getUserinfo(),
                    'thirdinfo' => $result
                ];
                $this->success(__('Logged in successful'), $data);
            }
        }
        $this->error(__('Operation failed'), $url);
    }

    /**
     * 重置密码
     *
     * @ApiMethod (POST)
     * @ApiParams (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams (name="newpassword", type="string", required=true, description="新密码")
     * @ApiParams (name="captcha", type="string", required=true, description="验证码")
     */
    public function resetpwd()
    {
        $type = $this->request->post("type", "mobile");
        $mobile = $this->request->post("mobile");
        $email = $this->request->post("email");
        $newpassword = $this->request->post("newpassword");
        $captcha = $this->request->post("captcha");
        if (!$newpassword || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        //验证Token
        if (!Validate::make()->check(['newpassword' => $newpassword], ['newpassword' => 'require|regex:\S{6,30}'])) {
            $this->error(__('Password must be 6 to 30 characters'));
        }
        if ($type == 'mobile') {
            if (!Validate::regex($mobile, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Sms::check($mobile, $captcha, 'resetpwd', true);
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Sms::flush($mobile, 'resetpwd');
        } else {
            if (!Validate::is($email, "email")) {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::getByEmail($email);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($email, $captcha, 'resetpwd', true);
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($email, 'resetpwd');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 会员信息(含资产概览)
     */
    public function info()
    {
        $user = $this->auth->getUser();
        $accounts = \app\common\library\Fund::accounts($this->auth->id);
        $positions = \app\common\library\Trade::positions($this->auth->id);

        // 汇率(演示用固定汇率)
        $rate = ['JPY' => 1, 'USD' => 150, 'USDT' => 150];
        $totalBalance = 0;
        $accountList = [];
        foreach ($accounts as $item) {
            $balance = (float)$item['balance'];
            $frozen = (float)$item['frozen'];
            $totalBalance += ($balance + $frozen) * (isset($rate[$item['currency']]) ? $rate[$item['currency']] : 1);
            $accountList[] = [
                'currency' => $item['currency'],
                'balance'  => $balance,
                'frozen'   => $frozen,
            ];
        }
        $marketValue = 0;
        foreach ($positions as $row) {
            $marketValue += $row['market_value'] * (isset($rate[$row['currency']]) ? $rate[$row['currency']] : 1);
        }
        $this->success('', [
            'id'         => $user['id'],
            'username'   => $user['username'],
            'nickname'   => $user['nickname'],
            'avatar'     => $user['avatar'] ? cdnurl($user['avatar'], true) : '',
            'mobile'     => $user['mobile'],
            'email'      => $user['email'],
            'kyc_status' => (int)$user['kyc_status'],
            'has_paypwd' => $user['pay_password'] ? 1 : 0,
            'accounts'   => $accountList,
            'total_asset' => round($totalBalance + $marketValue, 2),
            'total_balance' => round($totalBalance, 2),
            'market_value' => round($marketValue, 2),
        ]);
    }

    /**
     * 提交实名认证
     *
     * @ApiMethod (POST)
     * @ApiParams (name="real_name", type="string", required=true, description="真实姓名")
     * @ApiParams (name="id_number", type="string", required=true, description="证件号码")
     * @ApiParams (name="id_type", type="string", required=false, description="证件类型:idcard/passport/license")
     * @ApiParams (name="front_image", type="string", required=true, description="证件正面")
     * @ApiParams (name="back_image", type="string", required=true, description="证件反面")
     */
    public function verify()
    {
        $realName = $this->request->post('real_name');
        $idNumber = $this->request->post('id_number');
        if (!$realName || !$idNumber) {
            $this->error('请填写姓名与证件号码');
        }
        $user = $this->auth->getUser();
        $exists = \app\common\model\UserVerify::get(['user_id' => $this->auth->id]);
        $data = [
            'real_name'   => $realName,
            'id_number'   => $idNumber,
            'id_type'     => $this->request->post('id_type', 'idcard'),
            'country'     => $this->request->post('country', '日本'),
            'front_image' => $this->request->post('front_image', ''),
            'back_image'  => $this->request->post('back_image', ''),
            'hand_image'  => $this->request->post('hand_image', ''),
            'status'      => 'pending',
            'updatetime'  => time(),
        ];
        if ($exists && $exists['status'] == 'pass') {
            $this->error('已完成实名认证');
        }
        if ($exists) {
            $exists->save($data);
        } else {
            \app\common\model\UserVerify::create(array_merge($data, [
                'user_id'    => $this->auth->id,
                'createtime' => time(),
            ]));
        }
        $user->kyc_status = 1;
        $user->save();
        $this->success('认证资料已提交,等待审核');
    }

    /**
     * 我的实名认证
     */
    public function myverify()
    {
        $row = \app\common\model\UserVerify::get(['user_id' => $this->auth->id]);
        if (!$row) {
            $this->success('', ['status' => 'none', 'kyc_status' => (int)$this->auth->getUser()['kyc_status']]);
        }
        $data = $row->toArray();
        $data['id_number'] = substr($data['id_number'], 0, 4) . '****' . substr($data['id_number'], -4);
        $this->success('', $data);
    }

    /**
     * 新会员赠送演示资金
     */
    protected function grantDemoFunds($userId)
    {
        $demo = ['JPY' => 1000000, 'USD' => 5000, 'USDT' => 1000];
        foreach ($demo as $currency => $amount) {
            $account = \app\common\library\Fund::account($userId, $currency);
            if ((float)$account['balance'] <= 0) {
                $account->balance = $amount;
                $account->updatetime = time();
                $account->save();
            }
        }
    }
}

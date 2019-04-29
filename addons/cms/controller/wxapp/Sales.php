<?php

namespace addons\cms\controller\wxapp;


use app\admin\model\Admin;
use think\Validate;
use think\Exception;
use think\exception\ValidateException;

class Sales extends Base
{
    protected $noNeedLogin = '*';

    /**
     * 小程序登陆
     */
    public function login()
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');

        try {
            $validate = new Validate([
                'username' => 'require|length:3,30',
                'password' => 'require|length:3,30',
            ]);

            if (!$validate->check([
                'username' => $username,
                'password' => $password
            ])) {
                throw new ValidateException($validate->getError());
            }

            $admin = Admin::getByUsername($username);

            if (!$admin) {
                throw new Exception('用户名输入错误');
            }

            if ($admin->password != md5(md5($password) . $admin->salt)) {
                throw new Exception('密码输入错误');
            }

            $this->success('成功', ['user_info' => $admin]);
        } catch (ValidateException $e) {
            $this->error($e->getMessage());
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }


    }

}
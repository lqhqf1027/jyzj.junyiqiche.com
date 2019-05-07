<?php

namespace addons\cms\controller\wxapp;


use app\admin\model\Admin;

use think\Request;

use app\admin\model\OrderDetails;
use app\admin\model\OrderImg;
use think\Db;
use think\exception\PDOException;

use think\Validate;
use think\Exception;
use think\exception\ValidateException;
use think\Config as ThinkConfig;
use fast\Random;
use Upyun\Upyun;
use Upyun\Config;

class Sales extends Base
{
    protected $noNeedLogin = '*';
    protected $config = [

    ];


    /**
     * 小程序登陆
     */
    public function login()
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        $user_id = $this->request->post('user_id');

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

            if ($admin->rule_message != 'message6') {
                throw new Exception('该账户不是销售账号！');
            }

            $user = \app\admin\model\User::get($user_id);

            $admin_id = \app\admin\model\User::getByAdmin_id($admin->id);

            if (!$user->admin_id && !$admin_id) {
                $user->admin_id = $admin->id;

                $user->save();
            } else {
                throw new Exception('该账户已被授权');
            }

            $this->success('成功', ['user_info' => $admin]);
        } catch (ValidateException $e) {
            $this->error($e->getMessage());
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }


    }

    /**
     * 客户列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function client_list()
    {
        $user_id = $this->request->post('user_id');
        $type = $this->request->post('type');
        $page = $this->request->post('page', 1);

        try {
            $rule_message = Admin::get($user_id)->rule_message;

            $list = \app\admin\model\Order::
            field('id,username,phone')
                ->with(['orderdetails' => function ($q) {
                    $q->withField('licensenumber');
                }])
                ->where(function ($q) use ($rule_message, $user_id, $type) {
                    $where = ['type' => $type];
                    if ($rule_message != 'message1') {
                        $where['order.admin_id'] = $user_id;
                    }

                    $q->where($where);

                })
                ->order('id desc')
                ->page($page . ',20')
                ->select();

            $this->success('请求成功', ['client_list' => $list]);
        } catch (PDOException $e) {
            $this->error($e->getMessage());
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

    }

    /**
     *
     * 新增销售单
     */
    public function new_sales_order()
    {
        $user_id = $this->request->post('admin_id');
        $params = $this->request->post("row/a");

//        $this->success($user_id.','.$params);

        if (!$user_id || !$params) {
            $this->error('缺少参数');
        }

        $params['order_createtime'] = strtotime($params['order_createtime']);

        $params['customer_source'] = $params['customer_source'] == '转介绍' ? 'turn_to_introduce' : 'direct_the_guest';

        $params['genderdata'] = $params['genderdata'] == '男' ? 'male' : 'female';

        if ($params) {
            Db::startTrans();
            try {
                $params['admin_id'] = $user_id;
                $rule = $check = [];
                if ($params['customer_source'] === 'turn_to_introduce') {
                    $rule['turn_to_introduce_name'] = 'require';
                    $rule['turn_to_introduce_phone'] = 'require';
                    $rule['turn_to_introduce_card'] = 'require';

                    $check['turn_to_introduce_name'] = $params['turn_to_introduce_name'];
                    $check['turn_to_introduce_phone'] = $params['turn_to_introduce_phone'];
                    $check['turn_to_introduce_card'] = $params['turn_to_introduce_card'];

                }

                if ($params['are_married' == '是']) {
                    $rule['mate_id_cardimages'] = 'require';

                    $check['mate_id_cardimages'] = $params['mate_id_cardimages'];
                }

                if (!empty($check)) {
                    $validate = new Validate($rule);

                    if (!$validate->check($check)) {
                        throw new ValidateException($validate->getError());
                    }
                }

                $params['id_cardimages'] = $params['id_cardimages_positive'] . ',' . $params['id_cardimages_negative'];

                $order = new \app\admin\model\Order;

                if (!$order->allowField(true)->save($params)) {
                    throw new Exception('添加订单失败');
                };

                $params['order_id'] = $order->id;

//                $order_details = new OrderDetails();
//
//                $order_details->allowField(true)->save($params);

                $order_img = new OrderImg();

                $order_img->allowField(true)->save($params);

                Db::commit();
            } catch (ValidateException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }

            $this->success('添加订单成功');

        }

        $this->error('未提交任何信息');


    }

}
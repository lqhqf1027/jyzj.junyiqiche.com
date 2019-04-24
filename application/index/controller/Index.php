<?php

namespace app\index\controller;

use app\admin\model\WxPublicUser;
use app\common\controller\Frontend;
use app\common\library\Token;
use app\admin\model\Order;
use app\admin\model\OrderDetails;
use think\Controller;
use think\Db;
use think\Env;
use think\Exception;
use think\Session;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';


    public function _initialize()
    {
        parent::_initialize();

        $licensenumber = OrderDetails::get(['order_id' => Order::get(['wx_public_user_id' => Session::get('MEMBER')['id']])->id])->licensenumber;
        $this->view->assign(['userInfo'=>Session::get('MEMBER'), 'licensenumber' => $licensenumber]);

    }

    public function index()
    {
        $uid = Session::get('MEMBER');

        return Order::get(['wx_public_user_id' => $uid['id']]) ? $this->view->fetch('apply') : $this->view->fetch();

    }


    /**
     * 查询司机信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDriverInfo()
    {
        if ($this->request->isAjax()) {
            if ($this->request->isPost()) {
                $params = $this->request->post('');
//                return $params['id_card'];
                $id_card = Order::get(['id_card' => $params['id_card']]);
                $licensenumber = OrderDetails::get(['licensenumber' => $params['licensenumber']]);

                if (!$id_card || !$licensenumber) {
                    $this->error('未查询到客户信息');
                };
                if ($licensenumber['order_id'] !== $id_card['id']) {
                    $this->error('车牌号与身份信息不符合');
                } else {
                    $res = Order::field(['id,username,id_card,models_name'])->with(['orderdetails' => function ($q) {
                        $q->withField(['frame_number', 'licensenumber', 'engine_number']);
                    }])->select(['id' => $id_card['id']]);
                    $res = collection($res)->toArray()[0];
                    $this->success('查询成功', '', $res);
                }


            }
            $this->error('非法请求');

        }
        $this->error('非法请求');

    }

    /**
     * 认证君忆司机
     */
    public function applyDriverInfo()
    {

        if ($this->request->isAjax()) {
            if ($this->request->isPost()) {
                $params = $this->request->post('');
                Db::startTrans();
                try {
                    WxPublicUser::update(['id' => Session::get('MEMBER')['id'], 'is_apply' => $params['is_apply']]);
                    Order::update(['id' => $params['order_id'], 'wx_public_user_id' => Session::get('MEMBER')['id']]);
                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage(), '', '');
                }
                $this->success('认证成功', '', '');

            }
            $this->error('非法请求', '', '');


        }
        $this->error('非法请求', '', '');

    }

    /**
     * 已认证公众号君忆司机
     * @return string
     * @throws Exception
     */
    public function apply()
    {
        return $this->view->fetch();
    }

    /**卡片分享
     * @return false|string
     */
    public function sharedata()
    {
        $url = input('urll');//获取当前页面的url，接收请求参数

        $root['url'] = $url;
        //获取access_token，并缓存
        $file = RUNTIME_PATH . '/access_token';//缓存文件名access_token
        $appid = Env::get('wx_public.appid'); // 填写自己的appid
        $secret = Env::get('wx_public.secret'); // 填写自己的appsecret
        $expires = 3600;//缓存时间1个小时
        if (file_exists($file)) {
            $time = filemtime($file);
            if (time() - $time > $expires) {
                $token = null;
            } else {
                $token = file_get_contents($file);
            }
        } else {
            fopen("$file", "w+");
            $token = null;
        }
        if (!$token || strlen($token) < 6) {
            $res = file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $secret . "");

            $res = json_decode($res, true);
            $token = $res['access_token'];
// write('access_token', $token, 3600);
            @file_put_contents($file, $token);
        }

        //获取jsapi_ticket，并缓存
        $file1 = RUNTIME_PATH . '/jsapi_ticket';
        if (file_exists($file1)) {
            $time = filemtime($file1);
            if (time() - $time > $expires) {
                $jsapi_ticket = null;
            } else {
                $jsapi_ticket = file_get_contents($file1);
            }
        } else {
            fopen("$file1", "w+");
            $jsapi_ticket = null;
        }
        if (!$jsapi_ticket || strlen($jsapi_ticket) < 6) {
            $ur = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$token&type=jsapi";
            $res = file_get_contents($ur);
            $res = json_decode($res, true);
            $jsapi_ticket = $res['ticket'];
            @file_put_contents($file1, $jsapi_ticket);
        }

        $timestamp = time();//生成签名的时间戳
        $metas = range(0, 9);
        $metas = array_merge($metas, range('A', 'Z'));
        $metas = array_merge($metas, range('a', 'z'));
        $nonceStr = '';
        for ($i = 0; $i < 16; $i++) {
            $nonceStr .= $metas[rand(0, count($metas) - 1)];//生成签名的随机串
        }

        $string1 = "jsapi_ticket=" . $jsapi_ticket . "&noncestr=" . $nonceStr . "&timestamp=" . $timestamp . "&url=" . $url . "";
        $signature = sha1($string1);
        $root['appid'] = $appid;
        $root['nonceStr'] = $nonceStr;
        $root['timestamp'] = $timestamp;
        $root['signature'] = $signature;

        return json_encode($root);
    }
}

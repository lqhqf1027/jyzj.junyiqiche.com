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

    }

    public function index()
    {
//        return $this->redirect('admin/index/login');
        return $this->view->fetch();
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

    public function applyDriverInfo()
    {

        if ($this->request->isAjax()) {
            if ($this->request->isPost()) {
                $params = $this->request->post('');
                Db::startTrans();
                try {
                    WxPublicUser::update(['id' => Session::get('MEMBER')['id'], 'is_apply' => $params['is_apply']]);
                    Order::update(['id'=>$params['order_id'],'wx_public_user_id' => Session::get('MEMBER')['id']]);
                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage(), '', '');
                }
                $this->success('查询成功', '', '');

            }
            $this->error('非法请求', '', '');


        }
        $this->error('非法请求', '', '');

    }
}

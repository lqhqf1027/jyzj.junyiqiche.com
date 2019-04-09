<?php

namespace app\admin\controller\newcars;

use app\common\controller\Backend;
use think\Db;
use think\Config;
use think\Cache;
use app\common\library\Email;

/**
 * 新车客户信息
 *
 * @icon fa fa-circle-o
 */
class Newcarscustomer extends Backend
{

    /**
     * CarNewUserInfo模型对象
     * @var \app\admin\model\CarNewUserInfo
     */
    protected $model = null;

    protected $userid = null;//用户id
    protected $apikey = null;//apikey
    protected $sign = null;//sign  md5加密
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('SalesOrder');
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {

        $this->loadlang('newcars/newcarscustomer');
        $this->loadlang('order/salesorder');


        return $this->view->fetch();
    }


    /**待提车
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function prepare_lift_car()
    {

        $this->model = model('SalesOrder');
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {

            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username', true);
            $total = $this->model
                ->with(['planacar' => function ($query) {
                    $query->withField('payment,monthly,nperlist,margin,tail_section,gps');
                }, 'admin' => function ($query) {
                    $query->withField(['id', 'nickname', 'avatar']);
                }, 'models' => function ($query) {
                    $query->withField('name,models_name');
                }])
                ->where($where)
                ->where(function ($query) {
                    $query->where("car_new_inventory_id", "not null")
                        ->where("review_the_data", ["=", "take_the_car"], ["=", "take_the_data"], ["=", "inform_the_tube"], ["=", "send_the_car"], "or");
                })
                ->order($sort, $order)
                ->count();


            $list = $this->model
                ->with(['planacar' => function ($query) {
                    $query->withField('payment,monthly,nperlist,margin,tail_section,gps');
                }, 'admin' => function ($query) {
                    $query->withField(['id', 'nickname', 'avatar']);
                }, 'models' => function ($query) {
                    $query->withField('name,models_name');
                }])
                ->where($where)
                ->where(function ($query) {
                    $query->where("car_new_inventory_id", "not null")
                        ->where("review_the_data", ["=", "take_the_car"], ["=", "take_the_data"], ["=", "inform_the_tube"], ["=", "send_the_car"], "or");
                })
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $k => $row) {
                $row->visible(['id', 'order_no', 'username', 'detailed_address', 'createtime', 'financial_name', 'phone', 'difference', 'decorate', 'car_total_price', 'id_card', 'amount_collected', 'downpayment', 'review_the_data']);
                $row->visible(['planacar']);
                $row->getRelation('planacar')->visible(['payment', 'monthly', 'margin', 'nperlist', 'tail_section', 'gps',]);
                $row->visible(['admin']);
                $row->getRelation('admin')->visible(['id', 'nickname', 'avatar']);
                $row->visible(['models']);
                $row->getRelation('models')->visible(['name', 'models_name']);

                if ($list[$k]['models']['models_name']) {
                    $list[$k]['models']['name'] = $list[$k]['models']['name'] . " " . $list[$k]['models']['models_name'];
                }

            }


            $list = collection($list)->toArray();

            foreach ($list as $k => $v) {
                $department = Db::name('auth_group_access')
                    ->alias('a')
                    ->join('auth_group b', 'a.group_id = b.id')
                    ->where('a.uid', $v['admin']['id'])
                    ->value('b.name');
                $list[$k]['admin']['department'] = $department;
            }
            $result = array('total' => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();

    }


    /**已提车
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function already_lift_car()
    {
        $this->model = model('SalesOrder');
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username', true);
            $total = $this->model
                ->with(['planacar' => function ($query) {
                    $query->withField('payment,monthly,nperlist,margin,tail_section,gps');
                }, 'admin' => function ($query) {
                    $query->withField(['id', 'nickname', 'avatar']);
                }, 'models' => function ($query) {
                    $query->withField('name,models_name');
                }, 'newinventory' => function ($query) {
                    $query->withField('frame_number,engine_number,licensenumber,household,4s_shop');
                }])
                ->where($where)
                ->where(function ($query) {
                    $query->where("car_new_inventory_id", "not null")
                        ->where("review_the_data", "the_car");
                })
                ->order($sort, $order)
                ->count();


            $list = $this->model
                ->with(['planacar' => function ($query) {
                    $query->withField('payment,monthly,nperlist,margin,tail_section,gps');
                }, 'admin' => function ($query) {
                    $query->withField(['id', 'nickname', 'avatar']);
                }, 'models' => function ($query) {
                    $query->withField('name,models_name');
                }, 'newinventory' => function ($query) {
                    $query->withField('frame_number,engine_number,licensenumber,household,4s_shop');
                }])
                ->where($where)
                ->where(function ($query) {
                    $query->where("car_new_inventory_id", "not null")
                        ->where("review_the_data", "the_car");
                })
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $row) {
                $row->visible(['id', 'order_no', 'username', 'detailed_address', 'createtime', 'phone', 'financial_name', 'difference', 'decorate', 'car_total_price', 'id_card', 'amount_collected', 'downpayment', 'review_the_data', 'delivery_datetime','mortgage_registration_id']);
                $row->visible(['planacar']);
                $row->getRelation('planacar')->visible(['payment', 'monthly', 'margin', 'nperlist', 'tail_section', 'gps']);
                $row->visible(['admin']);
                $row->getRelation('admin')->visible(['id', 'nickname', 'avatar']);
                $row->visible(['models']);
                $row->getRelation('models')->visible(['name', 'models_name']);
                $row->visible(['newinventory']);
                $row->getRelation('newinventory')->visible(['frame_number', 'licensenumber', 'engine_number', 'household', '4s_shop']);

                if ($list[$k]['models']['models_name']) {
                    $list[$k]['models']['name'] = $list[$k]['models']['name'] . " " . $list[$k]['models']['models_name'];
                }

            }


            $list = collection($list)->toArray();

            foreach ($list as $k => $v) {
                $department = Db::name('auth_group_access')
                    ->alias('a')
                    ->join('auth_group b', 'a.group_id = b.id')
                    ->where('a.uid', $v['admin']['id'])
                    ->value('b.name');
                $list[$k]['admin']['department'] = $department;
            }
            $result = array('total' => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();

    }


    /**
     * 通知销售---补全客户信息进行提车
     */
    public function newcustomer()
    {
        $this->model = model('SalesOrder');

        if ($this->request->isAjax()) {
            $id = $this->request->post('id');

            $result = $this->model->isUpdate(true)->save(['id' => $id, 'review_the_data' => 'take_the_data']);
            //销售员
            $admin_id = $this->model->where('id', $id)->value('admin_id');

            $models_id = $this->model->where('id', $id)->value('models_id');
            //车型
            $models_name = DB::name('models')->where('id', $models_id)->value('name');
            //客户姓名
            $username = $this->model->where('id', $id)->value('username');

            if ($result !== false) {

                $data = newtake_car($models_name, $username);
                // var_dump($data);
                // die;
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = Db::name('admin')->where('id', $admin_id)->value('email');
                $result_s = $email
                    ->to($receiver)
                    ->subject($data['subject'])
                    ->message($data['message'])
                    ->send();
                if ($result_s) {
                    $this->success();
                } else {
                    $this->error('邮箱发送失败');
                }


            } else {
                $this->error('提交失败', null, $result);

            }
        }
    }


    /**资料已补全，提交车管进行提车
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sendcar()
    {
        $this->model = model('SalesOrder');

        if ($this->request->isAjax()) {
            $id = $this->request->post('id');
            $delivery = $this->request->post('delivery');

            $delivery = strtotime($delivery);

            $result = $this->model->isUpdate(true)->save(['id' => $id, 'review_the_data' => 'the_car','delivery_datetime'=>$delivery]);

            if ($result !== false) {


                $new_info = Db::name('sales_order')
                    ->where('id', $id)
                    ->field('username,admin_id,models_id,customer_source')
                    ->find();

                //车型
                $models_name = Db::name('models')->where('id', $new_info['models_id'])->value('name');

                $data = sales_takecar($models_name, $new_info['username']);

                if ($new_info['customer_source'] == 'turn_to_introduce') {
                    $useful_info = Db::name('sales_order')
                        ->where('id', $id)
                        ->field('models_id,admin_id,turn_to_introduce_name as referee_name,turn_to_introduce_phone as referee_phone,turn_to_introduce_card as referee_idcard,username as customer_name,phone as customer_phone')
                        ->find();
                    $useful_info['buy_way'] = '新车';

                    Db::name('referee')->insert($useful_info);

                    $last_id = Db::name('referee')->getLastInsID();

                    Db::name('sales_order')
                        ->where('id', $id)
                        ->setField('referee_id', $last_id);
                }


                $email = new Email();

                $receiver = Db::name('admin')->where('id', $new_info['admin_id'])->value('email');

                $result_s = $email
                    ->to($receiver)
                    ->subject($data['subject'])
                    ->message($data['message'])
                    ->send();
                if ($result_s) {
                    $this->success('', '', 'success');
                } else {
                    $this->error('邮箱发送失败');
                }
                //发送金融按揭专员
                $data_s = financial_takecar($models_name, $new_info['username']);

                $receiver = Db::name('admin')->where('rule_message', 'message27')->value('email');

                $result_ss = $email
                    ->to($receiver)
                    ->subject($data_s['subject'])
                    ->message($data_s['message'])
                    ->send();
                if ($result_ss) {
                    $this->success('', '', 'success');
                } else {
                    $this->error('邮箱发送失败');
                }

                $seventtime = \fast\Date::unixtime('day', -2);
                $newsales = [];
                $month = date("Y-m", $seventtime);
                $day = date('t', strtotime("$month +1 month -1 day"));
                for ($i = 0; $i < 4; $i++) {
                    $months = date("Y-m", $seventtime + (($i + 1) * 86400 * $day));
                    $firstday = strtotime(date('Y-m-01', strtotime($month)));
                    $secondday = strtotime(date('Y-m-01', strtotime($months)));

                    $newtake = Db::name('sales_order')
                        ->where('review_the_data', 'the_car')
                        ->where('delivery_datetime', 'between', [$firstday, $secondday])
                        ->count();

                    //新车销售情况
                    $newsales[$month . '(月)'] = $newtake;

                    $month = date("Y-m", $seventtime + (($i + 1) * 86400 * $day));

                    $day = date('t', strtotime("$months +1 month -1 day"));

                }
                Cache::set('newsales', $newsales);


            } else {
                $this->error('提交失败', null, $result);

            }
        }
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = Db::name('sales_order')
            ->where('id', $ids)
            ->field('mortgage_registration_id,downpayment,service_charge,registry_registration_id,withholding_service')
            ->find();

        $images = Db::name('sales_order')
            ->where('id', $ids)
            ->field('id_cardimages,residence_bookletimages,housingimages,bank_cardimages,deposit_contractimages,guarantee_agreementimages')
            ->find();


        if ($row['mortgage_registration_id']) {
            $mortgage_registration = Db::name('mortgage_registration')
                ->where('id', $row['mortgage_registration_id'])
                ->field('contract_number,other_lines,collect_account')
                ->find();

            $row = array_merge($row, $mortgage_registration);
        }

        if ($row['registry_registration_id']) {
            $registry_registration = Db::name('registry_registration')
                ->where('id', $row['registry_registration_id'])
                ->field('id_card,registered_residence,marry_and_divorceimages,credit_reportimages,halfyear_bank_flowimages,guarantee,
            residence_permitimages,driving_license,residence_permit,renting_contract,company_contractimages,lift_listimages,
            deposit,truth_management_protocolimages,confidentiality_agreementimages,supplementary_contract_agreementimages,explain_situation,
            tianfu_bank_cardimages,crime_promise,buy_rent,customer_query,fengbang_rent,maximum_guarantee_contractimages,transfer_agreement')
                ->find();

            $row = array_merge($row, $registry_registration);
        }

        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $orders = $this->request->post("order/a");
            $registration = $this->request->post("registration/a");
            $params['classification'] = 'new';
            $registration['classification'] = 'new';
            if ($params) {
                try {

                    if ($row['mortgage_registration_id']) {
                        Db::name('mortgage_registration')
                            ->where('id', $row['mortgage_registration_id'])
                            ->update($params);
                    } else {
                        Db::name('mortgage_registration')->insert($params);
                        $orders['mortgage_registration_id'] = Db::name('mortgage_registration')->getLastInsID();

                    }

                    if ($row['registry_registration_id']) {
                        Db::name('registry_registration')
                            ->where('id', $row['registry_registration_id'])
                            ->update($registration);
                    } else {
                        Db::name('registry_registration')->insert($registration);
                        $orders['registry_registration_id'] = Db::name('registry_registration')->getLastInsID();
                    }
                    $result = Db::name('sales_order')
                        ->where('id', $ids)
                        ->update($orders);

                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                } catch (\think\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign([
            "row" => $row,
            'images' => $images
        ]);

        return $this->view->fetch();
    }



}

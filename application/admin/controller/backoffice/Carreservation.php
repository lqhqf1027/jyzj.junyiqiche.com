<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/8/17
 * Time: 12:25
 */

namespace app\admin\controller\backoffice;

use app\admin\model\SalesOrder;
use app\common\controller\Backend;
use think\Db;
use app\common\library\Email;

class Carreservation extends Backend
{
    /**
     * @var null
     */
    protected $model = null;
    protected $dataLimitField = "backoffice_id"; //数据关联字段,当前控制器对应的模型表中必须存在该字段
    protected $dataLimit = 'auth'; //表示显示当前自己和所有子级管理员的所有数据

    protected $noNeedRight = ['*'];


    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        $this->view->assign([
            'total' => Db::name('sales_order')
                ->where('review_the_data', '=', 'inhouse_handling')
                ->count(),

            'total1' => Db::name('rental_order')
                ->where('review_the_data', '=', '')
                ->count(),
            'total2' => Db::name('second_sales_order')
                ->where('review_the_data', '=', 'is_reviewing_true')
                ->count(),
            'total3' => Db::name('full_parment_order')
                ->where('review_the_data', '=', 'inhouse_handling')
                ->count(),
            'total4' => Db::name('second_full_order')
                ->where('review_the_data', '=', 'inhouse_handling')
                ->count(),
        ]);
        return $this->view->fetch();
    }


    /**
     * 新车录入实际订车金额
     * @return string|\think\response\Json
     * @throws \think\Exception
     */
    public function newcarEntry()
    {
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $this->model = model('SalesOrder');

            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username', true);

            $can_use_id = $this->getUserId();

            $total = $this->model
                ->with(['planacar' => function ($query) {
                    $query->withField('payment,monthly,nperlist,margin,tail_section,gps,total_payment');
                }, 'admin' => function ($query) {
                    $query->withField('nickname');
                }, 'models' => function ($query) {
                    $query->withField('name,models_name');
                }, 'newinventory' => function ($query) {
                    $query->withField('frame_number,engine_number,household,4s_shop');
                }])
                ->where($where)
                ->where(function ($query) use ($can_use_id){
                    $back = null;
                    if(in_array($this->auth->id, $can_use_id['backoffice'])){
                        $back = $this->auth->id;
                    }else if(in_array($this->auth->id, $can_use_id['manager'])){
                        $back = ['in',$this->sales_name()];
                    }else if(in_array($this->auth->id, $can_use_id['admin'])){
                        $back = ['neq','null'];
                    }
                    $query->where([
                        'backoffice_id' => $back,
                        "review_the_data"=>["NEQ", "send_to_internal"]
                    ]);
                })
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->with(['planacar' => function ($query) {
                    $query->withField('payment,monthly,nperlist,margin,tail_section,gps');
                }, 'admin' => function ($query) {
                    $query->withField('nickname');
                }, 'models' => function ($query) {
                    $query->withField('name,models_name');
                }, 'newinventory' => function ($query) {
                    $query->withField('frame_number,engine_number,household,4s_shop');
                }])
                ->where($where)
                ->where(function ($query) use ($can_use_id){
                    $back = null;
                    if(in_array($this->auth->id, $can_use_id['backoffice'])){
                        $back = $this->auth->id;
                    }else if(in_array($this->auth->id, $can_use_id['manager'])){
                        $back = ['in',$this->sales_name()];
                    }else if(in_array($this->auth->id, $can_use_id['admin'])){
                        $back = ['neq','null'];
                    }
                    $query->where([
                        'backoffice_id' => $back,
                        "review_the_data"=>["NEQ", "send_to_internal"]
                    ]);
                })
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $k => $row) {

                $row->visible(['id', 'order_no', 'username', 'createtime', 'city', 'detailed_address', 'phone', 'id_card', 'car_total_price', 'downpayment', 'review_the_data']);
                $row->visible(['planacar']);
                $row->getRelation('planacar')->visible(['payment', 'monthly', 'margin', 'nperlist', 'tail_section', 'gps']);
                $row->visible(['admin']);
                $row->getRelation('admin')->visible(['nickname']);
                $row->visible(['models']);
                $row->getRelation('models')->visible(['name', 'models_name']);
                $row->visible(['newinventory']);
                $row->getRelation('newinventory')->visible(['frame_number', 'engine_number', 'household', '4s_shop']);

                if ($list[$k]['models']['models_name']) {
                    $list[$k]['models']['name'] = $list[$k]['models']['name'] . " " . $list[$k]['models']['models_name'];
                }

            }

            $list = collection($list)->toArray();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * 二手车录入实际订车金额
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function secondcarEntry()
    {
        $this->model = new \app\admin\model\SecondSalesOrder;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username', true);

            $can_use_id = $this->getUserId();

            $total = $this->model
                ->with(['plansecond' => function ($query) {
                    $query->withField('companyaccount,newpayment,monthlypaymen,periods,totalprices,bond,tailmoney');
                }, 'admin' => function ($query) {
                    $query->withField('nickname');
                }, 'models' => function ($query) {
                    $query->withField('name,models_name');
                }])
                ->where($where)
                ->where(function ($query) use ($can_use_id){
                    $back = null;
                    if(in_array($this->auth->id, $can_use_id['backoffice'])){
                        $back = $this->auth->id;
                    }else if(in_array($this->auth->id, $can_use_id['manager'])){
                        $back = ['in',$this->sales_name()];
                    }else if(in_array($this->auth->id, $can_use_id['admin'])){
                        $back = ['neq','null'];
                    }
                    $query->where([
                        'backoffice_id' => $back,
                        "review_the_data"=>["NEQ", "is_reviewing"]
                    ]);
                })
                ->order($sort, $order)
                ->count();


            $list = $this->model
                ->with(['plansecond' => function ($query) {
                    $query->withField('companyaccount,newpayment,monthlypaymen,periods,totalprices,bond,tailmoney');
                }, 'admin' => function ($query) {
                    $query->withField('nickname');
                }, 'models' => function ($query) {
                    $query->withField('name,models_name');
                }])
                ->where($where)
                ->where(function ($query) use ($can_use_id){
                    $back = null;
                    if(in_array($this->auth->id, $can_use_id['backoffice'])){
                        $back = $this->auth->id;
                    }else if(in_array($this->auth->id, $can_use_id['manager'])){
                        $back = ['in',$this->sales_name()];
                    }else if(in_array($this->auth->id, $can_use_id['admin'])){
                        $back = ['neq','null'];
                    }
                    $query->where([
                        'backoffice_id' => $back,
                        "review_the_data"=>["NEQ", "is_reviewing"]
                    ]);
                })
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $k => $row) {
                $row->visible(['id', 'order_no', 'username', 'city', 'detailed_address', 'createtime', 'phone', 'id_card', 'amount_collected', 'downpayment', 'review_the_data']);
                $row->visible(['plansecond']);
                $row->getRelation('plansecond')->visible(['newpayment', 'companyaccount', 'monthlypaymen', 'periods', 'totalprices', 'bond', 'tailmoney',]);
                $row->visible(['admin']);
                $row->getRelation('admin')->visible(['nickname']);
                $row->visible(['models']);
                $row->getRelation('models')->visible(['name', 'models_name']);

                if ($list[$k]['models']['models_name']) {
                    $list[$k]['models']['name'] = $list[$k]['models']['name'] . " " . $list[$k]['models']['models_name'];
                }

            }

            $list = collection($list)->toArray();

            $result = array('total' => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();

    }


    /**
     * 全款车录入实际订车金额
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function fullcarEntry()
    {
        $this->model = new \app\admin\model\FullParmentOrder;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username', true);

            $can_use_id = $this->getUserId();

            $total = $this->model
                ->with(['planfull' => function ($query) {
                    $query->withField('full_total_price');
                }, 'admin' => function ($query) {
                    $query->withField('nickname');
                }, 'models' => function ($query) {
                    $query->withField('name,models_name');
                }])
                ->where($where)
                ->where(function ($query) use ($can_use_id){
                    $back = null;
                    if(in_array($this->auth->id, $can_use_id['backoffice'])){
                        $back = $this->auth->id;
                    }else if(in_array($this->auth->id, $can_use_id['manager'])){
                        $back = ['in',$this->sales_name()];
                    }else if(in_array($this->auth->id, $can_use_id['admin'])){
                        $back = ['neq','null'];
                    }
                    $query->where([
                        'backoffice_id' => $back,
                        "review_the_data"=>["NEQ", "send_to_internal"]
                    ]);
                })
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['planfull' => function ($query) {
                    $query->withField('full_total_price');
                }, 'admin' => function ($query) {
                    $query->withField('nickname');
                }, 'models' => function ($query) {
                    $query->withField('name,models_name');
                }])
                ->where($where)
                ->where(function ($query) use ($can_use_id){
                    $back = null;
                    if(in_array($this->auth->id, $can_use_id['backoffice'])){
                        $back = $this->auth->id;
                    }else if(in_array($this->auth->id, $can_use_id['manager'])){
                        $back = ['in',$this->sales_name()];
                    }else if(in_array($this->auth->id, $can_use_id['admin'])){
                        $back = ['neq','null'];
                    }
                    $query->where([
                        'backoffice_id' => $back,
                        "review_the_data"=>["NEQ", "send_to_internal"]
                    ]);
                })
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();


            foreach ($list as $k => $row) {
                $row->visible(['id', 'order_no', 'detailed_address', 'city', 'username', 'genderdata', 'createtime', 'phone', 'id_card', 'amount_collected', 'review_the_data']);
                $row->visible(['planfull']);
                $row->getRelation('planfull')->visible(['full_total_price']);
                $row->visible(['admin']);
                $row->getRelation('admin')->visible(['nickname']);
                $row->visible(['models']);
                $row->getRelation('models')->visible(['name', 'models_name']);

                if ($list[$k]['models']['models_name']) {
                    $list[$k]['models']['name'] = $list[$k]['models']['name'] . " " . $list[$k]['models']['models_name'];
                }

            }

            $list = collection($list)->toArray();

            $result = array('total' => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * 租车
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function rentalcarEntry()
    {
        $this->model = new \app\admin\model\RentalOrder;
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username', true);

            $can_use_id = $this->getUserId();

            $total = $this->model
                    ->with(['admin' => function ($query) {
                        $query->withField('nickname');
                    }, 'models' => function ($query) {
                        $query->withField('name,models_name');
                    }, 'carrentalmodelsinfo' => function ($query) {
                        $query->withField('licenseplatenumber,vin');
                    }])
                    ->where($where)
                    ->where(function ($query) use ($can_use_id){
                        $back = null;
                        if(in_array($this->auth->id, $can_use_id['backoffice'])){
                            $back = $this->auth->id;
                        }else if(in_array($this->auth->id, $can_use_id['manager'])){
                            $back = ['in',$this->sales_name()];
                        }else if(in_array($this->auth->id, $can_use_id['admin'])){
                            $back = ['neq','null'];
                        }
                        $query->where([
                            'backoffice_id' => $back
                        ]);
                    })
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with(['admin' => function ($query) {
                        $query->withField('nickname');
                    }, 'models' => function ($query) {
                        $query->withField('name,models_name');
                    }, 'carrentalmodelsinfo' => function ($query) {
                        $query->withField('licenseplatenumber,vin');
                    }])
                    ->where($where)
                    ->where(function ($query) use ($can_use_id){
                        $back = null;
                        if(in_array($this->auth->id, $can_use_id['backoffice'])){
                            $back = $this->auth->id;
                        }else if(in_array($this->auth->id, $can_use_id['manager'])){
                            $back = ['in',$this->sales_name()];
                        }else if(in_array($this->auth->id, $can_use_id['admin'])){
                            $back = ['neq','null'];
                        }
                        $query->where([
                            'backoffice_id' => $back
                        ]);
                    })
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            

            foreach ($list as $k => $v) {
                $v->visible(['id', 'order_no', 'username', 'phone', 'id_card', 'cash_pledge', 'rental_price', 'tenancy_term', 'genderdata', 'review_the_data', 'createtime', 'delivery_datetime']);
                $v->visible(['admin']);
                $v->getRelation('admin')->visible(['nickname']);
                $v->visible(['models']);
                $v->getRelation('models')->visible(['name', 'models_name']);
                $v->visible(['carrentalmodelsinfo']);
                $v->getRelation('carrentalmodelsinfo')->visible(['licenseplatenumber', 'vin']);

                if ($list[$k]['models']['models_name']) {
                    $list[$k]['models']['name'] = $list[$k]['models']['name'] . " " . $list[$k]['models']['models_name'];
                }

            }

            $list = collection($list)->toArray();

            $result = array('total' => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();

    }

    /**
     * 全款车录入实际订车金额
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function secondfullcarEntry()
    {
        $this->model = new \app\admin\model\SecondFullOrder;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username', true);

            $can_use_id = $this->getUserId();

            $total = $this->model
                    ->with(['plansecondfull' => function ($query) {
                        $query->withField('totalprices');
                    }, 'admin' => function ($query) {
                        $query->withField('nickname');
                    }, 'models' => function ($query) {
                        $query->withField('name,models_name');
                    }])
                    ->where($where)
                    ->where(function ($query) use ($can_use_id){
                        $back = null;
                        if(in_array($this->auth->id, $can_use_id['backoffice'])){
                            $back = $this->auth->id;
                        }else if(in_array($this->auth->id, $can_use_id['manager'])){
                            $back = ['in',$this->sales_name()];
                        }else if(in_array($this->auth->id, $can_use_id['admin'])){
                            $back = ['neq','null'];
                        }
                        $query->where([
                            'backoffice_id' => $back,
                            "review_the_data"=>["NEQ", "send_to_internal"]
                        ]);
                    })
                    ->order($sort, $order)
                    ->count();

                $list = $this->model
                    ->with(['plansecondfull' => function ($query) {
                        $query->withField('totalprices');
                    }, 'admin' => function ($query) {
                        $query->withField('nickname');
                    }, 'models' => function ($query) {
                        $query->withField('name,models_name');
                    }])
                    ->where($where)
                    ->where(function ($query) use ($can_use_id){
                        $back = null;
                        if(in_array($this->auth->id, $can_use_id['backoffice'])){
                            $back = $this->auth->id;
                        }else if(in_array($this->auth->id, $can_use_id['manager'])){
                            $back = ['in',$this->sales_name()];
                        }else if(in_array($this->auth->id, $can_use_id['admin'])){
                            $back = ['neq','null'];
                        }
                        $query->where([
                            'backoffice_id' => $back,
                            "review_the_data"=>["NEQ", "send_to_internal"]
                        ]);
                    })
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();


            foreach ($list as $k => $row) {
                $row->visible(['id', 'order_no', 'detailed_address', 'city', 'username', 'genderdata', 'createtime', 'phone', 'id_card', 'amount_collected', 'review_the_data']);
                $row->visible(['plansecondfull']);
                $row->getRelation('plansecondfull')->visible(['totalprices']);
                $row->visible(['admin']);
                $row->getRelation('admin')->visible(['nickname']);
                $row->visible(['models']);
                $row->getRelation('models')->visible(['name', 'models_name']);

                if ($list[$k]['models']['models_name']) {
                    $list[$k]['models']['name'] = $list[$k]['models']['name'] . " " . $list[$k]['models']['models_name'];
                }

            }

            $list = collection($list)->toArray();

            $result = array('total' => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 获取销售经理的全部内勤
     * @return array
     */
    public function sales_name()
    {
        $message = Db::name('admin')
        ->where('id',$this->auth->id)
        ->value('rule_message');

        switch ($message){
            case 'message3':
                return Custominfotabs::getAdmin([
                    'rule_message'=>'message13'
                ]);
            case 'message4':
                return Custominfotabs::getAdmin([
                    'rule_message'=>'message20'
                ]);
            case 'message22':
                return Custominfotabs::getAdmin([
                    'rule_message'=>'message24'
                ]);
        }
    }


    /**
     * 得到操作员ID
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserId()
    {
        $arr = array();
        $arr['admin'] = array();
        $arr['backoffice'] = array();
        $arr['manager'] = Db::name('admin')
            ->where('rule_message', 'in', ['message3', 'message4', 'message22'])
            ->column('id');
        $admin = Db::name("admin")
            ->where("rule_message", "in", ["message1", "message21", "message3", "message4", "message22"])
            ->field("id")
            ->select();

        foreach ($admin as $v) {

            array_push($arr['admin'], $v['id']);
        }


        $sale = Db::name("admin")
            ->where("rule_message", "in", ["message13", "message20", "message24"])
            ->field("id")
            ->select();
        foreach ($sale as $v) {

            array_push($arr['backoffice'], $v['id']);
        }
        return $arr;
    }


    /**
     * 新车编辑实际录入金额
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function newactual_amount($ids = null)
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");

            //得到首期款
            $downpayment = Db::name("sales_order")
                ->where("id", $ids)
                ->field("downpayment")
                ->find()['downpayment'];

            //得到差额
            $difference = floatval($downpayment) - floatval($params['amount_collected']);

            if ($difference < 0) {
                $difference = 0;
            }


            $result = Db::name("sales_order")
                ->where("id", $ids)
                ->update([
                    'amount_collected' => $params['amount_collected'],
                    'decorate' => $params['decorate'],
                    'review_the_data' => 'send_car_tube',
                    'difference' => $difference
                ]);


            if ($result !== false) {

//                $channel = "demo-new_amount";
//                $content = "内勤提交的新车单，请及时进行车辆处理";
//                goeary_push($channel, $content);

                $data = Db::name("sales_order")->where('id', $ids)->find();
                //车型
                $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
                //销售员
                $admin_name = DB::name('admin')->where('id', $data['admin_id'])->value('nickname');
                //客户姓名
                $username = $data['username'];

                $data = newcar_inform($models_name, $admin_name, $username);
                // var_dump($data);
                // die;
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = DB::name('admin')->where('rule_message', "message14")->value('email');
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
                $this->error();
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        return $this->view->fetch();
    }


    /**
     * 二手车编辑实际录入金额
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function secondactual_amount($ids = null)
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");

            //得到首期款
            $downpayment = Db::name("second_sales_order")
                ->where("id", $ids)
                ->field("downpayment")
                ->find()['downpayment'];

            //得到差额
            $difference = floatval($downpayment) - floatval($params['amount_collected']);

            if ($difference < 0) {
                $difference = 0;
            }


            $result = Db::name("second_sales_order")
                ->where("id", $ids)
                ->update([
                    'amount_collected' => $params['amount_collected'],
                    'decorate' => $params['decorate'],
                    'review_the_data' => 'send_car_tube',
                    'difference' => $difference
                ]);


            if ($result !== false) {

//                $channel = "demo-second_amount";
//                $content = "内勤提交的二手车单，请及时进行车辆处理";
//                goeary_push($channel, $content);

                $data = Db::name("second_sales_order")->where('id', $ids)->find();
                //车型
                $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
                //销售员
                $admin_id = $data['admin_id'];
                $admin_name = DB::name('admin')->where('id', $data['admin_id'])->value('nickname');
                //客户姓名
                $username = $data['username'];

                $data = secondcar_inform($models_name, $admin_name, $username);
                // var_dump($data);
                // die;
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = DB::name('admin')->where('rule_message', "message14")->value('email');
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
                $this->error();
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        return $this->view->fetch();
    }


    /**
     * 全款新车编辑实际录入金额
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function fullactual_amount($ids = null)
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");

            $result = Db::name("full_parment_order")
                ->where("id", $ids)
                ->update([
                    'amount_collected' => $params['amount_collected'],
                    'decorate' => $params['decorate'],
                    'review_the_data' => 'is_reviewing_true',

                ]);


            if ($result !== false) {

//                $channel = "demo-fullcar_amount";
//                $content = "内勤提交的全款车单，请及时进行车辆处理";
//                goeary_push($channel, $content);

                $data = Db::name("full_parment_order")->where('id', $ids)->find();
                //车型
                $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
                //销售员
                $admin_id = $data['admin_id'];
                $admin_name = DB::name('admin')->where('id', $data['admin_id'])->value('nickname');
                //客户姓名
                $username = $data['username'];

                $data = fullcar_inform($models_name, $admin_name, $username);
                // var_dump($data);
                // die;
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = DB::name('admin')->where('rule_message', "message14")->value('email');
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
                $this->error();
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        return $this->view->fetch();
    }


    /**
     * 租车编辑实际录入金额
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function rentalactual_amount($ids = null)
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");

            //得到首期款
            $downpayment = Db::name("second_sales_order")
                ->where("id", $ids)
                ->field("downpayment")
                ->find()['downpayment'];

            //得到差额
            $difference = floatval($downpayment) - floatval($params['amount_collected']);

            if ($difference < 0) {
                $difference = 0;
            }


            $result = Db::name("second_sales_order")
                ->where("id", $ids)
                ->update([
                    'amount_collected' => $params['amount_collected'],
                    'decorate' => $params['decorate'],
                    'review_the_data' => 'send_car_tube',
                    'difference' => $difference
                ]);


            if ($result !== false) {

//                $channel = "demo-second_amount";
//                $content = "内勤提交的二手车单，请及时进行车辆处理";
//                goeary_push($channel, $content);

                $data = Db::name("second_sales_order")->where('id', $ids)->find();
                //车型
                $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
                //销售员
                $admin_id = $data['admin_id'];
                $admin_name = DB::name('admin')->where('id', $data['admin_id'])->value('nickname');
                //客户姓名
                $username = $data['username'];

                $data = secondcar_inform($models_name, $admin_name, $username);
                // var_dump($data);
                // die;
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = DB::name('admin')->where('rule_message', "message14")->value('email');
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
                $this->error();
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        return $this->view->fetch();
    }


    /**
     * 全款二手车编辑实际录入金额
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function secondfullactual_amount($ids = null)
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");

            $result = Db::name("second_full_order")
                ->where("id", $ids)
                ->update([
                    'amount_collected' => $params['amount_collected'],
                    'decorate' => $params['decorate'],
                    'review_the_data' => 'is_reviewing_true',

                ]);


            if ($result !== false) {

//                $channel = "demo-secondfullcar_amount";
//                $content = "内勤提交的全款二手车单，请及时进行车辆处理";
//                goeary_push($channel, $content);

                $data = Db::name("second_full_order")->where('id', $ids)->find();
                //车型
                $models_name = Db::name('models')->where('id', $data['models_id'])->value('name');
                //销售员
                $admin_id = $data['admin_id'];
                $admin_name = Db::name('admin')->where('id', $data['admin_id'])->value('nickname');
                //客户姓名
                $username = $data['username'];

                $data = secondfullcar_amount($models_name, $admin_name, $username);
                
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = Db::name('admin')->where('rule_message', "message15")->value('email');
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
                $this->error();
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        return $this->view->fetch();
    }


    public function amountPublic($ids,$type)
    {
        $tables = null;
        switch ($type){
            case 'secondfull':
                $tables = 'second_full_order';
                break;
            case 'rental':
                $tables = 'second_sales_order';
                break;
            case 'full':
                $tables = 'full_parment_order';
                break;
            case 'second':
                $tables = 'second_sales_order';
                break;
            case 'new':
                $tables = 'sales_order';
                break;
        }

        $data = Db::name($tables)->where('id', $ids)->find();

        //车型
        $models_name = Db::name('models')->where('id', $data['models_id'])->value('name');

        $admin_name = Db::name('admin')->where('id', $data['admin_id'])->value('nickname');

        $data = secondfullcar_amount($models_name, $admin_name, $data['username']);

        $email = new Email;
        // $receiver = "haoqifei@cdjycra.club";
        $receiver = Db::name('admin')->where('rule_message', $type=='secondfull'?'message15':'message14')->value('email');
        $result_s = $email
            ->to($receiver)
            ->subject($data['subject'])
            ->message($data['message'])
            ->send();

        return $result_s;
    }

}
<?php

namespace app\admin\controller\salesmanagement;

use app\admin\validate\rental\Order;
use app\common\controller\Backend;
use app\admin\model\PlanAcar as planAcarModel;
use app\admin\model\Models as modelsModel;
use app\admin\model\SalesOrder as salesOrderModel;
use app\admin\controller\Sharedetailsdatas;
use fast\Tree;
use think\Db;
use think\Config;
use app\common\library\Email;
use think\Session;
use think\Cache;

/**
 * 订单列管理
 *
 * @icon fa fa-circle-o
 */
class Orderlisttabs extends Backend
{

    /**
     * Ordertabs模型对象
     * @var \app\admin\model\Ordertabs
     */
    protected $model = null;
    // protected $multiFields = 'fulldel';
    protected $noNeedRight = ['*'];


    protected $dataLimitField = 'admin_id'; //数据关联字段,当前控制器对应的模型表中必须存在该字段
    protected $dataLimit = 'auth'; //表示显示当前自己和所有子级管理员的所有数据
    // protected  $dataLimit = 'false'; //表示显示当前自己和所有子级管理员的所有数据
    // protected $relationSearch = true;
    static protected $token = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('SalesOrder');
        $this->view->assign('genderdataList', $this->model->getGenderdataList());
        $this->view->assign('customerSourceList', $this->model->getCustomerSourceList());
        $this->view->assign('reviewTheDataList', $this->model->getReviewTheDataList());
    }

    /**
     * 得到可行管理员ID
     * @return array
     */
    public function getUserId()
    {
        $this->model = model("Admin");
       
        $backArray = array();
        $backArray['admin'] = array();
        $backArray['manager'] =Db::name('admin')
        ->where('rule_message','in',['message3','message4','message22'])
        ->column('id');

        $superAdmin = $this->model->where("rule_message", "message21")
            ->field("id")
            ->select();

        foreach ($superAdmin as $value) {
            array_push($backArray['admin'], $value['id']);
        }

        return $backArray;
    }

    /**
     * 销售经理获取自己部门顾问
     * @return array
     */
    public function get_manager()
    {
         $message = Db::name('admin')
         ->where('id',$this->auth->id)
         ->value('rule_message');

         switch ($message){
            case 'message3':
                return Db::name('admin')
                    ->where('rule_message','message8')
                    ->column('id');
            case 'message4':
                return Db::name('admin')
                    ->where('rule_message','message9')
                    ->column('id');
            case 'message22':
                return Db::name('admin')
                    ->where('rule_message','message23')
                    ->column('id');
         }
    }

    /**
     * 统计
     * @return array
     */
    public function getCount($table, $neq, $review_the_data)
    {
        $canUseId = $this->getUserId();
        //部门经理
        $get_id = null;
        if(in_array($this->auth->id,$canUseId['manager'])){
            $get_id = $this->get_manager();
        }

        return $count = Db::name($table)->where(function ($query) use ($canUseId,$get_id,$neq,$review_the_data) {
                if(in_array($this->auth->id,$canUseId['manager'])){
                    $query->where('admin_id', 'in', $get_id);

                }else if(in_array($this->auth->id,$canUseId['admin'])){
                    
                }
                else{
                    $query->where('admin_id', $this->auth->id);

                }
                $query->where('review_the_data', $neq, $review_the_data);
                    
            })->count();
    }

    public function index()
    {

        $canUseId = $this->getUserId();
        //部门经理
        $get_id = null;
        if(in_array($this->auth->id,$canUseId['manager'])){
            $get_id = $this->get_manager();
        }

        $this->view->assign([
            'total'  => $this->getCount('sales_order', 'NEQ', 'the_car'),
            'total1' => $this->getCount('sales_order', '=', 'the_car'),
            'total2' => $this->getCount('rental_order', 'NEQ', 'for_the_car'),
            'total3' => $this->getCount('rental_order', '=', 'for_the_car'),
            'total4' => $this->getCount('second_sales_order', 'NEQ', 'the_car'),
            'total5' => $this->getCount('second_sales_order', '=', 'the_car'),
            'total6' => $this->getCount('full_parment_order', 'NEQ', 'for_the_car'),
            'total7' => $this->getCount('full_parment_order', '=', 'for_the_car'),
            'total8' => $this->getCount('second_full_order', 'NEQ', 'for_the_car'),
            'total9' => $this->getCount('second_full_order', '=', 'for_the_car'),
        ]);

        return $this->view->fetch();

    }

    /**
     * 以租代购（新车）
     * @return string|\think\response\Json
     * @throws \think\Exception
     */
    public function orderAcar()
    {
        $this->model = model('SalesOrder');
        //当前是否为关联查询
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $this->view->assign("customerSourceList", $this->model->getCustomerSourceList());
        $this->view->assign("reviewTheDataList", $this->model->getReviewTheDataList());


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
                    $query->withField(['name', 'models_name']);
                }, 'newinventory' => function ($query) {
                    $query->withField('licensenumber');
                }])
                ->where($where)
                ->order($sort, $order)
                ->count();


            $list = $this->model
                ->with(['planacar' => function ($query) {
                    $query->withField('payment,monthly,nperlist,margin,tail_section,gps');
                }, 'admin' => function ($query) {
                    $query->withField(['id', 'nickname', 'avatar']);
                }, 'models' => function ($query) {
                    $query->withField(['name', 'models_name']);
                }, 'newinventory' => function ($query) {
                    $query->withField('licensenumber');
                }])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $k => $row) {
                $row->visible(['id', 'order_no', 'financial_name', 'username', 'createtime', 'phone', 'id_card', 'amount_collected', 'downpayment', 'review_the_data',
                    'id_cardimages', 'drivers_licenseimages', 'bank_cardimages', 'undertakingimages', 'accreditimages', 'faceimages', 'informationimages', 'financial_name']);
                $row->visible(['planacar']);
                $row->getRelation('planacar')->visible(['payment', 'monthly', 'margin', 'nperlist', 'tail_section', 'gps',]);
                $row->visible(['admin']);
                $row->getRelation('admin')->visible(['id', 'nickname', 'avatar']);
                $row->visible(['models']);
                $row->getRelation('models')->visible(['name', 'models_name']);
                $row->visible(['newinventory']);
                $row->getRelation('newinventory')->visible(['licensenumber']);

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

        return $this->view->fetch('index');

    }


    /**
     * 纯租订单
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function orderRental()
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
            $total = $this->model
                ->with(['admin' => function ($query) {
                    $query->withField(['id', 'nickname', 'avatar']);
                }, 'models' => function ($query) {
                    $query->withField(['name', 'models_name']);
                }, 'carrentalmodelsinfo' => function ($query) {
                    $query->withField('licenseplatenumber,vin');
                }])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['admin' => function ($query) {
                    $query->withField(['id', 'nickname', 'avatar']);
                }, 'models' => function ($query) {
                    $query->withField(['name', 'models_name']);
                }, 'carrentalmodelsinfo' => function ($query) {
                    $query->withField('licenseplatenumber,vin');
                }])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $k => $v) {
                $v->visible(['id', 'order_no', 'username', 'phone', 'id_card', 'cash_pledge', 'rental_price', 'tenancy_term', 'genderdata', 'review_the_data', 'createtime', 'delivery_datetime']);
                $v->visible(['admin']);
                $v->getRelation('admin')->visible(['id', 'nickname', 'avatar']);
                $v->visible(['models']);
                $v->getRelation('models')->visible(['name', 'models_name']);
                $v->visible(['carrentalmodelsinfo']);
                $v->getRelation('carrentalmodelsinfo')->visible(['licenseplatenumber', 'vin']);

                if ($list[$k]['models']['models_name']) {
                    $list[$k]['models']['name'] = $list[$k]['models']['name'] . " " . $list[$k]['models']['models_name'];
                }
                
                $department = Db::name('auth_group_access')
                    ->alias('a')
                    ->join('auth_group b', 'a.group_id = b.id')
                    ->where('a.uid', $v['admin']['id'])
                    ->value('b.name');
                $list[$k]['admin']['department'] = $department;

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

            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch('index');

    }

    /**
     * 以租代购（二手车）
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function orderSecond()
    {

        $this->model = new \app\admin\model\SecondSalesOrder;
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $this->view->assign("customerSourceList", $this->model->getCustomerSourceList());
        $this->view->assign("buyInsurancedataList", $this->model->getBuyInsurancedataList());
        $this->view->assign("reviewTheDataList", $this->model->getReviewTheDataList());
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username', true);
            $total = $this->model
                ->with(['plansecond' => function ($query) {
                    $query->withField('newpayment,monthlypaymen,periods,totalprices,bond,tailmoney,licenseplatenumber');
                }, 'admin' => function ($query) {
                    $query->withField(['id', 'nickname', 'avatar']);
                }, 'models' => function ($query) {
                    $query->withField(['name', 'models_name']);
                }])
                ->where($where)
                ->order($sort, $order)
                ->count();


            $list = $this->model
                ->with(['plansecond' => function ($query) {
                    $query->withField('newpayment,monthlypaymen,periods,totalprices,bond,tailmoney,licenseplatenumber');
                }, 'admin' => function ($query) {
                    $query->withField(['id', 'nickname', 'avatar']);
                }, 'models' => function ($query) {
                    $query->withField(['name', 'models_name']);
                }])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $row) {
                $row->visible(['id', 'order_no', 'username', 'genderdata', 'createtime', 'phone', 'id_card', 'amount_collected', 'downpayment', 'review_the_data',
                    'id_cardimages', 'drivers_licenseimages']);
                $row->visible(['plansecond']);
                $row->getRelation('plansecond')->visible(['newpayment', 'monthlypaymen', 'periods', 'totalprices', 'bond', 'tailmoney', 'licenseplatenumber']);
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

        return $this->view->fetch('index');

    }

    /** 
     * 全款(新车)
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function orderFull()
    {
        $this->model = new \app\admin\model\FullParmentOrder;
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username', true);
            $total = $this->model
                ->with(['planfull' => function ($query) {
                    $query->withField('full_total_price');
                }, 'admin' => function ($query) {
                    $query->withField(['id', 'nickname', 'avatar']);
                }, 'models' => function ($query) {
                    $query->withField(['name', 'models_name']);
                }])
                ->where($where)
                ->order($sort, $order)
                ->count();


            $list = $this->model
                ->with(['planfull' => function ($query) {
                    $query->withField('full_total_price');
                }, 'admin' => function ($query) {
                    $query->withField(['id', 'nickname', 'avatar']);
                }, 'models' => function ($query) {
                    $query->withField(['name', 'models_name']);
                }])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $k => $row) {
                $row->visible(['id', 'order_no', 'detailed_address', 'city', 'username', 'genderdata', 'createtime', 'phone', 'id_card', 'amount_collected', 'review_the_data']);
                $row->visible(['planfull']);
                $row->getRelation('planfull')->visible(['full_total_price']);
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

    /** 
     * 全款(二手车)
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function secondOrderFull()
    {
        $this->model = new \app\admin\model\SecondFullOrder;
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $this->view->assign("customerSourceList", $this->model->getCustomerSourceList());
        $this->view->assign("reviewTheDataList", $this->model->getReviewTheDataList());
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username', true);
            $total = $this->model
                ->with(['plansecondfull' => function ($query) {
                    $query->withField('totalprices');
                }, 'admin' => function ($query) {
                    $query->withField(['id', 'nickname', 'avatar']);
                }, 'models' => function ($query) {
                    $query->withField(['name', 'models_name']);
                }])
                ->where($where)
                ->order($sort, $order)
                ->count();


            $list = $this->model
                ->with(['plansecondfull' => function ($query) {
                    $query->withField('totalprices');
                }, 'admin' => function ($query) {
                    $query->withField(['id', 'nickname', 'avatar']);
                }, 'models' => function ($query) {
                    $query->withField(['name', 'models_name']);
                }])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $k => $row) {
                $row->visible(['id', 'order_no', 'detailed_address', 'city', 'username', 'genderdata', 'createtime', 'phone', 'id_card', 'amount_collected', 'review_the_data']);
                $row->visible(['plansecondfull']);
                $row->getRelation('plansecondfull')->visible(['totalprices']);
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


    /**
     * 根据方案id查询 车型名称，首付、月供等
     */
    public function getPlanAcarData($planId)
    {

        return Db::name('plan_acar')->alias('a')
            ->join('models b', 'a.models_id=b.id')
            ->join('financial_platform c', 'a.financial_platform_id= c.id')
            ->field('a.id,a.payment,a.monthly,a.nperlist,a.margin,a.tail_section,a.gps,a.note,
                        b.name as models_name')
            ->where('a.id', $planId)
            ->find();

    }

    /**提交内勤 */
    public function sedAudit()
    {
        $this->model = model('SalesOrder');

        if ($this->request->isAjax()) {
            $id = $this->request->post('id');

            $result = $this->model->isUpdate(true)->save(['id' => $id, 'review_the_data' => 'inhouse_handling']);
            //销售员
            $admin_name = DB::name('admin')->where('id', $this->auth->id)->value('nickname');

            $models_id = $this->model->where('id', $id)->value('models_id');

            $backoffice_id = $this->model->where('id', $id)->value('backoffice_id');
            //车型
            $models_name = DB::name('models')->where('id', $models_id)->value('name');
            //客户姓名
            $username = $this->model->where('id', $id)->value('username');

            if ($result !== false) {
                // //推送模板消息给风控
                // $sedArr = array(
                //     'touser' => 'oklZR1J5BGScztxioesdguVsuDoY',
                //     'template_id' => 'LGTN0xKp69odF_RkLjSmCltwWvCDK_5_PuAVLKvX0WQ', /**以租代购新车模板id */
                //     "topcolor" => "#FF0000",
                //     'url' => '',
                //     'data' => array(
                //         'first' =>array('value'=>'你有新客户资料待审核','color'=>'#FF5722') ,
                //         'keyword1' => array('value'=>$params['username'],'color'=>'#01AAED'),
                //         'keyword2' => array('value'=>'以租代购（新车）','color'=>'#01AAED'),
                //         'keyword3' => array('value'=>Session::get('admin')['nickname'],'color'=>'#01AAED'),
                //         'keyword4' =>array('value'=>date('Y年m月d日 H:i:s'),'color'=>'#01AAED') , 
                //         'remark' => array('value'=>'请前往系统进行查看操作')
                //     )
                // );
                // $sedResult= posts("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".self::$token,json_encode($sedArr));
                // if( $sedResult['errcode']==0 && $sedResult['errmsg'] =='ok'){
                //     $this->success('提交成功，请等待审核结果'); 
                // }else{
                //     $this->error('微信推送失败',null,$sedResult);
                // }

//                $channel = "demo-sales";
//                $content = "销售员" . $admin_name . "发出新车销售请求，请处理";
//                goeary_push($channel, $content);

                $data = newinternal_inform($models_name, $admin_name, $username);
                // var_dump($data);
                // die;
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = DB::name('admin')->where('id', $backoffice_id)->value('email');
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

    /**
     * 根据方案id查询 车型名称，首付、月供等
     *
     *
     */
    public function getPlanCarRentalData($planId)
    {

        return Db::name('car_rental_models_info')->alias('a')
            ->join('models b', 'a.models_id=b.id')
            ->field('a.id,a.licenseplatenumber,
                        b.name as models_name')
            ->where('a.id', $planId)
            ->find();

    }

    /**
     * 根据方案id查询 车型名称，首付、月供等
     */
    public function getPlanCarSecondData($planId)
    {

        return Db::name('secondcar_rental_models_info')->alias('a')
            ->join('models b', 'a.models_id=b.id')
            ->field('a.id,a.licenseplatenumber,a.newpayment,a.monthlypaymen,a.periods,a.totalprices,
                        b.name as models_name')
            ->where('a.id', $planId)
            ->find();

    }

    /**
     * 根据方案id查询 车型名称，首付、月供等
     */
    public function getPlanCarFullData($planId)
    {

        return Db::name('plan_full')->alias('a')
            ->join('models b', 'a.models_id=b.id')
            ->field('a.id,a.full_total_price,
                        b.name as models_name')
            ->where('a.id', $planId)
            ->find();

    }

    /**
     * 以租代购（新车）订车
     */
    public function newreserve()
    {
        $this->model = model('SalesOrder');

        $plan_acar = Db::name('plan_acar')->field('category_id')->select();
        foreach($plan_acar as $k => $v){
            foreach($v as $value){
                $ids[] = $value;
            }
        }

        //销售方案类别

        $rules = Db::name('admin')
        ->where('id',$this->auth->id)
        ->value('rule_message');

        if($rules =='message8' || $rules == 'message9'){
            $category = Db::name('scheme_category')
                ->where('id', 'in', $ids)
                ->where('city_id', '38')
                ->where('status',0)
                ->field('id,name')
                ->select();
        }else{
            $category = Db::name('scheme_category')->where('city_id', '38')->where('id', 'in', $ids)->field('id,name')->select();
        }


        $this->view->assign('category', $category);

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            //方案id
            $params['plan_acar_name'] = Session::get('plan_id');
            //方案重组名字
            $params['plan_name'] = Session::get('plan_name');
            //models_id
            $params['models_id'] = Session::get('models_id');
            //生成订单编号
            $params['order_no'] = date('Ymdhis');
            //pr($params);die();
            $data = Db::name('plan_acar')->where('id', $params['plan_acar_name'])->field('payment,monthly,nperlist,gps,margin,tail_section')->find();

            $params['car_total_price'] = $data['payment'] + $data['monthly'] * $data['nperlist'];
            $params['downpayment'] = $data['payment'] + $data['monthly'] + $data['margin'] + $data['gps'];


            //把当前销售员所在的部门的内勤id 入库

            //message8=>销售一部顾问，message13=>内勤一部
            //message9=>销售二部顾问，message20=>内勤二部
            $adminRule = Session::get('admin')['rule_message'];  //测试完后需要把注释放开
            // $adminRule = 'message8'; //测试数据
            if ($adminRule == 'message8') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message13'])->find()['id'];
                // return true;
            }
            if ($adminRule == 'message9') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message20'])->find()['id'];
                // return true;
            }
            if ($adminRule == 'message23') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message24'])->find()['id'];
                // return true;
            }
            if ($adminRule == 'message33') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message20'])->find()['id'];
                // return true;
            }
            if ($params) {
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    if ($result !== false) {

                        if (Session::has('appoint_sale')) {
                            Db::name('plan_acar')
                                ->where('id', Session::get('plan_id'))
                                ->setField('acar_status', 2);

                            Session::delete('appoint_sale');
                        }

                        //如果添加成功,将状态改为提交审核
                        $result_s = $this->model->isUpdate(true)->save(['id' => $this->model->id, 'review_the_data' => 'send_to_internal']);
                        if ($result_s) {
                            $this->success();
                        } else {
                            $this->error('更新状态失败');
                        }
                    } else {
                        $this->error($this->model->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        return $this->view->fetch();
    }

    /**
     * 查询城市
     */
    //获取城市
    public function getCities()
    {
        
        $this->model = model('Cities');
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage1();
            }
        }
    }

    /**
     * 重新定义-----来获取城市
     * 
     * Selectpage的实现方法.
     *
     * 当前方法只是一个比较通用的搜索匹配,请按需重载此方法来编写自己的搜索逻辑,$where按自己的需求写即可
     * 这里示例了所有的参数，所以比较复杂，实现上自己实现只需简单的几行即可
     */
    protected function selectpage1()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'htmlspecialchars']);

        //搜索关键词,客户端输入以空格分开,这里接收为数组
        $word = (array) $this->request->request('q_word/a');
        //当前页
        $page = $this->request->request('pageNumber');
        //分页大小
        $pagesize = $this->request->request('pageSize');
        //搜索条件
        $andor = $this->request->request('andOr', 'and', 'strtoupper');
        //排序方式
        $orderby = (array) $this->request->request('orderBy/a');
        //显示的字段
        $field = $this->request->request('showField');
        //主键
        $primarykey = $this->request->request('keyField');
        //主键值
        $primaryvalue = $this->request->request('keyValue');
        // pr($primaryvalue);
        // die;
        //搜索字段
        $searchfield = (array) $this->request->request('searchField/a');
        //自定义搜索条件
        $custom = (array) $this->request->request('custom/a');
        $order = [];
        foreach ($orderby as $k => $v) {
            $order[$v[0]] = $v[1];
        }
        $field = $field ? $field : 'name';

        //如果有primaryvalue,说明当前是初始化传值
        if ($primaryvalue !== null) {
            $where = [$primarykey => ['in', $primaryvalue]];
        } else {
            $where = function ($query) use ($word, $andor, $field, $searchfield, $custom) {
                $logic = $andor == 'AND' ? '&' : '|';
                $searchfield = is_array($searchfield) ? implode($logic, $searchfield) : $searchfield;
                foreach ($word as $k => $v) {
                    $query->where(str_replace(',', $logic, $searchfield), 'like', "%{$v}%");
                }
                if ($custom) {
                    foreach ($custom as $k => $v) {
                        $query->where($k, 'NEQ', $v);
                    }
                }
            };
        }
        // $adminIds = $this->getDataLimitAdminIds();
        // if (is_array($adminIds)) {
        //     $this->model->where($this->dataLimitField, 'in', $adminIds);
        // }
        $list = [];
        $total = $this->model->where($where)->count();
        if ($total > 0) {
            // if (is_array($adminIds)) {
            //     $this->model->where($this->dataLimitField, 'in', $adminIds);
            // }
            $datalist = $this->model->where($where)
                ->order($order)
                ->page($page, $pagesize)
                ->field($this->selectpageFields)
                ->select();
            foreach ($datalist as $index => $item) {
                unset($item['password'], $item['salt']);
                $list[] = [
                    $primarykey => isset($item[$primarykey]) ? $item[$primarykey] : '',
                    $field => isset($item[$field]) ? $item[$field] : '',
                ];
            }
        }
        //这里一定要返回有list这个字段,total是可选的,如果total<=list的数量,则会隐藏分页按钮
        return json(['list' => $list, 'total' => $total]);
    }


    /**
     * 添加--查询城市下门店
     */
    public function getStore()
    {
        $this->model = model('CompanyStore');
        // //当前是否为关联查询
        // $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
        }
        
    }

    /**
     * Selectpage的实现方法.
     *
     * 当前方法只是一个比较通用的搜索匹配,请按需重载此方法来编写自己的搜索逻辑,$where按自己的需求写即可
     * 这里示例了所有的参数，所以比较复杂，实现上自己实现只需简单的几行即可
     */
    protected function selectpage()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'htmlspecialchars']);

        //搜索关键词,客户端输入以空格分开,这里接收为数组
        $word = (array) $this->request->request('q_word/a');
        //当前页
        $page = $this->request->request('pageNumber');
        //分页大小
        $pagesize = $this->request->request('pageSize');
        //搜索条件
        $andor = $this->request->request('andOr', 'and', 'strtoupper');
        //排序方式
        $orderby = (array) $this->request->request('orderBy/a');
        //显示的字段
        $field = $this->request->request('showField');
        //主键
        $primarykey = $this->request->request('keyField');
        //主键值
        $primaryvalue = $this->request->request('keyValue');
        //搜索字段
        $searchfield = (array) $this->request->request('searchField/a');
        //自定义搜索条件
        $custom = (array) $this->request->request('custom/a');
        $order = [];
        foreach ($orderby as $k => $v) {
            $order[$v[0]] = $v[1];
        }
        $field = $field ? $field : 'name';

        //如果有primaryvalue,说明当前是初始化传值
        if ($primaryvalue !== null) {
            $where = [$primarykey => ['in', $primaryvalue]];
        } else {
            $where = function ($query) use ($word, $andor, $field, $searchfield, $custom) {
                $logic = $andor == 'AND' ? '&' : '|';
                $searchfield = is_array($searchfield) ? implode($logic, $searchfield) : $searchfield;
                foreach ($word as $k => $v) {
                    $query->where(str_replace(',', $logic, $searchfield), 'like', "%{$v}%");
                }
                if ($custom && is_array($custom)) {
                    foreach ($custom as $k => $v) {
                        $query->where($k, '=', $v);
                    }
                }
            };
        }
        // $adminIds = $this->getDataLimitAdminIds();
        // if (is_array($adminIds)) {
        //     $this->model->where($this->dataLimitField, 'in', $adminIds);
        // }
        $list = [];
        $total = $this->model->where($where)->count();
        if ($total > 0) {
            // if (is_array($adminIds)) {
            //     $this->model->where($this->dataLimitField, 'in', $adminIds);
            // }
            $datalist = $this->model->where($where)
                ->order($order)
                ->page($page, $pagesize)
                ->field($this->selectpageFields)
                ->select();
            foreach ($datalist as $index => $item) {
                unset($item['password'], $item['salt']);
                $list[] = [
                    $primarykey => isset($item[$primarykey]) ? $item[$primarykey] : '',
                    $field => isset($item[$field]) ? $item[$field] : '',
                ];
            }
        }
        //这里一定要返回有list这个字段,total是可选的,如果total<=list的数量,则会隐藏分页按钮
        return json(['list' => $list, 'total' => $total]);
    }

    /**
     * 添加--查询门店下的方案类别
     */
    public function getCategory()
    {
        $this->model = model('Schemecategory');
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            $plan_acar = Db::name('plan_acar')->field('category_id')->select();
            foreach($plan_acar as $k => $v){
                foreach($v as $value){
                    $ids[] = $value;
                }
            }

            //销售方案类别
            $custom = (array) $this->request->request('custom/a');
            // pr($custom);
            // die;
            $rules = Db::name('admin')
            ->where('id',$this->auth->id)
            ->value('rule_message');

            if($rules =='message8' || $rules == 'message9'){
                $category = $this->model
                    ->where('id', 'in', $ids)
                    ->where('store_ids', 'in', $custom['store_id'])
                    ->where('status',0)
                    ->field('id,name')
                    ->select();
            }else{
                $category = Db::name('scheme_category')->where('id', 'in', $ids)->where('store_ids', 'in', $custom['store_id'])->field('id,name')->select();
            }
            // pr($category);
            // die;
            $result = array("list" => $category);

            return json($result);
        
        }
        
    }


    /**
     *  以租代购（新车）预定编辑.
     */
    public function newreserveedit($ids = null)
    {
        $this->model = model('SalesOrder');

        $row = $this->model->get($ids);
        if ($row) {
            //关联订单于方案
            $result = Db::name('sales_order')->alias('a')
                ->join('plan_acar b', 'a.plan_acar_name = b.id')
                ->join('models c', 'c.id=b.models_id')
                ->field('b.id as plan_id,b.category_id as category_id,b.payment,b.monthly,b.nperlist,b.gps,b.margin,b.tail_section,c.name as models_name')
                ->where(['a.id' => $row['id']])
                ->find();
        }

        $result['downpayment'] = $result['payment'] + $result['monthly'] + $result['gps'] + $result['margin'];

        $category = DB::name('scheme_category')->field('id,name')->select();

        $this->view->assign('category', $category);

        $this->view->assign('result', $result);

        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');

            if ($params) {
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign('row', $row);

        return $this->view->fetch('newreserveedit');

    }

    /**
     *  以租代购（新车）审核资料录入.
     */
    public function newcontroladd($ids = null)
    {
        $this->model = model('SalesOrder');

        $row = $this->model->get($ids);
        if ($row) {
            //关联订单于方案
            $result = Db::name('sales_order')->alias('a')
                ->join('plan_acar b', 'a.plan_acar_name = b.id')
                ->join('models c', 'c.id=b.models_id')
                ->field('b.id as plan_id,b.category_id as category_id,b.payment,b.monthly,b.nperlist,b.gps,b.margin,b.tail_section,c.name as models_name')
                ->where(['a.id' => $row['id']])
                ->find();
        }

        $result['downpayment'] = $result['payment'] + $result['monthly'] + $result['gps'] + $result['margin'];

        $category = DB::name('scheme_category')->field('id,name')->select();

        $this->view->assign('category', $category);

        $this->view->assign('result', $result);

        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');

            if ($params) {
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign('row', $row);

        return $this->view->fetch('newcontroladd');

    }

    /**
     * 以租代购（新车）客户提车资料录入
     */
    public function newinformation($ids = null)
    {
        $this->model = model('SalesOrder');

        $row = $this->model->get($ids);
        if ($row) {
            //关联订单于方案
            $result = Db::name('sales_order')->alias('a')
                ->join('plan_acar b', 'a.plan_acar_name = b.id')
                ->join('models c', 'c.id=b.models_id')
                ->field('b.id as plan_id,b.category_id as category_id,b.payment,b.monthly,b.nperlist,b.gps,b.margin,b.tail_section,c.name as models_name')
                ->where(['a.id' => $row['id']])
                ->find();
        }

        $result['downpayment'] = $result['payment'] + $result['monthly'] + $result['gps'] + $result['margin'];

        $category = DB::name('scheme_category')->field('id,name')->select();

        $this->view->assign('category', $category);

        $this->view->assign('result', $result);

        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');

            if ($params) {
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result_ss = $row->allowField(true)->save($params);
                    if ($result_ss !== false) {

                        $this->model->isUpdate(true)->save(['id' => $row['id'], 'review_the_data' => 'inform_the_tube']);

                        $this->success();

                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign('row', $row);

        return $this->view->fetch('newinformation');

    }


    /**
     * 资料已补全，提交车管进行提车
     * @param null $ids
     */
    public function newinformtube($ids = null)
    {
        $this->model = model('SalesOrder');

        if ($this->request->isAjax()) {
            $id = $this->request->post('id');

            $result = $this->model->isUpdate(true)->save(['id' => $id, 'review_the_data' => 'send_the_car']);
            //销售员
            $admin_name = DB::name('admin')->where('id', $this->auth->id)->value('nickname');

            $models_id = $this->model->where('id', $id)->value('models_id');

            $backoffice_id = $this->model->where('id', $id)->value('backoffice_id');
            //车型
            $models_name = DB::name('models')->where('id', $models_id)->value('name');
            //客户姓名
            $username = $this->model->where('id', $id)->value('username');

            if ($result !== false) {

//                $channel = "demo-newsend_car";
//                $content = "客户：" . $username . "对车型：" . $models_name . "的购买，资料已经补全，可以进行提车，请及时登陆后台进行处理 ";
//                goeary_push($channel, $content);

                $data = newsend_car($models_name, $username);
                // var_dump($data);
                // die;
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = Db::name('admin')->where('rule_message', 'message14')->value('email');
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


    /**
     * 显示方案列表
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function planacar()
    {
        if ($this->request->isAjax()) {


            $category_id = input("category_id");
            $category_id = json_decode($category_id, true);

            $result = DB::name('plan_acar')->alias('a')
                ->join('models b', 'b.id=a.models_id')
                ->join('scheme_category s', 'a.category_id = s.id')
                ->where([
                    'a.category_id' => $category_id,
                    'a.acar_status' => 1
                ])
                //   ->where('sales_id', NULL)
                //   ->whereOr('sales_id', $this->auth->id)
                ->field('a.id,a.payment,a.monthly,a.nperlist,a.margin,a.tail_section,a.gps,a.sales_id,a.note,b.models_name as model_name,b.name as models_name,b.id as models_id,s.category_note')
                ->order('id desc')
                ->select();
            foreach ($result as $k => $v) {

                $result[$k]['downpayment'] = $v['payment'] + $v['monthly'] + $v['margin'] + $v['gps'];
                $result[$k]['models_name'] = $v['models_name'] . " " . $v['model_name'];
                $result[$k]['admin_id'] = $this->auth->id;
            }

            $result = json_encode($result);

            return $result;
        }
    }


    /**
     * 分页
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function page()
    {
        $category_id = input("category_id");
        $category_id = json_decode($category_id, true);

        $num = $this->request->post('num');

        $num = intval($num);
        $limit_number = $num * 15;


        $result = DB::name('plan_acar')->alias('a')
            ->join('models b', 'b.id=a.models_id')
            ->join('scheme_category s', 'a.category_id = s.id')
            ->where([
                'a.category_id' => $category_id,
                'a.acar_status' => 1
            ])
            // ->where('sales_id', NULL)
            // ->whereOr('sales_id', $this->auth->id)
            ->field('a.id,a.payment,a.monthly,a.nperlist,a.margin,a.tail_section,a.gps,a.note,a.sales_id,b.models_name as model_name,b.name as models_name,b.id as models_id,s.category_note')
            ->limit($limit_number, 15)
            ->order('id desc')
            ->select();

        foreach ($result as $k => $v) {

            $result[$k]['downpayment'] = $v['payment'] + $v['monthly'] + $v['margin'] + $v['gps'];
            $result[$k]['admin_id'] = $this->auth->id;
            $result[$k]['models_name'] = $v['models_name'] . " " . $v['model_name'];
        }

        echo json_encode($result);


    }


    /**
     * 方案组装
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function planname()
    {
        if ($this->request->isAjax()) {


            $plan_id = input("id");
            $plan_id = json_decode($plan_id, true);
            $sql = Db::name('models')->alias('a')
                ->join('plan_acar b', 'b.models_id=a.id')
                ->field('a.models_name as model_name,a.name as models_name,b.id,b.payment,b.monthly,b.gps,b.tail_section,b.margin,b.category_id,b.models_id,b.sales_id')
                ->where(['b.ismenu' => 1, 'b.id' => $plan_id])
                ->find();
            $plan_name = $sql['models_name'] . " " . $sql['model_name'] . '【首付' . $sql['payment'] . '，' . '月供' . $sql['monthly'] . '，' . 'GPS ' . $sql['gps'] . '，' . '尾款 ' . $sql['tail_section'] . '，' . '保证金' . $sql['margin'] . '】';

            Session::set('plan_id', $plan_id);
            Session::set('plan_name', $plan_name);
            Session::set('models_id', $sql['models_id']);
            if ($sql['sales_id']) {
                Session::set('appoint_sale', $sql['sales_id']);
            }
        }
    }

    /**
     *  以租代购（新车）提车资料编辑.
     */
    public function edit($ids = null, $posttype = null)
    {
        $this->model = model('SalesOrder');
        /**如果是点击的提交保证金按钮 */
        if ($posttype == 'the_guarantor') {
            $row = $this->model->get($ids);
            if ($row) {
                //关联订单于方案
                $result = Db::name('sales_order')->alias('a')
                    ->join('plan_acar b', 'a.plan_acar_name = b.id')
                    ->join('models c', 'c.id=b.models_id')
                    ->field('b.id as plan_id,b.category_id as category_id,b.payment,b.monthly,b.nperlist,b.gps,b.margin,b.tail_section,c.models_name as model_name,c.name as models_name')
                    ->where(['a.id' => $row['id']])
                    ->find();
            }

            $result['downpayment'] = $result['payment'] + $result['monthly'] + $result['gps'] + $result['margin'];

            $result['models_name'] = $result['models_name'] . " " . $result['model_name'];

            $category = DB::name('scheme_category')->field('id,name')->select();

            $this->view->assign('category', $category);

            $this->view->assign('result', $result);

            if (!$row) {
                $this->error(__('No Results were found'));
            }
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                if (!in_array($row[$this->dataLimitField], $adminIds)) {
                    $this->error(__('You have no permission'));
                }
            }
            if ($this->request->isPost()) {
                $params = $this->request->post('row/a');
                if ($params) {
                    try {
                        //是否采用模型验证
                        if ($this->modelValidate) {
                            $name = basename(str_replace('\\', '/', get_class($this->model)));
                            $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                            $row->validate($validate);
                        }
                        $result_ss = $row->allowField(true)->save($params);
                        if ($result_ss !== false) {
                            //如果添加成功,将状态改为提交审核
                            $result_s = $this->model->isUpdate(true)->save(['id' => $row['id'], 'review_the_data' => 'is_reviewing_true']);

                            $models_name = Db::name('models')->where('id', $row['models_id'])->value('name');

//                            $channel = "demo-newdata_cash";
//                            $content = "客户：" . $row['username'] . "对车型：" . $models_name . "的购买，保证金收据已上传，请及时登陆后台进行处理 ";
//                            goeary_push($channel, $content);

                            $data = newdata_cash($models_name, $row['username']);
                            // var_dump($data);
                            // die;
                            $email = new Email;
                            // $receiver = "haoqifei@cdjycra.club";
                            $receiver = Db::name('admin')->where('rule_message', 'message7')->value('email');
                            $result_sss = $email
                                ->to($receiver)
                                ->subject($data['subject'])
                                ->message($data['message'])
                                ->send();
                            if ($result_sss) {
                                $this->success();
                            } else {
                                $this->error('邮箱发送失败');
                            }

                        } else {
                            $this->error($this->model->getError());
                        }
                    } catch (\think\exception\PDOException $e) {
                        $this->error($e->getMessage());
                    }
                }
                $this->error(__('Parameter %s can not be empty', ''));
            }
            //复制$row的值区分编辑和保证金收据

            $this->view->assign('row', $row);

            return $this->view->fetch('new_the_guarantor');
        }
        if ($posttype == 'edit') {
            /**点击的编辑按钮 */
            $row = $this->model->get($ids);
            if ($row) {
                //关联订单于方案
                $result = Db::name('sales_order')->alias('a')
                    ->join('plan_acar b', 'a.plan_acar_name = b.id')
                    ->join('models c', 'c.id=b.models_id')
                    ->field('b.id as plan_id,b.category_id as category_id,b.payment,b.monthly,b.nperlist,b.gps,b.margin,b.tail_section,c.models_name as model_name,c.name as models_name')
                    ->where(['a.id' => $row['id']])
                    ->find();
            }

            $result['downpayment'] = $result['payment'] + $result['monthly'] + $result['gps'] + $result['margin'];

            $result['models_name'] = $result['models_name'] . " " . $result['model_name'];

            $category = DB::name('scheme_category')->field('id,name')->select();

            $this->view->assign('category', $category);

            $this->view->assign('result', $result);

            if (!$row) {
                $this->error(__('No Results were found'));
            }
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                if (!in_array($row[$this->dataLimitField], $adminIds)) {
                    $this->error(__('You have no permission'));
                }
            }
            if ($this->request->isPost()) {
                $params = $this->request->post('row/a');
                // pr($params);
                // die;

                if ($params) {
                    try {
                        //是否采用模型验证
                        if ($this->modelValidate) {
                            $name = basename(str_replace('\\', '/', get_class($this->model)));
                            $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                            $row->validate($validate);
                        }
                        $result = $row->allowField(true)->save($params);
                        if ($result !== false) {
                            $this->success();
                        } else {
                            $this->error($row->getError());
                        }
                    } catch (\think\exception\PDOException $e) {
                        $this->error($e->getMessage());
                    }
                }
                $this->error(__('Parameter %s can not be empty', ''));
            }
            $this->view->assign('row', $row);

            return $this->view->fetch('newedit');
        }
    }

    /**
     *  以租代购（新车）补录资料.
     */
    public function newcollectioninformation($ids = null)
    {
        $this->model = model('SalesOrder');

        $row = $this->model->get($ids);
        if ($row) {
            //关联订单于方案
            $result = Db::name('sales_order')->alias('a')
                ->join('plan_acar b', 'a.plan_acar_name = b.id')
                ->join('models c', 'c.id=b.models_id')
                ->field('b.id as plan_id,b.category_id as category_id,b.payment,b.monthly,b.nperlist,b.gps,b.margin,b.tail_section,c.models_name as model_name,c.name as models_name')
                ->where(['a.id' => $row['id']])
                ->find();
        }

        $result['downpayment'] = $result['payment'] + $result['monthly'] + $result['gps'] + $result['margin'];

        $result['models_name'] = $result['models_name'] . " " . $result['model_name'];

        $category = Db::name('scheme_category')->field('id,name')->select();

        $this->view->assign('category', $category);

        $this->view->assign('result', $result);

        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            $params['review_the_data'] = 'is_reviewing_true';
            if ($params) {
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        //车型
                        $models_name = Db::name('models')->where('id', $row['models_id'])->value('name');

//                        $channel = "demo-new_collection_data";
//                        $content = "客户： " . $row['usename'] . "对车型： " . $models_name . "的购买，补录：" . $row['text'] . "资料已完成";
//                        goeary_push($channel, $content);

                        $data = new_collection_data($models_name, $row['username'], $row['text']);

                        $email = new Email();

                        $receiver = Db::name('admin')->where('rule_message', 'message7')->value('email');

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

                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign('row', $row);

        return $this->view->fetch('newcollectioninformation');
    }

    /**
     *  租车.
     */


    /**
     * 租车预定
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function reserve()
    {
        $this->model = new \app\admin\model\RentalOrder;

        $result = Db::name('car_rental_models_info')->alias('a')
            ->join('models b', 'b.id=a.models_id')
            ->field('a.id,a.licenseplatenumber,a.kilometres,a.Parkingposition,a.companyaccount,a.cashpledge,a.threemonths,a.sixmonths,a.manysixmonths,a.note,b.models_name as model_name,b.name as models_name')
            ->where('a.status_data', '')
            ->where('a.shelfismenu', '=', '1')
            ->select();
        // pr($result);
        // die;
        $this->view->assign('result', $result);

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            // $ex = explode(',', $params['plan_acar_name']);


            $params['plan_car_rental_name'] = Session::get('plan_id');

            $params['car_rental_models_info_id'] = $params['plan_car_rental_name'];

            $params['plan_name'] = Session::get('plan_name');


            $models_id = DB::name('car_rental_models_info')->where('id', $params['plan_car_rental_name'])->value('models_id');

            $params['models_id'] = $models_id;
            // pr($params);die;

            //把当前销售员所在的部门的内勤id 入库

            //message8=>销售一部顾问，message13=>内勤一部
            //message9=>销售二部顾问，message20=>内勤二部
            $adminRule = Session::get('admin')['rule_message'];  //测试完后需要把注释放开
            // $adminRule = 'message8'; //测试数据
            if ($adminRule == 'message8') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message13'])->find()['id'];
                // return true;
            }
            if ($adminRule == 'message9') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message20'])->find()['id'];
                // return true;
            }
            if ($adminRule == 'message23') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message24'])->find()['id'];
                // return true;
            }
            if ($adminRule == 'message33') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message20'])->find()['id'];
                // return true;
            }
            if ($params) {
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    if ($result !== false) {
                        // 如果添加成功,将状态改为车管正在审核
                        $result_s = $this->model->isUpdate(true)->save(['id' => $this->model->id, 'review_the_data' => 'is_reviewing_true']);

                        $admin_nickname = DB::name('admin')->alias('a')->join('rental_order b', 'b.admin_id=a.id')->where('b.id', $this->model->id)->value('a.nickname');

                        Session::set('rental_id', $this->model->id);

                        $this->model = model('car_rental_models_info');

                        $this->model->isUpdate(true)->save(['id' => Session::get('plan_id'), 'status_data' => 'is_reviewing']);

                        if ($result_s) {

//                            $channel = "demo-reserve";
//                            $content = "销售员" . $admin_nickname . "提交的租车单，请及时处理";
//                            goeary_push($channel, $content);

                            $data = Db::name("rental_order")->where('id', Session::get('rental_id'))->find();

                            //车型
                            $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
                            //销售员

                            $admin_name = DB::name('admin')->where('id', $data['admin_id'])->value('nickname');
                            //客户姓名
                            $username = $data['username'];

                            $data = rentalcar_inform($models_name, $admin_name, $username);
                            // var_dump($data);
                            // die;
                            $email = new Email;
                            // $receiver = "haoqifei@cdjycra.club";
                            $receiver = DB::name('admin')->where('rule_message', 'message15')->value('email');

                            $result_ss = $email
                                ->to($receiver)
                                ->subject($data['subject'])
                                ->message($data['message'])
                                ->send();
                            if ($result_ss) {
                                $this->success();
                            } else {
                                $this->error('邮箱发送失败');
                            }

                        } else {
                            $this->error('更新状态失败');
                        }
                    } else {
                        $this->error($this->model->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        return $this->view->fetch();
    }


    /**
     * 方案组装
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function rentalplanname()
    {
        if ($this->request->isAjax()) {


            $plan_id = input("id");
            $plan_id = json_decode($plan_id, true);

            $sql = Db::name('models')->alias('a')
                ->join('car_rental_models_info b', 'b.models_id=a.id')
                ->field('a.id,b.licenseplatenumber,b.companyaccount,b.cashpledge,b.threemonths,b.sixmonths,b.manysixmonths,b.note,a.models_name as model_name,a.name as models_name')
                ->where(['b.id' => $plan_id])
                ->find();

            $plan_name = $sql['models_name'] . " " . $sql['model_name'] . '【押金' . $sql['cashpledge'] . '，' . '3月内租金（元）' . $sql['threemonths'] . '，' . '6月内租金（元） ' . $sql['sixmonths'] . '，' . '6月以上租金（元） ' . $sql['manysixmonths'] . '】';

            Session::set('plan_id', $plan_id);

            Session::set('plan_name', $plan_name);

        }
    }


    /**
     * 租车客户信息的补全
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function rentaladd($ids = null)
    {
        $this->model = new \app\admin\model\RentalOrder;
        $row = $this->model->get($ids);
        if ($row) {

            $result = DB::name('rental_order')->alias('a')
                ->join('car_rental_models_info b', 'b.id=a.plan_car_rental_name')
                ->join('models c', 'c.id=b.models_id')
                ->field('a.id,a.username,a.plan_car_rental_name,a.phone,a.deposit_receiptimages,a.down_payment,a.plan_name,b.licenseplatenumber,b.kilometres,b.Parkingposition,b.companyaccount,b.cashpledge,b.threemonths,b.sixmonths,b.manysixmonths,b.note,c.models_name as model_name,c.name as models_name')
                ->where('a.id', $row['id'])
                ->find();
        }
        $result['models_name'] = $result['models_name'] . " " . $result['model_name'];

        $this->view->assign('result', $result);

        if ($this->request->isPost()) {

            $params = $this->request->post('row/a');
            //生成订单编号
            $params['order_no'] = date('Ymdhis');
            $params['plan_car_rental_name'] = $result['plan_car_rental_name'];
            $params['username'] = $result['username'];
            $params['phone'] = $result['phone'];
            $params['plan_name'] = $result['plan_name'];
            $params['deposit_receiptimages'] = $result['deposit_receiptimages'];
            $params['down_payment'] = $result['down_payment'];

            if ($params) {
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        //如果添加成功,将状态改为暂不提交风控审核
                        $result_s = $row->isUpdate(true)->save(['id' => $row['id'], 'review_the_data' => 'is_reviewing_false']);
                        if ($result_s) {
                            $this->success();
                        } else {
                            $this->error('更新状态失败');
                        }
                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        return $this->view->fetch();
    }


    /**
     * 租车订单修改
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function rentaledit($ids = NULL)
    {
        $this->model = new \app\admin\model\RentalOrder;
        $row = $this->model->get($ids);
        if ($row) {

            $result = DB::name('rental_order')->alias('a')
                ->join('car_rental_models_info b', 'b.id=a.plan_car_rental_name')
                ->join('models c', 'c.id=b.models_id')
                ->field('a.id,a.username,a.plan_car_rental_name,a.phone,a.deposit_receiptimages,a.down_payment,a.plan_name,b.licenseplatenumber,b.kilometres,b.Parkingposition,b.companyaccount,b.cashpledge,b.threemonths,b.sixmonths,b.manysixmonths,b.note,c.models_name as model_name,c.name as models_name')
                ->where('a.id', $row['id'])
                ->find();
        }

        $result['models_name'] = $result['models_name'] . " " . $result['model_name'];

        $this->view->assign('result', $result);

        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 租车补录资料
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function rentalinformation($ids = NULL)
    {
        $this->model = new \app\admin\model\RentalOrder;
        $row = $this->model->get($ids);
        if ($row) {

            $result = DB::name('rental_order')->alias('a')
                ->join('car_rental_models_info b', 'b.id=a.plan_car_rental_name')
                ->join('models c', 'c.id=b.models_id')
                ->field('a.id,a.username,a.plan_car_rental_name,a.phone,a.deposit_receiptimages,a.down_payment,a.plan_name,b.licenseplatenumber,b.kilometres,b.Parkingposition,b.companyaccount,b.cashpledge,b.threemonths,b.sixmonths,b.manysixmonths,b.note,c.models_name as model_name,c.name as models_name')
                ->where('a.id', $row['id'])
                ->find();
        }

        $result['models_name'] = $result['models_name'] . " " . $result['model_name'];

        $this->view->assign('result', $result);

        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $params['review_the_data'] = 'is_reviewing_control';
            if ($params) {
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        //车型
                        $models_name = Db::name('models')->where('id', $row['models_id'])->value('name');

                        $data = rental_collection_data($models_name, $row['username'], $row['text']);

                        $email = new Email();

                        $receiver = Db::name('admin')->where('rule_message', 'message7')->value('email');

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

                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 租车删除
     */
    public function rentaldel($ids = "")
    {
        $this->model = new \app\admin\model\RentalOrder;
        if ($ids) {
            $pk = $this->model->getPk();
            $plan_car_rental_name = DB::name('rental_order')->where('id', $ids)->value('plan_car_rental_name');
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $count = $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();
            $count = 0;
            foreach ($list as $k => $v) {
                $count += $v->delete();
            }
            if ($count) {
                DB::name('car_rental_models_info')->where('id', $plan_car_rental_name)->setField('status_data', '');
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }


    /**
     * 提交风控审核
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function control()
    {
        $this->model = new \app\admin\model\RentalOrder;

        if ($this->request->isAjax()) {
            $id = $this->request->post('id');

            $admin_nickname = DB::name('admin')->alias('a')->join('rental_order b', 'b.admin_id=a.id')->where('b.id', $id)->value('a.nickname');

            $result = DB::name('rental_order')->where('id', $id)->setField('review_the_data', 'is_reviewing_control');

            if ($result !== false) {

                $data = Db::name("rental_order")->where('id', $id)->find();
                //车型
                $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
                //销售员
                $admin_id = $data['admin_id'];
                $admin_name = DB::name('admin')->where('id', $data['admin_id'])->value('nickname');
                //客户姓名
                $username = $data['username'];

                $data = rentalcontrol_inform($models_name, $admin_name, $username);
                // var_dump($data);
                // die;
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = DB::name('admin')->where('rule_message', "message7")->value('email');
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


                // //推送模板消息给风控
                // $sedArr = array(
                //     'touser' => 'oklZR1J5BGScztxioesdguVsuDoY',
                //     'template_id' => 'LGTN0xKp69odF_RkLjSmCltwWvCDK_5_PuAVLKvX0WQ', /**以租代购新车模板id */
                //     "topcolor" => "#FF0000",
                //     'url' => '',
                //     'data' => array(
                //         'first' =>array('value'=>'你有新客户资料待审核','color'=>'#FF5722') ,
                //         'keyword1' => array('value'=>$params['username'],'color'=>'#01AAED'),
                //         'keyword2' => array('value'=>'以租代购（新车）','color'=>'#01AAED'),
                //         'keyword3' => array('value'=>Session::get('admin')['nickname'],'color'=>'#01AAED'),
                //         'keyword4' =>array('value'=>date('Y年m月d日 H:i:s'),'color'=>'#01AAED') , 
                //         'remark' => array('value'=>'请前往系统进行查看操作')
                //     )
                // );
                // $sedResult= posts("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".self::$token,json_encode($sedArr));
                // if( $sedResult['errcode']==0 && $sedResult['errmsg'] =='ok'){
                //     $this->success('提交成功，请等待审核结果'); 
                // }else{
                //     $this->error('微信推送失败',null,$sedResult);
                // }


            } else {
                $this->error('提交失败', null, $result);

            }
        }
    }

    /**
     * 以租代购（二手车）订车
     */
    public function secondreserve()
    {
        $this->model = new \app\admin\model\SecondSalesOrder;
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $this->view->assign("customerSourceList", $this->model->getCustomerSourceList());
        $this->view->assign("buyInsurancedataList", $this->model->getBuyInsurancedataList());
        $this->view->assign("reviewTheDataList", $this->model->getReviewTheDataList());
        $newRes = array();

        $sql = Db::name('models')->alias('a')
                ->join('secondcar_rental_models_info b', 'b.models_id=a.id')
                ->field('a.id,a.models_name as model_name,a.name as models_name,b.id,b.newpayment,b.monthlypaymen,b.periods,b.totalprices,b.bond,b.licenseplatenumber,b.bond')
                ->where(['b.status_data' => '', 'b.shelfismenu' => 1])
                ->whereOr('sales_id', $this->auth->id)
                ->select();
        foreach ((array)$sql as $bValue) {
            $bValue['models_name'] = $bValue['models_name'] . " " . $bValue['model_name'] . '---车牌号：' . $bValue['licenseplatenumber'] . '【新首付' . $bValue['newpayment'] . '，' . '月供' . $bValue['monthlypaymen'] . '，' . '期数' . $bValue['periods'] . '，' .'保证金'.$bValue['bond'].'，'. '全款方案总价（元）' . $bValue['totalprices'] . '】';
            $newB[] = $bValue;
        }
        $data  =  $newB;
        
        $this->view->assign('data', $data);

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            $ex = explode(',', $params['plan_car_second_name']);

            $result = DB::name('secondcar_rental_models_info')->where('id', $params['plan_car_second_name'])->field('newpayment,monthlypaymen,periods,bond,models_id')->find();

            $params['car_total_price'] = $result['newpayment'] + $result['monthlypaymen'] * $result['periods'];
            $params['downpayment'] = $result['newpayment'] + $result['monthlypaymen'] + $result['bond'];

            $params['plan_car_second_name'] = reset($ex); //截取id
            $params['plan_name'] = addslashes(end($ex)); //
            //生成订单编号
            $params['order_no'] = date('Ymdhis');
            $params['models_id'] = $result['models_id'];
            //把当前销售员所在的部门的内勤id 入库

            //message8=>销售一部顾问，message13=>内勤一部
            //message9=>销售二部顾问，message20=>内勤二部
            $adminRule = Session::get('admin')['rule_message'];  //测试完后需要把注释放开
            // $adminRule = 'message8'; //测试数据
            if ($adminRule == 'message8') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message13'])->find()['id'];
                // return true;
            }
            if ($adminRule == 'message9') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message20'])->find()['id'];
                // return true;
            }
            if ($adminRule == 'message23') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message24'])->find()['id'];
                // return true;
            }
            if ($adminRule == 'message33') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message20'])->find()['id'];
                // return true;
            }
            if ($params) {
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    if ($result !== false) {
                        //如果添加成功,将状态改为提交审核
                        $result_s = $this->model->isUpdate(true)->save(['id' => $this->model->id, 'review_the_data' => 'is_reviewing']);
                        if ($result_s) {
                            $this->success();
                        } else {
                            $this->error('更新状态失败');
                        }
                    } else {
                        $this->error($this->model->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        return $this->view->fetch();
    }

    /**
     * 以租代购（二手车）审核资料上传
     */
    public function secondaudit($ids = "")
    {
        $this->model = new \app\admin\model\SecondSalesOrder;
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $this->view->assign("customerSourceList", $this->model->getCustomerSourceList());
        $this->view->assign("buyInsurancedataList", $this->model->getBuyInsurancedataList());
        $this->view->assign("reviewTheDataList", $this->model->getReviewTheDataList());
        $row = $this->model->get($ids);
        if ($row) {
            //关联订单于方案
            $result = Db::name('second_sales_order')->alias('a')
                ->join('secondcar_rental_models_info b', 'a.plan_car_second_name = b.id')
                ->field('b.id as plan_id')
                ->where(['a.id' => $row['id']])
                ->find();
        }
        
        $sql = Db::name('models')->alias('a')
                ->join('secondcar_rental_models_info b', 'b.models_id=a.id')
                ->field('a.models_name as model_name,a.name as models_name,b.id,b.newpayment,b.monthlypaymen,b.periods,b.totalprices,b.licenseplatenumber')
                ->where(['b.shelfismenu' => 1])
                ->whereOr('sales_id', $this->auth->id)
                ->select();
           
        foreach ((array)$sql as $bValue) {
                $bValue['models_name'] = $bValue['models_name'] . " " . $bValue['model_name'] . '---车牌号为：' . $bValue['licenseplatenumber'] . '【新首付' . $bValue['newpayment'] . '，' . '月供' . $bValue['monthlypaymen'] . '，' . '期数（月）' . $bValue['periods'] . '，' . '总价（元）' . $bValue['totalprices'] . '】';
                $newB[] = $bValue;
        }
           
        $data = $newB;

        $this->view->assign('data', $data);
        $this->view->assign('result', $result);

        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if ($params) {
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign('row', $row);

        return $this->view->fetch('secondaudit');
    }

    /**
     *  二手车.
     */
    /**提交内勤处理 */
    public function setAudit()
    {
        $this->model = new \app\admin\model\SecondSalesOrder;
        if ($this->request->isAjax()) {

            $id = $this->request->post('id');
            $result = $this->model->save(['review_the_data' => 'is_reviewing_true'], function ($query) use ($id) {
                $query->where('id', $id);
            });

            if ($result) {

                $this->model = model('secondcar_rental_models_info');

                $plan_car_second_name = DB::name('second_sales_order')->where('id', $id)->value('plan_car_second_name');

                $this->model->isUpdate(true)->save(['id' => $plan_car_second_name, 'status_data' => 'for_the_car']);

                $data = Db::name("second_sales_order")->where('id', $id)->find();
                //车型
                $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
                //内勤
                $backoffice_id = $data['backoffice_id'];

                $admin_name = DB::name('admin')->where('id', $data['admin_id'])->value('nickname');
                //客户姓名
                $username = $data['username'];

                $data = secondinternal_inform($models_name, $admin_name, $username);
                // var_dump($data);
                // die;
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = DB::name('admin')->where('id', $backoffice_id)->value('email');
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

        }
    }

    /**
     *  二手车编辑.
     */
    public function secondedit($ids = null, $posttype = null)
    {
        $this->model = new \app\admin\model\SecondSalesOrder;
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $this->view->assign("customerSourceList", $this->model->getCustomerSourceList());
        $this->view->assign("buyInsurancedataList", $this->model->getBuyInsurancedataList());
        $this->view->assign("reviewTheDataList", $this->model->getReviewTheDataList());
        /**如果是点击的提交保证金按钮 */
        if ($posttype == 'the_guarantor') {
            $row = $this->model->get($ids);
            if ($row) {
                //关联订单于方案
                $result = Db::name('second_sales_order')->alias('a')
                    ->join('secondcar_rental_models_info b', 'a.plan_car_second_name = b.id')
                    ->field('b.id as plan_id')
                    ->where(['a.id' => $row['id']])
                    ->find();
            }
            
            $sql = Db::name('models')->alias('a')
                    ->join('secondcar_rental_models_info b', 'b.models_id=a.id')
                    ->field('a.models_name as model_name,a.name as models_name,b.id,b.newpayment,b.monthlypaymen,b.periods,b.totalprices,b.licenseplatenumber')
                    ->where(['b.shelfismenu' => 1])
                    ->whereOr('sales_id', $this->auth->id)
                    ->select();
               
            foreach ((array)$sql as $bValue) {
                    $bValue['models_name'] = $bValue['models_name'] . " " . $bValue['model_name'] . '---车牌号为：' . $bValue['licenseplatenumber'] . '【新首付' . $bValue['newpayment'] . '，' . '月供' . $bValue['monthlypaymen'] . '，' . '期数（月）' . $bValue['periods'] . '，' . '总价（元）' . $bValue['totalprices'] . '】';
                    $newB[] = $bValue;
            }
            
            $data = $newB;
            
            $this->view->assign('data', $data);
            $this->view->assign('result', $result);

            if (!$row) {
                $this->error(__('No Results were found'));
            }
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                if (!in_array($row[$this->dataLimitField], $adminIds)) {
                    $this->error(__('You have no permission'));
                }
            }
            if ($this->request->isPost()) {
                $params = $this->request->post('row/a');
                if ($params) {
                    try {
                        //是否采用模型验证
                        if ($this->modelValidate) {
                            $name = basename(str_replace('\\', '/', get_class($this->model)));
                            $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                            $row->validate($validate);
                        }
                        $result = $row->allowField(true)->save($params);
                        if ($result !== false) {
                            //如果添加成功,将状态改为提交审核
                            $result_s = $this->model->isUpdate(true)->save(['id' => $row['id'], 'review_the_data' => 'is_reviewing_true']);

                            $admin_nickname = DB::name('admin')->alias('a')->join('second_sales_order b', 'b.admin_id=a.id')->where('b.id', $row['id'])->value('a.nickname');


                            //请求地址
                            $uri = "http://goeasy.io/goeasy/publish";
                            // 参数数组
                            $data = [
                                'appkey' => "BC-04084660ffb34fd692a9bd1a40d7b6c2",
                                'channel' => "demo-second-the_guarantor",
                                'content' => "销售员" . $admin_nickname . "提交的二手单已经提供保证金，请及时处理"
                            ];
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $uri);//地址
                            curl_setopt($ch, CURLOPT_POST, 1);//请求方式为post
                            curl_setopt($ch, CURLOPT_HEADER, 0);//不打印header信息
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//返回结果转成字符串
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//post传输的数据。
                            $return = curl_exec($ch);
                            curl_close($ch);
                            // print_r($return);

                            if ($result_s) {
                                $this->success();
                            } else {
                                $this->error('更新状态失败');
                            }
                        } else {
                            $this->error($this->model->getError());
                        }
                    } catch (\think\exception\PDOException $e) {
                        $this->error($e->getMessage());
                    }
                }
                $this->error(__('Parameter %s can not be empty', ''));
            }
            //复制$row的值区分编辑和保证金收据

            $this->view->assign('row', $row);

            return $this->view->fetch('secondthe_guarantor');
        }
        if ($posttype == 'edit') {
            /**点击的编辑按钮 */
            $row = $this->model->get($ids);
            if ($row) {
                //关联订单于方案
                $result = Db::name('second_sales_order')->alias('a')
                    ->join('secondcar_rental_models_info b', 'a.plan_car_second_name = b.id')
                    ->field('b.id as plan_id')
                    ->where(['a.id' => $row['id']])
                    ->find();
            }
            
            $sql = Db::name('models')->alias('a')
                    ->join('secondcar_rental_models_info b', 'b.models_id=a.id')
                    ->field('a.models_name as model_name,a.name as models_name,b.id,b.newpayment,b.monthlypaymen,b.periods,b.totalprices,b.licenseplatenumber')
                    ->where(['b.shelfismenu' => 1])
                    ->whereOr('sales_id', $this->auth->id)
                    ->select();
                
            foreach ((array)$sql as $bValue) {
                    $bValue['models_name'] = $bValue['models_name'] . " " . $bValue['model_name'] . '---车牌号为：' . $bValue['licenseplatenumber'] . '【新首付' . $bValue['newpayment'] . '，' . '月供' . $bValue['monthlypaymen'] . '，' . '期数（月）' . $bValue['periods'] . '，' . '总价（元）' . $bValue['totalprices'] . '】';
                    $newB[] = $bValue;
            }
            $data = $newB;    
            
            $this->view->assign('data', $data);
            $this->view->assign('result', $result);

            if (!$row) {
                $this->error(__('No Results were found'));
            }
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                if (!in_array($row[$this->dataLimitField], $adminIds)) {
                    $this->error(__('You have no permission'));
                }
            }
            if ($this->request->isPost()) {
                $params = $this->request->post('row/a');
                if ($params) {
                    try {
                        //是否采用模型验证
                        if ($this->modelValidate) {
                            $name = basename(str_replace('\\', '/', get_class($this->model)));
                            $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                            $row->validate($validate);
                        }
                        $result = $row->allowField(true)->save($params);
                        if ($result !== false) {
                            $this->success();
                        } else {
                            $this->error($row->getError());
                        }
                    } catch (\think\exception\PDOException $e) {
                        $this->error($e->getMessage());
                    }
                }
                $this->error(__('Parameter %s can not be empty', ''));
            }
            $this->view->assign('row', $row);

            return $this->view->fetch('secondedit');
        }
    }

    /**
     *  二手车补录资料
     */
    public function secondinformation($ids = null)
    {
        $this->model = new \app\admin\model\SecondSalesOrder;
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $this->view->assign("customerSourceList", $this->model->getCustomerSourceList());
        $this->view->assign("buyInsurancedataList", $this->model->getBuyInsurancedataList());
        $this->view->assign("reviewTheDataList", $this->model->getReviewTheDataList());

        $row = $this->model->get($ids);
        if ($row) {
            //关联订单于方案
            $result = Db::name('second_sales_order')->alias('a')
                ->join('secondcar_rental_models_info b', 'a.plan_car_second_name = b.id')
                ->field('b.id as plan_id')
                ->where(['a.id' => $row['id']])
                ->find();
        }
        
        $sql = Db::name('models')->alias('a')
                ->join('secondcar_rental_models_info b', 'b.models_id=a.id')
                ->field('a.models_name as model_name,a.name as models_name,b.id,b.newpayment,b.monthlypaymen,b.periods,b.totalprices,b.licenseplatenumber')
                ->where(['b.shelfismenu' => 1])
                ->whereOr('sales_id', $this->auth->id)
                ->select();
           
        foreach ((array)$sql as $bValue) {
                $bValue['models_name'] = $bValue['models_name'] . " " . $bValue['model_name'] . '---车牌号为：' . $bValue['licenseplatenumber'] . '【新首付' . $bValue['newpayment'] . '，' . '月供' . $bValue['monthlypaymen'] . '，' . '期数（月）' . $bValue['periods'] . '，' . '总价（元）' . $bValue['totalprices'] . '】';
                $newB[] = $bValue;
        }
        $data = $newB;
        
        $this->view->assign('data', $data);
        $this->view->assign('result', $result);

        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            $params['review_the_data'] = 'is_reviewing_control';
            if ($params) {
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        //车型
                        $models_name = Db::name('models')->where('id', $row['models_id'])->value('name');

                        $data = second_collection_data($models_name, $row['username'], $row['text']);

                        $email = new Email();

                        $receiver = Db::name('admin')->where('rule_message', 'message7')->value('email');

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

                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign('row', $row);

        return $this->view->fetch('secondinformation');

    }

    /**
     *  二手车添加.
     */
    public function secondadd()
    {
        $this->model = new \app\admin\model\SecondSalesOrder;
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $this->view->assign("customerSourceList", $this->model->getCustomerSourceList());
        $this->view->assign("buyInsurancedataList", $this->model->getBuyInsurancedataList());
        $this->view->assign("reviewTheDataList", $this->model->getReviewTheDataList());
        $newRes = array();
        
        $sql = Db::name('models')->alias('a')
                ->join('secondcar_rental_models_info b', 'b.models_id=a.id')
                ->field('a.id,a.models_name as model_name,a.name as models_name,b.id,b.newpayment,b.monthlypaymen,b.periods,b.totalprices,b.bond,b.licenseplatenumber')
                ->where(['b.shelfismenu' => 1])
                ->whereOr('sales_id', $this->auth->id)
                ->select();
           
        foreach ((array)$sql as $bValue) {
                $bValue['models_name'] = $bValue['models_name'] . " " . $bValue['model_name'] . '---车牌号为：' . $bValue['licenseplatenumber'] . '【新首付' . $bValue['newpayment'] . '，' . '月供' . $bValue['monthlypaymen'] . '，' . '期数（月）' . $bValue['periods'] . '，' . '总价（元）' . $bValue['totalprices'] . '】';
                $newB[] = $bValue;
        }
        $data = $newB;
        $this->view->assign('data', $data);

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            $ex = explode(',', $params['plan_car_second_name']);

            $result = DB::name('secondcar_rental_models_info')->where('id', $params['plan_car_second_name'])->field('newpayment,monthlypaymen,periods,bond,models_id')->find();

            $params['car_total_price'] = $result['newpayment'] + $result['monthlypaymen'] * $result['periods'];
            $params['downpayment'] = $result['newpayment'] + $result['monthlypaymen'] + $result['bond'];

            $params['plan_car_second_name'] = reset($ex); //截取id
            $params['plan_name'] = addslashes(end($ex)); //
            //生成订单编号
            $params['order_no'] = date('Ymdhis');
            $params['models_id'] = $result['models_id'];
            //把当前销售员所在的部门的内勤id 入库

            //message8=>销售一部顾问，message13=>内勤一部
            //message9=>销售二部顾问，message20=>内勤二部
            $adminRule = Session::get('admin')['rule_message'];  //测试完后需要把注释放开
            // $adminRule = 'message8'; //测试数据
            if ($adminRule == 'message8') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message13'])->find()['id'];
                // return true;
            }
            if ($adminRule == 'message9') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message20'])->find()['id'];
                // return true;
            }
            if ($adminRule == 'message23') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message24'])->find()['id'];
                // return true;
            }
            if ($params) {
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    if ($result !== false) {
                        //如果添加成功,将状态改为提交审核
                        $result_s = $this->model->isUpdate(true)->save(['id' => $this->model->id, 'review_the_data' => 'is_reviewing']);
                        if ($result_s) {
                            $this->success();
                        } else {
                            $this->error('更新状态失败');
                        }
                    } else {
                        $this->error($this->model->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        return $this->view->fetch();
    }

    /**
     * 二手车删除
     */
    public function seconddel($ids = "")
    {
        $this->model = new \app\admin\model\SecondSalesOrder;
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $count = $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();
            $count = 0;
            foreach ($list as $k => $v) {
                $count += $v->delete();
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    /**
     *  全款新车.
     */
    /**
     * 添加.
     */
    public function fulladd()
    {
        $this->model = new \app\admin\model\FullParmentOrder;
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $this->view->assign('customerSourceList', $this->model->getCustomerSourceList());
        $newRes = array();
       
        $sql = Db::name('models')->alias('a')
                ->join('plan_full b', 'b.models_id=a.id')
                ->field('a.models_name as model_name,a.name as models_name,b.id,b.full_total_price')
                ->where(['b.ismenu' => 1])
                ->select();
           
        foreach ((array)$sql as $bValue) {
                $bValue['models_name'] = $bValue['models_name'] . " " . $bValue['model_name'] . '【全款总价' . $bValue['full_total_price'] . '】';
                $newB[] = $bValue;
        }
        $data = $newB;
           
        $this->view->assign('data', $data);

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');

            if ($params['customer_source'] == "straight") {
                $params['introduce_name'] = null;
                $params['introduce_phone'] = null;
                $params['introduce_card'] = null;
            }

            $ex = explode(',', $params['plan_plan_full_name']);

            $result = DB::name('plan_full')->where('id', $params['plan_plan_full_name'])->field('models_id')->find();

            $params['plan_plan_full_name'] = reset($ex); //截取id
            $params['plan_name'] = addslashes(end($ex)); //
            //生成订单编号
            $params['order_no'] = date('Ymdhis');

            $params['models_id'] = $result['models_id'];
            //把当前销售员所在的部门的内勤id 入库

            //message8=>销售一部顾问，message13=>内勤一部
            //message9=>销售二部顾问，message20=>内勤二部
            $adminRule = Session::get('admin')['rule_message'];  //测试完后需要把注释放开
            // $adminRule = 'message8'; //测试数据
            if ($adminRule == 'message8') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message13'])->find()['id'];
                // return true;
            }
            if ($adminRule == 'message9') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message20'])->find()['id'];
                // return true;
            }
            if ($adminRule == 'message23') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message24'])->find()['id'];
                // return true;
            }
            if ($adminRule == 'message33') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message20'])->find()['id'];
                // return true;
            }
            if ($params) {
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    if ($result !== false) {
                        //如果添加成功,将状态改为提交审核
                        $result_s = $this->model->isUpdate(true)->save(['id' => $this->model->id, 'review_the_data' => 'send_to_internal']);
                        if ($result_s) {
                            $this->success();
                        } else {
                            $this->error('更新状态失败');
                        }
                    } else {
                        $this->error($this->model->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        return $this->view->fetch();
    }

    /**
     * 全款新车编辑.
     */
    public function fulledit($ids = NULL)
    {
        $this->model = new \app\admin\model\FullParmentOrder;
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $this->view->assign('customerSourceList', $this->model->getCustomerSourceList());

        $row = $this->model->get($ids);

        //关联订单于方案
        $result = Db::name('full_parment_order')->alias('a')
            ->join('plan_full b', 'a.plan_plan_full_name = b.id')
            ->field('b.id as plan_id')
            ->where(['a.id' => $row['id']])
            ->find();

       
        $sql = Db::name('models')->alias('a')
                ->join('plan_full b', 'b.models_id=a.id')
                ->field('a.models_name as model_name,a.name as models_name,b.id,b.full_total_price')
                ->where(['b.ismenu' => 1])
                ->select();
            
        foreach ((array)$sql as $bValue) {
                $bValue['models_name'] = $bValue['models_name'] . " " . $bValue['model_name'] . '【全款总价' . $bValue['full_total_price'] . '】';
                $newB[] = $bValue;
        }

        $data = $newB;

        $this->view->assign(
            [
                "data" => $data,
                "result" => $result
            ]
        );

        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $ex = explode(',', $params['plan_plan_full_name']);

            if ($params['customer_source'] == "straight") {
                $params['introduce_name'] = null;
                $params['introduce_phone'] = null;
                $params['introduce_card'] = null;
            }
            $result = DB::name('plan_full')->where('id', $params['plan_plan_full_name'])->field('models_id')->find();

            $params['plan_plan_full_name'] = reset($ex); //截取id
            $params['plan_name'] = addslashes(end($ex));
            $params['models_id'] = $result['models_id'];

            if ($params) {
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }


    /**
     * 全款新车提交内勤
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function submitCar()
    {
        $this->model = new \app\admin\model\FullParmentOrder;
        if ($this->request->isAjax()) {
            $id = $this->request->post('id');

            $admin_nickname = DB::name('admin')->alias('a')->join('full_parment_order b', 'b.admin_id=a.id')->where('b.id', $id)->value('a.nickname');

            $result = $this->model->isUpdate(true)->save(['id' => $id, 'review_the_data' => 'inhouse_handling']);

            if ($result !== false) {

                $data = Db::name("full_parment_order")->where('id', $id)->find();
                //车型
                $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
                //销售员
                $backoffice_id = $data['backoffice_id'];
                $admin_name = DB::name('admin')->where('id', $data['admin_id'])->value('nickname');
                //客户姓名
                $username = $data['username'];

                $data = fullinternal_inform($models_name, $admin_name, $username);
                // var_dump($data);
                // die;
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = DB::name('admin')->where('id', $backoffice_id)->value('email');
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

    /**
     * 全款新车删除
     */
    public function fulldel($ids = "")
    {
        $this->model = new \app\admin\model\FullParmentOrder;
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $count = $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();
            $count = 0;
            foreach ($list as $k => $v) {
                $count += $v->delete();
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    /**
     *  全款二手车.
     */
    /**
     * 全款二手车预定.
     */
    public function secondfulladd()
    {
        $this->model = new \app\admin\model\SecondFullOrder;
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $this->view->assign("customerSourceList", $this->model->getCustomerSourceList());
        $this->view->assign("reviewTheDataList", $this->model->getReviewTheDataList());
        $newRes = array();
        
        
        $sql = Db::name('models')->alias('a')
                ->join('secondcar_rental_models_info b', 'b.models_id=a.id')
                ->field('a.models_name as model_name,a.name as models_name,b.id,b.totalprices,b.licenseplatenumber')
                ->where(['b.status_data' => '', 'b.shelfismenu' => 1])
                ->select();
            
        foreach ((array)$sql as $bValue) {
                $bValue['models_name'] = $bValue['models_name'] . " " . $bValue['model_name'] . '---车牌号为：' . $bValue['licenseplatenumber'] . '【全款总价' . $bValue['totalprices'] . '】';
                $newB[] = $bValue;
        }

        $data = $newB;
        $this->view->assign('data', $data);

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');

            if ($params['customer_source'] == "straight") {
                $params['introduce_name'] = null;
                $params['introduce_phone'] = null;
                $params['introduce_card'] = null;
            }

            $ex = explode(',', $params['plan_second_full_name']);

            $result = Db::name('secondcar_rental_models_info')->where('id', $params['plan_second_full_name'])->field('models_id')->find();

            $params['plan_second_full_name'] = reset($ex); //截取id
            $params['plan_name'] = addslashes(end($ex)); //
            //生成订单编号
            $params['order_no'] = date('Ymdhis');

            $params['models_id'] = $result['models_id'];
            //把当前销售员所在的部门的内勤id 入库

            //message8=>销售一部顾问，message13=>内勤一部
            //message9=>销售二部顾问，message20=>内勤二部
            $adminRule = Session::get('admin')['rule_message'];  //测试完后需要把注释放开
            // $adminRule = 'message8'; //测试数据
            if ($adminRule == 'message8') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message13'])->find()['id'];
                // return true;
            }
            if ($adminRule == 'message9') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message20'])->find()['id'];
                // return true;
            }
            if ($adminRule == 'message23') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message24'])->find()['id'];
                // return true;
            }
            if ($adminRule == 'message33') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message20'])->find()['id'];
                // return true;
            }
            if ($params) {
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    if ($result !== false) {
                        //如果添加成功,将状态改为提交审核
                        $result_s = $this->model->isUpdate(true)->save(['id' => $this->model->id, 'review_the_data' => 'send_to_internal']);
                        if ($result_s) {
                            $this->success();
                        } else {
                            $this->error('更新状态失败');
                        }
                    } else {
                        $this->error($this->model->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        return $this->view->fetch();
    }

    /**
     * 全款二手车提交内勤
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function secondfullinternal()
    {
        $this->model = new \app\admin\model\SecondFullOrder;
        if ($this->request->isAjax()) {
            $id = $this->request->post('id');

            $admin_nickname = Db::name('admin')->alias('a')->join('second_full_order b', 'b.admin_id=a.id')->where('b.id', $id)->value('a.nickname');

            $result = $this->model->isUpdate(true)->save(['id' => $id, 'review_the_data' => 'inhouse_handling']);

            if ($result !== false) {

                $this->model = model('secondcar_rental_models_info');

                $plan_second_full_name = Db::name('second_full_order')->where('id', $id)->value('plan_second_full_name');

                $this->model->isUpdate(true)->save(['id' => $plan_second_full_name, 'status_data' => 'send_the_car']);

                $data = Db::name("second_full_order")->where('id', $id)->find();
                //车型
                $models_name = Db::name('models')->where('id', $data['models_id'])->value('name');
                //销售员
                $backoffice_id = $data['backoffice_id'];
                $admin_name = Db::name('admin')->where('id', $data['admin_id'])->value('nickname');
                //客户姓名
                $username = $data['username'];

                $data = second_full_backoffice($models_name, $admin_name, $username);
                
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = Db::name('admin')->where('id', $backoffice_id)->value('email');
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

    /**
     * 全款二手车删除
     */
    public function secondfulldel($ids = "")
    {
        $this->model = new \app\admin\model\SecondFullOrder;

        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $count = $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();
            $count = 0;
            foreach ($list as $k => $v) {
                $count += $v->delete();
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    /**
     * 全款二手车编辑.
     */
    public function secondfulledit($ids = NULL)
    {
        $this->model = new \app\admin\model\SecondFullOrder;
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $this->view->assign("customerSourceList", $this->model->getCustomerSourceList());
        $this->view->assign("reviewTheDataList", $this->model->getReviewTheDataList());

        $row = $this->model->get($ids);

        //关联订单于方案
        $result = Db::name('second_full_order')->alias('a')
            ->join('secondcar_rental_models_info b', 'a.plan_second_full_name = b.id')
            ->field('b.id as plan_id')
            ->where(['a.id' => $row['id']])
            ->find();


        $sql = Db::name('models')->alias('a')
                ->join('secondcar_rental_models_info b', 'b.models_id=a.id')
                ->field('a.models_name as model_name,a.name as models_name,b.id,b.totalprices,b.licenseplatenumber')
                ->where(['b.status_data' => '', 'b.shelfismenu' => 1])
                ->select();
            
        foreach ((array)$sql as $bValue) {
                $bValue['models_name'] = $bValue['models_name'] . " " . $bValue['model_name'] . '---车牌号为：' . $bValue['licenseplatenumber'] . '【全款总价' . $bValue['totalprices'] . '】';
                $newB[] = $bValue;
        }

        $data = $newB;

        $this->view->assign(
            [
                "data" => $data,
                "result" => $result
            ]
        );

        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $ex = explode(',', $params['plan_second_full_name']);

            if ($params['customer_source'] == "straight") {
                $params['introduce_name'] = null;
                $params['introduce_phone'] = null;
                $params['introduce_card'] = null;
            }
            $result = Db::name('secondcar_rental_models_info')->where('id', $params['plan_second_full_name'])->field('models_id')->find();

            $params['plan_second_full_name'] = reset($ex); //截取id
            $params['plan_name'] = addslashes(end($ex));
            $params['models_id'] = $result['models_id'];

            if ($params) {
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 删除订单
     */
    public function del_order()
    {
        if ($this->request->isAjax()) {
            $flag = input('flag');
            $id = input('id');

            switch ($flag) {
                //删除以租代购新车
                case -1:
                    $del_table = Db::name('sales_order')
                        ->where('id', $id)
                        ->field('mortgage_registration_id,registry_registration_id,mortgage_id,referee_id,customer_downpayment_id,violation_inquiry_id')
                        ->find();


                    if ($del_table['mortgage_registration_id']) {
                        Db::name('mortgage_registration')
                            ->where('id', $del_table['mortgage_registration_id'])
                            ->delete();
                    }

                    if ($del_table['registry_registration_id']) {
                        Db::name('registry_registration')
                            ->where('id', $del_table['registry_registration_id'])
                            ->delete();
                    }

                    if ($del_table['mortgage_id']) {
                        Db::name('mortgage')
                            ->where('id', $del_table['mortgage_id'])
                            ->delete();
                    }

                    if ($del_table['referee_id']) {
                        Db::name('referee')
                            ->where('id', $del_table['referee_id'])
                            ->delete();
                    }

                    if ($del_table['customer_downpayment_id']) {
                        Db::name('customer_downpayment')
                            ->where('id', $del_table['customer_downpayment_id'])
                            ->delete();
                    }

                    if ($del_table['violation_inquiry_id']) {
                        Db::name('violation_inquiry')
                            ->where('id', $del_table['violation_inquiry_id'])
                            ->delete();
                    }


                    $res = Db::name('sales_order')
                        ->where('id', $id)
                        ->delete();

                    break;
                //删除租车
                case -2:
                    $del_table = Db::name('rental_order')
                        ->where('id', $id)
                        ->field('referee_id,customer_downpayment_id,car_rental_models_info_id,violation_inquiry_id')
                        ->find();

                    if ($del_table['referee_id']) {
                        Db::name('referee_id')
                            ->where('id', $del_table['referee_id'])
                            ->delete();
                    }

                    if ($del_table['customer_downpayment_id']) {
                        Db::name('customer_downpayment')
                            ->where('id', $del_table['customer_downpayment_id'])
                            ->delete();
                    }

                    if ($del_table['car_rental_models_info_id']) {
                        Db::name('car_rental_models_info')
                            ->where('id', $del_table['car_rental_models_info_id'])
                            ->setField('status_data', null);
                    }

                    if ($del_table['violation_inquiry_id']) {
                        Db::name('violation_inquiry')
                            ->where('id', $del_table['violation_inquiry_id'])
                            ->delete();
                    }

                    $res = Db::name('rental_order')
                        ->where('id', $id)
                        ->delete();

                    break;
                //删除二手车
                case -3:
                    $del_table = Db::name('second_sales_order')
                        ->where('id', $id)
                        ->field('mortgage_registration_id,registry_registration_id,referee_id,customer_downpayment_id,plan_car_second_name,violation_inquiry_id')
                        ->find();

                    if ($del_table['plan_car_second_name']) {
                        Db::name('secondcar_rental_models_info')
                            ->where('id', $del_table['plan_car_second_name'])
                            ->setField('status_data', null);
                    }

                    if ($del_table['mortgage_registration_id']) {
                        Db::name('mortgage_registration')
                            ->where('id', $del_table['mortgage_registration_id'])
                            ->delete();
                    }

                    if ($del_table['registry_registration_id']) {
                        Db::name('registry_registration')
                            ->where('id', $del_table['registry_registration_id'])
                            ->delete();
                    }

                    if ($del_table['referee_id']) {
                        Db::name('referee')
                            ->where('id', $del_table['referee_id'])
                            ->delete();
                    }

                    if ($del_table['customer_downpayment_id']) {
                        Db::name('customer_downpayment')
                            ->where('id', $del_table['customer_downpayment_id'])
                            ->delete();
                    }

                    if ($del_table['violation_inquiry_id']) {
                        Db::name('violation_inquiry')
                            ->where('id', $del_table['violation_inquiry_id'])
                            ->delete();
                    }

                    $res = Db::name('second_sales_order')
                        ->where('id', $id)
                        ->delete();

                    break;
                //删除全款车
                case -4:

                    $del_table = Db::name('full_parment_order')
                        ->where('id', $id)
                        ->field('registry_registration_id,mortgage_registration_id,customer_downpayment_id,referee_id,violation_inquiry_id,mortgage_id')
                        ->find();

                    if ($del_table['registry_registration_id']) {
                        Db::name('registry_registration')
                            ->where('id', $del_table['registry_registration_id'])
                            ->delete();
                    }

                    if ($del_table['mortgage_registration_id']) {
                        Db::name('mortgage_registration')
                            ->where('id', $del_table['mortgage_registration_id'])
                            ->delete();
                    }

                    if ($del_table['customer_downpayment_id']) {
                        Db::name('customer_downpayment')
                            ->where('id', $del_table['customer_downpayment_id'])
                            ->delete();
                    }

                    if ($del_table['referee_id']) {
                        Db::name('referee')
                            ->where('id', $del_table['referee_id'])
                            ->delete();
                    }

                    if ($del_table['violation_inquiry_id']) {
                        Db::name('violation_inquiry')
                            ->where('id', $del_table['violation_inquiry_id'])
                            ->delete();
                    }

                    if ($del_table['mortgage_id']) {
                        Db::name('mortgage')
                            ->where('id', $del_table['mortgage_id'])
                            ->delete();
                    }

                    $res = Db::name('full_parment_order')
                        ->where('id', $id)
                        ->delete();

                    break;

                case  -5:
                    $del_table = Db::name('second_full_order')
                        ->where('id', $id)
                        ->field('registry_registration_id,mortgage_registration_id,customer_downpayment_id,referee_id,violation_inquiry_id')
                        ->find();


                    if ($del_table['registry_registration_id']) {
                        Db::name('registry_registration')
                            ->where('id', $del_table['registry_registration_id'])
                            ->delete();
                    }

                    if ($del_table['mortgage_registration_id']) {
                        Db::name('mortgage_registration')
                            ->where('id', $del_table['mortgage_registration_id'])
                            ->delete();
                    }

                    if ($del_table['customer_downpayment_id']) {
                        Db::name('customer_downpayment')
                            ->where('id', $del_table['customer_downpayment_id'])
                            ->delete();
                    }

                    if ($del_table['referee_id']) {
                        Db::name('referee')
                            ->where('id', $del_table['referee_id'])
                            ->delete();
                    }

                    if ($del_table['violation_inquiry_id']) {
                        Db::name('violation_inquiry')
                            ->where('id', $del_table['violation_inquiry_id'])
                            ->delete();
                    }

                    $res = Db::name('second_full_order')
                        ->where('id', $id)
                        ->delete();

                    break;
            }

            if ($res) {
                $this->success('', '', 'success');
            } else {
                $this->error('', '', 'error');
            }
        }
    }

    


}

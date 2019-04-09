<?php

namespace app\admin\controller\riskcontrol;

use app\common\controller\Backend;
use think\Db;
use think\Config;
use think\db\exception\DataNotFoundException;
use app\admin\model\SalesOrder as salesOrderModel;
use app\admin\model\SecondSalesOrder as secondSalesOrderModel;
use app\admin\model\RentalOrder as rentalOrderModel;

use app\admin\controller\Bigdata as bg;
use app\common\library\Email;
use think\Cache;
use think\Session;
use app\admin\controller\Sharedetailsdatas;

/**
 * 订单列管理.
 *
 * @icon fa fa-circle-o
 */
class Creditreview extends Backend
{
    /**
     * Ordertabs模型对象
     *
     * @var \app\admin\model\Ordertabs
     */
    protected $model = null;
    protected $userid = 'junyi'; //用户id
    protected $Rc4 = 'd477d6d1803125f1'; //apikey
    protected $sign = null; //sign  md5加密
    protected $searchFields = 'username';
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('SalesOrder');
        $this->view->assign('genderdataList', $this->model->getGenderdataList());
        $this->view->assign('customerSourceList', $this->model->getCustomerSourceList());
        $this->view->assign('reviewTheDataList', $this->model->getReviewTheDataList());
        //共享userid 、sign
        $this->sign = md5($this->userid . $this->Rc4);
    }

    public function index()
    {

        $this->loadlang('order/salesorder');

        $this->view->assign([
            'total' => $this->model
                ->where('review_the_data', ['=', 'is_reviewing_true'], ['=', 'for_the_car'], ['=', 'is_reviewing_pass'], ['=', 'not_through'], ['=', 'conclude_the_contract'],
                    ['=', 'tube_into_stock'], ['=', 'take_the_car'], ['=', 'take_the_data'], ['=', 'inform_the_tube'], ['=', 'send_the_car'], 'or')
                ->count(),

            'total1' => Db::name('rental_order')
                ->where('review_the_data', ['=', 'is_reviewing_pass'], ['=', 'is_reviewing_nopass'], ['=', 'is_reviewing_control'], 'or')
                ->count(),
            'total2' => Db::name('second_sales_order')
                ->where('review_the_data', ['=', 'is_reviewing_control'], ['=', 'is_reviewing_pass'], ['=', 'not_through'], ['=', 'for_the_car'], 'or')
                ->count(),
            'total3' => $this->model
                ->where('review_the_data', '=', 'the_car')
                ->count(),
            'total4' => Db::name('rental_order')
                ->where('review_the_data', '=', 'for_the_car')
                ->count(),
            'total5' => Db::name('second_sales_order')
                ->where('review_the_data', '=', 'the_car')
                ->count(),
        ]);

        return $this->view->fetch();
    }


    /**
     * 展示需要审核的新车销售单
     * @return string|\think\response\Json
     * @throws \think\Exception
     */
    public function newcarAudit()
    {
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
                    $query->withField(['id', 'avatar', 'nickname']);
                }, 'models' => function ($query) {
                    $query->withField(['name', 'models_name']);
                }])
                ->where($where)
                ->where('review_the_data', ['=', 'is_reviewing_true'], ['=', 'for_the_car'], ['=', 'is_reviewing_pass'], ['=', 'not_through'], ['=', 'the_car'], ['=', 'conclude_the_contract'],
                    ['=', 'tube_into_stock'], ['=', 'take_the_car'], ['=', 'take_the_data'], ['=', 'inform_the_tube'], ['=', 'send_the_car'], ['=', 'collection_data'], 'or')
                ->order($sort, $order)
                ->count();


            $list = $this->model
                ->with(['planacar' => function ($query) {
                    $query->withField('payment,monthly,nperlist,margin,tail_section,gps');
                }, 'admin' => function ($query) {
                    $query->withField(['id', 'avatar', 'nickname']);
                }, 'models' => function ($query) {
                    $query->withField(['name', 'models_name']);
                }])
                ->where($where)
                ->where('review_the_data', ['=', 'is_reviewing_true'], ['=', 'for_the_car'], ['=', 'is_reviewing_pass'], ['=', 'not_through'], ['=', 'the_car'], ['=', 'conclude_the_contract'],
                    ['=', 'tube_into_stock'], ['=', 'take_the_car'], ['=', 'take_the_data'], ['=', 'inform_the_tube'], ['=', 'send_the_car'], ['=', 'collection_data'], 'or')
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $row) {
                $row->visible(['id', 'plan_acar_name', 'order_no', 'username', 'financial_name', 'detailed_address', 'createtime', 'phone', 'difference', 'decorate', 'car_total_price', 'id_card', 'amount_collected', 'downpayment',
                    'review_the_data', 'id_cardimages', 'drivers_licenseimages', 'bank_cardimages', 'undertakingimages', 'accreditimages', 'faceimages', 'informationimages']);
                $row->visible(['planacar']);
                $row->getRelation('planacar')->visible(['payment', 'monthly', 'margin', 'nperlist', 'tail_section', 'gps',]);
                $row->visible(['admin']);
                $row->getRelation('admin')->visible(['id', 'avatar', 'nickname']);
                $row->visible(['models']);
                $row->getRelation('models')->visible(['name']);
                
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
                //是否有da数据
                $list[$k]['bigdata'] = self::matchBigData($v['id']);
            }
            $result = array('total' => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch("index");

    }

    /**
     *匹配新车是否有大数据结果集
     * @param $orderId 订单Id
     * @param $orderType  订单类型
     * @return mixed  Id
     * @throws DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function matchBigData($orderId)
    {

        $data = salesOrderModel::with(['bigdata' => function ($query) {
            $query->withField('id');
        }])->select(['id' => $orderId]);
        return collection($data)->toArray()[0]['bigdata']['id'];
    }


    /**
     * 展示需要审核的租车单
     * @return string|\think\response\Json
     * @throws \think\Exception
     */
    public function rentalcarAudit()
    {
        $this->model = model('RentalOrder');
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
                    $query->withField(['id', 'avatar', 'nickname']);
                }, 'models' => function ($query) {
                    $query->withField(['name', 'models_name']);
                }, 'carrentalmodelsinfo' => function ($query) {
                    $query->withField('licenseplatenumber,vin');
                }])
                ->where($where)
                ->order($sort, $order)
                ->where('review_the_data', ['=', 'is_reviewing_pass'], ['=', 'is_reviewing_nopass'], ['=', 'is_reviewing_control'], ['=', 'for_the_car'], ['=', 'collection_data'], 'or')
                ->count();

            $list = $this->model
                ->with(['admin' => function ($query) {
                    $query->withField(['id', 'avatar', 'nickname']);
                }, 'models' => function ($query) {
                    $query->withField(['name', 'models_name']);
                }, 'carrentalmodelsinfo' => function ($query) {
                    $query->withField('licenseplatenumber,vin');
                }])
                ->where($where)
                ->order($sort, $order)
                ->where('review_the_data', ['=', 'is_reviewing_pass'], ['=', 'is_reviewing_nopass'], ['=', 'is_reviewing_control'], ['=', 'for_the_car'], ['=', 'collection_data'], 'or')
                ->select();
            foreach ($list as $k => $row) {
                $row->visible(['id', 'plan_car_rental_name', 'order_no', 'createtime', 'username', 'phone', 'id_card', 'cash_pledge', 'rental_price', 'tenancy_term', 'review_the_data']);
                $row->visible(['admin']);
                $row->getRelation('admin')->visible(['id', 'avatar', 'nickname']);
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
                //是否有da数据

                $list[$k]['bigdata'] = self::matchBigData2($v['id']);
            }
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch('index');

    }

    /**
     *匹配租车是否有大数据结果集
     * @param $orderId 订单Id
     * @return mixed  Id
     * @throws DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function matchBigData2($orderId)
    {

        $data = rentalOrderModel::with(['bigdata' => function ($query) {
            $query->withField('id');
        }])->select(['id' => $orderId]);
        return collection($data)->toArray()[0]['bigdata']['id'];
    }


    /**
     * 展示需要审核的二手车单
     * @return string|\think\response\Json
     * @throws DataNotFoundException
     * @throws \think\Exception
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function secondhandcarAudit()

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

            $total = $this->model
                ->with(['plansecond' => function ($query) {
                    $query->withField('companyaccount,licenseplatenumber,newpayment,monthlypaymen,periods,totalprices,bond,tailmoney');
                }, 'admin' => function ($query) {
                    $query->withField(['id', 'avatar', 'nickname']);
                }, 'models' => function ($query) {
                    $query->withField(['name', 'models_name']);
                }])
                ->where($where)
                ->where('review_the_data', ['=', 'for_the_car'], ['=', 'is_reviewing_pass'], ['=', 'is_reviewing_control'], ['=', 'not_through'], ['=', 'the_car'], ['=', 'collection_data'], 'or')
                ->order($sort, $order)
                ->count();


            $list = $this->model
                ->with(['plansecond' => function ($query) {
                    $query->withField('companyaccount,licenseplatenumber,newpayment,monthlypaymen,periods,totalprices,bond,tailmoney');
                }, 'admin' => function ($query) {
                    $query->withField(['id', 'avatar', 'nickname']);
                }, 'models' => function ($query) {
                    $query->withField(['name', 'models_name']);
                }])
                ->where($where)
                ->where('review_the_data', ['=', 'for_the_car'], ['=', 'is_reviewing_pass'], ['=', 'is_reviewing_control'], ['=', 'not_through'], ['=', 'the_car'], ['=', 'collection_data'], 'or')
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $row) {
                $row->visible(['id', 'plan_car_second_name', 'order_no', 'username', 'city', 'detailed_address', 'createtime', 'phone', 'id_card', 'amount_collected', 'downpayment', 'review_the_data',
                    'id_cardimages', 'drivers_licenseimages',]);
                $row->visible(['plansecond']);
                $row->getRelation('plansecond')->visible(['newpayment', 'licenseplatenumber', 'companyaccount', 'monthlypaymen', 'periods', 'totalprices', 'bond', 'tailmoney',]);
                $row->visible(['admin']);
                $row->getRelation('admin')->visible(['id', 'avatar', 'nickname']);
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
                //是否有da数据
                $list[$k]['bigdata'] = self::matchBigData3($v['id']);
            }
            $result = array('total' => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();

    }

    /**
     *匹配二手车是否有大数据结果集
     * @param $orderId 订单Id
     * @param $orderType  订单类型
     * @return mixed  Id
     * @throws DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function matchBigData3($orderId)
    {

        $data = secondSalesOrderModel::with(['bigdata' => function ($query) {
            $query->withField('id');
        }])->select(['id' => $orderId]);
        return collection($data)->toArray()[0]['bigdata']['id'];
    }

    /**
     * 根据方案id查询 车型名称，首付、月供等. 新车
     */
    public function getPlanAcarData($planId)
    {
        return Db::name('plan_acar')->alias('a')
            ->join('models b', 'a.models_id=b.id')
            ->join('financial_platform c', 'a.financial_platform_id= c.id')
            ->field('a.id,a.payment,a.monthly,a.nperlist,a.margin,a.tail_section,a.gps,a.note,
                        b.name as models_name,
                        c.name as financial_platform_name')
            ->where('a.id', $planId)
            ->find();
    }

    /**
     * 根据方案id查询 车型名称，首付、月供等. 二手车
     */
    public function getPlanSecondCarData($planId)
    {
        return Db::name('secondcar_rental_models_info')->alias('a')
            ->join('models b', 'a.models_id=b.id')
            ->field('a.id,a.newpayment,a.monthlypaymen,a.periods,a.totalprices,
                        b.name as models_name')
            ->where('a.id', $planId)
            ->find();
    }

    /** 审核销售提交过来的销售新车单*/

    public function newauditResult($ids = null, $bigdatatype = null)

    {


        if ($bigdatatype == 'sales_order') $bigdatatype = 'sales_order_id';
        if ($bigdatatype == 'rental_order') $bigdatatype = 'rental_order_id';
        if ($bigdatatype == 'second_sales_order') $bigdatatype = 'second_sales_order_id';
        $result = model('BigData')->get([$bigdatatype => $ids]);
        if (empty($result)) $this->error('请先查看一次大数据', '', $result);
        $row = model('SalesOrder')->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->getDataLimitAdminIds();
        //身份证图片

        $id_cardimages = $row['id_cardimages'] == '' ? [] : explode(',', $row['id_cardimages']);
        //驾照图片
        $drivers_licenseimages = $row['drivers_licenseimages'] == '' ? [] : explode(',', $row['drivers_licenseimages']);
        //户口簿图片
        $residence_bookletimages = $row['residence_bookletimages'] == '' ? [] : explode(',', $row['residence_bookletimages']);
        //住房合同/房产证图片
        $housingimages = $row['housingimages'] == '' ? [] : explode(',', $row['housingimages']);
        //银行卡图片
        $bank_cardimages = $row['bank_cardimages'] == '' ? [] : explode(',', $row['bank_cardimages']);
        //申请表图片
        $application_formimages = $row['application_formimages'] == '' ? [] : explode(',', $row['application_formimages']);
        //定金合同
        $deposit_contractimages = $row['deposit_contractimages'] == '' ? [] : explode(',', $row['deposit_contractimages']);
        //定金收据
        $deposit_receiptimages = $row['deposit_receiptimages'] == '' ? [] : explode(',', $row['deposit_receiptimages']);
        //通话清单
        $call_listfiles = $row['call_listfiles'] == '' ? [] : explode(',', $row['call_listfiles']);
        /**不必填 */
        //保证金收据
        $new_car_marginimages = $row['new_car_marginimages'] == '' ? [] : explode(',', $row['new_car_marginimages']);
        $this->view->assign(

            [
                'row' => $row,
                'cdn' => Config::get('upload')['cdnurl'],
                'id_cardimages' => $id_cardimages,
                'drivers_licenseimages' => $drivers_licenseimages,
                'residence_bookletimages' => $residence_bookletimages,
                'housingimages' => $housingimages,
                'bank_cardimages' => $bank_cardimages,
                'application_formimages' => $application_formimages,
                'deposit_contractimages' => $deposit_contractimages,
                'deposit_receiptimages' => $deposit_receiptimages,
                'call_listfiles' => $call_listfiles,
                'new_car_marginimages' => $new_car_marginimages
            ]
        );

        return $this->view->fetch('newauditResult');

    }


    /**
     * 新车单----审核通过
     */
    public function newpass()
    {
        if ($this->request->isAjax()) {
//            pr($this->setBigDataSuccess());
//            die;
            $this->model = model('SalesOrder');

            $id = input("id");

            $id = json_decode($id, true);
            //金融平台
            $financial_name = $this->model->where('id', $id)->value('financial_name');
            if ($financial_name == "一汽租赁") {

                $admin_nickname = DB::name('admin')->alias('a')->join('sales_order b', 'b.admin_id=a.id')->where('b.id', $id)->value('a.nickname');

                $result = $this->model->save(['review_the_data' => 'for_the_car'], function ($query) use ($id) {
                    $query->where('id', $id);
                });

                if ($result) {

                    $this->success();

                } else {
                    $this->error();
                }

            } else {

                $admin_nickname = DB::name('admin')->alias('a')->join('sales_order b', 'b.admin_id=a.id')->where('b.id', $id)->value('a.nickname');

                $result = $this->model->save(['review_the_data' => 'is_reviewing_pass'], function ($query) use ($id) {
                    $query->where('id', $id);
                });

                if ($result) {

                    $this->success();

                } else {
                    $this->error();
                }

            }

        }
    }

    public function setBigDataSuccess()
    {
        /*  $data = $this->getBigData(12,'sales_order');

          $data['share_data']['params']['data']['loanRecords'][] =[
              'overdueAmount'=>'(10000,50000]',
              'orgName'=>25467,
              'overdueTotal'=>'',
              'name'=>$data['name'],
              'certNo'=>$data['id_card'],
              'overdueM6'=>'',
              'loanStatusCode'=>'',
              'overdueM3'=>$data['name'],
              //借款金额 =
              'loanAmount'=>'',
              'overdueM3'=>$data['name'],
              'overdueM3'=>$data['name'],
  */

//        ] ;
//        $data['share_data']['params']['data']['loanRecords'][]['orgName'] = 25467;
//                $data['share_data']['params']['data']['loanRecords'][]['overdueTotal'] = $data['name'];
//
//        $data['share_data']['params']['data']['loanRecords'][]['name'] = $data['name'];
//        $data['share_data']['params']['data']['loanRecords'][]['certNo'] = $data['id_card'];
////        $data['share_data']['params']['data']['loanRecords'][]['overdueAmount'] = '(10000,50000]';
////        $data['share_data']['params']['data']['loanRecords'][]['overdueAmount'] = '(10000,50000]';
////        $data['share_data']['params']['data']['loanRecords'][]['overdueAmount'] = '(10000,50000]';
////        $data['share_data']['params']['data']['loanRecords'][]['overdueAmount'] = '(10000,50000]';

//        return  $data;
    }


    /**
     * 通知销售---签订金融合同
     */
    public function newsales()
    {
        $this->model = model('SalesOrder');

        if ($this->request->isAjax()) {
            $id = $this->request->post('id');

            $result = $this->model->isUpdate(true)->save(['id' => $id, 'review_the_data' => 'conclude_the_contract']);
            //销售员
            $admin_id = $this->model->where('id', $id)->value('admin_id');

            $models_id = $this->model->where('id', $id)->value('models_id');
            //车型
            $models_name = DB::name('models')->where('id', $models_id)->value('name');
            //客户姓名
            $username = $this->model->where('id', $id)->value('username');

            if ($result !== false) {

                $data = newpass_finance($models_name, $username);
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


    /**
     * 通知车管---录入库存
     */
    public function newtube()
    {
        $this->model = model('SalesOrder');

        if ($this->request->isAjax()) {
            $id = $this->request->post('id');

            $result = $this->model->isUpdate(true)->save(['id' => $id, 'review_the_data' => 'tube_into_stock']);
            //销售员
            $admin_id = $this->model->where('id', $id)->value('admin_id');

            $models_id = $this->model->where('id', $id)->value('models_id');
            //车型
            $models_name = DB::name('models')->where('id', $models_id)->value('name');
            //客户姓名
            $username = $this->model->where('id', $id)->value('username');

            if ($result !== false) {

                $data = newcontrol_tube($models_name, $username);
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

    //

    /**
     * 通知车管---录入库存---其他金融
     */
    public function newtubefinance()
    {
        $this->model = model('SalesOrder');

        if ($this->request->isAjax()) {
            $id = $this->request->post('id');

            $result = $this->model->isUpdate(true)->save(['id' => $id, 'review_the_data' => 'tube_into_stock']);
            //销售员
            $admin_id = $this->model->where('id', $id)->value('admin_id');

            $models_id = $this->model->where('id', $id)->value('models_id');
            //车型
            $models_name = DB::name('models')->where('id', $models_id)->value('name');
            //客户姓名
            $username = $this->model->where('id', $id)->value('username');

            if ($result !== false) {

                $data = newcontrol_tube_finance($models_name, $username);
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
     * 展示可选库存车
     */
    public function recyclebin($ids = null)
    {
        $this->model = model('CarNewInventory');
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('models.name', true);
            $total = $this->model
                ->with(['models' => function ($query) {
                    $query->withField('name,models_name');
                }])
                ->where($where)
                ->where("statuss", 1)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['models' => function ($query) {
                    $query->withField('name,models_name');
                }])
                ->where($where)
                ->where("statuss", 1)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
                
            //展示的信息
            foreach ($list as $key=>$row) {

                if ($list[$key]['models']['models_name']) {
                    $list[$key]['models']['name'] = $list[$key]['models']['name'] . " " . $list[$key]['models']['models_name'];
                }

            }
            
            $result = array("total" => $total, "rows" => $list);

            return json($result); 
        }
        $this->view->assign('sales_order_id', $ids);
        return $this->view->fetch();
    }

    /**
     * 选择车辆
     */
    public function choose()
    {
        if ($this->request->isAjax()) {

            $id = $this->request->post('id');
            $ids = $this->request->post('ids');
            // pr($id);
            // pr($ids);
            // die;
            Db::name("sales_order")
                ->where("id", $ids)
                ->update([
                    'car_new_inventory_id' => $id,
                    'review_the_data' => "take_the_car",
                    'delivery_datetime' => time()
                ]);

            Db::name("car_new_inventory")
                ->where("id", $id)
                ->setField("statuss", 0);

            $result = Db::name('sales_order')->where('id', $ids)->find();

            $models_name = Db::name('models')->where('id', $result['models_id'])->value('name');

            $data = newchoose_stock($models_name, $result['username']);
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

            //金融平台
            $financial_name = $result['financial_name'];

            if ($financial_name == "一汽租赁") {

                if ($result_s) {
                    $this->success();
                } else {
                    $this->error('邮箱发送失败');
                }

            } else {

                $data = newpass_finance($models_name, $result['username']);
                // var_dump($data);
                // die;
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = Db::name('admin')->where('id', $result['admin_id'])->value('email');
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

            }


            //介绍人

            $order_info = Db::name("sales_order")
                ->where("id", $ids)
                ->field("customer_source,turn_to_introduce_name,turn_to_introduce_phone,turn_to_introduce_card,admin_id,models_id,username,phone")
                ->find();

            if ($order_info['customer_source'] == "turn_to_introduce") {

                $insert_data = [
                    'models_id' => $order_info['models_id'],
                    'admin_id' => $order_info['admin_id'],
                    'referee_name' => $order_info['turn_to_introduce_name'],
                    'referee_phone' => $order_info['turn_to_introduce_phone'],
                    'referee_idcard' => $order_info['turn_to_introduce_card'],
                    'customer_name' => $order_info['username'],
                    'customer_phone' => $order_info['phone'],
                    'buy_way' => '新车'
                ];

                Db::name("referee")->insert($insert_data);

                $last_id = Db::name("referee")->getLastInsID();

                Db::name("sales_order")
                    ->where("id", $ids)
                    ->setField("referee_id", $last_id);
            }

            $this->success('', '', $ids);

        }

        $seventtime = \fast\Date::unixtime('month', -6);
        $newonesales = $newtwosales = $newthreesales = [];
        for ($i = 0; $i < 8; $i++) {
            $month = date("Y-m", $seventtime + ($i * 86400 * 30));
            //销售一部
            $one_sales = DB::name('auth_group_access')->where('group_id', '18')->select();
            foreach ($one_sales as $k => $v) {
                $one_admin[] = $v['uid'];
            }
            $newonetake = Db::name('sales_order')
                ->where('review_the_data', 'the_car')
                ->where('admin_id', 'in', $one_admin)
                ->where('delivery_datetime', 'between', [$seventtime + ($i * 86400 * 30), $seventtime + (($i + 1) * 86400 * 30)])
                ->count();
            //销售二部
            $two_sales = DB::name('auth_group_access')->where('group_id', '22')->field('uid')->select();
            foreach ($two_sales as $k => $v) {
                $two_admin[] = $v['uid'];
            }
            $newtwotake = Db::name('sales_order')
                ->where('review_the_data', 'the_car')
                ->where('admin_id', 'in', $two_admin)
                ->where('delivery_datetime', 'between', [$seventtime + ($i * 86400 * 30), $seventtime + (($i + 1) * 86400 * 30)])
                ->count();
            //销售三部
            $three_sales = DB::name('auth_group_access')->where('group_id', '37')->field('uid')->select();
            foreach ($three_sales as $k => $v) {
                $three_admin[] = $v['uid'];
            }
            $newthreetake = Db::name('sales_order')
                ->where('review_the_data', 'the_car')
                ->where('admin_id', 'in', $three_admin)
                ->where('delivery_datetime', 'between', [$seventtime + ($i * 86400 * 30), $seventtime + (($i + 1) * 86400 * 30)])
                ->count();
            //销售一部
            $newonesales[$month] = $newonetake;
            //销售二部
            $newtwosales[$month] = $newtwotake;
            //销售三部
            $newthreesales[$month] = $newthreetake;
        }
        // pr($newtake);die;
        Cache::set('newonesales', $newonesales);
        Cache::set('newtwosales', $newtwosales);
        Cache::set('newthreesales', $newthreesales);


    }

    /**
     * 选择库存车
     * @param null $ids
     * @return string
     * @throws DataNotFoundException
     * @throws \think\Exception
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function choosestock($ids = null)
    {

        if ($this->request->isPost()) {

            $id = input("post.id");

            Db::name("sales_order")
                ->where("id", $ids)
                ->update([
                    'car_new_inventory_id' => $id,
                    'review_the_data' => "take_the_car",
                    'delivery_datetime' => time()
                ]);

            Db::name("car_new_inventory")
                ->where("id", $id)
                ->setField("statuss", 0);

            $result = Db::name('sales_order')->where('id', $ids)->find();

            $models_name = Db::name('models')->where('id', $result['models_id'])->value('name');

            $data = newchoose_stock($models_name, $result['username']);
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

            //金融平台
            $financial_name = $result['financial_name'];

            if ($financial_name == "一汽租赁") {

                if ($result_s) {
                    $this->success();
                } else {
                    $this->error('邮箱发送失败');
                }

            } else {

                $channel = "demo-newpass_finance";
                $content = "你发起的客户：" . $result['username'] . "对车型：" . $models_name . "的购买，已经通过风控审核和车辆匹配，请及时通知客户进行签订金融合同";
                goeary_push($channel, $content . "|" . $result['admin_id']);

                $data = newpass_finance($models_name, $result['username']);
                // var_dump($data);
                // die;
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = Db::name('admin')->where('id', $result['admin_id'])->value('email');
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

            }


            //介绍人

            $order_info = Db::name("sales_order")
                ->where("id", $ids)
                ->field("customer_source,turn_to_introduce_name,turn_to_introduce_phone,turn_to_introduce_card,admin_id,models_id,username,phone")
                ->find();

            if ($order_info['customer_source'] == "turn_to_introduce") {

                $insert_data = [
                    'models_id' => $order_info['models_id'],
                    'admin_id' => $order_info['admin_id'],
                    'referee_name' => $order_info['turn_to_introduce_name'],
                    'referee_phone' => $order_info['turn_to_introduce_phone'],
                    'referee_idcard' => $order_info['turn_to_introduce_card'],
                    'customer_name' => $order_info['username'],
                    'customer_phone' => $order_info['phone'],
                    'buy_way' => '新车'
                ];

                Db::name("referee")->insert($insert_data);

                $last_id = Db::name("referee")->getLastInsID();

                Db::name("sales_order")
                    ->where("id", $ids)
                    ->setField("referee_id", $last_id);
            }

            $this->success('', '', $ids);

        }

        //展示的信息
        $stock = Db::name("car_new_inventory")
            ->alias("i")
            ->join("crm_models m", "i.models_id=m.id")
            ->where("statuss", 1)
            ->field("i.id,m.name,i.licensenumber,i.frame_number,i.engine_number,i.household,i.4s_shop,i.note")
            ->select();

        $this->view->assign([
            'stock' => $stock
        ]);

        $seventtime = \fast\Date::unixtime('month', -6);
        $newonesales = $newtwosales = $newthreesales = [];
        for ($i = 0; $i < 8; $i++) {
            $month = date("Y-m", $seventtime + ($i * 86400 * 30));
            //销售一部
            $one_sales = DB::name('auth_group_access')->where('group_id', '18')->select();
            foreach ($one_sales as $k => $v) {
                $one_admin[] = $v['uid'];
            }
            $newonetake = Db::name('sales_order')
                ->where('review_the_data', 'the_car')
                ->where('admin_id', 'in', $one_admin)
                ->where('delivery_datetime', 'between', [$seventtime + ($i * 86400 * 30), $seventtime + (($i + 1) * 86400 * 30)])
                ->count();
            //销售二部
            $two_sales = DB::name('auth_group_access')->where('group_id', '22')->field('uid')->select();
            foreach ($two_sales as $k => $v) {
                $two_admin[] = $v['uid'];
            }
            $newtwotake = Db::name('sales_order')
                ->where('review_the_data', 'the_car')
                ->where('admin_id', 'in', $two_admin)
                ->where('delivery_datetime', 'between', [$seventtime + ($i * 86400 * 30), $seventtime + (($i + 1) * 86400 * 30)])
                ->count();
            //销售三部
            $three_sales = DB::name('auth_group_access')->where('group_id', '37')->field('uid')->select();
            foreach ($three_sales as $k => $v) {
                $three_admin[] = $v['uid'];
            }
            $newthreetake = Db::name('sales_order')
                ->where('review_the_data', 'the_car')
                ->where('admin_id', 'in', $three_admin)
                ->where('delivery_datetime', 'between', [$seventtime + ($i * 86400 * 30), $seventtime + (($i + 1) * 86400 * 30)])
                ->count();
            //销售一部
            $newonesales[$month] = $newonetake;
            //销售二部
            $newtwosales[$month] = $newtwotake;
            //销售三部
            $newthreesales[$month] = $newthreetake;
        }
        // pr($newtake);die;
        Cache::set('newonesales', $newonesales);
        Cache::set('newtwosales', $newtwosales);
        Cache::set('newthreesales', $newthreesales);

        return $this->view->fetch();
    }


    /**
     * 新车单----需提供保证金
     * @throws DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function newdata()
    {
        if ($this->request->isAjax()) {

            $this->model = model('SalesOrder');

            $id = input("id");

            $id = json_decode($id, true);

            $admin_nickname = Db::name('admin')->alias('a')->join('sales_order b', 'b.admin_id=a.id')->where('b.id', $id)->value('a.nickname');

            $result = $this->model->save(['review_the_data' => 'the_guarantor'], function ($query) use ($id) {
                $query->where('id', $id);
            });

            if ($result) {

                $data = Db::name("sales_order")->where('id', $id)->find();

                //车型
                $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
                //销售id
                $admin_id = $data['admin_id'];
                //客户姓名
                $username = $data['username'];

                $data = newdata_inform($models_name, $username);
                // var_dump($data);
                // die;
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = DB::name('admin')->where('id', $admin_id)->value('email');

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
     *  编辑新车
     */
    public function auditedit($ids = null)
    {
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

        return $this->view->fetch('auditedit');
    }

    /**
     *  编辑租车
     */
    public function rentalauditedit($ids = null)
    {
        $this->model = new \app\admin\model\RentalOrder;
        $row = $this->model->get($ids);
        if ($row) {

            $result = DB::name('rental_order')->alias('a')
                ->join('car_rental_models_info b', 'b.id=a.plan_car_rental_name')
                ->join('models c', 'c.id=b.models_id')
                ->field('a.id,a.username,a.plan_car_rental_name,a.phone,a.deposit_receiptimages,a.down_payment,a.plan_name,b.licenseplatenumber,b.kilometres,b.Parkingposition,b.companyaccount,b.cashpledge,b.threemonths,b.sixmonths,b.manysixmonths,b.note,c.name as models_name')
                ->where('a.id', $row['id'])
                ->find();
        }

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
        return $this->view->fetch('rentalauditedit');
    }

    /**
     *  编辑二手车
     */
    public function secondauditedit($ids = null)
    {
        $this->model = new \app\admin\model\SecondSalesOrder;
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $this->view->assign("customerSourceList", $this->model->getCustomerSourceList());
        $this->view->assign("buyInsurancedataList", $this->model->getBuyInsurancedataList());
        $this->view->assign("reviewTheDataList", $this->model->getReviewTheDataList());
        $row = $this->model->get($ids);
//        pr($row);die;
        if ($row) {
            //关联订单于方案
            $result = Db::name('second_sales_order')->alias('a')
                ->join('secondcar_rental_models_info b', 'a.plan_car_second_name = b.id')
                ->field('b.id as plan_id')
                ->where(['a.id' => $row['id']])
                ->find();
        }
        $newRes = array();
        //品牌
        $res = Db::name('brand')->field('id as brandid,name as brand_name,brand_logoimage')->select();
        // pr(Session::get('admin'));die;
        foreach ((array)$res as $key => $value) {
            $sql = Db::name('models')->alias('a')
                ->join('secondcar_rental_models_info b', 'b.models_id=a.id')
                ->field('a.name as models_name,b.id,b.newpayment,b.monthlypaymen,b.periods,b.totalprices')
                ->where(['a.brand_id' => $value['brandid'], 'b.shelfismenu' => 1])
                ->whereOr('sales_id', $this->auth->id)
                ->select();
            $newB = [];
            foreach ((array)$sql as $bValue) {
                $bValue['models_name'] = $bValue['models_name'] . '【新首付' . $bValue['newpayment'] . '，' . '月供' . $bValue['monthlypaymen'] . '，' . '期数（月）' . $bValue['periods'] . '，' . '总价（元）' . $bValue['totalprices'] . '】';
                $newB[] = $bValue;
            }
            $newRes[] = array(
                'brand_name' => $value['brand_name'],
                // 'brand_logoimage'=>$value['brand_logoimage'],
                'data' => $newB,
            );
        }
        // pr($newRes);die;
        $this->view->assign('newRes', $newRes);
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

        return $this->view->fetch('secondauditedit');
    }


    /**
     * 新车单----审核不通过
     * @throws DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function newnopass()
    {
        if ($this->request->isAjax()) {

            $this->model = model('SalesOrder');

            $id = input("id");


            $id = json_decode($id, true);

            $admin_nickname = DB::name('admin')->alias('a')->join('sales_order b', 'b.admin_id=a.id')->where('b.id', $id)->value('a.nickname');

            $result = $this->model->save(['review_the_data' => 'not_through'], function ($query) use ($id) {
                $query->where('id', $id);
            });

            if ($result) {

                $data = Db::name("sales_order")->where('id', $id)->find();

                //车型
                $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
                //销售id
                $admin_id = $data['admin_id'];
                //客户姓名
                $username = $data['username'];

                $data = newnopass_inform($models_name, $username);
                // var_dump($data);
                // die;
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = DB::name('admin')->where('id', $admin_id)->value('email');

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
     * 新车单----审核不通过，待补录资料
     * @throws DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function newinformation()
    {
        if ($this->request->isAjax()) {

            $this->model = model('SalesOrder');

            $id = input("id");
            $text = input("text");
            $id = json_decode($id, true);

            $result = $this->model->save(['review_the_data' => 'collection_data', 'text' => $text], function ($query) use ($id) {
                $query->where('id', $id);
            });

            if ($result) {

                $data = Db::name("sales_order")->where('id', $id)->find();
                $admin_nickname = Db::name('admin')->where('id', $data['admin_id'])->value('nickname');

                //车型
                $models_name = Db::name('models')->where('id', $data['models_id'])->value('name');

                $data_s = new_information($models_name, $data['username'], $text);

                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = Db::name('admin')->where('id', $data['admin_id'])->value('email');

                $result_s = $email
                    ->to($receiver)
                    ->subject($data_s['subject'])
                    ->message($data_s['message'])
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

    /** 审核提交过来的租车单*/
    public function rentalauditResult($ids = null)
    {

        $this->model = new \app\admin\model\RentalOrder;
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->getDataLimitAdminIds();

        // $list = collection($row)->toArray();
        // pr($row);die;

        //身份证图片
        $id_cardimages = $row['id_cardimages'] == '' ? [] : explode(',', $row['id_cardimages']);
        //驾照图片 
        $drivers_licenseimages = $row['drivers_licenseimages'] == '' ? [] : explode(',', $row['drivers_licenseimages']);
        //户口簿图片 
        $residence_bookletimages = $row['residence_bookletimages'] == '' ? [] : explode(',', $row['residence_bookletimages']);
        //通话清单
        $call_listfilesimages = $row['call_listfilesimages'] == '' ? [] : explode(',', $row['call_listfilesimages']);
        $this->view->assign(
            [
                'row' => $row,
                'cdn' => Config::get('upload')['cdnurl'],
                'id_cardimages' => $id_cardimages,
                'drivers_licenseimages' => $drivers_licenseimages,
                'residence_bookletimages' => $residence_bookletimages,
                'call_listfilesimages' => $call_listfilesimages
            ]

        );

        return $this->view->fetch('rentalauditResult');

    }


    /**
     * 租车单----审核通过
     * @throws DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function rentalpass()
    {
        if ($this->request->isAjax()) {

            $this->model = new \app\admin\model\RentalOrder;

            $id = input("id");

            $id = json_decode($id, true);

            $admin_nickname = DB::name('admin')->alias('a')->join('rental_order b', 'b.admin_id=a.id')->where('b.id', $id)->value('a.nickname');

            $result = $this->model->save(['review_the_data' => 'is_reviewing_pass', 'delivery_datetime' => time()], function ($query) use ($id) {
                $query->where('id', $id);
            });

            $plan_car_rental_name = $this->model->where('id', $id)->value('plan_car_rental_name');

            DB::name('car_rental_models_info')->where('id', $plan_car_rental_name)->setField('status_data', 'is_reviewing_pass');

            if ($result) {

                $data = Db::name("rental_order")->where('id', $id)->find();

                //车型
                $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
                //销售员
                $admin_id = $data['admin_id'];
                //客户姓名
                $username = $data['username'];

                $data = rentalpass_inform($models_name, $username);
                // var_dump($data);
                // die;
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = DB::name('admin')->where('id', $admin_id)->value('email');
                $result_s = $email
                    ->to("812731116@qq.com")
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
     * 租车单----审核不通过
     * @throws DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function rentalnopass()
    {
        if ($this->request->isAjax()) {

            $this->model = new \app\admin\model\RentalOrder;

            $id = input("id");

            $id = json_decode($id, true);

            $admin_nickname = DB::name('admin')->alias('a')->join('rental_order b', 'b.admin_id=a.id')->where('b.id', $id)->value('a.nickname');

            $result = $this->model->save(['review_the_data' => 'is_reviewing_nopass'], function ($query) use ($id) {
                $query->where('id', $id);
            });

            if ($result) {

                $data = Db::name("rental_order")->where('id', $id)->find();

//                $channel = "demo-rental_nopass";
//                $content = "销售员" . $admin_nickname . "提交的租车单没有通过风控审核";
//                goeary_push($channel, $content . "|" . $data['admin_id']);


                //车型
                $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
                //销售员
                $admin_id = $data['admin_id'];
                //客户姓名
                $username = $data['username'];

                $data = rentalnopass_inform($models_name, $username);
                // var_dump($data);
                // die;
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = DB::name('admin')->where('id', $admin_id)->value('email');
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
     * 租车单----审核不通过，待补录资料
     * @throws DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function rentalinformation()
    {
        if ($this->request->isAjax()) {

            $this->model = new \app\admin\model\RentalOrder;

            $id = input("id");
            $text = input("text");
            $id = json_decode($id, true);

            $result = $this->model->save(['review_the_data' => 'collection_data', 'text' => $text], function ($query) use ($id) {
                $query->where('id', $id);
            });

            if ($result) {

                $data = Db::name("rental_order")->where('id', $id)->find();
                $admin_nickname = Db::name('admin')->where('id', $data['admin_id'])->value('nickname');

//                $channel = "demo-rental_information";
//                $content = "销售员" . $admin_nickname . "提交的新车单没有通过风控审核,需要补录：" . $text ."资料";
//                goeary_push($channel, $content . "|" . $data['admin_id']);

                //车型
                $models_name = Db::name('models')->where('id', $data['models_id'])->value('name');

                $data_s = rental_information($models_name, $data['username'], $text);

                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = Db::name('admin')->where('id', $data['admin_id'])->value('email');

                $result_s = $email
                    ->to($receiver)
                    ->subject($data_s['subject'])
                    ->message($data_s['message'])
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

    /** 审核销售提交过来的销售二手车单*/
    public function secondhandcarResult($ids = null)
    {
        $this->model = new \app\admin\model\SecondSalesOrder;
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->getDataLimitAdminIds();

        // $list = collection($row)->toArray();
        // pr($row);die;

        //身份证图片

        $id_cardimages = $row['id_cardimages'] == '' ? [] : explode(',', $row['id_cardimages']);
        //驾照图片
        $drivers_licenseimages = $row['drivers_licenseimages'] == '' ? [] : explode(',', $row['drivers_licenseimages']);
        //户口簿图片
        $residence_bookletimages = $row['residence_bookletimages'] == '' ? [] : explode(',', $row['residence_bookletimages']);
        //住房合同/房产证图片
        $housingimages = $row['housingimages'] == '' ? [] : explode(',', $row['housingimages']);
        //银行卡图片
        $bank_cardimages = $row['bank_cardimages'] == '' ? [] : explode(',', $row['bank_cardimages']);
        //申请表图片
        $application_formimages = $row['application_formimages'] == '' ? [] : explode(',', $row['application_formimages']);
        //定金合同
        $deposit_contractimages = $row['deposit_contractimages'] == '' ? [] : explode(',', $row['deposit_contractimages']);
        //定金收据
        $deposit_receiptimages = $row['deposit_receiptimages'] == '' ? [] : explode(',', $row['deposit_receiptimages']);
        //通话清单
        $call_listfiles = $row['call_listfiles'] == '' ? [] : explode(',', $row['call_listfiles']);
        /**不必填 */
        //保证金收据
        $new_car_marginimages = $row['new_car_marginimages'] == '' ? [] : explode(',', $row['new_car_marginimages']);
        $this->view->assign(

            [
                'row' => $row,
                'cdn' => Config::get('upload')['cdnurl'],
                'id_cardimages' => $id_cardimages,
                'drivers_licenseimages' => $drivers_licenseimages,
                'residence_bookletimages' => $residence_bookletimages,
                'housingimages' => $housingimages,
                'bank_cardimages' => $bank_cardimages,
                'application_formimages' => $application_formimages,
                'deposit_contractimages' => $deposit_contractimages,
                'deposit_receiptimages' => $deposit_receiptimages,
                'call_listfiles' => $call_listfiles,
                'new_car_marginimages' => $new_car_marginimages
            ]
        );

        return $this->view->fetch('secondhandcarResult');

    }


    /**
     * 二手车单----审核通过
     */
    public function secondpass()
    {
        if ($this->request->isAjax()) {

            $this->model = new \app\admin\model\SecondSalesOrder;

            $id = input("id");

            $id = json_decode($id, true);

            $admin_nickname = DB::name('admin')->alias('a')->join('second_sales_order b', 'b.admin_id=a.id')->where('b.id', $id)->value('a.nickname');

            $result = $this->model->save(['review_the_data' => 'is_reviewing_pass'], function ($query) use ($id) {
                $query->where('id', $id);
            });

            if ($result) {

                $this->success();

            } else {
                $this->error();
            }

        }
    }


    /**
     * 二手车单-----选择库存车
     * @param null $ids
     * @return string
     * @throws DataNotFoundException
     * @throws \think\Exception
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function secondchoosestock($ids = null)
    {

        if ($this->request->isPost()) {

            $id = input("post.id");

            Db::name("second_sales_order")
                ->where("id", $ids)
                ->update([
                    'second_car_id' => $id,
                    'review_the_data' => "for_the_car",
                    'delivery_datetime' => time()
                ]);

            $result = Db::name('second_sales_order')->where('id', $ids)->find();
            //车型
            $models_name = Db::name('models')->where('id', $result['models_id'])->value('name');

            //发送销售
//            $channel = "demo-secondpass_inform";
//            $content = "客户：" . $result['username'] . "对车型：" . $models_name . "的购买，已经通过风控审核，匹配完车辆，通知客户进行提车";
//            goeary_push($channel, $content . "|" . $result['admin_id']);

            $data = secondpass_inform($models_name, $result['username']);
            // var_dump($data);
            // die;
            $email = new Email;
            // $receiver = "haoqifei@cdjycra.club";
            $receiver = DB::name('admin')->where('id', $result['admin_id'])->value('email');

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


            //发送车管
//            $channel = "demo-secondpass_tubeinform";
//            $content = "客户：" . $result['username'] . "对车型：" . $models_name . "的购买，已经通过风控审核，匹配完车辆";
//            goeary_push($channel, $content);

            $data = secondpass_tubeinform($models_name, $result['username']);
            // var_dump($data);
            // die;
            $email = new Email;
            // $receiver = "haoqifei@cdjycra.club";
            $receiver = DB::name('admin')->where('rule_message', "message14")->value('email');

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

        }

        //展示的信息
        $stock = Db::name("secondcar_rental_models_info")
            ->alias("i")
            ->join("models m", "i.models_id=m.id")
            ->where("status_data", 'NEQ', "the_car")
            ->where('shelfismenu', '=', '1')
            ->field("i.id,m.name,i.licenseplatenumber,i.vin,i.engine_number,i.companyaccount,i.Parkingposition,i.note")
            ->select();

        $this->view->assign([
            'stock' => $stock
        ]);

        $seventtime = \fast\Date::unixtime('month', -6);
        $secondonesales = $secondtwosales = $secondthreesales = [];
        for ($i = 0; $i < 8; $i++) {
            $month = date("Y-m", $seventtime + ($i * 86400 * 30));
            //销售一部
            $one_sales = DB::name('auth_group_access')->where('group_id', '18')->select();
            foreach ($one_sales as $k => $v) {
                $one_admin[] = $v['uid'];
            }
            $secondonetake = Db::name('second_sales_order')
                ->where('review_the_data', 'for_the_car')
                ->where('admin_id', 'in', $one_admin)
                ->where('delivery_datetime', 'between', [$seventtime + ($i * 86400 * 30), $seventtime + (($i + 1) * 86400 * 30)])
                ->count();
            //销售二部
            $two_sales = DB::name('auth_group_access')->where('group_id', '22')->field('uid')->select();
            foreach ($two_sales as $k => $v) {
                $two_admin[] = $v['uid'];
            }
            $secondtwotake = Db::name('second_sales_order')
                ->where('review_the_data', 'for_the_car')
                ->where('admin_id', 'in', $two_admin)
                ->where('delivery_datetime', 'between', [$seventtime + ($i * 86400 * 30), $seventtime + (($i + 1) * 86400 * 30)])
                ->count();
            //销售三部
            $three_sales = DB::name('auth_group_access')->where('group_id', '37')->field('uid')->select();
            foreach ($three_sales as $k => $v) {
                $three_admin[] = $v['uid'];
            }
            $secondthreetake = Db::name('second_sales_order')
                ->where('review_the_data', 'for_the_car')
                ->where('admin_id', 'in', $three_admin)
                ->where('delivery_datetime', 'between', [$seventtime + ($i * 86400 * 30), $seventtime + (($i + 1) * 86400 * 30)])
                ->count();
            //销售一部
            $secondonesales[$month] = $secondonetake;
            //销售二部
            $secondtwosales[$month] = $secondtwotake;
            //销售三部
            $secondthreesales[$month] = $secondthreetake;
        }
        Cache::set('secondonesales', $secondonesales);
        Cache::set('secondtwosales', $secondtwosales);
        Cache::set('secondthreesales', $secondthreesales);

        return $this->view->fetch();
    }


    /**
     * 二手车单----需提供担保人
     * @throws DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function seconddata()
    {
        if ($this->request->isAjax()) {

            $this->model = new \app\admin\model\SecondSalesOrder;

            $id = input("id");

            $id = json_decode($id, true);

            $admin_nickname = DB::name('admin')->alias('a')->join('second_sales_order b', 'b.admin_id=a.id')->where('b.id', $id)->value('a.nickname');

            $result = $this->model->save(['review_the_data' => 'the_guarantor'], function ($query) use ($id) {
                $query->where('id', $id);
            });

            if ($result) {

                $data = Db::name("second_sales_order")->where('id', $id)->find();

//                $channel = "demo-second_data";
//                $content = "销售员" . $admin_nickname . "提交的二手车单需要提交保证金";
//                goeary_push($channel, $content . "|" . $data['admin_id']);


                //车型
                $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
                //销售id
                $admin_id = $data['admin_id'];
                //客户姓名
                $username = $data['username'];

                $data = seconddata_inform($models_name, $username);
                // var_dump($data);
                // die;
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = DB::name('admin')->where('id', $admin_id)->value('email');

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
     * 二手车单----审核不通过
     * @throws DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function secondnopass()
    {
        if ($this->request->isAjax()) {

            $this->model = new \app\admin\model\SecondSalesOrder;

            $id = input("id");

            $id = json_decode($id, true);

            $admin_nickname = DB::name('admin')->alias('a')->join('second_sales_order b', 'b.admin_id=a.id')->where('b.id', $id)->value('a.nickname');

            $result = $this->model->save(['review_the_data' => 'not_through'], function ($query) use ($id) {
                $query->where('id', $id);
            });

            if ($result) {

                $data = Db::name("second_sales_order")->where('id', $id)->find();

//                $channel = "demo-second_nopass";
//                $content = "销售员" . $admin_nickname . "提交的二手车单没有通过风控审核";
//                goeary_push($channel, $content . "|" . $data['admin_id']);


                //车型
                $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
                //销售id
                $admin_id = $data['admin_id'];
                //客户姓名
                $username = $data['username'];

                $data = secondnopass_inform($models_name, $username);
                // var_dump($data);
                // die;
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = DB::name('admin')->where('id', $admin_id)->value('email');

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
     * 二手车单----审核不通过，待补录资料
     * @throws DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function secondinformation()
    {
        if ($this->request->isAjax()) {

            $this->model = new \app\admin\model\SecondSalesOrder;

            $id = input("id");
            $text = input("text");

            $id = json_decode($id, true);

            $result = $this->model->save(['review_the_data' => 'collection_data', 'text' => $text], function ($query) use ($id) {
                $query->where('id', $id);
            });

            if ($result) {

                $data = Db::name("second_sales_order")->where('id', $id)->find();
                $admin_nickname = Db::name('admin')->where('id', $data['admin_id'])->value('nickname');

//                $channel = "demo-second_information";
//                $content = "销售员" . $admin_nickname . "提交的二手车单没有通过风控审核,需要补录：" . $text ."资料";
//                goeary_push($channel, $content . "|" . $data['admin_id']);

                //车型
                $models_name = Db::name('models')->where('id', $data['models_id'])->value('name');

                $data_s = second_information($models_name, $data['username'], $text);

                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = Db::name('admin')->where('id', $data['admin_id'])->value('email');

                $result_s = $email
                    ->to($receiver)
                    ->subject($data_s['subject'])
                    ->message($data_s['message'])
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
     * 点击审核按钮之前，判断是否有查看大数据
     * @param null $ids
     * @param null $bigdatatype
     */
    public function match_bigdata($ids = null, $bigdatatype = null)
    {
        if ($this->request->isAjax()) {
            if ($this->request->isPost()) {
                if ($bigdatatype == 'sales_order') $bigdatatype = 'sales_order_id';
                if ($bigdatatype == 'rental_order') $bigdatatype = 'rental_order_id';
                if ($bigdatatype == 'second_sales_order') $bigdatatype = 'second_sales_order_id';
                $result = Db::name('big_data')->where(function ($query) use ($ids, $bigdatatype) {
                    $query->where(
                        [
                            $bigdatatype => $ids
                        ]);
                })->find();
                if (empty($result)) $this->error('请先查看一次大数据', '', $result);
                else $this->success('返回成功', '', $result);

            } else {
                $this->error('非法请求');
            }

        } else {
            $this->error('非法请求');
        }
    }


    /**
     * 查看大数据  新车、二手车、租车
     * @param null $ids
     * @param null $bigdatatype 数据表区分
     * @return null|string
     * @throws \think\Exception
     */
    public function bigdata($ids = null, $bigdatatype = null)
    {

        //$bigdatatype为表名
        //唐玉全	15828604423	510623196501259219  +aXoOaVsOaNriIsInBhcmFtcyI6eyJ0eCI6IjEwMiIsImRhdGEiOnsibG9hblJlY29yZHMiOltdLCJyaXNrUmVzdWx0cyI6W10sImZsb3dJZCI6IjcxNmFkOTk1N2MwNTQ0NWU4ZmVmNDE1OTIxMWFmYTBiIn19fQ==
        $bigdata = $this->toViewBigData($ids, $bigdatatype);


        $this->assignconfig([
            'zcFraudScore' => $bigdata['risk_data']['data']['zcFraudScore']
        ]);
        $this->view->assign('bigdata', $bigdata);
        return $this->view->fetch();
    }

    public function toViewBigData($ids, $table)
    {

        $row = Db::name($table)->find(function ($query) use ($ids) {
            $query->field('id,username,id_card,phone')->where('id', $ids);
        });

        // $row = $this->getTabledata();

        $params = array();
        $params['sign'] = $this->sign;
        $params['userid'] = $this->userid;
        $params['params'] = json_encode(
            [
                'tx' => '101',
                'data' => [
                    'name' => $row['username'],
                    'idNo' => $row['id_card'],
                    'queryReason' => '10',
                ],
            ]
        );
        // return $this->bigDataHtml();
        //判断数据库里是否有当前用户的大数据
        $data = $this->getBigData($row['id'], $table);
        if (empty($data)) {
            //如果数据为空，调取大数据接口
            $result[$table . '_id'] = $row['id'];
            $result['name'] = $row['username'];
            $result['phone'] = $row['phone'];
            $result['id_card'] = $row['id_card'];
            $result['createtime'] = time();
            // pr($result);die;
            $result['share_data'] = posts('https://www.zhichengcredit.com/echo-center/api/echoApi/v3', $params);
//            pr($result);die;
            /**共享数据接口 */
            //只有errorCode返回 '0000'  '0001'  '0005' 时为正确查询
            if ($result['share_data']['errorCode'] == '0000' || $result['share_data']['errorCode'] == '0001' || $result['share_data']['errorCode'] == '0005') {
//                //测试
//                $result['share_data']['params']['data']['loanRecords'] = [
//                    ['approvalStatusCode'=>201,'certNo'=>542301198001015308,'loanAmount'=>'(50000,100000]','loanDate'=>]
//                ];
//                pr($result['share_data']);return;
                //风险数据接口
                /**
                 * @params pricedAuthentification
                 * 收费验证环节
                 * 1-身份信息认证
                 * 2-手机号实名验证
                 * 3-银行卡三要素验证
                 * 4-银行卡四要素
                 * 当提交 3、4时 银行卡为必填项
                 */
                $params_risk['sign'] = $this->sign;
                $params_risk['userid'] = $this->userid;
                $params_risk['params'] = json_encode(
                    [
                        'data' => [
                            'name' => $row['username'],

                            'idNo' => $row['id_card'],
                            'mobile' => $row['phone'],
                        ],
                        'queryReason' => '10',//贷前审批s
                        'pricedAuthentification' => '1,2'

                    ]
                );

                $result['risk_data'] = posts('https://www.zhichengcredit.com/echo-center/api/mixedRiskQuery/queryMixedRiskList/v3 ', $params_risk);
                /**风险数据接口 */
                if ($result['risk_data']['errorcode'] == '0000' || $result['risk_data']['errorcode'] == '0001' || $result['risk_data']['errorcode'] == '0005') {
                    //转义base64入库
                    $result['share_data'] = base64_encode(ARRAY_TO_JSON($result['share_data']));
                    $result['risk_data'] = base64_encode(ARRAY_TO_JSON($result['risk_data']));
                    // return $result;
                    $writeDatabases = Db::name('big_data')->insert($result);
                    if ($writeDatabases) {

                        return $this->getBigData($row['id'], $table);
                        // $this->view->assign('bigdata', $this->getBigData($row['id']));

                    } else {
                        die('<h1><center>数据写入失败</center></h1>');
                    }
                } else {
                    die("<h1><center>风险接口-》{$result['risk_data']['message']}</center></h1>");

                }

            } else {
                die("<h1><center>共享接口-》{$result['share_data']['message']}</center></h1>");

            }
        } else {
            return $data;
        }
    }

    /**
     * 查询大数据表
     * @param int $order_id
     * @return data
     */
    public function getBigData($order_id, $table)
    {
        $bigData = Db::name('big_data')->alias('a')
            ->join("{$table} b", "a.{$table}_id = b.id")
            ->where(["a.{$table}_id" => $order_id])
            ->field('a.*')
            ->find();

        if (!empty($bigData)) {
            $bigData['share_data'] = object_to_array(json_decode(base64_decode($bigData['share_data'])));
            $bigData['risk_data'] = object_to_array(json_decode(base64_decode($bigData['risk_data'])));
            return $bigData;

        } else {
            return [];
        }
    }


}
  



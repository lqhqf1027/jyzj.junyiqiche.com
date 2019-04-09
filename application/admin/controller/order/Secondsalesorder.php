<?php

namespace app\admin\controller\order;

use app\common\controller\Backend;
use think\DB;
use app\common\library\Email;

/**
 * 二手车订单列管理
 *
 * @icon fa fa-circle-o
 */
class Secondsalesorder extends Backend
{
    
    /**
     * Order模型对象
     * @var \app\admin\model\second\sales\Order
     */
    protected $model = null;
    /**
     * //数据关联字段,当前控制器对应的模型表中必须存在该字段
     * @var string
     */
    protected $dataLimitField = 'admin_id';
    /**
     *  //表示显示当前自己和所有子级管理员的所有数据
     * @var string
     */
    protected $dataLimit = 'auth';


    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\SecondSalesOrder;
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $this->view->assign("customerSourceList", $this->model->getCustomerSourceList());
        $this->view->assign("buyInsurancedataList", $this->model->getBuyInsurancedataList());
        $this->view->assign("reviewTheDataList", $this->model->getReviewTheDataList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    
    /**提交内勤处理 */
    public function setAudit()
    {
        if ($this->request->isAjax()) {

            $id = $this->request->post('id');
            $result = $this->model->save(['review_the_data'=>'is_reviewing_true'],function($query) use ($id){
                $query->where('id',$id);
                });

            if($result){

                $this->model = model('secondcar_rental_models_info');

                $plan_car_second_name = DB::name('second_sales_order')->where('id', $id)->value('plan_car_second_name');

                $this->model->isUpdate(true)->save(['id'=>$plan_car_second_name,'status_data'=>'for_the_car']);

                $channel = "demo-second_backoffice";
                $content =  "提交的二手车单，请尽快进行处理";
                goeary_push($channel, $content);

                $data = Db::name("second_sales_order")->where('id', $id)->find();
                //车型
                $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
                //销售员
                $admin_id = $data['admin_id'];
                $admin_name = DB::name('admin')->where('id', $data['admin_id'])->value('nickname');
                //客户姓名
                $username= $data['username'];

                $data = secondinternal_inform($models_name,$admin_name,$username);
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
                if($result_s){
                    $this->success();
                }
                else {
                    $this->error('邮箱发送失败');
                }

                
            }
            else{

                $this->error();
            }
            
        }
    }

    /**
     * 编辑.
     */
    public function edit($ids = null, $posttype = null)
    {
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
            $newRes = array();
            //品牌
            $res = Db::name('brand')->field('id as brandid,name as brand_name,brand_logoimage')->select();
            // pr(Session::get('admin'));die;
            foreach ((array) $res as $key => $value) {
                $sql = Db::name('models')->alias('a')
                    ->join('secondcar_rental_models_info b', 'b.models_id=a.id')
                    ->field('a.name as models_name,b.id,b.newpayment,b.monthlypaymen,b.periods,b.totalprices')
                    ->where(['a.brand_id' => $value['brandid'], 'b.shelfismenu' => 1])
                    ->whereOr('sales_id', $this->auth->id)
                    ->select();
                $newB = [];
                foreach ((array) $sql as $bValue) {
                    $bValue['models_name'] = $bValue['models_name'].'【新首付'.$bValue['newpayment'].'，'.'月供'.$bValue['monthlypaymen'].'，'.'期数（月）'.$bValue['periods'].'，'.'总价（元）'.$bValue['totalprices'].'】';
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
                            $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name.'.edit' : true) : $this->modelValidate;
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
                                'appkey'  => "BC-04084660ffb34fd692a9bd1a40d7b6c2",
                                'channel' => "demo-second-the_guarantor",
                                'content' => "销售员" . $admin_nickname . "提交的二手单已经提供保证金，请及时处理"
                            ];
                            $ch = curl_init ();
                            curl_setopt ( $ch, CURLOPT_URL, $uri );//地址
                            curl_setopt ( $ch, CURLOPT_POST, 1 );//请求方式为post
                            curl_setopt ( $ch, CURLOPT_HEADER, 0 );//不打印header信息
                            curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );//返回结果转成字符串
                            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );//post传输的数据。
                            $return = curl_exec ( $ch );
                            curl_close ( $ch );
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

            return $this->view->fetch('the_guarantor');
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
            $newRes = array();
            //品牌
            $res = Db::name('brand')->field('id as brandid,name as brand_name,brand_logoimage')->select();
            // pr(Session::get('admin'));die;
            foreach ((array) $res as $key => $value) {
                $sql = Db::name('models')->alias('a')
                    ->join('secondcar_rental_models_info b', 'b.models_id=a.id')
                    ->field('a.name as models_name,b.id,b.newpayment,b.monthlypaymen,b.periods,b.totalprices')
                    ->where(['a.brand_id' => $value['brandid'], 'b.shelfismenu' => 1])
                    ->whereOr('sales_id', $this->auth->id)
                    ->select();
                $newB = [];
                foreach ((array) $sql as $bValue) {
                    $bValue['models_name'] = $bValue['models_name'].'【新首付'.$bValue['newpayment'].'，'.'月供'.$bValue['monthlypaymen'].'，'.'期数（月）'.$bValue['periods'].'，'.'总价（元）'.$bValue['totalprices'].'】';
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
                $ex = explode(',', $params['plan_car_second_name']);

                $result = DB::name('secondcar_rental_models_info')->where('id', $params['plan_car_second_name'])->field('newpayment,monthlypaymen,periods,bond,models_id')->find();

                $params['car_total_price'] = $result['newpayment'] + $result['monthlypaymen'] * $result['periods'];
                $params['downpayment'] = $result['newpayment'] + $result['monthlypaymen'] + $result['bond'];

                $params['plan_car_second_name'] = reset($ex); //截取id
                $params['plan_name'] = addslashes(end($ex)); //
            
                $params['models_id'] = $result['models_id'];
                if ($params) {
                    try {
                        //是否采用模型验证
                        if ($this->modelValidate) {
                            $name = basename(str_replace('\\', '/', get_class($this->model)));
                            $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name.'.edit' : true) : $this->modelValidate;
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

            return $this->view->fetch();
        }
    }

    /**
     * 添加.
     */
    public function add()
    {
        $newRes = array();
        //品牌
        $res = Db::name('brand')->field('id as brandid,name as brand_name,brand_logoimage')->select();
        // pr(Session::get('admin'));die;
        foreach ((array) $res as $key => $value) {
            $sql = Db::name('models')->alias('a')
                ->join('secondcar_rental_models_info b', 'b.models_id=a.id')
                ->field('a.id,a.name as models_name,b.id,b.newpayment,b.monthlypaymen,b.periods,b.totalprices,b.bond')
                ->where(['a.brand_id' => $value['brandid'], 'b.shelfismenu' => 1])
                ->whereOr('sales_id', $this->auth->id)
                ->select();
            $newB = [];
            foreach ((array) $sql as $bValue) {
                $bValue['models_name'] = $bValue['models_name'].'【新首付'.$bValue['newpayment'].'，'.'月供'.$bValue['monthlypaymen'].'，'.'期数（月）'.$bValue['periods'].'，'.'总价（元）'.$bValue['totalprices'].'】';
                $newB[] = $bValue;
            }
            $newRes[] = array(
                'brand_name' => $value['brand_name'],
                // 'brand_logoimage'=>$value['brand_logoimage'],
                'data' => $newB,
            );
        }
        $this->view->assign('newRes', $newRes);

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
            // $adminRule =Session::get('admin')['rule_message'];  //测试完后需要把注释放开
            $adminRule = 'message8'; //测试数据
            if ($adminRule == 'message8') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message13'])->find()['id'];
                // return true;
            }
            if ($adminRule == 'message9') {
                $params['backoffice_id'] = Db::name('admin')->where(['rule_message' => 'message13'])->find()['id'];
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
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name.'.add' : true) : $this->modelValidate;
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


}

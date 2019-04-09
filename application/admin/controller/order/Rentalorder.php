<?php

namespace app\admin\controller\order;

use app\common\controller\Backend;
use think\Db;
use think\Session;
use think\Request;
use app\common\library\Email;

/**
 * 租车订单列管理
 *
 * @icon fa fa-circle-o
 */
class Rentalorder extends Backend
{
    
    /**
     * Order模型对象
     * @var \app\admin\model\rental\Order
     */
    protected $model = null;
    protected $dataLimitField = 'admin_id'; //数据关联字段,当前控制器对应的模型表中必须存在该字段
    protected $dataLimit = 'auth'; //表示显示当前自己和所有子级管理员的所有数据

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\RentalOrder;
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

     //车辆管理人员审核
    // public function vehicleManagement()
    // {

    //     if ($this->request->isAjax()) {
    //         $this->model = model('car_rental_models_info');
    //         $ids = input("ids");

    //         $ids = json_decode($ids, true);

    //         $ids = explode(',',$ids);            

    //         Session::set('planName', $ids['1']);
    //         Session::set('plan_id', $ids[0]);

    //         $result = $this->model
    //             ->where('id', $ids[0])
    //             ->setField('review_the_data', 'is_reviewing');

    //         $sales_name = DB::name('admin')->where('id', $this->auth->id)->value('nickname');
    //         $licenseplatenumber = $this->model->where('id', $ids[0])->value('licenseplatenumber');

    //         //请求地址
    //         $uri = "http://goeasy.io/goeasy/publish";
    //         // 参数数组
    //         $data = [
    //             'appkey'  => "BC-04084660ffb34fd692a9bd1a40d7b6c2",
    //             'channel' => "demo",
    //             'content' => "销售员" . $sales_name . "对车牌号" . $licenseplatenumber . "发出租车请求，请处理"
    //         ];
    //         $ch = curl_init ();
    //         curl_setopt ( $ch, CURLOPT_URL, $uri );//地址
    //         curl_setopt ( $ch, CURLOPT_POST, 1 );//请求方式为post
    //         curl_setopt ( $ch, CURLOPT_HEADER, 0 );//不打印header信息
    //         curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );//返回结果转成字符串
    //         curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );//post传输的数据。
    //         $return = curl_exec ( $ch );
    //         curl_close ( $ch );
    //         // print_r($return);

    //         $review_the_data = $this->model->where('id',$id)->value('review_the_data');

    //         if ($result) {
    //             $this->success('', '', $result);
    //         } else {
    //             $this->error();
    //         }
    //     }
    // }
    
    //租车单添加
    // public function add()
    // {
 
    //     $newRes = array();
    //     //品牌
    //     $res = Db::name('brand')->field('id as brandid,name as brand_name,brand_logoimage')->select();
        
    //     foreach ((array)$res as $key=>$value) {
    //         $sql = Db::name('models')->alias('a')
    //         ->join('car_rental_models_info b','b.models_id=a.id')
    //         ->field('a.name as models_name,b.id,b.licenseplatenumber,b.sales_id,b.cashpledge,b.threemonths,b.sixmonths,b.manysixmonths,b.shelfismenu')
    //         ->where(['a.brand_id'=>$value['brandid'],'b.shelfismenu'=>1])
    //         ->whereOr('sales_id', $this->auth->id)
    //         ->where('review_the_data', '')
    //         ->select();
    //         $newB =[];
    //         foreach((array)$sql as $bValue){
    //             $bValue['models_name'] =$bValue['models_name'].'【押金'.$bValue['cashpledge'].'，'.'3月内租金（元）'.$bValue['threemonths'].'，'.'6月内租金（元） '.$bValue['sixmonths'].'，'.'6月以上租金（元） '.$bValue['manysixmonths'].'】';
    //             $newB[] = $bValue;
            
    //         }
    
    //         $newRes[]=array(
    //             'brand_name' => $value['brand_name'],
    //             // 'brand_logoimage'=>$value['brand_logoimage'],
    //             'data'=>$newB
    //         );


    //     }
        
    //     $this->view->assign('newRes',$newRes);

    //     return $this->view->fetch();
    // }

    //补全租车资料
    // public function completionData()
    // {
    //     if ($this->request->isPost()) {
            

    //         $params = $this->request->post("row/a");
    //         $plan_car_rental_name = Session::get('plan_id');

    //         $ex = explode(',',$params['plan_car_rental_name']);
             
    //         $params['plan_car_rental_name'] = reset($ex);//截取id
    //         $params['plan_name'] = addslashes(end($ex));//
            
    //         //生成订单编号
    //         $params['plan_car_rental_name'] = $plan_car_rental_name;
    //         $params['order_no'] = date('Ymdhis');
    //         Session::set('order_no', $params['order_no']);
    //         $params['admin_id'] = $this->auth->id;
    //         //将租车订单数据存入session中
           
    //         if ($params) {
    //             if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
    //                 $params[$this->dataLimitField] = $this->auth->id;
    //             }
    //             try {
    //                 //是否采用模型验证
    //                 if ($this->modelValidate) {
    //                     $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
    //                     $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
    //                     $this->model->validate($validate);
    //                 }
    //                 $result = $this->model->allowField(true)->save($params);
    //                 //将新增的id存入session中
    //                 $userId = Db::name('rental_order')->getLastInsID();
    //                 Session::set('userId',$userId);
    //                 if ($result) {
    //                     $this->success('','',$userId);
    //                 } else {
    //                     $this->error();
    //                 }
    //             } catch (\think\exception\PDOException $e) {
    //                 $this->error($e->getMessage());
    //             } catch (\think\Exception $e) {
    //                 $this->error($e->getMessage());
    //             }
    //         }
    //         $this->error(__('Parameter %s can not be empty', ''));
            
    //     }
    // }

    //提交审核 --- 弹窗里的提交审核
    // public function audit()
    // {
    //     // var_dump(123);
    //     // die;
    //     if ($this->request->isPost()) {

    //         $id = Session::get('userId');

    //         $admin_nickname = DB::name('admin')->alias('a')->join('rental_order b', 'b.admin_id=a.id')->where('b.id', $id)->value('a.nickname');

    //         $result = $this->model->save(['review_the_data'=>'is_reviewing_true'],function($query) use ($id){
    //             $query->where('id',$id);
    //             });

    //         //请求地址
    //         $uri = "http://goeasy.io/goeasy/publish";
    //         // 参数数组
    //         $data = [
    //             'appkey'  => "BC-04084660ffb34fd692a9bd1a40d7b6c2",
    //             'channel' => "demo-pop_rental",
    //             'content' => "销售员" . $admin_nickname . "提交的租车单，请及时处理"
    //         ];
    //         $ch = curl_init ();
    //         curl_setopt ( $ch, CURLOPT_URL, $uri );//地址
    //         curl_setopt ( $ch, CURLOPT_POST, 1 );//请求方式为post
    //         curl_setopt ( $ch, CURLOPT_HEADER, 0 );//不打印header信息
    //         curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );//返回结果转成字符串
    //         curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );//post传输的数据。
    //         $return = curl_exec ( $ch );
    //         curl_close ( $ch );
    //         // print_r($return);

    //         if($result){

    //             $this->success();
    //         }
    //         else{

    //             $this->error();
    //         }
            
    //     }
        
    // }
     
    //提交审核 --- 表格里的提交审核
    // public function setAudit()
    // {
    //      if ($this->request->isAjax()) {
    //         $id = $this->request->post('id');

    //         $admin_nickname = DB::name('admin')->alias('a')->join('rental_order b', 'b.admin_id=a.id')->where('b.id', $id)->value('a.nickname');

    //         $plan_car_rental_name = DB::name('rental_order')->where('id', $id)->value('plan_car_rental_name');
           
    //         $result = DB::name('car_rental_models_info')->isUpdate(true)->save(['id'=>$plan_car_rental_name,'review_the_data'=>'is_reviewing']);

    //         //请求地址
    //         $uri = "http://goeasy.io/goeasy/publish";
    //         // 参数数组
    //         $data = [
    //             'appkey'  => "BC-04084660ffb34fd692a9bd1a40d7b6c2",
    //             'channel' => "demo-table_rental",
    //             'content' => "销售员" . $admin_nickname . "提交的租车单，请及时处理"
    //         ];
    //         $ch = curl_init ();
    //         curl_setopt ( $ch, CURLOPT_URL, $uri );//地址
    //         curl_setopt ( $ch, CURLOPT_POST, 1 );//请求方式为post
    //         curl_setopt ( $ch, CURLOPT_HEADER, 0 );//不打印header信息
    //         curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );//返回结果转成字符串
    //         curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );//post传输的数据。
    //         $return = curl_exec ( $ch );
    //         curl_close ( $ch );
    //         // print_r($return);


    //         if($result!==false){        
    //             // //推送模板消息给风控
    //             // $sedArr = array(
    //             //     'touser' => 'oklZR1J5BGScztxioesdguVsuDoY',
    //             //     'template_id' => 'LGTN0xKp69odF_RkLjSmCltwWvCDK_5_PuAVLKvX0WQ', /**以租代购新车模板id */
    //             //     "topcolor" => "#FF0000",
    //             //     'url' => '',
    //             //     'data' => array(
    //             //         'first' =>array('value'=>'你有新客户资料待审核','color'=>'#FF5722') ,
    //             //         'keyword1' => array('value'=>$params['username'],'color'=>'#01AAED'),
    //             //         'keyword2' => array('value'=>'以租代购（新车）','color'=>'#01AAED'),
    //             //         'keyword3' => array('value'=>Session::get('admin')['nickname'],'color'=>'#01AAED'),
    //             //         'keyword4' =>array('value'=>date('Y年m月d日 H:i:s'),'color'=>'#01AAED') , 
    //             //         'remark' => array('value'=>'请前往系统进行查看操作')
    //             //     )
    //             // );
    //             // $sedResult= posts("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".self::$token,json_encode($sedArr));
    //             // if( $sedResult['errcode']==0 && $sedResult['errmsg'] =='ok'){
    //             //     $this->success('提交成功，请等待审核结果'); 
    //             // }else{
    //             //     $this->error('微信推送失败',null,$sedResult);
    //             // }
    //                 $this->success('提交成功，请等待审核结果'); 
               
                
    //         }else{
    //             $this->error('提交失败',null,$result);
                
    //         }
    //     }
    // }

    //暂不提交审核
    // public function noaudit()
    // {
    //     // var_dump(123);
    //     // die;
    //     if ($this->request->isPost()) {

    //         $id = Session::get('userId');
    //         $result = $this->model->save(['review_the_data'=>'is_reviewing_false'],function($query) use ($id){
    //             $query->where('id',$id);
    //             });

    //         if($result){

    //             $this->success();
    //         }
    //         else{

    //             $this->error();
    //         }
            
    //     }
    // }
    
    //租车订单修改
    // public function edit($ids = NULL) 
    // {
    //     $row = $this->model->get($ids);

    //     //关联订单于方案
    //     $result = Db::name('rental_order')->alias('a')
    //         ->join('car_rental_models_info b','a.plan_car_rental_name = b.id')
    //         ->field('b.id as plan_id')
    //         ->where(['a.id'=>$row['id']])
    //         ->find()
    //         ; 

    //     $newRes = array();
    //     //品牌
    //     $res = Db::name('brand')->field('id as brandid,name as brand_name,brand_logoimage')->select();
            
    //     foreach ((array)$res as $key=>$value) {
    //         $sql = Db::name('models')->alias('a')
    //             ->join('car_rental_models_info b','b.models_id=a.id')
    //             ->field('a.name as models_name,b.id,b.licenseplatenumber,b.sales_id,b.cashpledge,b.threemonths,b.sixmonths,b.manysixmonths,b.shelfismenu')
    //             ->where(['a.brand_id'=>$value['brandid'],'b.shelfismenu'=>1])
    //             ->whereOr('sales_id', $this->auth->id)
    //             ->where('review_the_data', '')
    //             ->select();
    //         $newB =[];
    //         foreach((array)$sql as $bValue){
    //             $bValue['models_name'] =$bValue['models_name'].'【押金'.$bValue['cashpledge'].'，'.'3月内租金（元）'.$bValue['threemonths'].'，'.'6月内租金（元） '.$bValue['sixmonths'].'，'.'6月以上租金（元） '.$bValue['manysixmonths'].'】';
    //             $newB[] = $bValue;
                
    //         }
        
    //         $newRes[]=array(
    //             'brand_name' => $value['brand_name'],
    //             // 'brand_logoimage'=>$value['brand_logoimage'],
    //             'data'=>$newB
    //         );
    
    
    //     }
    //     $this->view->assign(
    //         [
    //             "newRes" => $newRes,
    //             "result" => $result
    //         ]
    //     );
    
    //     if (!$row)
    //         $this->error(__('No Results were found'));
    //     $adminIds = $this->getDataLimitAdminIds();
    //     if (is_array($adminIds)) {
    //         if (!in_array($row[$this->dataLimitField], $adminIds)) {
    //             $this->error(__('You have no permission'));
    //         }
    //     }
    //     if ($this->request->isPost()) {
    //         $params = $this->request->post("row/a");
    //         if ($params) {
    //             try {
    //                 //是否采用模型验证
    //                 if ($this->modelValidate) {
    //                     $name = basename(str_replace('\\', '/', get_class($this->model)));
    //                     $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
    //                     $row->validate($validate);
    //                 }
    //                 $result = $row->allowField(true)->save($params);
    //                 if ($result !== false) {
    //                     $this->success();
    //                 } else {
    //                     $this->error($row->getError());
    //                 }
    //             } catch (\think\exception\PDOException $e) {
    //                 $this->error($e->getMessage());
    //             }
    //         }
    //         $this->error(__('Parameter %s can not be empty', ''));
    //     }
    //     $this->view->assign("row", $row);
    //     return $this->view->fetch();
    // }

    //放弃
    // public function giveup()
    // {
    //     $plan_id = Session::get('plan_id');
    //     $this->model = model('car_rental_models_info');

    //     $order_no = Session::get('order_no');

    //     $result = $this->model->where('id', $plan_id)->setField('review_the_data', '');
    //     if($result){
    //         $this->success();
    //     }
    //     else{
    //         $this->error();
    //     }
        
        
    // }




    /**
     * 2018.8.21 修改订单
     */

    //租车预定
    public function reserve()
    {

        $result = DB::name('car_rental_models_info')->alias('a')
            ->join('models b', 'b.id=a.models_id')

            ->field('a.id,a.licenseplatenumber,a.kilometres,a.Parkingposition,a.companyaccount,a.cashpledge,a.threemonths,a.sixmonths,a.manysixmonths,a.note,b.name as models_name')

            ->where('a.status_data', '')

            ->select();

        $this->view->assign('result', $result);

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            // $ex = explode(',', $params['plan_acar_name']);

            $params['plan_car_rental_name'] = Session::get('plan_id'); 
            
            $params['plan_name'] = Session::get('plan_name'); 

            $models_id = DB::name('car_rental_models_info')->where('id', Session::get('plan_id'))->value('models_id');

            $params['models_id'] = $models_id;
            
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
                        // 如果添加成功,将状态改为车管正在审核
                        $result_s = $this->model->isUpdate(true)->save(['id' => $this->model->id, 'review_the_data' => 'is_reviewing_true']);

                        $admin_nickname = DB::name('admin')->alias('a')->join('rental_order b', 'b.admin_id=a.id')->where('b.id', $this->model->id)->value('a.nickname');

                        Session::set('rental_id',$this->model->id);

                        $this->model = model('car_rental_models_info');

                        $this->model->isUpdate(true)->save(['id'=>$params['plan_car_rental_name'],'status_data'=>'is_reviewing']);
                        
                        if ($result_s) {
                            
                            $channel = "demo-reserve";
                            $content =  "销售员" . $admin_nickname . "提交的租车单，请及时处理";
                            goeary_push($channel, $content);
                                        
                            $data = Db::name("rental_order")->where('id', Session::get('rental_id'))->find();
                            //车型
                            $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
                            //销售员
                            $admin_id = $data['admin_id'];
                            $admin_name = DB::name('admin')->where('id', $data['admin_id'])->value('nickname');
                            //客户姓名
                            $username= $data['username'];

                            $data = rentalcar_inform($models_name,$admin_name,$username);
                            // var_dump($data);
                            // die;
                            $email = new Email;
                            // $receiver = "haoqifei@cdjycra.club";
                            $receiver = DB::name('admin')->where('id', $admin_id)->value('email');
                            $result_ss = $email
                                ->to($receiver)
                                ->subject($data['subject'])
                                ->message($data['message'])
                                ->send();
                            if($result_ss){
                                $this->success();
                            }
                            else {
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

    //方案组装
    public function planname()
    {
        if ($this->request->isAjax()) {

        
            $plan_id = input("id");
            $plan_id = json_decode($plan_id, true);
            
            $sql = Db::name('models')->alias('a')
                ->join('car_rental_models_info b', 'b.models_id=a.id')
                ->field('a.id,b.licenseplatenumber,b.companyaccount,b.cashpledge,b.threemonths,b.sixmonths,b.manysixmonths,b.note,a.name as models_name')
                ->where(['b.id' => $plan_id])
                ->find();
                
            $plan_name =$sql['models_name'].'【押金'.$sql['cashpledge'].'，'.'3月内租金（元）'.$sql['threemonths'].'，'.'6月内租金（元） '.$sql['sixmonths'].'，'.'6月以上租金（元） '.$sql['manysixmonths'].'】';

            Session::set('plan_id',$plan_id);

            Session::set('plan_name',$plan_name);

        }
    }

    //租车客户信息的补全
    public function add($ids = null)
    {
        $row = $this->model->get($ids);
        if ($row) {

            $result = DB::name('rental_order')->alias('a')

            ->join('car_rental_models_info b', 'b.id=a.plan_car_rental_name')

            ->join('models c', 'c.id=b.models_id')

            ->field('a.id,a.username,a.plan_car_rental_name,a.phone,a.deposit_receiptimages,a.down_payment,a.plan_name,b.licenseplatenumber,b.kilometres,b.Parkingposition,b.companyaccount,b.cashpledge,b.threemonths,b.sixmonths,b.manysixmonths,b.note,c.name as models_name')

            ->where('a.id',$row['id'])

            ->find();
        }   
        
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
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name.'.add' : true) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    if ($result !== false) {
                        //如果添加成功,将状态改为暂不提交风控审核
                        $result_s = $this->model->isUpdate(true)->save(['id' => $this->model->id, 'review_the_data' => 'is_reviewing_false']);
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

    // 租车订单修改
    public function edit($ids = NULL) 
    {
        $row = $this->model->get($ids);
        if ($row) {

            $result = DB::name('rental_order')->alias('a')

            ->join('car_rental_models_info b', 'b.id=a.plan_car_rental_name')

            ->join('models c', 'c.id=b.models_id')

            ->field('a.id,a.username,a.plan_car_rental_name,a.phone,a.deposit_receiptimages,a.down_payment,a.plan_name,b.licenseplatenumber,b.kilometres,b.Parkingposition,b.companyaccount,b.cashpledge,b.threemonths,b.sixmonths,b.manysixmonths,b.note,c.name as models_name')

            ->where('a.id',$row['id'])

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
        return $this->view->fetch();
    }
 
    /**
     * 删除
     */
    public function del($ids = "")
    {
        if ($ids) {
            $pk = $this->model->getPk();
            $plan_car_rental_name = DB::name('rental_order')->where('id',$ids)->value('plan_car_rental_name');
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
                DB::name('car_rental_models_info')->where('id', $plan_car_rental_name)->setField('review_the_data', '');
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    //提交风控审核
    public function control()
    {

        if ($this->request->isAjax()) {
            $id = $this->request->post('id');

            $admin_nickname = DB::name('admin')->alias('a')->join('rental_order b', 'b.admin_id=a.id')->where('b.id', $id)->value('a.nickname');

            $result = DB::name('rental_order')->where('id',$id)->setField('review_the_data', 'is_reviewing_control');

            if($result!==false){
                
                $channel = "demo-rental_control";
                $content =  "销售员" . $admin_nickname . "提交的租车单，请及时进行审核处理";
                goeary_push($channel, $content);


                $data = Db::name("rental_order")->where('id', $id)->find();
                //车型
                $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
                //销售员
                $admin_id = $data['admin_id'];
                $admin_name = DB::name('admin')->where('id', $data['admin_id'])->value('nickname');
                //客户姓名
                $username= $data['username'];

                $data = rentalcontrol_inform($models_name,$admin_name,$username);
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
               
                
            }else{
                $this->error('提交失败',null,$result);
                
            }
        }
    }

}

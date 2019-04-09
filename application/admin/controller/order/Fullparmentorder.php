<?php

namespace app\admin\controller\order;

use app\common\controller\Backend;
use think\DB;
use app\common\library\Email;

/**
 * 订单列管理
 *
 * @icon fa fa-circle-o
 */
class Fullparmentorder extends Backend
{
    
    /**
     * Order模型对象
     * @var \app\admin\model\full\parment\Order
     */
    protected $model = null;
    protected $dataLimitField = 'admin_id'; //数据关联字段,当前控制器对应的模型表中必须存在该字段
    protected $dataLimit = 'auth'; //表示显示当前自己和所有子级管理员的所有数据


    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\FullParmentOrder;
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $this->view->assign('customerSourceList', $this->model->getCustomerSourceList());

    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 添加.
     */
    public function add()
    {
        $newRes = array();
        //品牌
        $res = DB::name('brand')->field('id as brandid,name as brand_name,brand_logoimage')->select();
        // pr(Session::get('admin'));die;
        foreach ((array) $res as $key => $value) {
            $sql = Db::name('models')->alias('a')
                ->join('plan_full b', 'b.models_id=a.id')
                ->field('a.name as models_name,b.id,b.full_total_price')
                ->where(['a.brand_id' => $value['brandid'], 'b.ismenu' => 1])

                ->select();
            $newB = [];
            foreach ((array) $sql as $bValue) {
                $bValue['models_name'] = $bValue['models_name'].'【全款总价'.$bValue['full_total_price'].'】';
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

            if($params['customer_source']=="straight"){
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
     * 编辑.
     */
    public function edit($ids = NULL) 
    {
        $row = $this->model->get($ids);

        //关联订单于方案
        $result = Db::name('full_parment_order')->alias('a')
            ->join('plan_full b','a.plan_plan_full_name = b.id')
            ->field('b.id as plan_id')
            ->where(['a.id'=>$row['id']])
            ->find()
            ; 

        $newRes = array();
        //品牌
        $res = DB::name('brand')->field('id as brandid,name as brand_name,brand_logoimage')->select();
        // pr(Session::get('admin'));die;
        foreach ((array) $res as $key => $value) {
            $sql = Db::name('models')->alias('a')
                ->join('plan_full b', 'b.models_id=a.id')
                ->field('a.name as models_name,b.id,b.full_total_price')
                ->where(['a.brand_id' => $value['brandid'], 'b.ismenu' => 1])
    
                ->select();
            $newB = [];
            foreach ((array) $sql as $bValue) {
                $bValue['models_name'] = $bValue['models_name'].'【全款总价'.$bValue['full_total_price'].'】';
                $newB[] = $bValue;
            }
    
            $newRes[] = array(
                'brand_name' => $value['brand_name'],
                // 'brand_logoimage'=>$value['brand_logoimage'],
                'data' => $newB,
            );
        }
            
        $this->view->assign(
            [
                "newRes" => $newRes,
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

            if($params['customer_source']=="straight"){
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

    //提交内勤
    public function submitCar()
    {
        if ($this->request->isAjax()) {
            $id = $this->request->post('id');

            $admin_nickname = DB::name('admin')->alias('a')->join('full_parment_order b', 'b.admin_id=a.id')->where('b.id', $id)->value('a.nickname');
           
            $result = $this->model->isUpdate(true)->save(['id'=>$id,'review_the_data'=>'inhouse_handling']);

            if($result!==false){

                $channel = "demo-full_backoffice";
                $content =  "销售员" . $admin_nickname . "提交的全款车单，请尽快进行金额录入";
                goeary_push($channel, $content);

                $data = Db::name("full_parment_order")->where('id', $id)->find();
                //车型
                $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
                //销售员
                $admin_id = $data['admin_id'];
                $admin_name = DB::name('admin')->where('id', $data['admin_id'])->value('nickname');
                //客户姓名
                $username= $data['username'];

                $data = fullinternal_inform($models_name,$admin_name,$username);
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
                
            }else{
                $this->error('提交失败',null,$result);
                
            }
        }
    }

    //提取车辆
    // public function getCar()
    // {
    //     if ($this->request->isAjax()) {
    //         $id = $this->request->post('id');

    //         $admin_nickname = DB::name('admin')->alias('a')->join('full_parment_order b', 'b.admin_id=a.id')->where('b.id', $id)->value('a.nickname');
           
    //         $result = $this->model->isUpdate(true)->save(['id'=>$id,'review_the_data'=>'for_the_car']);

    //         //请求地址
    //         $uri = "http://goeasy.io/goeasy/publish";
    //         // 参数数组
    //         $data = [
    //             'appkey'  => "BC-04084660ffb34fd692a9bd1a40d7b6c2",
    //             'channel' => "demo-submitCar",
    //             'content' => "销售员" . $admin_nickname . "要进行提车"
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
                   
    //             $data = Db::name("full_parment_order")->where('id', $id)->find();
    //             //车型
    //             $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
    //             //销售员
    //             $admin_id = $data['admin_id'];
    //             $admin_name = DB::name('admin')->where('id', $data['admin_id'])->value('nickname');
    //             //客户姓名
    //             $username= $data['username'];

    //             $data = fullautomobile_inform($models_name,$admin_name,$username);
    //             // var_dump($data);
    //             // die;
    //             $email = new Email;
    //             // $receiver = "haoqifei@cdjycra.club";
    //             $receiver = DB::name('admin')->where('id', $admin_id)->value('email');
    //             $result_s = $email
    //                 ->to($receiver)
    //                 ->subject($data['subject'])
    //                 ->message($data['message'])
    //                 ->send();
    //             if($result_s){
    //                 $this->success();
    //             }
    //             else {
    //                 $this->error('邮箱发送失败');
    //             }
                
    //         }else{
    //             $this->error('提交失败',null,$result);
                
    //         }
    //     }
    // }
        
       
}

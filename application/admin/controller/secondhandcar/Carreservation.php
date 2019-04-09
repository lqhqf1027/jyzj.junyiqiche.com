<?php

namespace app\admin\controller\secondhandcar;

use app\common\controller\Backend;
use think\Db;
use app\common\library\Email;

/**
 * 短信验证码管理
 *
 * @icon fa fa-circle-o
 */
class Carreservation extends Backend
{
    
    /**
     * Sms模型对象
     * @var \app\admin\model\Sms
     */
    protected $model = null;
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        

    }
    


    public function index()
    {

        return $this->view->fetch();
    }



    /**待车管确认
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function secondcarWaitconfirm()
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
                        $query->withField(['id','avatar','nickname']);
                    }, 'models' => function ($query) {
                        $query->withField('name,models_name');
                    }])
                    ->where($where)
                    ->where("review_the_data", "send_car_tube")
                    ->where("amount_collected", "not null")
                    ->order($sort, $order)
                    ->count();


            $list = $this->model
                    ->with(['plansecond' => function ($query) {
                        $query->withField('companyaccount,licenseplatenumber,newpayment,monthlypaymen,periods,totalprices,bond,tailmoney');
                    }, 'admin' => function ($query) {
                        $query->withField(['id','avatar','nickname']);
                    }, 'models' => function ($query) {
                        $query->withField('name,models_name');
                    }])
                    ->where($where)
                    ->where("review_the_data", "send_car_tube")
                    ->where("amount_collected", "not null")
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();
            foreach ($list as $k => $row) {
                    $row->visible(['id', 'order_no', 'username', 'detailed_address', 'createtime', 'phone', 'id_card', 'amount_collected', 'downpayment', 'review_the_data']);
                    $row->visible(['plansecond']);
                    $row->getRelation('plansecond')->visible(['newpayment', 'licenseplatenumber', 'companyaccount', 'monthlypaymen', 'periods', 'totalprices', 'bond', 'tailmoney',]);
                    $row->visible(['admin']);
                    $row->getRelation('admin')->visible(['id','avatar','nickname']);
                    $row->visible(['models']);
                    $row->getRelation('models')->visible(['name', 'models_name']);

                    if ($list[$k]['models']['models_name']) {
                        $list[$k]['models']['name'] = $list[$k]['models']['name'] . " " . $list[$k]['models']['models_name'];
                    }

            }

            $list = collection($list)->toArray();
            foreach ($list as $k=>$v){
                $department = Db::name('auth_group_access')
                    ->alias('a')
                    ->join('auth_group b','a.group_id = b.id')
                    ->where('a.uid',$v['admin']['id'])
                    ->value('b.name');
                $list[$k]['admin']['department'] = $department;
            }
            $result = array('total' => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();

    }



    /**车管已确认
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function secondcarConfirm()
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
                        $query->withField(['id','avatar','nickname']);
                    }, 'models' => function ($query) {
                        $query->withField('name,models_name');
                    }])
                    ->where($where)
                    ->where("review_the_data", 'not in', ['is_reviewing', 'is_reviewing_true', 'send_car_tube'])
                    ->where("amount_collected", "not null")
                    ->order($sort, $order)
                    ->count();


            $list = $this->model
                    ->with(['plansecond' => function ($query) {
                        $query->withField('companyaccount,licenseplatenumber,newpayment,monthlypaymen,periods,totalprices,bond,tailmoney');
                    }, 'admin' => function ($query) {
                        $query->withField(['id','avatar','nickname']);
                    }, 'models' => function ($query) {
                        $query->withField('name,models_name');
                    }])
                    ->where($where)
                    ->where("review_the_data", 'not in', ['is_reviewing', 'is_reviewing_true', 'send_car_tube'])
                    ->where("amount_collected", "not null")
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();
            foreach ($list as $k => $row) {
                    $row->visible(['id', 'order_no', 'username', 'city', 'detailed_address', 'createtime', 'phone', 'id_card', 'amount_collected', 'downpayment', 'review_the_data']);
                    $row->visible(['plansecond']);
                    $row->getRelation('plansecond')->visible(['newpayment', 'licenseplatenumber', 'companyaccount', 'monthlypaymen', 'periods', 'totalprices', 'bond', 'tailmoney',]);
                    $row->visible(['admin']);
                    $row->getRelation('admin')->visible(['id','avatar','nickname']);
                    $row->visible(['models']);
                    $row->getRelation('models')->visible(['name', 'models_name']);

                    if ($list[$k]['models']['models_name']) {
                        $list[$k]['models']['name'] = $list[$k]['models']['name'] . " " . $list[$k]['models']['models_name'];
                    }
                    
            }

            $list = collection($list)->toArray();
            foreach ($list as $k=>$v){
                $department = Db::name('auth_group_access')
                    ->alias('a')
                    ->join('auth_group b','a.group_id = b.id')
                    ->where('a.uid',$v['admin']['id'])
                    ->value('b.name');
                $list[$k]['admin']['department'] = $department;
            }
            $result = array('total' => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();

    }



    /**提交匹配金融
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function setAudit()
    {
        if ($this->request->isAjax()) {

            $id = $this->request->post('id');

            $result = DB::name('second_sales_order')->where('id',$id)->setField('review_the_data', 'is_reviewing_finance');

            if($result!==false){

//                $channel = "demo-second_finance";
//                $content =  "车管提交的二手车单，请及时进行匹配金融";
//                goeary_push($channel, $content);

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
                 
                $data = Db::name("second_sales_order")->where('id', $id)->find();
                //车型
                $models_name = DB::name('models')->where('id', $data['models_id'])->value('name');
                //销售员
                $admin_id = $data['admin_id'];
                $admin_name = DB::name('admin')->where('id', $data['admin_id'])->value('nickname');
                //客户姓名
                $username= $data['username'];

                $data = secondfinance_inform($models_name,$admin_name,$username);
                // var_dump($data);
                // die;
                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = DB::name('admin')->where('rule_message', "message11")->value('email');
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

}

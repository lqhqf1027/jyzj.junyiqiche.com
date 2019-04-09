<?php

namespace app\admin\controller\order;

use app\common\controller\Backend;
use think\Db;
use think\Session;
use think\Request;

/**
 * 订单列管理.
 *
 * @icon fa fa-circle-o
 */
class Salesorder extends Backend
{
    /**
     * SalesOrder模型对象
     *
     * @var \app\admin\model\SalesOrder
     */
    protected $model = null;
    protected $dataLimitField = 'admin_id'; //数据关联字段,当前控制器对应的模型表中必须存在该字段
    protected $dataLimit = 'auth'; //表示显示当前自己和所有子级管理员的所有数据
    protected $userid = null; //用户id
    protected $apikey = null; //apikey
    protected $sign = null; //sign  md5加密
    /**
     * models_id
     * @var null
     */
    protected $models_id = null;
    protected static $token = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('SalesOrder');
        $this->view->assign('genderdataList', $this->model->getGenderdataList());
        $this->view->assign('customerSourceList', $this->model->getCustomerSourceList());
        $this->view->assign('reviewTheDataList', $this->model->getReviewTheDataList());
        //获取token
        // self::$token = $this->getAccessToken();

        $this->userid = 'cdjy01';
        $this->apikey = '1de2474bcaaac1e4';
        $this->sign = md5($this->userid.$this->apikey);
    }

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array('total' => $total, 'rows' => $list);

            return json($result);
        }
        $this->assignconfig('num', 1);

        return $this->view->fetch();
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
            
            $newRes = array();
            //品牌
            $res = Db::name('brand')->field('id as brandid,name as brand_name,brand_logoimage')->select();
            // pr(Session::get('admin'));die;
            foreach ((array) $res as $key => $value) {
                $sql = Db::name('models')->alias('a')
                    ->join('plan_acar b', 'b.models_id=a.id')
                    ->join('financial_platform c', 'b.financial_platform_id=c.id')
                    ->field('a.name as models_name,b.id,b.payment,b.monthly,b.gps,b.tail_section,c.name as financial_platform_name')
                    ->where(['a.brand_id' => $value['brandid'], 'b.ismenu' => 1])
                    ->select();
                $newB = [];
                foreach ((array) $sql as $bValue) {
                    $bValue['models_name'] = $bValue['models_name'].'【首付'.$bValue['payment'].'，'.'月供'.$bValue['monthly'].'，'.'GPS '.$bValue['gps'].'，'.'尾款 '.$bValue['tail_section'].'】'.'---'.$bValue['financial_platform_name'];
                    $newB[] = $bValue;
                }
                $newRes[] = array(
                    'brand_name' => $value['brand_name'],

                    'data' => $newB,
                );
            }

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

                            $admin_nickname = DB::name('admin')->alias('a')->join('sales_order b', 'b.admin_id=a.id')->where('b.id', $row['id'])->value('a.nickname');


                            //请求地址
                            $uri = "https://goeasy.io/goeasy/publish";
                            // 参数数组
                            $data = [
                                'appkey'  => "BC-04084660ffb34fd692a9bd1a40d7b6c2",
                                'channel' => "demo-the_guarantor",
                                'content' => "销售员" . $admin_nickname . "提交的销售单已经提供保证金，请及时处理"
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

                            $result_s = $this->model->isUpdate(true)->save(['id' => $row['id'], 'review_the_data' => 'is_reviewing']);
                            if ($result_s) {
                                $this->success();
                            } else {
                                $this->error('状态更新失败');
                            }
                            $this->success();

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

                $params['plan_acar_name'] = Session::get('plan_id'); 

                $data = DB::name('plan_acar')->where('id', $params['plan_acar_name'])->field('payment,monthly,nperlist,gps,margin,tail_section')->find();
           
           
                $params['car_total_price'] = $data['payment'] + $data['monthly'] * $data['nperlist'];
                $params['downpayment'] = $data['payment'] + $data['monthly'] + $data['margin'] + $data['gps'];
            
                $params['plan_name'] = Session::get('plan_name');

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

        //销售方案类别
        $category = DB::name('scheme_category')->field('id,name')->select();

        // die;
        
        $this->view->assign('category', $category);

        if ($this->request->isPost()) {
             $params = $this->request->post('row/a');
            //方案id
            $params['plan_acar_name'] = Session::get('plan_id');
            //方案重组名字
            $params['plan_name'] = Session::get('plan_name');
            //models_id
            $params['models_id'] = Session::get('models_id');
            $data = DB::name('plan_acar')->where('id', $params['plan_acar_name'])->field('payment,monthly,nperlist,gps,margin,tail_section')->find();
            $params['car_total_price'] = $data['payment'] + $data['monthly'] * $data['nperlist'];
            $params['downpayment'] = $data['payment'] + $data['monthly'] + $data['margin'] + $data['gps'];
            //生成订单编号
            $params['order_no'] = date('Ymdhis');
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

    //方案组装
    public function planname()
    {
        if ($this->request->isAjax()) {

        
            $plan_id = input("id");
            $plan_id = json_decode($plan_id, true);
            $sql = Db::name('models')->alias('a')
                ->join('plan_acar b', 'b.models_id=a.id')
                ->field('a.name as models_name,b.id,b.payment,b.monthly,b.gps,b.tail_section,b.margin,b.category_id,b.models_id')
                ->where(['b.ismenu' => 1, 'b.id' => $plan_id])
                ->find();
            $plan_name = $sql['models_name'].'【首付'.$sql['payment'].'，'.'月供'.$sql['monthly'].'，'.'GPS '.$sql['gps'].'，'.'尾款 '.$sql['tail_section'].'，'.'保证金'.$sql['margin'].'】';

            Session::set('plan_id',$plan_id);
            Session::set('plan_name',$plan_name);
            Session::set('models_id',$sql['models_id']);
        }
    }
    
    //显示方案列表
    public function planacar()
    {
        if ($this->request->isAjax()) {
        
            $category_id = input("category_id");
            $category_id = json_decode($category_id, true);

            $result = DB::name('plan_acar')->alias('a')
                    ->join('models b', 'b.id=a.models_id')

                    ->where('a.category_id', $category_id)
                   
                    ->where('sales_id', NULL)

                    ->whereOr('sales_id', $this->auth->id)

                    ->field('a.id,a.payment,a.monthly,a.nperlist,a.margin,a.tail_section,a.gps,a.note,b.name as models_name,b.id as models_id')

                    ->select();
            foreach ($result as $k =>$v) {

                $result[$k]['downpayment'] = $v['payment'] + $v['monthly'] + $v['margin'] + $v['gps'];

            }

            $result = json_encode($result);
           
            return $result;
        }
    }
    
    /**
     * 获取通话清单,第一步登陆，获取验证码
     */
    public function getCallListfiles()
    {
        //接口参数userid、apikey、
        if ($this->request->isAjax()) {
            //登陆
            $params = $this->request->post('');

            $params['userid'] = $this->userid;
            $params['sign'] = $this->sign;
            $params['op'] = 'collect';
            //    return $params['sign'];
            $result = posts('https://www.zhicheng-afu.com/ZSS/api/yixin_yys/V1', $params);
            if ($result['errorcode'] == '0000' && !isset($result['data']['type'])) {  // 如果返回值没有type，就直接获取数据
                $params['op'] = 'get';
                $params['sid'] = $result['data']['sid'];
                $result = posts('https://www.zhicheng-afu.com/ZSS/api/yixin_yys/V1', $params);
                if ($result['errorcode'] == '0000') {
                    $result['get_data'] = 'yes';
                    $this->success($result['message'], null, $result);
                } else {
                    $this->error($result['message'], null, $result);
                }
            }
            if ($result['errorcode'] == '0000' && isset($result['data']['type'])) {
                //需要返回手机号码
                $result['username'] = $params['username'];
                $this->success($result['message'], null, $result);
            } else {
                $this->error($result['message'], null, $result);
            }
        }
    }

    /**
     * 第二步，得到sid，提交手机验证码,此步骤可能重复，.
     */
    public function getCallListfiles2()
    {
        if ($this->request->isAjax()) {
            $params = $this->request->post('');
            $params['userid'] = $this->userid;
            $params['sign'] = $this->sign;
            $params['op'] = 'code';

            $result = posts('https://www.zhicheng-afu.com/ZSS/api/yixin_yys/V1', $params);

            if ($result['errorcode'] == '0000' && !isset($result['data']['type'])) {   // 如果返回值没有type，就直接获取数据
                //如果验证成功，如果需要再次提交验证码，则继续返回，判断type是否有设定
                $params['op'] = 'get';
                $params['sid'] = $result['data']['sid'];
                $result = posts('https://www.zhicheng-afu.com/ZSS/api/yixin_yys/V1', $params);
                if ($result['errorcode'] == '0000') {
                    $result['get_data'] = 'yes';
                    $this->success($result['message'], null, $result);
                } else {
                    $this->error($result['message'], null, $result);
                }
            }
            if ($result['errorcode'] == '0000' && isset($result['data']['type'])) { //如果存在这个type，需要继续返回前端获取验证码
            } else {
                $this->error($result['message'], null, $result);
            }
        }
    }

    /**
     * 获取验证码
     *
     * @params userid,sign,op,sid,
     *
     * @return array
     */
    public function getCode($sid)
    {
        $params['userid'] = $this->userid;
        $params['sign'] = $this->sign;
        $params['op'] = 'code';
        $params['sid'] = $sid;

        return posts('https://www.zhicheng-afu.com/ZSS/api/yixin_yys/V1', $params);
    }

    public function https_request($url, $data = null, $time_out = 60, $out_level = 's', $headers = array())
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_NOSIGNAL, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        if ($out_level == 's') {
            //超时以秒设置
            curl_setopt($curl, CURLOPT_TIMEOUT, $time_out); //设置超时时间
        } elseif ($out_level == 'ms') {
            curl_setopt($curl, CURLOPT_TIMEOUT_MS, $time_out);  //超时毫秒，curl 7.16.2中被加入。从PHP 5.2.3起可使用
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if ($headers) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers); //如果有header头 就发送header头信息
        }
        $output = curl_exec($curl);
        curl_close($curl);

        return $output;
    }

    /*
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
}

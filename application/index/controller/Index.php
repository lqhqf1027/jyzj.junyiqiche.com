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
use think\Request;
use think\Session;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';


    public function _initialize()
    {
        parent::_initialize();
//        $memberId = Session::get('MEMBER')['id'];
//
//        //判断是否扫码进入；
//        $order_id = Request::instance()->param('order_id') ;
//        if ($order_id) {
//            return 1;die;
//            $s = self::isApplyDriver($order_id);
//
//            if ($s['wx_public_user_id'] && $s['wx_public_user_id'] !== $memberId) die('<h1 style="margin-top: 20%;color: red;"><center> 该车辆已被 ' . $s['username'] . ' 授权</center></h1>');
//
//            return Order::update(['id' => $order_id, 'wx_public_user_id' => $memberId]) && WxPublicUser::update(['id' => Session::get('MEMBER')['id'], 'is_apply' => 1]) ? alert('认证成功!！','','https://jyzj.junyiqiche.com/index') : die('<h1 style="margin-top: 20%;color: red;"><center> 认证失败</center></h1>');
//
//        }
//
//        $licensenumber = OrderDetails::get(['order_id' => Order::get(['wx_public_user_id' => $memberId])->id])->licensenumber;
//        $this->view->assign(['userInfo' => Session::get('MEMBER'), 'licensenumber' => $licensenumber]);

    }


    public function index()
    {
        $uid = Session::get('MEMBER');

        $userinfo = Db::name('wx_public_user')->where('openid', $uid['openid'])->find();

        $day_7 = intval($userinfo['query_time']) + intval((7 * 86400));

        //判断首先时间字段都不为空
        if (!empty($userinfo['query_time'])) {
            //计算第一次时间加上7天 
            //如果登录进来当前时间大于数据表里的时间
            if (time() > $day_7) {

                //重置次数
                Db::name('user')->where('openid', $uid['openid'])->setField('query_number', 2);
            } else {

            }
            //扔出7天后的时间

        }

        if ($userinfo['query_number'] == 0) {
            $this->assign('time7', date('Y-m-d H:i:s', $day_7));


        }


        //违章信息
        $order_id = Db::name('order')->where(['wx_public_user_id' => $uid['id']])->find()['id'];
        $detail = Db::name('order_details')->where(['order_id' => $order_id])->find();

        //用户头像,用户的查询次数
        $this->assign([
            'userinfo' => $uid,
            'detail' => json_decode($detail['violation_details'], true),
            'user_query_num' => $userinfo['query_number'],
            'userInput' => $detail
        ]);


        return Order::get(['wx_public_user_id' => $uid['id']]) ? $this->view->fetch('apply') : $this->view->fetch();

    }

    /**
     * 判断该条订单是否 被认证过
     * @param $order_id
     * @return bool
     * @throws \think\exception\DbException
     */
    public static function isApplyDriver($order_id)
    {
        return Order::get($order_id)->getData();
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
                // return $params['id_card'];
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

    /**
     * 认证君忆司机[已填写表单-》提交认证]
     */
    public function applyDriverInfo()
    {

        if ($this->request->isAjax()) {

                $params = $this->request->post('');

                Db::startTrans();
                try {
                    if (self::isApplyDriver($params['order_id'])['wx_public_user_id']) throw new Exception('该车型已被认证过');
                    WxPublicUser::update(['id' => Session::get('MEMBER')['id'], 'is_apply' => $params['is_apply']]);
                    Order::update(['id' => $params['order_id'], 'wx_public_user_id' => Session::get('MEMBER')['id']]);
                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage(), '', '');
                }
                $this->success('认证成功', '', '');


            $this->error('非法请求', '', '');


        }
        $this->error('非法请求', '', '');

    }

    /**
     * 已认证公众号君忆司机
     * @return string
     * @throws Exception
     */
    public function apply()
    {

        return $this->view->fetch();
    }

    /**卡片分享
     * @return false|string
     */
    public function sharedata()
    {
        $url = input('urll');//获取当前页面的url，接收请求参数

        $root['url'] = $url;
        //获取access_token，并缓存
        $file = RUNTIME_PATH . '/access_token';//缓存文件名access_token
        $appid = Env::get('wx_public.appid'); // 填写自己的appid
        $secret = Env::get('wx_public.secret'); // 填写自己的appsecret
        $expires = 3600;//缓存时间1个小时
        if (file_exists($file)) {
            $time = filemtime($file);
            if (time() - $time > $expires) {
                $token = null;
            } else {
                $token = file_get_contents($file);
            }
        } else {
            fopen("$file", "w+");
            $token = null;
        }
        if (!$token || strlen($token) < 6) {
            $res = file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $secret . "");

            $res = json_decode($res, true);
            $token = $res['access_token'];
            // write('access_token', $token, 3600);
            @file_put_contents($file, $token);
        }

        //获取jsapi_ticket，并缓存
        $file1 = RUNTIME_PATH . '/jsapi_ticket';
        if (file_exists($file1)) {
            $time = filemtime($file1);
            if (time() - $time > $expires) {
                $jsapi_ticket = null;
            } else {
                $jsapi_ticket = file_get_contents($file1);
            }
        } else {
            fopen("$file1", "w+");
            $jsapi_ticket = null;
        }
        if (!$jsapi_ticket || strlen($jsapi_ticket) < 6) {
            $ur = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$token&type=jsapi";
            $res = file_get_contents($ur);
            $res = json_decode($res, true);
            $jsapi_ticket = $res['ticket'];
            @file_put_contents($file1, $jsapi_ticket);
        }

        $timestamp = time();//生成签名的时间戳
        $metas = range(0, 9);
        $metas = array_merge($metas, range('A', 'Z'));
        $metas = array_merge($metas, range('a', 'z'));
        $nonceStr = '';
        for ($i = 0; $i < 16; $i++) {
            $nonceStr .= $metas[rand(0, count($metas) - 1)];//生成签名的随机串
        }

        $string1 = "jsapi_ticket=" . $jsapi_ticket . "&noncestr=" . $nonceStr . "&timestamp=" . $timestamp . "&url=" . $url . "";
        $signature = sha1($string1);
        $root['appid'] = $appid;
        $root['nonceStr'] = $nonceStr;
        $root['timestamp'] = $timestamp;
        $root['signature'] = $signature;

        return json_encode($root);
    }


    public function ocrView()
    {

        if (request()->isPost()) {
            $uid = Session::get('MEMBER');

            $userinfo = Db::name('wx_public_user')->where('openid', $uid['openid'])->find();

            //判断查询次数是否为0
            if ($userinfo['query_number'] == 0) {
                return json(array('state' => 4, 'errmsg' => '当前可查询次数为0,请于' . date('Y-m-d H:i:s', intval($userinfo['query_time']) + intval((7 * 86400))) . '后再查询', 'data' => ''));

            }
            // 获取表单上传文件 例如上传了001.jpg
            $file = request()->file('file');
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if ($info) {
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                $path = $info->getSaveName();
                // 成功上传后 返回上传信息
                // return json(array('info'=>'https://apply.aicheyide.com/uploads'.DS.$path));
                $data = array(
                    'api_key' => '6YWpf8Xx8g1Ll2F5w8bNOpNkmOby1Sdh',
                    'api_secret' => 'BV_r5bgSN3DY9SELbKpmVUZ52hI-GCPp',
                    'image_url' => 'https://driver.junyiqiche.com' . DS . 'uploads' . DS . $path
                );
                // return  $res->driving('https://apply.aicheyide.com' .DS. 'uploads'.DS.$path);
                //旷视ocr接口，提取行驶证信息
                $res = self::curl_post_contents("https://api-cn.faceplusplus.com/cardpp/v1/ocrvehiclelicense", $data);
                if (empty(json_decode($res, true)['cards'])) {
                    return json(array('state' => 1, 'errmsg' => '请上传合法清晰的行驶证 “正页” 图片', 'data' => ''));
                } else {
                    //判断车牌是否是公司的车
                    $res_plate_no = Db::name('order_details')->where('licensenumber', json_decode($res, true)['cards'][0]['plate_no'])->find();
                    if (empty($res_plate_no)) {
                        //如果是空的，那么就不是公司的车辆
                        return json(array('state' => 3, 'errmsg' => '该车辆为非君忆公司车辆！如有疑问请联系管理员@包', 'data' => ''));

                    } else {
                        return json(array('state' => 2, 'errmsg' => '识别成功', 'data' => json_decode($res, true)['cards']));

                    }


                }

                // return posts("https://api-cn.faceplusplus.com/cardpp/v1/ocrvehiclelicense",$data);
                // return json(array('info'=>upload(makePostData(ROOT_PATH . 'public' . DS . 'uploads'.DS.$path, 'image/jpeg'))));
            } else {
                // 上传失败返回错误信息
                return json(array('state' => 0, 'errmsg' => '上传失败'));
            }
        }


    }


    public function selCarInfo()
    {

        $data = input('post.');
        //判断车牌是否是公司的车
        $res_plate_no = Db::name('order_details')->where('licensenumber', $data['plate_no'])->find();
        if (empty($res_plate_no)) {
            //如果是空的，那么就不是公司的车辆
            return json(array('error_code' => 5, 'reason' => '该车辆为非君忆公司车辆！如有疑问请联系管理员@包', 'result' => ''));

        }
        $uid = Session::get('MEMBER');

        $userinfo = Db::name('wx_public_user')->where('openid', $uid['openid'])->find();

        //判断查询次数是否为0
        if ($userinfo['query_number'] == 0) {
            return json(array('error_code' => 4, 'reason' => '当前可查询次数为0,请于' . date('Y-m-d H:i:s', intval($userinfo['query_time']) + intval((7 * 86400))) . '后再查询', 'result' => ''));

        }
        $this->selQueryNumber();


        $plate_no = array(
            'key' => '217fb8552303cb6074f88dbbb5329be7',
            'hphm' => urlencode(mb_substr($data['plate_no'], 0, 2, "UTF-8"))
        );

        //聚合查询城市前缀
        $car_city_name = gets("http://v.juhe.cn/sweizhang/carPre?key=217fb8552303cb6074f88dbbb5329be7&hphm={$plate_no['hphm']}");

        // $car_city_name_arr =  json_decode($car_city_name,true);

        if ($car_city_name['error_code'] == 0) {
            ##如果返回的错误码不等于0，就返回官方的错误信息
            // return json(array('state' =>$car_city_name['result']['city_code']));

            //根据需要的查询条件，查询车辆的违章信息
            $city = $car_city_name['result']['city_code']; //城市代码，必传

            $carno = $data['plate_no']; //车牌号，必传
            $engineno = $data['engine_no']; //发动机号，需要的城市必传
            $classno = $data['vin']; //车架号，需要的城市必传
 
            
            //更新车牌号，车架号，发动机号
            $order_id = Db::name('order')->where('wx_public_user_id',$uid['id'])->find()['id'];
            $result = [
                'licensenumber' => $carno,
                'frame_number' => $classno,
                'engine_number' => $engineno
            ];
            Db::name('order_details')->where('order_id',$order_id)->update($result);
                        
            if(strlen($carno)==9){
                return  gets("http://v.juhe.cn/sweizhang/query?city={$city}&hphm={$carno}&engineno={$engineno}&classno={$classno}&key=217fb8552303cb6074f88dbbb5329be7"); 
                                
            }
            else{
                return  gets("http://v.juhe.cn/sweizhang/query?city={$city}&hphm={$carno}&hpzl=52&engineno={$engineno}&classno={$classno}&key=217fb8552303cb6074f88dbbb5329be7"); 
 

            }


        } else {
            return $car_city_name;
        }

    }

    public function selQueryNumber()
    {

        $uid = Session::get('MEMBER');

        $userinfo = Db::name('wx_public_user')->where('openid', $uid['openid'])->find();
        //判断首先时间字段为空的情况下，代表新用户第一次查询，新增时间,次数变为1
        //  if(empty($userinfo['query_time'])){
        //     Db::name('wx_public_user')->where('openid',session('MEMBER')['openid'])->update(['query_time'=>time(),'query_number'=>1]); 


        // } 
        //计算第一次时间加上7天
        $day_7 = intval($userinfo['query_time']) + intval((7 * 86400));
        //如果当前的时间小于第一次设置的时间+7天并且次数小于2，证明是在有效7天内进行第二次查询，次数变为0
        if (time() < $day_7) {
            Db::name('wx_public_user')->where('openid', $uid['openid'])->setDec('query_number');
        }
        if (time() > $day_7) {

            if ($userinfo['query_number'] == 2) {
                Db::name('wx_public_user')->where('openid', $uid['openid'])->setField('query_time', time());
            }
            Db::name('wx_public_user')->where('openid', $uid['openid'])->setDec('query_number');

        } else {

            //查询
            return json(array('error_code' => 3, 'result' => '', 'reason' => '请于' . date('Y-m-d H:i:s', $day_7) . '后再试'));
        }
    }

    //查询是否有车牌号
    public function getUserVin()
    {
        //判断车牌是否是公司的车
        $res_plate_no = Db::name('order_details')->where('licensenumber', input('post.pateNo_val'))->find();
        if (empty($res_plate_no)) {
            //如果是空的，那么就不是公司的车辆
            return json(array('error_code' => 1, 'reason' => '该车辆为非君忆公司车辆！如有疑问请联系管理员@包', 'result' => ''));
        } else {
            return json(array('error_code' => 2, 'reason' => '查询成功', 'result' => $res_plate_no));

        }
    }

    /**
     *
     * curl Post数据
     * @param $url http地址
     * @param $data &链接的字符串或者数组
     * @param $timeout 默认请求超时
     * 成功返回字符串
     */
    static function curl_post_contents($url, $data = array(), $timeout = 10)
    {
        $userAgent = 'xx5.com PHP5 (curl) ' . phpversion();
        $referer = $url;
        if (!is_array($data) || !$url) return '';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);            //设置访问的url地址
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);            //设置超时
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);   //用户访问代理 User-Agent
        curl_setopt($ch, CURLOPT_REFERER, $referer);      //设置 referer
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);      //跟踪301
        curl_setopt($ch, CURLOPT_POST, 1);             //指定post数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);      //添加变量
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);      //返回结果
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }

}

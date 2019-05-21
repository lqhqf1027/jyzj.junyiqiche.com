<?php

namespace app\admin\controller\vehicle;

use app\admin\model\Order;
use app\admin\model\OrderDetails;
use app\admin\model\OrderImg;
use app\common\controller\Backend;
use fast\Date;
use think\Cache;
use think\Db;
use think\Config;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;
use think\Session;
use Endroid\QrCode\QrCode;
use wechat\Wx;
use think\Env;
use app\admin\model\WxPublicUser;

use fast\Http;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class Pushwechat extends Backend
{

    /**
     * Vehiclemanagement模型对象
     * @var \app\admin\model\vehicle\Vehiclemanagement
     */
    protected $model = null;
    protected $noNeedRight = ['*'];

    protected $noNeedLogin = ['sendannual', 'sendtrafficforce', 'timing_violation'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Order();

    }

    /**
     * 发送微信公众号模板消息
     * @param $data
     * @return array
     */
    public static function sendXcxTemplateMsg($data = '')
    {
        Cache::rm('access_token');
        $appid = Env::get('wx_public.appid');
        $secret = Env::get('wx_public.secret');
        $wx = new wx($appid, $secret);
        $access_token = $wx->getWxtoken()['access_token'];
        // pr($access_token);
        // die;
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";
        return posts($url, $data);
    }


    /**
     * 获取用户openid
     * @param $user_id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getOpenid($wx_public_user_id)
    {
        return Db::name('wx_public_user')->where(['id' => $wx_public_user_id])->find()['openid'];
    }

    /**
     * 发送年检公众号模板消息
     */
    public function sendannual($ids = '')
    {
        $detail = Collection($this->model->field('username,phone,wx_public_user_id,models_name')
            ->with(['orderdetails' => function ($q) use ($ids) {
                $q->withField('licensenumber,frame_number,annual_inspection_time,annual_inspection_status')->where('annual_inspection_status', 'in', ['soon', 'overdue'])->where('order_id', 1877);
            }])->select())->toArray();

        //是否存在数据
        if ($detail) {
            foreach ($detail as $key => $value) {

                $openid = $this->getOpenid($value['wx_public_user_id']);

                //是否有openid
                if ($openid) {

                    $type = $value['orderdetails']['annual_inspection_status'] == 'soon' ? '即将到期' : '已到期';
                    $first = $value['username'] . '您好，您车牌号为＂' . $value['orderdetails']['licensenumber'] . '＂车辆的年检' . $type;
                    $time = date('Y-m-d', $value['orderdetails']['annual_inspection_time']);

                    $temp_msg = array(
                        'touser' => "{$openid}",
                        'template_id' => "aBEssxm7rNQKj_j0gDimieBcbjR1NbNrqvG8SV0wjSE",
                        'data' => array(
                            'first' => array(
                                "value" => "{$first}",
                            ),
                            'keyword1' => array(
                                "value" => "{$value['orderdetails']['licensenumber']}",
                            ),
                            'keyword2' => array(
                                "value" => "{$value['orderdetails']['frame_number']}",
                            ),
                            'keyword3' => array(
                                "value" => "{$time}",
                                "color" => '#FF5722'
                            ),
                            "remark" => array(
                                "value" => "请及时处理",
                            )

                        ),
                    );

                    $res = $this->sendXcxTemplateMsg(json_encode($temp_msg));

                }

            }

            $this->success();
        }

    }


    /**
     * 发送保险公众号模板消息
     */
    public function sendtrafficforce($ids = '')
    {
        $detail = Collection($this->model->field('username,phone,wx_public_user_id,models_name')
            ->with(['orderdetails' => function ($q) use ($ids) {
                $q->withField('licensenumber,frame_number,traffic_force_insurance_time,traffic_force_insurance_status')->where('traffic_force_insurance_status', 'in', ['soon', 'overdue'])->where('order_id', 1864);
            }])->select())->toArray();

        //是否存在数据
        if ($detail) {
            foreach ($detail as $key => $value) {

                $openid = $this->getOpenid($value['wx_public_user_id']);

                //是否有openid
                if ($openid) {

                    $type = $value['orderdetails']['traffic_force_insurance_status'] == 'soon' ? '即将到期' : '已到期';
                    $first = $value['username'] . '您好，您车牌号为＂' . $value['orderdetails']['licensenumber'] . '＂车辆的保险' . $type;
                    $time = date('Y-m-d', $value['orderdetails']['traffic_force_insurance_time']);

                    $temp_msg = array(
                        'touser' => "{$openid}",
                        'template_id' => "xYsJcFIfIGaS6PX1EnSpqJ68xp-ENHTT6IWzDX7HrCo",
                        'data' => array(
                            'first' => array(
                                "value" => "{$first}",
                            ),
                            'keyword1' => array(
                                "value" => "{$$value['username']}",
                            ),
                            'keyword2' => array(
                                "value" => "{$value['orderdetails']['licensenumber']}",
                            ),
                            'keyword3' => array(
                                "value" => "{$value['models_name']}",
                                "color" => '#FF5722'
                            ),
                            'keyword4' => array(
                                "value" => "{$time}",
                                "color" => '#FF5722'
                            ),
                            "remark" => array(
                                "value" => "请及时处理",
                            )

                        ),
                    );

                    $res = $this->sendXcxTemplateMsg(json_encode($temp_msg));

                }

            }

            $this->success();
        }

    }

    //更新unionid
    public function wechat()
    {
        Cache::rm('access_token');
        $appid = Env::get('wx_public.appid');
        $secret = Env::get('wx_public.secret');
        $wx = new wx($appid, $secret);
        $access_token = $wx->getWxtoken()['access_token'];
        $result = WxPublicUser::where('id', '>', 99)->select();

        $data = [
            'user_list' => [

            ]
        ];
        foreach ($result as $k => $v) {

            $openid = [
                'openid' => $v['openid']
            ];
            array_push($data['user_list'], $openid);

        }
        $data = json_encode($data);
        // pr($data);
        // die;
        $r = posts("https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token={$access_token}", $data);

        foreach ($r['user_info_list'] as $k => $v) {
            // 更新当前用户信息
            WxPublicUser::where(['openid' => $v['openid']])->update(['unionid' => $v['unionid']]);
        }


    }

    /**
     * 定时推送违章查询
     */
    public function timing_violation()
    {
        echo phpinfo();die;
        ini_set('memory_limit','3072M');    // 临时设置最大内存占用为3G
        set_time_limit(0);   // 设置脚本最大执行时间 为0 永不过期

        $i = 0;
        while ($i <= 10) {
            echo "i=$i ";
            sleep(100);
            $i++;
        }


//        $redis = new \Redis();
//        $redis->connect('127.0.0.1', 6379);
//        $redis->set("name", "redis3.1");
//        echo $redis->get("name");
////        echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/?order_id=';
////        $http = new swoole_http_server();
//        echo phpinfo();die;
//        swoole_timer_tick();
//        new \swoole_http_server();
//        Swoole\Timer::tick();
//echo phpinfo();die;
//
//
//
//
//
//        die;
//        $redis = new \Redis();
//
//        $redis->connect('120.78.135.109', '6379');
//
//        $redis->auth('654321');
//
//        $info = OrderDetails::field('id,order_id,licensenumber,engine_number,frame_number')
//            ->where('licensenumber&engine_number&frame_number', 'not in', ['null', ''])
//            ->distinct(true)
//            ->field('licensenumber')
//            ->limit(100)
//            ->select();
//
////        foreach ($info as $k => $v){
////            $redis->hMSet('test',array('id'=>$v['id'],'order_id'=>$v['order_id'],'licensenumber'=>$v['licensenumber']));
////        }
//
//        dump($redis->hGetAll('test'));
//
//        die;
//
////        $page = $this->request->get('page');
////
////        $res = OrderDetails::where('licensenumber', 'not null')
////            ->order('id desc')
////            ->page($page . ',10')
////            ->field('id,licensenumber')
////            ->select();
////
////        pr(collection($res)->toArray());
////        die;
//
////        $data = \fast\Http::sendRequest('http://v.juhe.cn/carInfo/querySimple.php', [
////            'number' => '川XF009U',
////            'key' => 'c4069179480d70096a37a8e9d11a3d0b',
////        ], 'GET');
////
////
////        pr(json_decode($data['msg'], true));
////
////        die;
//
//
//        try {
//            set_time_limit(300);
//            $info = OrderDetails::field('id,order_id,licensenumber,engine_number,frame_number')
//                ->where('licensenumber&engine_number&frame_number', 'not in', ['null', ''])
//                ->distinct(true)->field('licensenumber')
//
//                ->chunk(100, function ($item) {
//
//                    $item = collection($item)->toArray();
//                    $data = array();
//                    foreach ($item as $k => $v) {
//
//                        $data[] = [
//                            'hphm' => mb_substr($v['licensenumber'], 0, 2),
//                            'hphms' => $v['licensenumber'],
//                            'engineno' => $v['engine_number'],
//                            'classno' => $v['frame_number'],
//                            'order_id' => $v['order_id'],
//                            'username' => '123',
//                        ];
//
////
//
//
//                    }
////                    pr($data);
//                    pr(illegal($data));
//                    return false;
//                }, null, 'desc');
//
//        } catch (Exception $e) {
//            pr($e->getMessage());
//        }

    }


}

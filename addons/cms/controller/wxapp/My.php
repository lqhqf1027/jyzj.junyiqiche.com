<?php

namespace addons\cms\controller\wxapp;

use addons\cms\model\Config;
use addons\cms\model\OrderDetails;
use addons\cms\model\User;
use addons\cms\model\Order;
use aipspeech\AipSpeech;
use app\common\model\Type;
use fast\Auth;
use think\Cache;
use think\Db;
use Endroid\QrCode\QrCode;
use fast\Random;
use think\Exception;
use think\exception\PDOException;

/**
 * 我的
 */
class My extends Base
{
    protected $noNeedLogin = ['*'];
    protected $uid = '';

    public function _initialize()
    {
        parent::_initialize();
//        $auth = Auth::instance();
//        $this->uid = $auth->id;
    }

    public function index()
    {
        $user_id = $this->request->post('user_id');
        if (!(int)$user_id) $this->error('参数错误');
        try {
            $userInfo = User::field('id,nickname,avatar,invite_code,invitation_code_img,level,cif_driver')
                ->find($user_id);
            if (!$userInfo) $this->error('未查询到用户信息');
            //查询邀请码背景图片
            $userInfo['invite_bg_img'] = Config::get(['name' => 'invite_bg_img'])->value;

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('请求成功', ['userInfo' => $userInfo]);

    }

    /**
     * 生成二维码
     * @throws \Endroid\QrCode\Exceptions\ImageTypeInvalidException
     */
    public function setQrcode()
    {
        $user_id = $this->request->post('user_id');
        if (!(int)$user_id) $this->error('参数错误');
        $time = date('YmdHis');
        $qrCode = new QrCode();
        $qrCode->setText($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/?user_id=' . $user_id)
            ->setSize(150)
            ->setPadding(10)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabel(' ')
            ->setLabelFontSize(10)
            ->setImageType(\Endroid\QrCode\QrCode::IMAGE_TYPE_PNG);
        $fileName = DS . 'uploads' . DS . 'qrcode' . DS . $time . '_' . $user_id . '.png';
        $qrCode->save(ROOT_PATH . 'public' . $fileName);
        if ($qrCode) {
            User::update(['id' => $user_id, 'invitation_code_img' => $fileName]) ? $this->success('创建成功', $fileName) : $this->error('创建失败');
        }
        $this->error('未知错误');
    }

    /**
     * 查询违章
     */
    public function query_violation()
    {
        $user_id = $this->request->post('user_id');

        $is_update = $this->request->post('is_update');

        try {
            $order_info = \app\admin\model\Order::getByUser_id($user_id)->visible(['id', 'username']);

            $order_details = OrderDetails::getByOrder_id($order_info->id);
            if ($order_details) {
                $status = $order_details->is_it_illegal;

                if ($is_update) {
                    $status = 'no_queries';
                }
                if ($status == 'no_queries') {
                    $parms = [
                        [
                            'hphm' => trim(mb_substr($order_details->licensenumber, 0, 2)),
                            'hphms' => trim($order_details->licensenumber),
                            'engineno' => trim($order_details->engine_number),
                            'classno' => trim($order_details->frame_number),
                            'order_id' => trim($order_details->order_id),
                            'username' => trim($order_info->username)
                        ]
                    ];

                    \app\admin\controller\vehicle\Vehiclemanagement::illegal($parms, true);

                    $order_details = OrderDetails::getByOrder_id($order_info->id)->visible(['licensenumber', 'frame_number', 'engine_number', 'total_deduction', 'total_fine', 'violation_details', 'number_of_queries'])->toArray();

                    if ($order_details['violation_details']) {
                        $order_details['violation_details'] = json_decode($order_details['violation_details'], true);
                        $order_details['violation_number'] = count($order_details['violation_details']);
                    }
                } else {
                    $order_details = $order_details->visible(['licensenumber', 'frame_number', 'engine_number', 'total_deduction', 'total_fine', 'violation_details', 'number_of_queries'])->toArray();

                    if ($order_details['violation_details']) {
                        $order_details['violation_details'] = json_decode($order_details['violation_details'], true);
                        $order_details['violation_number'] = count($order_details['violation_details']);
                    }
                }
                $this->success('查询成功', $order_details);

            } else {
                throw new Exception('发生未知错误');
            }

        } catch (PDOException $e) {
            $this->error($e->getMessage());
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }


    }

    /* 君忆司机认证，表单
    * @throws \think\exception\DbException
    */
    public function confirmFormDriver()
    {
        $params = $this->request->post('');
        $err = '';
        foreach ($params as $key => $item) {
            if (!in_array($key, ['user_id', 'username', 'phone', 'licensenumber', 'id_card']) || !$item) $err .= ' ' . $key;
        }
        if ($err) $this->error($err . '参数错误');
        //根据车牌号查询出order_details id
//        return $oder_id;
        $data = Order::get(['username' => $params['username'], 'phone' => $params['phone'], 'id_card' => $params['id_card']]);
        $licensenumber = OrderDetails::get(['order_id' => $data->id])->licensenumber;
        if ($data && $licensenumber) {
            if ($licensenumber != $params['licensenumber']) $this->error('输入的车主信息与车牌号' . $params['licensenumber'] . '不匹配');
        } else {
            $this->error(' 未查询到认证信息');
        }
        if (Order::update(['id' => $data->id, 'user_id' => $params['user_id']]) && User::update(['id' => $params['user_id'], 'cif_driver' => 1])) $this->error('认证成功');

        $this->error(' 认证失败');


    }

    /**
     * 用户认证
     */
    public function client_authentication()
    {
        $user_id = $this->request->post('user_id');
        $order_id = $this->request->post('order_id');

        Db::startTrans();
        try {
            $order = Order::get($order_id);

            if ($order) {
                $order->user_id = $user_id;
                $order->save();

                $user = \app\admin\model\User::get($user_id);

                if ($user) {
                    $user->cif_driver = 1;

                    $user->save();
                } else {
                    throw new Exception('未找到该用户');
                }
            } else {
                throw new Exception('未找到该订单');
            }
            Db::commit();
        } catch (PDOException $e) {
            Db::rollback();
            $this->error($e->getMessage());
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        $this->success('认证成功');

    }

    /**
     * 搜索品牌车型
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function search_vehicles()
    {

        $search = $this->request->post('search');

        if ($search == '') {
            $this->success('查询条件不能为空');
        }

        try {
            $info = collection(\app\admin\model\BrandCate::field('id,name,bfirstletter')
                ->with(['models' => function ($q) {
                    $q->where('status', 'normal')->field('id,name,brand_id');
                }])->where('name', 'like', '%' . $search . '%')->select())->toArray();

            if (empty($info)) {
                $info = collection(\app\admin\model\BrandCate::field('id,name,bfirstletter')
                    ->with(['models' => function ($q) use ($search) {
                        $q->where([
                            'status' => 'normal',
                            'name' => ['like', '%' . $search . '%']
                        ])->field('id,name,brand_id');
                    }])->select())->toArray();
            }
        } catch (PDOException $e) {
            $this->error($e->getMessage());
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        //二维数组根据某个字段a-z顺序排列数组
        array_multisort(array_column($info, 'bfirstletter'), SORT_ASC, $info);

        foreach ($info as $k => $v) {
            if (!$v['models']) {
                unset($info[$k]);
                continue;
            }
        }

        if ($info) {
            $info = array_values($info);
            $this->success('搜索成功', ['brandList' => $info]);
        } else {
            $this->success('未查询到数据');
        }


    }

    public function tencent()
    {
        $filepath = 'voice/tmp_265c59f4dcebc75ec0cf861afa69a8e4.wav';

        $filepath = urlencode($filepath);

//        $this->success(gets("https://czz.junyiqiche.com/addons/cms/wxapp.index/index"));
        $time = time();
        $Nonce = rand(1, 100);
        $this->success(gets("https://aai.tencentcloudapi.com/?Action=SentenceRecognition&ProjectId=0&SubServiceType=2&EngSerViceType=16k&SourceType=0&Version=2018-05-22&VoiceFormat=wav&UsrAudioKey=asr_1_template12&Url=" . $filepath . "&Timestamp={$time}&Nonce={$Nonce}&SecretId=AKIDOcLvMY2Dg2Y0IPuSK2eWDoEGI9oQoY5T"));
    }

    //音频上传编解码
    public function wxupload()
    {

        $upload_res = $_FILES['voice'];

        try {

            $wavname = substr($upload_res['name'], 0, strripos($upload_res['name'], ".")) . ".wav";

            //手机录音文件

            $path = 'voice/' . $upload_res['name'];

            $newpath = 'voice/' . $wavname;

            file_put_contents($path, file_get_contents($upload_res['tmp_name']));

            chmod($path, 0777);

            $exec1 = "ffmpeg -i /home/wwwroot/jyzj.junyiqiche.com/public/$path -acodec pcm_s16le -ac 2 -ar 16000 /home/wwwroot/jyzj.junyiqiche.com/public/$newpath";

            exec($exec1, $info, $status);

            chmod($newpath, 0777);

            if ($status == 0) {

                $data['path'] = $newpath;

                $client = new AipSpeech('16101235', 'bXanfwBIVCsONzVTnVDB7SYo', 'MDVyVOy2QKvCHE9M8dPzk2ekR0evwaNe');

                $result = $client->asr(file_get_contents($newpath), 'wav', 16000, array(
                    'dev_pid' => 1536,
                ));

//                switch ($result['err_no']){
//                    case 3301:
//                        $msg = '请上传清晰的音频';
//                        break;
//                    default:
//                        $msg = '未知';
//                }
                // 识别本地文件
                $result['err_no'] == 0 ? $this->success('识别成功', $result) : $this->error('识别失败,错误码：' . $result['err_no']);

//                $this->success("转码成功", $data);

            }


        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        $this->error("发生未知错误", "！");

    }

    public function test()
    {
        header('Content-Type:application/json; charset=utf-8');

//        $picturedata = fread(fopen('16k.pcm', "r"), filesize('16k.pcm'));

        $access_token = posts('https://openapi.baidu.com/oauth/2.0/token', [
            'grant_type' => 'client_credentials',
            'client_id' => 'aGq0ysie2Kw6uFl2hifyRfHw',
            'client_secret' => '6AVnZ6HoSNnCXWfgbLwA0pvuAwoPN13T'
        ]);
//$this->success($this->StrToBin('asdasdsxcvxvxc'));
//        $picturedata =  $this->StrToBin(file_get_contents('16k.pcm'));
//        if($access_token['code']==1){
        $access_token = $access_token['access_token'];

        $data = array(
            'format' => 'pcm',
            'rate' => 16000,
            'channel' => 1,
            'cuid' => '70-85-C2-83-80-09',
            'token' => $access_token,
            'dev_pid' => 80001,
            'speech' => base64_encode('16k.pcm'),
            "len" => filesize('16k.pcm'),
        );

        $a = json_encode($data);

        $b = $data;
        $this->success(posts('https://vop.baidu.com/pro_api', $a));
//        }
//
//        $this->success($access_token);
    }

    public function StrToBin($str)
    {
        //1.列出每个字符
        $arr = preg_split('/(?<!^)(?!$)/u', $str);
        //2.unpack字符
        foreach ($arr as &$v) {
            $temp = unpack('H*', $v);
            $temp = $this->str_split_unicode($temp[1], 50);

            $str = '';
            foreach ($temp as $key => $value) {
                $str .= base_convert($value, 16, 2);
            }
            $v = $str;
            unset($temp);
        }

        return join(' ', $arr);
    }

    public function str_split_unicode($str, $l = 0)
    {

        if ($l-- > 0) {

            $ret = array();

            $len = mb_strlen($str, "UTF-8");

            for ($i = 0; $i < $len; $i += $l) {

                $ret[] = mb_substr($str, $i, $l, "UTF-8");

            }

            return $ret;

        }

        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);

    }
}


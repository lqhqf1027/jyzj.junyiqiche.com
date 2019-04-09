<?php
/**
 * Created by PhpStorm.
 * User: glen9
 * Date: 2018/9/20
 * Time: 17:12
 */

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;

class Zcaf extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['share', 'test1', 'rc4J', 'is_json'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['test2'];
    protected $userid = 'junyi'; //用户id
    protected $Rc4Key = 'd477d6d1803125f1'; //apikey  测试key 12b39127a265ce21   正式key d477d6d1803125f1
    protected $sign = null; //sign  md5加密

    /**
     * 共享接口
     * @ApiMethod   (POST)
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function share()
    {

        if ($this->request->isPost()) {
//            return $this->Rc4Key;
            //判读是否为json
            if ($this->is_json($this->request->request()['params']) == false) return json(['errorCode' => '4008', 'message' => '输入的参数部分格式不正确，需json 格式']);
            //获取平台传入过来的params 二维
            $params = json_decode($this->request->request()['params'], true)['params'];

            //rc4解密 并转换为数组
            $shareResult = json_decode($this->rc4($this->Rc4Key, base64_decode(urldecode(utf8_decode($params)))), true);
//            pr($shareResult) ;return;
            if ($shareResult == false) return json(['errorCode' => '4002', 'message' => '参数解密出现异常']);
            $data = Db::name('big_data')->where(['name' => $shareResult['data']['name'], 'id_card' => $shareResult['data']['idNo']])->field(['share_data', 'name', 'id_card', 'phone'])->find();
           if (!empty($data)) {

                //转成数组
                $result = object_to_array(json_decode(base64_decode($data['share_data'])));
                if(isset($result['params']['data']['loanRecords'])){
                    foreach ($result['params']['data']['loanRecords'] as $key=>$value){
                        unset($result['params']['data']['loanRecords'][$key]['orgName']);
                    }
                }
               if(isset($result['params']['data']['riskResults'])&&empty($result['params']['data']['riskResults'])) unset($result['params']['data']['riskResults']);

               if(isset($result['params']['data']['queryStatistics'])) unset($result['params']['data']['queryStatistics']);
               if(isset($result['params']['data']['contractBreakRate'])) unset($result['params']['data']['contractBreakRate']);
               if(isset($result['params']['data']['queryHistory'])) unset($result['params']['data']['queryHistory']);
               if(isset($result['params']['data']['zcCreditScore'])) unset($result['params']['data']['zcCreditScore']);
               if(isset($result['params']['data']['flowId'])) unset($result['params']['data']['flowId']);
                $result['params']['tx'] = '202';
                //对params rc4加密
                $rc4Params = urlencode(base64_encode($this->rc4($this->Rc4Key, json_encode($result['params']))));
                //返回0000代表查询成功有数据
                if ($result['errorCode'] == '0000') {
                    //组装到params
                    return json(['errorCode' => '0000', 'message' => '查询成功', 'params' => $rc4Params]);

                }
                if ($result['errorCode'] == '0001') {
                    //组装到params
                    return json(['errorCode' => '0000', 'message' => '查询成功无数据', 'params' => $rc4Params]);

                }

            }
            return json(['errorCode' => '4101', 'message' => 'API 用户不存在']);
        } else {
            return json(['errorCode' => '0006', 'message' => '非法请求']);
        }
    }

    /**
     * rc4加密   ,解密只需在加密一次
     * @param $pwd
     * @param $data
     * @return string
     */
    public function rc4($pwd, $data)//$pwd密钥　$data需加密字符串
    {
        $key[] = "";
        $box[] = "";

        $pwd_length = strlen($pwd);
        $data_length = strlen($data);

        for ($i = 0; $i < 256; $i++) {
            $key[$i] = ord($pwd[$i % $pwd_length]);//ord返回字符串 string 第一个字符的 ASCII 码值。
            $box[$i] = $i;
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $key[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $data_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;

            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;

            $k = $box[(($box[$a] + $box[$j]) % 256)];
            $cipher .= chr(ord($data[$i]) ^ $k);
        }

        return $cipher;
    }

    /**
     * 判断是否为json
     * @param $string
     * @return bool
     */
    function is_json($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }


}

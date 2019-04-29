<?php
namespace app\wechat\controller;
/**
 * Created by PhpStorm.
 * User: glen9
 * Date: 2019/4/29
 * Time: 17:56
 */

class Server extends \think\Controller
{
    protected $config = [
        'app_id' => 'wx3b2ff8ef5b4b0cde',
        'secret' => '1dfdcffcdd1f4a802b2be1ead9fe144e',
        'token' => 'junyiqiche',
        'response_type' => 'array',
    ];

    public function check()
    {
        $app = \EasyWeChat\Factory::officialAccount($this->config);

        $response = $app->server->serve();

// 将响应输出
        $response->send();
        exit; // Laravel 里请使用：return $response;
    }

}
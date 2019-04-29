<?php

namespace app\wechat\controller;


use EasyWeChat\Foundation\Application;

/**
 * Created by PhpStorm.
 * User: glen9
 * Date: 2019/4/29
 * Time: 17:56
 */
class Server
{
    protected $config = [
        'app_id' => 'wx3b2ff8ef5b4b0cde',
        'secret' => '1dfdcffcdd1f4a802b2be1ead9fe144e',
        'token' => 'junyiqiche',
        'response_type' => 'array',
    ];

    public function check()
    {
        $app = new Application($this->config);
//        $app = \EasyWeChat\Factory::officialAccount($this->config);
//
        $response = $app->server->serve();
//
//// 将响应输出
        $response->send();
        exit;
//        exit; // Laravel 里请使用：return $response;
        echo 1;
    }

}
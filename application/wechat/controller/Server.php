<?php

namespace app\wechat\controller;


use app\common\model\Config;
use EasyWeChat\Foundation\Application;
use think\Controller;
use think\Env;

/**
 * Created by PhpStorm.
 * User: glen9
 * Date: 2019/4/29
 * Time: 17:56
 */
class Server extends Controller
{

    protected $config = [];
    protected $wxServer = null;
    public function __construct()
    {
        $this->config = [
            'app_id' => Env::get('wx_public.appid'),
            'secret' => Env::get('wx_public.secret'),
            'token' => Env::get('wx_public.token'),
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' => RUNTIME_PATH . 'easywechat_log/easywechat.log',
            ],
        ];
        $this->wxServer = new Application($this->config);

    }

    public function check()
    {
        $res= $this->wxServer->server;
        $follow_after_sendMsg = Config::get(['name' => 'follow_after_sendMsg'])->value; //关注后自动回复
        $res->setMessageHandler(function ($message) use ($follow_after_sendMsg) {
            return $follow_after_sendMsg;

            switch ($message->MsgType) {
                case 'event':
                    return '收到事件消息';
                    break;
                case 'text':
                    return '收到文字消息';
                    break;
                case 'image':
                    return '收到图片消息';
                    break;
                case 'voice':
                    return '收到语音消息';
                    break;
                case 'video':
                    return '收到视频消息';
                    break;
                case 'location':
                    return '收到坐标消息';
                    break;
                case 'link':
                    return '收到链接消息';
                    break;
                // ... 其它消息
                default:
                    return '收到其它消息';
                    break;
            }
        });

        $response =  $res->serve();

        $response->send(); // Laravel 里请使用：return $response;
    }

}
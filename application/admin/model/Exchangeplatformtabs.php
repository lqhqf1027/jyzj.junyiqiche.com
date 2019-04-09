<?php

namespace app\admin\model;

use think\Model;

class Exchangeplatformtabs extends Model
{
    

    protected $connection = [
        // 数据库类型
        'type'        => 'mysql',
        // 数据库连接DSN配置
        'dsn'         => '',
        // 服务器地址
        'hostname'    => '120.78.135.109',
        // 数据库名
        'database'    => 'apply',
        // 数据库用户名
        'username'    => 'root',
        // 数据库密码
        'password'    => 'aicheyide',
        // 数据库连接端口
        'hostport'    => '',
        // 数据库连接参数
        'params'      => [],
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',

    ];

    // 表名
    protected $name = 'driver';
    

}

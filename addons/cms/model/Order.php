<?php

namespace addons\cms\model;

use addons\cms\library\OrderException;
use addons\epay\library\Service;
use app\common\library\Auth;
use app\common\model\User;
use think\Exception;
use think\Model;
use think\Request;

/**
 * 订单模型
 */
class Order extends Model
{
    protected $name = "order";

}

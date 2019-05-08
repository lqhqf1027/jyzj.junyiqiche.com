<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/15
 * Time: 18:16
 */

namespace app\admin\model;


use think\Model;

class OrderDetails extends Model
{
    protected $name = 'order_details';

    //关联客服
    public function order()
    {
        return $this->belongsTo('Order', 'order_id', 'id', [])->setEagerlyType(0);
    }

}
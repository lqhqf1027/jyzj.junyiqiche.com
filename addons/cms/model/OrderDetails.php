<?php
/**
 * Created by PhpStorm.
 * User: glen9
 * Date: 2019/4/19
 * Time: 13:09
 */

namespace addons\cms\model;


use think\Model;

class OrderDetails extends Model
{
    protected $name = "order_details";



    public function order(){
        return $this->hasOne('Order','order_id','id');
    }

}
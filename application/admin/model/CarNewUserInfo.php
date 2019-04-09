<?php

namespace app\admin\model;

use think\Model;

class CarNewUserInfo extends Model
{
    // 表名
    protected $name = 'car_new_user_info';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [

    ];
    

    







    public function salesorder()
    {
        return $this->belongsTo('SalesOrder', 'sales_order_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}

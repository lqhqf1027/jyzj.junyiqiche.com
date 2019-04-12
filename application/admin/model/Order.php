<?php

namespace app\admin\model;

use think\Model;


class Order extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'order';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'customer_source_text',
        'genderdata_text',
        'nperlist_text',
        'delivery_datetime_text',
        'type_text'
    ];
    

    
    public function getCustomerSourceList()
    {
        return ['direct_the_guest' => __('Customer_source direct_the_guest'), 'turn_to_introduce' => __('Customer_source turn_to_introduce')];
    }

    public function getGenderdataList()
    {
        return ['male' => __('Male'), 'female' => __('Female')];
    }

    public function getNperlistList()
    {
        return ['12' => __('Nperlist 12'), '24' => __('Nperlist 24'), '36' => __('Nperlist 36'), '48' => __('Nperlist 48'), '60' => __('Nperlist 60')];
    }

    public function getTypeList()
    {
        return ['mortgage' => __('Type mortgage'), 'used_car_mortgage' => __('Type used_car_mortgage'), 'car_rental' => __('Type car_rental'), 'full_new_car' => __('Type full_new_car'), 'full_used_car' => __('Type full_used_car'), 'sublet' => __('Type sublet'), 'affiliated' => __('Type affiliated')];
    }


    public function getCustomerSourceTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['customer_source']) ? $data['customer_source'] : '');
        $list = $this->getCustomerSourceList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getGenderdataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['genderdata']) ? $data['genderdata'] : '');
        $list = $this->getGenderdataList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getNperlistTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['nperlist']) ? $data['nperlist'] : '');
        $list = $this->getNperlistList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getDeliveryDatetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['delivery_datetime']) ? $data['delivery_datetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setDeliveryDatetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function orderdetails()
    {
        return $this->hasOne('OrderDetails', 'order_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function orderimg()
    {
        return $this->hasOne('OrderImg', 'order_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}

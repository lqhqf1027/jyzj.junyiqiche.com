<?php

namespace app\admin\model\vehicle;

use think\Model;


class Vehiclemanagement extends Model
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
        'order_createtime_text',
        'delivery_datetime_text',
        'type_text',
        'lift_car_status_text'
    ];
    

    
    public function getCustomerSourceList()
    {
        return ['direct_the_guest' => __('Customer_source direct_the_guest'), 'turn_to_introduce' => __('Customer_source turn_to_introduce')];
    }

    public function getGenderdataList()
    {
        return ['male' => __('Male'), 'female' => __('Female')];
    }

    public function getTypeList()
    {
        return ['mortgage' => __('Type mortgage'), 'used_car_mortgage' => __('Type used_car_mortgage'), 'car_rental' => __('Type car_rental'), 'full_new_car' => __('Type full_new_car'), 'full_used_car' => __('Type full_used_car'), 'sublet' => __('Type sublet'), 'affiliated' => __('Type affiliated')];
    }

    public function getLiftCarStatusList()
    {
        return ['yes' => __('Lift_car_status yes'), 'no' => __('Lift_car_status no')];
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


    public function getOrderCreatetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['order_createtime']) ? $data['order_createtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
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


    public function getLiftCarStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['lift_car_status']) ? $data['lift_car_status'] : '');
        $list = $this->getLiftCarStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setOrderCreatetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setDeliveryDatetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function orderdetails()
    {
        return $this->hasOne('app\admin\model\OrderDetails', 'order_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}

<?php

namespace app\admin\model\violation;

use think\Model;

class Inquiry extends Model
{
    // 表名
    protected $name = 'violation_inquiry';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'car_type_text',
        'final_time_text',
        'customer_status_text',
        'customer_time_text',
        'start_renttime_text',
        'end_renttime_text'
    ];
    

    
    public function getCarTypeList()
    {
        return ['1' => __('Car_type 1'),'2' => __('Car_type 2'),'3' => __('Car_type 3'),'4' => __('Car_type 4')];
    }     

    public function getCustomerStatusList()
    {
        return ['0' => __('Customer_status 0'),'1' => __('Customer_status 1')];
    }     


    public function getCarTypeTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['car_type']) ? $data['car_type'] : '');
        $list = $this->getCarTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getFinalTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['final_time']) ? $data['final_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCustomerStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['customer_status']) ? $data['customer_status'] : '');
        $list = $this->getCustomerStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCustomerTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['customer_time']) ? $data['customer_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStartRenttimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['start_renttime']) ? $data['start_renttime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getEndRenttimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['end_renttime']) ? $data['end_renttime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setFinalTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setCustomerTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setStartRenttimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setEndRenttimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}

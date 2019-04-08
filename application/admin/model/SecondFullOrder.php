<?php

namespace app\admin\model;

use think\Model;

class SecondFullOrder extends Model
{
    // 表名
    protected $name = 'second_full_order';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'genderdata_text',
        'customer_source_text',
        'delivery_datetime_text',
        'review_the_data_text'
    ];
    

    
    public function getGenderdataList()
    {
        return ['male' => __('Genderdata male'),'female' => __('Genderdata female')];
    }     

    public function getCustomerSourceList()
    {
        return ['direct_the_guest' => __('直客'), 'turn_to_introduce' => __('转介绍')];
    }     

    public function getReviewTheDataList()
    {
        return ['is_reviewing_true' => __('Review_the_data is_reviewing_true'),'for_the_car' => __('Review_the_data for_the_car'),'send_to_internal' => __('Review_the_data send_to_internal'),'inhouse_handling' => __('Review_the_data inhouse_handling')];
    }     


    public function getGenderdataTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['genderdata']) ? $data['genderdata'] : '');
        $list = $this->getGenderdataList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCustomerSourceTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['customer_source']) ? $data['customer_source'] : '');
        $list = $this->getCustomerSourceList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getDeliveryDatetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['delivery_datetime']) ? $data['delivery_datetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getReviewTheDataTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['review_the_data']) ? $data['review_the_data'] : '');
        $list = $this->getReviewTheDataList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setDeliveryDatetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


    public function admin()
    {
        return $this->belongsTo('Admin', 'admin_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function models()
    {
        return $this->belongsTo('Models', 'models_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联方案
     * @return \think\model\relation\BelongsTo|\think\model\relation\HasOne
     */
    public function plansecondfull()
    {
        return $this->belongsTo('SecondcarRentalModelsInfo', 'plan_second_full_name', 'id', [], 'LEFT')->setEagerlyType(0);

    }

}

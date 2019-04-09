<?php

namespace app\admin\model;

use think\Model;

class Trench extends Model
{
    // 表名
    protected $name = 'trench';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'data7_text',
        'data14_text',
        'data20_text',
        'salestype_text'
    ];
    

    
    public function getData7List()
    {
        return ['12' => __('Data7 12'),'24' => __('Data7 24'),'36' => __('Data7 36'),'48' => __('Data7 48'),'60' => __('Data7 60')];
    }     

    public function getData14List()
    {
        return ['male' => __('Data14 male'),'female' => __('Data14 female')];
    }     

    public function getData20List()
    {
        return ['direct_the_guest' => __('Data20 direct_the_guest'),'turn_to_introduce' => __('Data20 turn_to_introduce')];
    }     

    public function getSalestypeList()
    {
        return ['new_car' => __('Salestype new_car'),'rental_car' => __('Salestype rental_car'),'second_car' => __('Salestype second_car'),'full_car' => __('Salestype full_car'),'second_full_car' => __('Salestype second_full_car')];
    }     


    public function getData7TextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['data7']) ? $data['data7'] : '');
        $list = $this->getData7List();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getData14TextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['data14']) ? $data['data14'] : '');
        $list = $this->getData14List();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getData20TextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['data20']) ? $data['data20'] : '');
        $list = $this->getData20List();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getSalestypeTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['salestype']) ? $data['salestype'] : '');
        $list = $this->getSalestypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}

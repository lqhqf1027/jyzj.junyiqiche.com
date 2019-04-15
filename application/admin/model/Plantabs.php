<?php

namespace app\admin\model;

use think\Model;


class Plantabs extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'plan';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'nperlist_text',
        'working_insurance_text',
        'car_licensetime_text',
        'type_text'
    ];
    

    
    public function getNperlistList()
    {
        return ['12' => __('Nperlist 12'), '24' => __('Nperlist 24'), '36' => __('Nperlist 36'), '48' => __('Nperlist 48'), '60' => __('Nperlist 60')];
    }

    public function getWorkingInsuranceList()
    {
        return ['yes' => __('Working_insurance yes'), 'no' => __('Working_insurance no')];
    }

    public function getTypeList()
    {
        return ['mortgage' => __('Type mortgage'), 'used_car_mortgage' => __('Type used_car_mortgage'), 'car_rental' => __('Type car_rental')];
    }


    public function getNperlistTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['nperlist']) ? $data['nperlist'] : '');
        $list = $this->getNperlistList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getWorkingInsuranceTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['working_insurance']) ? $data['working_insurance'] : '');
        $list = $this->getWorkingInsuranceList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCarLicensetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['car_licensetime']) ? $data['car_licensetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setCarLicensetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function schemecategory()
    {
        return $this->belongsTo('app\admin\model\SchemeCategory', 'category_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function models()
    {
        return $this->belongsTo('app\admin\model\Models', 'models_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}

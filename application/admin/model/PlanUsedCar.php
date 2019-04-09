<?php

namespace app\admin\model;

use think\Model;

class PlanUsedCar extends Model
{
    // 表名
    protected $name = 'plan_used_car';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'statusdata_text',
        'nperlist_text',
        'contrarytodata_text'
    ];
    

    
    public function getStatusdataList()
    {
        return ['0' => __('Statusdata 0'),'1' => __('Statusdata 1'),'2' => __('Statusdata 2')];
    }     

    public function getNperlistList()
    {
        return ['12' => __('Nperlist 12'),'24' => __('Nperlist 24'),'36' => __('Nperlist 36'),'48' => __('Nperlist 48'),'60' => __('Nperlist 60')];
    }     

    public function getContrarytodataList()
    {
        return ['1' => __('Contrarytodata 1'),'2' => __('Contrarytodata 2')];
    }     


    public function getStatusdataTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['statusdata'];
        $list = $this->getStatusdataList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getNperlistTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['nperlist'];
        $list = $this->getNperlistList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getContrarytodataTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['contrarytodata'];
        $list = $this->getContrarytodataList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function models()
    {
        return $this->belongsTo('Models', 'models_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function financialplatform()
    {
        return $this->belongsTo('FinancialPlatform', 'financial_platform_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}

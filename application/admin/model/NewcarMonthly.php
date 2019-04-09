<?php

namespace app\admin\model;

use think\Model;

class NewcarMonthly extends Model
{
    // 表名
    protected $name = 'newcar_monthly';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'monthly_data_text'
    ];
    

    
    public function getMonthlyDataList()
    {
        return ['failure' => __('Monthly_data failure'),'success' => __('Monthly_data success')];
    }     


    public function getMonthlyDataTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['monthly_data']) ? $data['monthly_data'] : '');
        $list = $this->getMonthlyDataList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}

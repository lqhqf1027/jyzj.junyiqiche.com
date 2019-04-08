<?php

namespace app\admin\model;

use think\Model;

class PlanFull extends Model
{
    // 表名
    protected $name = 'plan_full';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'ismenu_text'
    ];
    

    
    public function getIsmenuList()
    {
        return ['1' => __('Ismenu 1')];
    }     


    public function getIsmenuTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['ismenu'];
        $list = $this->getIsmenuList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function models()
    {
        return $this->belongsTo('Models', 'models_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}

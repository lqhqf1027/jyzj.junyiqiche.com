<?php

namespace app\admin\model;

use think\Model;

class Models extends Model
{
    // 表名
    protected $name = 'models';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [

    ];
    public function brand()
    {
        return $this->belongsTo('Brand', 'brand_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function series()
    {
        return $this->belongsTo('Brand', 'series_name', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function model()
    {
        return $this->belongsTo('ModelsDetails', 'model_name', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function planacar()
    {
        return $this->hasOne('PlanAcar','models_id','id',[],'LEFT')->setEagerlyType(0);
    }

}

<?php

namespace addons\cms\model;

use think\Model;

class Models extends Model
{
    // 表名
    protected $name = 'models';
    
    // 自动写入时间戳字段
//    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
//    protected $createTime = 'createtime';
//    protected $updateTime = 'updatetime';
//
//    // 追加属性
//    protected $append = [
//
//    ];

    /**
     * 关联品牌
     * @return \think\model\relation\BelongsTo
     */
    public function brand()
    {
        return $this->belongsTo('Brand', 'brand_id', 'id')->setEagerlyType(0);
    }

    /**
     * 关联新车方案
     * @return \think\model\relation\HasOne
     */
    public function planacar()
    {
        return $this->hasOne('PlanAcar','models_id','id',[],'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联二手车方案
     * @return \think\model\relation\HasOne
     */
    public function secondcarplan()
    {
        return $this->hasOne('SecondcarRentalModelsInfo','models_id','id',[],'LEFT')->setEagerlyType(0);
    }

    public function logistics()
    {
        return $this->hasOne('Logistics','models_id','id',[],'LEFT')->setEagerlyType(0);
    }

}

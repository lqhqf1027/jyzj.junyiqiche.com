<?php

namespace addons\cms\model;

use think\Model;

class SecondcarRentalModelsInfo extends Model
{
    // 表名
    protected $name = 'secondcar_rental_models_info';

//    // 追加属性
    protected $append = [
        'type'
    ];
    


    public function getTypeAttr($value, $data)
    {
        return 'used';
    }


    /**
     * 关联车型
     * @return \think\model\relation\BelongsTo
     */
    public function models()
    {
        return $this->belongsTo('Models', 'models_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联标签
     * @return \think\model\relation\BelongsTo
     */
    public function label()
    {
        return $this->belongsTo('Label','label_id','id',[],'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联门店
     * @return \think\model\relation\BelongsTo
     */
    public function companystore()
    {
        return $this->belongsTo('CompanyStore','store_id','id',[],'LEFT')->setEagerlyType(0);
    }

    public function collections()
    {
        return $this->hasOne('Collection','secondcar_rental_models_info_id','id',[],'LEFT')->setEagerlyType(0);
    }
}

<?php

namespace addons\cms\model;

use think\Model;

class Logistics extends Model
{
    // 表名
    protected $name = 'cms_logistics_project';

//    // 追加属性
    protected $append = [
        'type'

    ];
    public function getTypeAttr($value, $data)
    {        

        return  'logistics';
    }


    /**
     * 关联专题
     * @return \think\model\relation\BelongsTo
     */
    public function subject()
    {
        return $this->belongsTo('Subject', 'subject_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联标签
     * @return \think\model\relation\BelongsTo
     */
    public function label()
    {
        return $this->belongsTo('Label', 'label_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联门店
     * @return \think\model\relation\BelongsTo
     */
    public function store()
    {
        return $this->belongsTo('CompanyStore', 'store_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联收藏表
     * @return \think\model\relation\HasOne
     */
    public function collections()
    {
        return $this->hasOne('Collection','logistics_project_id','id',[],'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联预约表
     * @return \think\model\relation\HasOne
     */
    public function subscribe()
    {
        return $this->hasOne('Subscribe','logistics_project_id','id',[],'LEFT')->setEagerlyType(0);
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
     * 关联门店
     * @return \think\model\relation\BelongsTo
     */
    public function companystore()
    {
        return $this->belongsTo('CompanyStore','store_id','id',[],'LEFT')->setEagerlyType(0);
    }
}

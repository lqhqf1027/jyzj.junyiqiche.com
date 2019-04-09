<?php

namespace addons\cms\model;

use think\Model;

class PlanAcar extends Model
{
    // 表名
    protected $name = 'plan_acar';
 
    // 追加属性
    protected $append = [
        'type',
    ];

    public function getTypeAttr($value, $data)
    {        
         return 'new';
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
     * 关联销售
     * @return \think\model\relation\BelongsTo
     */
    public function admin()
    {
        return $this->belongsTo('Admin', 'sales_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    /**
     *关联金融平台
     * @return \think\model\relation\BelongsTo
     */
    public function financialplatform()
    {
        return $this->belongsTo('FinancialPlatform', 'financial_platform_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    /**
     * 关联方案类型
     * @return \think\model\relation\BelongsTo
     */
    public function schemecategory()
    {
        return $this->belongsTo('Schemecategory','category_id','id',[],'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联专题
     * @return \think\model\relation\BelongsTo
     */
    public function subject()
    {
        return $this->belongsTo('Subject','subject_id','id',[],'LEFT')->setEagerlyType(0);
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


    /**
     * 关联城市
     * @return \think\model\relation\BelongsToMany
     */
    public function city()
    {
        return $this->belongsToMany('Cities','cms_company_store','city_id','plan_acar_id');
    }

    /**
     * 关联品牌
     * @return \think\model\relation\BelongsToMany
     */
    public function brand()
    {
        return $this->belongsTo('Brand', 'brand_id', 'id',[],'JOIN')->setEagerlyType(1);

//        return $this->belongsToMany('Brand','models','brand_id','plan_acar_id');
    }

    /**
     * 关联收藏表
     * @return \think\model\relation\HasOne
     */
    public function collections()
    {
        return $this->hasOne('Collection','plan_acar_id','id',[],'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联预约表
     * @return \think\model\relation\HasOne
     */
    public function subscribe()
    {
        return $this->hasOne('Subscribe','plan_acar_id','id',[],'LEFT')->setEagerlyType(0);
    }

}

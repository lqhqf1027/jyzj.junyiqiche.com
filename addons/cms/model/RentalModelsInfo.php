<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/21
 * Time: 10:56
 */

namespace addons\cms\model;


use think\Model;

class RentalModelsInfo extends Model
{
    protected $name = 'car_rental_models_info';

    protected $append = [
        'type',
    ];

    public function getTypeAttr()
    {
        return 'rent';
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
        return $this->belongsTo('Label', 'label_id', 'id', [], 'LEFT')->setEagerlyType(0);
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
     * 关联收藏表
     * @return \think\model\relation\HasOne
     */
    public function collections()
    {
        return $this->hasOne('Collection','car_rental_models_info_id','id',[],'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联预约表
     * @return \think\model\relation\HasOne
     */
    public function subscribe()
    {
        return $this->hasOne('Subscribe','car_rental_models_info_id','id',[],'LEFT')->setEagerlyType(0);
    }
}
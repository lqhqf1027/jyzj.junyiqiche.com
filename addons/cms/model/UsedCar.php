<?php
/**
 * Created by PhpStorm.
 * User: glen9
 * Date: 2018/11/22
 * Time: 11:31
 */

namespace addons\cms\model;
use think\Model;

class UsedCar extends Model
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
     * 关联标签
     * @return \think\model\relation\BelongsTo
     */
    public function label()
    {
        return $this->belongsTo('Label','label_id','id',[],'LEFT')->setEagerlyType(0);

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
     * 关联收藏表
     * @return \think\model\relation\HasOne
     */
    public function collections()
    {
        return $this->hasOne('Collection','secondcar_rental_models_info_id','id',[],'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联预约表
     * @return \think\model\relation\HasOne
     */
    public function subscribe()
    {
        return $this->hasOne('Subscribe','secondcar_rental_models_info_id','id',[],'LEFT')->setEagerlyType(0);
    }
}

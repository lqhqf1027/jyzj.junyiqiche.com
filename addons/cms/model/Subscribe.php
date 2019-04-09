<?php

namespace addons\cms\model;

use think\Model;

class Subscribe extends Model
{
    // 表名
    protected $name = 'subscribe';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
//
//    // 追加属性
//    protected $append = [
//        'state_text'
//    ];
    

    
    public function getStateList()
    {
        return ['newcustomer' => __('新客户'),'send' => __('已发送')];
    }     


    public function getStateTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['state']) ? $data['state'] : '');
        $list = $this->getStateList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    /**
     * 关联用户
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('User','user_id','id',[],'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联新车方案
     * @return \think\model\relation\BelongsTo
     */
    public function newplan()
    {
        return $this->belongsTo('PlanAcar','plan_acar_id','id',[],'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联二手车方案
     * @return \think\model\relation\BelongsTo
     */
    public function usedplan()
    {
        return $this->belongsTo('SecondcarRentalModelsInfo','secondcar_rental_models_info_id','id',[],'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联新能源方案
     * @return \think\model\relation\BelongsTo
     */
    public function energyplan()
    {
        return $this->belongsTo('Logistics','logistics_project_id','id',[],'LEFT')->setEagerlyType(0);
    }



}

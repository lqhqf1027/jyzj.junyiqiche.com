<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/8/29
 * Time: 14:14
 */

namespace app\admin\model;

use think\Model;

class RentalOrder extends Model
{
// 表名
    protected $name = 'rental_order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    // 追加属性
    protected $append = [
        'genderdata_text',
        'gps_installation_datetime_text',
        'information_audition_datetime_text',
        'Insurance_status_datetime_text',
        'general_manager_datetime_text',
        'delivery_datetime_text'
    ];



    public function getGenderdataList()
    {
        return ['male' => __('Genderdata male'),'female' => __('Genderdata female')];
    }


    public function getGenderdataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['genderdata']) ? $data['genderdata'] : '');
        $list = $this->getGenderdataList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getGpsInstallationDatetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['gps_installation_datetime']) ? $data['gps_installation_datetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getInformationAuditionDatetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['information_audition_datetime']) ? $data['information_audition_datetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getInsuranceStatusDatetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['Insurance_status_datetime']) ? $data['Insurance_status_datetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getGeneralManagerDatetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['general_manager_datetime']) ? $data['general_manager_datetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getDeliveryDatetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['delivery_datetime']) ? $data['delivery_datetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setGpsInstallationDatetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setInformationAuditionDatetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setInsuranceStatusDatetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setGeneralManagerDatetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setDeliveryDatetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    public function sales()
    {
        return $this->belongsTo('Admin','sales_id','id',[],'LEFT')->setEagerlyType(0);
    }

    public function models()
    {
        return $this->belongsTo('Models','models_id','id',[],'LEFT')->setEagerlyType(0);
    }

    public function carrentalmodelsinfo()
    {
        return $this->belongsTo('CarRentalModelsInfo','car_rental_models_info_id','id',[],'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联admin
     * @return \think\model\relation\BelongsTo
     */
    public function admin()
    {
        return $this->belongsTo('Admin','admin_id','id',[],'LEFT')->setEagerlyType(0);
    }

    /**
     * @return \think\model\relation\BelongsTo
     */
//    public function planrental()
//    {
//        return $this->belongsTo('PlanRental','plan_car_rental_name','id',[],'LEFT')->setEagerlyType(0);
//    }

    /**
     * @return \think\model\relation\BelongsTo
     */
    public function customerdownpayment()
    {
        return $this->belongsTo('CustomerDownpayment','customer_downpayment_id','id',[],'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联租车大数据，跟新审批结果 字段
     * @return \think\model\relation\hasOne
     */
    public  function bigdata(){
        return $this->hasOne('BigData','rental_order_id','id')->setEagerlyType(0);
    }
}
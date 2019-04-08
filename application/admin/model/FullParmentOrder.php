<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/8/29
 * Time: 10:27
 */

namespace app\admin\model;

use  think\Model;

class FullParmentOrder extends Model
{

    // 表名
    protected $name = 'full_parment_order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    // 追加属性
    protected $append = [
        'genderdata_text',
        'delivery_datetime_text'
    ];


    public function getGenderdataList()
    {
        return ['male' => __('Genderdata male'), 'female' => __('Genderdata female')];
    }


    public function getGenderdataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['genderdata']) ? $data['genderdata'] : '');
        $list = $this->getGenderdataList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getDeliveryDatetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['delivery_datetime']) ? $data['delivery_datetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setDeliveryDatetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    public function getCustomerSourceList()
    {
        return ['direct_the_guest' => __('直客'), 'turn_to_introduce' => __('转介绍')];
    }


    public function mortgageregistration()
    {
        return $this->belongsTo('MortgageRegistration', 'mortgage_registration_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function models()
    {
        return $this->belongsTo('Models','models_id','id',[],'LEFT')->setEagerlyType(0);
    }

    public function carnewinventory()
    {
        return $this->belongsTo('CarNewInventory','car_new_inventory_id','id',[],'LEFT')->setEagerlyType(0);
    }

    public function sales()
    {
        return $this->belongsTo('Admin','sales_id','id',[],'LEFT')->setEagerlyType(0);
    }

    public function admin()
    {
        return $this->belongsTo('Admin','admin_id','id',[],'LEFT')->setEagerlyType(0);
    }

    public function planfull()
    {
        return $this->belongsTo('PlanFull','plan_plan_full_name','id',[],'LEFT')->setEagerlyType(0);
    }

    /**
     * @return \think\model\relation\BelongsTo
     */
    public function registryregistration()
    {
        return $this->belongsTo('RegistryRegistration','registry_registration_id','id',[],'LEFT')->setEagerlyType(0);
    }

    /**
     * @return \think\model\relation\BelongsTo
     */
    public function customerdownpayment()
    {
        return $this->belongsTo('CustomerDownpayment','customer_downpayment_id','id',[],'LEFT')->setEagerlyType(0);
    }

    /**
     * @return \think\model\relation\BelongsTo
     */
    public function mortgage()
    {
        return $this->belongsTo('Mortgage','mortgage_id','id',[],'LEFT')->setEagerlyType(0);
    }

    /**
     * 新车库存表
     * @return \think\model\relation\BelongsTo
     */
    public function newinventory()
    {
        return $this->belongsTo('CarNewInventory', 'car_new_inventory_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
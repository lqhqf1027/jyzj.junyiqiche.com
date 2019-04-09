<?php

namespace app\admin\model;

use think\Model;

class CarRentalConfirmation extends Model
{
    // 表名
    protected $name = 'car_rental_confirmation';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'gps_installation_datetime_text',
        'information_audition_datetime_text',
        'Insurance_status_datetime_text',
        'finance_datetime_text',
        'general_manager_datetime_text'
    ];
    

    



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


    public function getFinanceDatetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['finance_datetime']) ? $data['finance_datetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getGeneralManagerDatetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['general_manager_datetime']) ? $data['general_manager_datetime'] : '');
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

    protected function setFinanceDatetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setGeneralManagerDatetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}

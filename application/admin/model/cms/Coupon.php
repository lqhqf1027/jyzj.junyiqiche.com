<?php

namespace app\admin\model\cms;

use think\Model;

class Coupon extends Model
{
    // 表名
    protected $name = 'cms_coupon';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'release_datetime_text',
        'validity_datetime_text'
    ];
    

    



    public function getReleaseDatetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['release_datetime']) ? $data['release_datetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getValidityDatetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['validity_datetime']) ? $data['validity_datetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setReleaseDatetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setValidityDatetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}

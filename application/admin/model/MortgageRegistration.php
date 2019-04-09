<?php

namespace app\admin\model;

use think\Model;

class MortgageRegistration extends Model
{
    // 表名
    protected $name = 'mortgage_registration';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'signtime_text'
    ];
    

    



    public function getSigntimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['signtime']) ? $data['signtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setSigntimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}

<?php

namespace app\admin\model;

use think\Model;

class PreviousCustomer extends Model
{
    // 表名
    protected $name = 'previous_customer';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'transfer_time_text',
        'type_text'
    ];
    

    
    public function getTypeList()
    {
        return ['mortgage' => __('Type mortgage'),'full_amount' => __('Type full_amount')];
    }     


    public function getTransferTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['transfer_time']) ? $data['transfer_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getTypeTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setTransferTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}

<?php

namespace app\admin\model\previous;

use think\Model;

class Customer extends Model
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
        'nperlist_text',
        'transfer_time_text',
        'classification_text'
    ];
    

    
    public function getNperlistList()
    {
        return ['12' => __('Nperlist 12'),'24' => __('Nperlist 24'),'36' => __('Nperlist 36'),'48' => __('Nperlist 48'),'60' => __('Nperlist 60')];
    }     

    public function getClassificationList()
    {
        return ['new' => __('New'),'used' => __('Used'),'full' => __('Full')];
    }     


    public function getNperlistTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['nperlist']) ? $data['nperlist'] : '');
        $list = $this->getNperlistList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getTransferTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['transfer_time']) ? $data['transfer_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getClassificationTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['classification']) ? $data['classification'] : '');
        $list = $this->getClassificationList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setTransferTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}

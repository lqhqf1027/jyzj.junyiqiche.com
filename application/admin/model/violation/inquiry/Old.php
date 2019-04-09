<?php

namespace app\admin\model\violation\inquiry;

use think\Model;

class Old extends Model
{
    // 表名
    protected $name = 'violation_inquiry_old';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'peccancy_status_text',
        'final_time_text',
        'import_time_text',
        'status_text'
    ];
    

    
    public function getPeccancyStatusList()
    {
        return ['1' => __('Peccancy_status 1'),'2' => __('Peccancy_status 2')];
    }     

    public function getStatusList()
    {
        return ['1' => __('Status 1'),'2' => __('Status 2'),'3' => __('Status 3')];
    }     


    public function getPeccancyStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['peccancy_status']) ? $data['peccancy_status'] : '');
        $list = $this->getPeccancyStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getFinalTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['final_time']) ? $data['final_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getImportTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['import_time']) ? $data['import_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setFinalTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setImportTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}

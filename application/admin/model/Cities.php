<?php

namespace app\admin\model;

use think\Model;

class Cities extends Model
{
    // 表名
    protected $name = 'cms_cities';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'status_text'
    ];
    

    
    public function getStatusList()
    {
        return ['normal' => __('Normal'),'hidden' => __('Hidden')];
    }     


    public function getStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function subject()
    {
        return $this->hasOne('Subject','city_id','id',[],'LEFT')->setEagerlyType(0);
    }

    public function companystore()
    {
        return $this->hasOne('CompanyStore','city_id','id',[],'LEFT')->setEagerlyType(0);
    }




}

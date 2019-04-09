<?php

namespace addons\cms\model;

use think\Model;

class Cities extends Model
{
    // 表名
    protected $name = 'cms_cities';
    
    // 自动写入时间戳字段

//
//    // 定义时间戳字段名
//    protected $createTime = 'createtime';
//    protected $updateTime = 'updatetime';

//    protected $append = [
//        'status_text'
//    ];
    

    
//    public function getStatusList()
//    {
//        return ['normal' => __('Normal'),'hidden' => __('Hidden')];
//    }
//
//
//    public function getStatusTextAttr($value, $data)
//    {
//        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
//        $list = $this->getStatusList();
//        return isset($list[$value]) ? $list[$value] : '';
//    }

    public function storeList(){
        return $this->hasMany('CompanyStore','city_id','id');
    }


}

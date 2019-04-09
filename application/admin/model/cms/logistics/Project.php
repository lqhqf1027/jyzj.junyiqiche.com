<?php

namespace app\admin\model\cms\logistics;

use think\Model;

class Project extends Model
{
    // 表名
    protected $name = 'cms_logistics_project';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'nperlist_text'
    ];
    

    
    public function getNperlistList()
    {
        return ['12' => __('Nperlist 12'),'24' => __('Nperlist 24'),'36' => __('Nperlist 36'),'48' => __('Nperlist 48'),'60' => __('Nperlist 60')];
    }     


    public function getNperlistTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['nperlist']) ? $data['nperlist'] : '');
        $list = $this->getNperlistList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}

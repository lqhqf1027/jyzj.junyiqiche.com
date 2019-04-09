<?php

namespace app\admin\model;

use think\Model;

class Logistics extends Model
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
        'nperlist_text',
        'acar_status_text'
    ];
    

    
    public function getNperlistList()
    {
        return ['12' => __('Nperlist 12'),'24' => __('Nperlist 24'),'36' => __('Nperlist 36'),'48' => __('Nperlist 48'),'60' => __('Nperlist 60')];
    }     

    public function getAcarStatusList()
    {
        return ['1' => __('Acar_status 1'),'2' => __('Acar_status 2')];
    }     


    public function getNperlistTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['nperlist']) ? $data['nperlist'] : '');
        $list = $this->getNperlistList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getAcarStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['acar_status']) ? $data['acar_status'] : '');
        $list = $this->getAcarStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    //专题
    public function subject()
    {
        return $this->belongsTo('Subject', 'subject_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    //标签
    public function label()
    {
        return $this->belongsTo('Label', 'label_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    //门店
    public function store()
    {
        return $this->belongsTo('CompanyStore', 'store_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    //品牌
    public function models()
    {
        return $this->belongsTo('Models', 'models_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}

<?php

namespace app\admin\model;

use think\Model;

class SecondcarRentalModelsInfo extends Model
{
    // 表名
    protected $name = 'secondcar_rental_models_info';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'shelf_text'
    ];
    

    
    public function getShelfismenuList()
    {
        return ['1' => __('Shelfismenu 1')];
    }     


    public function getShelfTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['shelf'];
        $list = $this->getShelfismenuList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function models()
    {
        return $this->belongsTo('Models', 'models_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    //关联标签
    public function label()
    {
        return $this->belongsTo('Label','label_id','id',[],'LEFT')->setEagerlyType(0);
    }

    //关联门店
    public function companystore()
    {
        return $this->belongsTo('CompanyStore','store_id','id',[],'LEFT')->setEagerlyType(0);
    }
}

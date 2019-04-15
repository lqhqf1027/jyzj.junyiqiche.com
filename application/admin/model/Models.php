<?php

namespace app\admin\model;

use think\Model;


class Models extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'models';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text'
    ];
    

    
    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function brandcate()
    {
        return $this->belongsTo('app\admin\model\Brand', 'brand_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}

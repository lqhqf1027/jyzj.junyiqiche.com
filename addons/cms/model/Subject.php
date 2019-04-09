<?php

namespace addons\cms\model;

use think\Model;

class Subject extends Model
{
    // 表名
    protected $name = 'cms_subject';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [

    ];

    public function getPlanIdAttr($value)
    {
        return json_decode($value,true);
    }
    

    







}

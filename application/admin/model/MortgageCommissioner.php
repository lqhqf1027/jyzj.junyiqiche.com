<?php

namespace app\admin\model;

use think\Model;

class MortgageCommissioner extends Model
{
    // 表名
    protected $name = 'mortgage_commissioner';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'platformtype_text'
    ];
    

    
    public function getPlatformtypeList()
    {
        return ['new' => __('Platformtype new'),'other' => __('Platformtype other')];
    }     


    public function getPlatformtypeTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['platformtype']) ? $data['platformtype'] : '');
        $list = $this->getPlatformtypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}

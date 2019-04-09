<?php

namespace app\admin\model;

use think\Model;

class WechatUser extends Model
{
    // 表名
    protected $name = 'wechat_user';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'subscribe_text',
        'sex_text',
        'subscribe_time_text'
    ];
    

    
    public function getSubscribeList()
    {
        return ['0' => __('Subscribe 0'),'1' => __('Subscribe 1')];
    }     

    public function getSexList()
    {
        return ['0' => __('Sex 0'),'1' => __('Sex 1'),'2' => __('Sex 2')];
    }     


    public function getSubscribeTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['subscribe'];
        $list = $this->getSubscribeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getSexTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['sex'];
        $list = $this->getSexList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getSubscribeTimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['subscribe_time'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setSubscribeTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}

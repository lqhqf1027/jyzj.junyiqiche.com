<?php

namespace app\common\model;

use think\Model;

class CustomerResource extends Model
{
    // 表名
    protected $name = 'customer_resource';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'genderdata_text',
        'distributinternaltime_text',
        'distributsaletime_text',
        'feedbacktime_text',
        'customerlevel_text',
        'followuptime_text'
    ];
    

    
    public function getGenderdataList()
    {
        return ['male' => __('Genderdata male'),'female' => __('Genderdata female')];
    }     

    public function getCustomerlevelList()
    {
        return ['relation' => __('Customerlevel relation'),'intention' => __('Customerlevel intention'),'nointention' => __('Customerlevel nointention'),'giveup' => __('Customerlevel giveup')];
    }     


    public function getGenderdataTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['genderdata'];
        $list = $this->getGenderdataList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getDistributinternaltimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['distributinternaltime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getDistributsaletimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['distributsaletime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getFeedbacktimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['feedbacktime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCustomerlevelTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['customerlevel'];
        $list = $this->getCustomerlevelList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getFollowuptimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['followuptime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setDistributinternaltimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setDistributsaletimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setFeedbacktimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setFollowuptimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


    public function platform()
    {
        return $this->belongsTo('Platform', 'platform_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}

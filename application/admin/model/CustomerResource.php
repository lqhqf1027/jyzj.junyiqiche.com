<?php

namespace app\admin\model;

use think\Model;


class CustomerResource extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'customer_resource';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'genderdata_text',
        'distributinternaltime_text',
        'distributsaletime_text',
        'feedbacktime_text',
        'customerlevel_text',
        'lift_state_text',
        'invalidtime_text',
        'giveup_time_text',
        'status_text'
    ];
    

    
    public function getGenderdataList()
    {
        return ['male' => __('Genderdata male'), 'female' => __('Genderdata female')];
    }

    public function getCustomerlevelList()
    {
        return ['relation' => __('Customerlevel relation'), 'intention' => __('Customerlevel intention'), 'nointention' => __('Customerlevel nointention'), 'giveup' => __('Customerlevel giveup')];
    }

    public function getLiftStateList()
    {
        return ['examine' => __('Examine'), 'wind_notpass' => __('Wind_notpass'), 'preparation' => __('Preparation'), 'lift_car' => __('Lift_car')];
    }

    public function getStatusList()
    {
        return ['今日头条' => __('今日头条'), '百度' => __('百度'), '58同城' => __('58同城'), '抖音' => __('抖音')];
    }

    public function getPlatformList()
    {
        return ['转介绍' => __('转介绍'), '自己邀约' => __('自己邀约'), '其他' => __('其他')];
    }


    public function getGenderdataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['genderdata']) ? $data['genderdata'] : '');
        $list = $this->getGenderdataList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getDistributinternaltimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['distributinternaltime']) ? $data['distributinternaltime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getDistributsaletimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['distributsaletime']) ? $data['distributsaletime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getFeedbacktimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['feedbacktime']) ? $data['feedbacktime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCustomerlevelTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['customerlevel']) ? $data['customerlevel'] : '');
        $list = $this->getCustomerlevelList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getLiftStateTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['lift_state']) ? $data['lift_state'] : '');
        $list = $this->getLiftStateList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getInvalidtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['invalidtime']) ? $data['invalidtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getGiveupTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['giveup_time']) ? $data['giveup_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setDistributinternaltimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setDistributsaletimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setFeedbacktimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setInvalidtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setGiveupTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function backoffice()
    {
        return $this->belongsTo('app\admin\model\Admin', 'backoffice_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function admin()
    {
        return $this->belongsTo('app\admin\model\Admin', 'sales_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}

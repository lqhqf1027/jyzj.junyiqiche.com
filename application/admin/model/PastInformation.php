<?php

namespace app\admin\model;

use think\Model;

class PastInformation extends Model
{
    // 表名
    protected $name = 'past_information';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'signtime_text',
        'wealthytime_text',
        'tickettime_text',
        'paymenttime_text',
        'buytime_text',
        'transfertime_text',
        'renttime_text',
        'backtime_text',
        'types_text'
    ];
    

    
    public function getTypesList()
    {
        return ['full' => __('Types full'),'rent' => __('Types rent'),'mortgage' => __('Types mortgage')];
    }     


    public function getSigntimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['signtime']) ? $data['signtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getWealthytimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['wealthytime']) ? $data['wealthytime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getTickettimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['tickettime']) ? $data['tickettime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getPaymenttimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['paymenttime']) ? $data['paymenttime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getBuytimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['buytime']) ? $data['buytime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getTransfertimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['transfertime']) ? $data['transfertime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getRenttimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['renttime']) ? $data['renttime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getBacktimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['backtime']) ? $data['backtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getTypesTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['types']) ? $data['types'] : '');
        $list = $this->getTypesList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setSigntimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setWealthytimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setTickettimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setPaymenttimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setBuytimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setTransfertimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setRenttimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setBacktimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}

<?php

namespace app\admin\model;

use think\Model;

class Prizerecord extends Model
{
    // 表名
    protected $name = 'cms_prize_record';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'awardtime_text'
    ];
    

    



    public function getAwardtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['awardtime']) ? $data['awardtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setAwardtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


    public function prize()
    {
        return $this->belongsTo('Prize', 'prize_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}

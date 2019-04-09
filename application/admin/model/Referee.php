<?php

namespace app\admin\model;

use think\Model;

class Referee extends Model
{
    // 表名
    protected $name = 'referee';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'make_moneytime_text',
        'request_fundstime_text'
    ];
    

    



    public function getMakeMoneytimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['make_moneytime']) ? $data['make_moneytime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getRequestFundstimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['request_fundstime']) ? $data['request_fundstime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setMakeMoneytimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setRequestFundstimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    /**
     * 关联车型
     * @return \think\model\relation\BelongsTo
     */
    public  function models(){


        return $this->belongsTo('Models', 'models_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    /**查询销售id的昵称
     * @return \think\model\relation\BelongsTo
     */
    public function admin()
    {
        return $this->belongsTo('Admin', 'admin_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


}

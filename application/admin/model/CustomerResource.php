<?php

namespace app\admin\model;

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
        'genderdata_text'
    ];
    

    
    public function getGenderdataList()
    {
        return ['male' => __('Genderdata male'),'female' => __('Genderdata female')];
    }


    public function getCustomerlevelList()
    {
        return ['relation' => __('Relation'),'intention' => __('Intention'),'nointention' => __('Nointention')];
    }



    public function getGenderdataTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['genderdata'];
        $list = $this->getGenderdataList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function getCustomerlevelTextAttr($value, $data)
    {
        $value = $value ? $value : $data['customerlevel'];
        $list = $this->getCustomerlevelList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    /**
     * Notes:关联推广平台
     * User: glen9
     * Date: 2018/9/9
     * Time: 9:57
     * @return \think\model\relation\BelongsTo
     */
    public function platform()
    {
        return $this->belongsTo('Platform', 'platform_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    /**
     * Notes:关联销售
     * User: glen9
     * Date: 2018/9/9
     * Time: 10:05
     * @return \think\model\relation\BelongsTo
     */
    public function admin()
    {
        return $this->belongsTo('Admin', 'sales_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联内勤
     * @return \think\model\relation\BelongsTo
     */
    public function backoffice()
    {
        return $this->belongsTo('Admin','backoffice_id','id',[],'LEFT')->setEagerlyType(0);
    }
}

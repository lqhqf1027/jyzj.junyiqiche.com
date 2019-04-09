<?php

namespace app\admin\model;

use think\Model;

class PlanAcar extends Model
{
    // 表名
    protected $name = 'plan_acar';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'nperlist_text',
        'ismenu_text',

        'working_insurance_text',
        'status_text'

    ];

    public function getNperlistList()
    {
        return ['12' => __('Nperlist 12'), '24' => __('Nperlist 24'), '36' => __('Nperlist 36'), '48' => __('Nperlist 48'), '60' => __('Nperlist 60')];
    }

    public function getIsmenuList()
    {
        return ['1' => __('Ismenu 1')];
    }

    public function getWorkingInsuranceList()
    {
        return ['yes' => __('Working_insurance yes'), 'no' => __('Working_insurance no')];
    }

    public function getStatusList()
    {
        return ['1' => __('Status 1'), '2' => __('Status 2')];

    }

    public function getNperlistTextAttr($value, $data)
    {

        $value = $value ? $value : $data['nperlist'];

        $value = $value ? $value : (isset($data['nperlist']) ? $data['nperlist'] : '');

        $list = $this->getNperlistList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsmenuTextAttr($value, $data)
    {
        $value = $value ? $value : $data['ismenu'];
        $list = $this->getIsmenuList();
    }

    public
    function getWorkingInsuranceTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['working_insurance']) ? $data['working_insurance'] : '');
        $list = $this->getWorkingInsuranceList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public
    function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    /**
     * 关联车型
     * @return \think\model\relation\BelongsTo
     */
    public
    function models()
    {
        return $this->belongsTo('Models', 'models_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联销售
     * @return \think\model\relation\BelongsTo
     */
    public
    function admin()
    {
        return $this->belongsTo('Admin', 'sales_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    //关联专题
    public
    function subject()
    {
        return $this->belongsTo('Subject', 'subject_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    //关联标签
    public
    function label()
    {
        return $this->belongsTo('Label', 'label_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    //关联门店
    public
    function companystore()
    {
        return $this->belongsTo('CompanyStore', 'store_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    //关联门店
    public
    function city()
    {
        return $this->belongsToMany('Cities', 'cms_company_store', 'city_id', 'plan_acar_id');
    }

    public
    function brand()
    {
        return $this->belongsToMany('Brand', 'models', 'brand_id', 'plan_acar_id');
    }

    /* 关联方案类型
    * @return \think\model\relation\BelongsTo
    */
    public
    function schemecategory()
    {
        return $this->belongsTo('SchemeCategory', 'category_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


}

<?php

namespace app\admin\model;

use think\Model;

class BrandCate extends Model
{
    // 表名
    protected $name = 'brand_cate';

    public function models()
    {
        return $this->hasMany('Models', 'brand_id', 'id');
    }
    
}

<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/9/15
 * Time: 10:31
 */

namespace app\admin\model;


use think\Model;

class PlanRental extends Model
{
// 表名
    protected $name = 'plan_rental';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
}
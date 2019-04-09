<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/8/30
 * Time: 9:49
 */

namespace app\admin\model;


use think\Model;

class Mortgage extends Model
{
    // 表名
    protected $name = 'mortgage';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;




}
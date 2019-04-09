<?php
/**
 * Created by PhpStorm.
 * User: glen9
 * Date: 2018/9/27
 * Time: 16:49
 */

namespace app\common\model;
use think\Model;

class Bigdata extends Model
{
    // 表名
    protected $name = 'big_data';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
}
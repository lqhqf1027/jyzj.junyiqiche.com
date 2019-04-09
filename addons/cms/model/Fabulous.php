<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/11/22
 * Time: 17:20
 */

namespace addons\cms\model;


use think\Model;

class Fabulous extends Model
{
    // 表名
    protected $name = 'cms_fabulous';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'fabuloustime';

    protected $updateTime = false;
}
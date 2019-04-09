<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/11/22
 * Time: 17:20
 */

namespace addons\cms\model;


use think\Model;

class Collection extends Model
{
    // 表名
    protected $name = 'cms_collection';
// 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'collectiontime';

    protected $updateTime = false;
}
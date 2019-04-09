<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/11/28
 * Time: 10:30
 */

namespace addons\cms\model;


use think\Model;

class UserSign extends Model
{
    protected $name = 'cms_user_sign';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    protected $createTime = false;
    protected $updateTime = 'lastModifyTime';
}
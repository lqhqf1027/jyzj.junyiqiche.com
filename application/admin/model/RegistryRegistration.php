<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/8/29
 * Time: 17:34
 */

namespace app\admin\model;


use think\Model;

class RegistryRegistration extends Model
{
    protected $name = 'registry_registration';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;



}
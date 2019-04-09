<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/8/31
 * Time: 11:09
 */

namespace app\admin\model;


use think\Model;

class FinancialAccount extends Model
{
    // 表名
    protected $name = 'financial_account';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

    // 追加属性
    protected $append = [

    ];





}
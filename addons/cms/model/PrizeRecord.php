<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/20
 * Time: 16:52
 */

namespace addons\cms\model;


use think\Model;

class PrizeRecord extends Model
{
    protected $name = 'cms_prize_record';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'awardtime';
    protected $updateTime = false;

    public  function prizeData(){
        return $this->belongsTo('Prize','prize_id','id');
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/20
 * Time: 16:11
 */

namespace addons\cms\model;


use think\Model;

class Prize extends Model
{
   protected $name = 'cms_prize';
    public  function cityName(){
        return $this->belongsTo('Cities','city_id','id')->field('cities_name,id');
    }
}
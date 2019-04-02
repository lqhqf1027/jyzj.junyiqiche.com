<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/2
 * Time: 14:30
 */

namespace addons\cms\model;


use think\Model;

class User extends Model
{
      protected $name = 'user';

    public function pastInformation()
    {
        return $this->hasOne('PastInformation', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
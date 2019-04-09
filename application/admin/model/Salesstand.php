<?php

namespace app\admin\model;

use think\Model;
use think\Db;
class Salesstand extends MOdel{
    

    public  static function getOrderCount($table,$review_the_data,$admin_id=null,$delivery_datetime=null)
    {

        return Db::name($table)->where(function($query) use ($review_the_data,$admin_id,$delivery_datetime){
            //历史成交量
            if($review_the_data && !$admin_id && !$delivery_datetime){
                $query->where(['review_the_data'=>$review_the_data]);
            }
            //历史成交量----部门
            if($review_the_data && $admin_id && !$delivery_datetime){
                $query->where(['review_the_data'=> $review_the_data, 'admin_id' =>['in',$admin_id]]);
            }
            // 本月成交量----部门一个月
            if($review_the_data && $admin_id && $delivery_datetime){
                $query->where(['review_the_data'=> $review_the_data, 'admin_id' => ['in',$admin_id], 'delivery_datetime' =>['between',$delivery_datetime]]);
            }

        })->count();
    }

    //查询部门
    public  static function getAdmin($table,$group_id)
    {

        return Db::name($table)->where(function($query) use ($group_id){
          
            $query->where(['group_id'=> $group_id]);
    

        })->select();
    }

   
}
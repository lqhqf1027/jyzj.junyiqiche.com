<?php

namespace app\admin\model;

use think\Model;
use think\Db;
class Dashboard extends MOdel{
    

    public  static function getOrderCount($table,$review_the_data,$customer_source=null,$delivery_datetime=null,$createtime=null)
    {

        return Db::name($table)->where(function($query) use ($review_the_data,$customer_source,$delivery_datetime,$createtime){
            //历史成交数
            if($review_the_data && !$customer_source && !$delivery_datetime && !$createtime){
                $query->where(['review_the_data'=>$review_the_data]);
            }
            //本月成交数
            if($review_the_data && !$customer_source && $delivery_datetime && !$createtime){
                $query->where(['review_the_data'=> $review_the_data, 'delivery_datetime' =>['between',$delivery_datetime]]);
            }
            // 本月订车数
            if($review_the_data && !$customer_source && !$delivery_datetime && $createtime){
                $query->where(['review_the_data'=>['NEQ', $review_the_data],'createtime' =>['between',$createtime]]);
            }
            // 直客和转介绍成交数
            if($review_the_data && $customer_source && $delivery_datetime && !$createtime){
                $query->where(['review_the_data' => $review_the_data, 'customer_source' => $customer_source, 'delivery_datetime' =>['between',$delivery_datetime]]);
            }

        })->count();
    }

    //违章查询
    public  static function getViolationCount($table,$peccancy_status,$total_deduction=null)
    {

        return Db::name($table)->where(function($query) use ($table,$peccancy_status,$total_deduction){

            //违章总车辆
            if($peccancy_status && !$total_deduction){
                $query->where(['peccancy_status' => $peccancy_status]);
            }
            //违章扣分查询
            if($peccancy_status && $total_deduction){
                $query->where(['peccancy_status' => $peccancy_status, 'total_deduction' => ['between', $total_deduction]]);
            }
        
            
        })->count();
    }


    //交强险需续保查询或已过期
    public static function getStrongCount($table,$status)
    {

        return Db::name($table)->where(function($query) use ($table,$status){
           
            $query->where(['strong_status' => $status]);
            
        })->count();


    }

    //商业险需续保查询或已过期
    public static function getBusinessCount($table,$status)
    {

        return Db::name($table)->where(function($query) use ($table,$status){

            $query->where(['business_status' => $status]);

        })->count();


    }

    //交强险和商业险需年检查询或已过期
    public static function getYearCount($table,$year_status)
    {

        return Db::name($table)->where(function($query) use ($table,$year_status){
           
            $query->where(['year_status' => $year_status]);
            
        })->count();
    }

    //车辆情况
    public  static function getCarCount($table,$status_data,$shelfismenu=null)
    {

        return Db::name($table)->where(function($query) use ($table,$status_data,$shelfismenu){
           
            //租车已租车辆
            if($status_data && !$shelfismenu){
                $query->where(['status_data' => $status_data]);
            }
            //租车待租车辆或二手车可卖车辆
            if($status_data && $shelfismenu){
                $query->where(['status_data' => $status_data, 'shelfismenu' => $shelfismenu]);
            }
            
        })->count();
    }


}
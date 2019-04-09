<?php

namespace addons\cms\model;

use think\Model;

class CompanyStore extends Model
{
    // 表名
    protected $name = 'cms_company_store';


    /**
     * 关联城市
     * @return \think\model\relation\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo('Cities', 'city_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联新车方案
     * @return \think\model\relation\HasOne
     */
    public function planacar()
    {
        return $this->hasOne('PlanAcar', 'store_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联二手车方案
     * @return \think\model\relation\HasOne
     */
    public function secondcarinfo()
    {
        return $this->hasOne('UsedCar', 'store_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    /**
     *  关联新能源方案
     * @return \think\model\relation\HasOne
     */

    public function logistics()
    {
        return $this->hasOne('Logistics', 'store_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    /**
     * 统计门店下新车所有可卖车型个数
     * @return \think\model\relation\HasMany
     */
    public function planacarCount()
    {

        return $this->hasMany('PlanAcar', 'store_id', 'id')
            ->field('id,store_id,label_id,monthly,payment,weigh,models_main_images,models_id');
    }

    /**
     * 首页新车方案
     * @return \think\model\relation\HasMany
     */
    public function planacarIndex()
    {

        return $this->hasMany('PlanAcar', 'store_id', 'id')
            ->field('id,store_id,monthly,payment,models_main_images,recommendismenu,specialismenu,specialimages,popularity,ismenu');
    }

    /**
     * 统计门店下二手车所有可卖车型个数
     * @return \think\model\relation\HasMany
     */
    public function usedcarCount()
    {
        return $this->hasMany('UsedCar', 'store_id', 'id')
            ->field('id,store_id,kilometres,newpayment,models_main_images,car_licensedate,shelfismenu,popularity,totalprices,models_id');
    }

    /**
     * 统计门店下新能源车所有可卖车型个数
     * @return \think\model\relation\HasMany
     */
    public function logisticsCount()
    {
        return $this->hasMany('Logistics', 'store_id', 'id')->field('id,store_id,
        payment,monthly,models_main_images,popularity,ismenu,models_id');
    }

    public function rentalmodelsinfo()
    {
        return $this->hasMany('RentalModelsInfo', 'store_id', 'id')->field('id,store_id,
        shelfismenu,modelsimages,models_main_images,manysixmonths');
    }

    public static function getCarList($store_id)
    {
        return self::with([
            ['planacarCount', 'usedcarCount', 'logisticsCount']
        ])->select(['store_id' => $store_id]);

    }


    /**
     * 查询门店下有多少张优惠券
     * @param $store_id 门店Id
     * @param $user_id 用户ID
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */


    public static function getLogistics($store_id, $user_id)
    {
        $isLogic = collection(Coupon::all(function ($q) use ($user_id, $store_id) {
            $q->field('id,display_diagramimages,user_id,limit_collar')
                ->where([
                    'store_ids' => ['like', '%,' . $store_id . ',%'],
//                    'user_id' => ['like', '%,' . $user_id . ',%'],
                    'ismenu' => 1,//正常上架状态
//                    'release_datetime' => ['GT', time()],//领用截至日期小于当前时间
                    'circulation' => ['GT', 0], // 发放总量大于0
                    'remaining_amount' => ['GT', 0]
                ])
            ->where('release_datetime > :time or release_datetime is null',['time'=>time()]);
        }))->toArray();
//return $isLogic;
        foreach ($isLogic as $key => $value) {

                   $isLogic[$key]['user_id'] = array_filter(explode(',', $value['user_id'])); //转换数组并去除空值


            //查询每人限量*张
                   if (!empty($value['limit_collar'])) {  //非空即为不限量,有具体的领用张数

//                       return array_count_values($isLogic[$key]['user_id'])[$user_id];
                       //array_count_values 计算某个值出现在数组中的次数
                       //如果当前用户领用的券大于等于限领的优惠券张数 ，返回空数组，不可再领用
//                       return array_count_values($isLogic[$key]['user_id'])[$user_id];
                       if (array_count_values($isLogic[$key]['user_id'])[$user_id] >= $value['limit_collar']){
                           unset($isLogic[$key]);
                       }


                   }
           }
               return $isLogic?array_values($isLogic):'';

    }


}


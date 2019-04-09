<?php
/**
 * Created by PhpStorm.
 * User: glen9
 * Date: 2018/11/20
 * Time: 16:16
 */

namespace addons\cms\controller\wxapp;

use addons\cms\model\Coupon;
use think\Cache;
use think\console\command\make\Model;
use think\Db;
use think\Config;
use addons\cms\model\CompanyStore as companyStoreModel;
use addons\cms\model\Cities as citiesModel;
use addons\cms\model\Config as configModel;
use addons\cms\model\Models as modelsModel;
use addons\cms\model\Brand as brandModel;
use think\helper\Time;

class Store extends Base
{


    protected $noNeedLogin = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 门店首页展示
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function store_show()
    {
        //组装门店首页静态展示图
        $new = [];
        $new['store_layout'] = Config::get('upload')['cdnurl'] . configModel::get(['name' => 'company'])->value;
        $new['cdn_url'] = Config::get('upload')['cdnurl'];
        //组装城市首字母
        $data = collection(citiesModel::field('id,cities_name,province_letter')->with(
            [
                'storeList' => function ($q) {
                    $q->where(['statuss' => 'normal']);

                },

            ]
        )->where(['status' => 'normal', 'pid' => ['neq', 0]])->select())->toArray();
        $firstCity = [];

        foreach ($data as $key => $value) {

            if ($value['store_list']) {
                foreach ($data[$key]['store_list'] as $k => $v) {
                    $data[$key]['store_list'][$k]['planacar_count_count'] = $this->countPlan($value['id'], $v['id'], 'plan_acar');
                    $data[$key]['store_list'][$k]['usedcar_count_count'] = $this->countPlan($value['id'], $v['id'], 'secondcar_rental_models_info');
                    $data[$key]['store_list'][$k]['logistics_count_count'] = $this->countPlan($value['id'], $v['id'], 'cms_logistics_project');


                }
            }

        }
        foreach ($data as $key => $value) {

            $arrList = [
                'id' => $value['id'],
                'province_letter' => $value['province_letter'],
                'cities_name' => $value['cities_name'],
                'store_list' => $value['store_list']
            ];
            if ($value['province_letter'] == 'C') {
                $firstCity['C'] = [$arrList];
                continue;
            }

            $new['list'][$value['province_letter']][] = $arrList;

        }

        $new['list'] = array_merge($firstCity, $new['list']);
        $this->success('查询成功', $new);
    }

    /**
     * 统计方案根据车型去重数量
     * @param $city_id
     * @param $store_id
     * @param $planName
     * @return int|string
     * @throws \think\Exception
     */
    public function countPlan($city_id, $store_id, $planName)
    {
        $check = $planName == 'secondcar_rental_models_info' ? 'c.shelfismenu' : 'c.ismenu';
        return $num = Db::name('cms_cities')
            ->alias('a')
            ->join('cms_company_store b', 'a.id = b.city_id')
            ->join($planName . ' c', 'b.id = c.store_id')
            ->where([
                'a.id' => $city_id,
                'b.id' => $store_id,
                $check => 1,
            ])
            ->count('distinct c.models_id');
    }

    /**
     * 门店详情
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function store_details()
    {
        //获取品牌缓存；
//        Cache::pull('BRAND_CACHE');
//        die;
//        $cacheBrand = Cache::get('BRAND_CACHE') ? Cache::get('BRAND_CACHE') : cache::set('BRAND_CACHE', self::matchBrand());
        $store_id = $this->request->post('store_id');//门店id
        $user_id = $this->request->post('user_id');//用户id
        $cartype = $this->request->post('cartype');//用户id
//        pr($store_id);die;
        if (!$store_id || !$user_id) {
            $this->error('参数错误或缺失参数,请求失败', 'error');
        }
        //获取门店下是否有优惠券
        $isLogic = companyStoreModel::getLogistics($store_id, $user_id);
        /*foreach ($isLogic as $key => $value) {
            $isLogic[$key]['user_id'] = array_filter(explode(',', $value['user_id'])); //转换数组并去除空值
            //查询每人限量*张
            if (!empty($value['limit_collar'])) {  //非空即为不限量,有具体的领用张数
                //array_count_values 计算某个值出现在数组中的次数
                //如果当前用户领用的券大于等于限领的优惠券张数 ，返回空数组，不可再领用
                return array_count_values($isLogic[$key]['user_id'])[$user_id];
                if (array_count_values($isLogic[$key]['user_id'])[$user_id] >= $value['limit_collar']) return '';
                else continue;

            }
        }*/

        //门店信息
        $store_info = companyStoreModel::find($store_id)->hidden(['createtime', 'updatetime', 'status', 'plan_acar_id', 'statuss', 'store_qrcode']);
        $store_info['store_img'] = !empty($store_info['store_img']) ? explode(',', $store_info['store_img']) : ''; //转换图片为数组

        $result['info'] = $store_info;
        $result['logic'] = $isLogic;

        $plans = $type_name = null;
        switch ($cartype) {
            case 'new':
                $plans = Share::getVariousTypePlan('', true, 'planacarIndex', 'new', $store_id);
                $type_name = '新车';
                break;
            case 'used':
                $plans = Share::getVariousTypePlan('', false, 'usedcarCount', 'used', $store_id);
                $type_name = '二手车';
                break;
            case 'logistics':
                $plans = Share::getVariousTypePlan('', true, 'logisticsCount', 'logistics', $store_id);
                $type_name = '新能源车';
                break;
            case 'rent':
                $plans = Share::getVariousTypePlan('', false, 'rentalmodelsinfo', 'rent', $store_id);
                $type_name = '租车';
                break;
            default:
                $this->error('cartype参数错误');
        }

        $result['plans'] = ['type' => $cartype, 'car_type_name' => $type_name, 'carList' => $plans];


        $this->success('请求成功', $result);
    }

    /**
     * 领取优惠券接口
     */
    public function receiveCoupons()
    {
        $user_id = $this->request->post('user_id');
        $coupon_id = $this->request->post('coupon_id');

        if (!$coupon_id || !$user_id) {
            $this->error('参数错误或缺失参数,请求失败', 'error');
        }

        $coupon_received = Coupon::where([
            'id' => $coupon_id,
            'remaining_amount' => ['GT', 0],
//            'release_datetime' =>['GT',time()],
            'ismenu' => 1
        ])->where('release_datetime > :time or release_datetime is null', ['time' => time()])
            ->field('user_id,limit_collar,remaining_amount')
            ->find();
//$this->success($coupon_received);
        if (!$coupon_received) {
            $this->error('优惠券已超过领取截止日期或已发放完了');
        }

        $user_id_arr = array_count_values(array_filter(explode(',', $coupon_received['user_id'])));

        if ($user_id_arr[$user_id] && $user_id_arr[$user_id] >= $coupon_received['limit_collar']) {
            $this->error('该优惠券您只能领取:' . $coupon_received['limit_collar'] . '份', $coupon_received['limit_collar']);
        }

        $res = Coupon::update([
            'id' => $coupon_id,
            'user_id' => $coupon_received['user_id'] ? $coupon_received['user_id'] . $user_id . ',' : ',' . $user_id . ',',
            'remaining_amount' => intval($coupon_received['remaining_amount']) - 1
        ]);

        $res ? $this->success('领取优惠券成功') : $this->error('领取优惠券失败');
    }


}
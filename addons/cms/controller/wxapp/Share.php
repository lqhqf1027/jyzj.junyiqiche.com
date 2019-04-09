<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/11/20
 * Time: 16:09
 */

namespace addons\cms\controller\wxapp;

use addons\cms\model\Brand;
use addons\cms\model\CompanyStore;
use addons\cms\model\Logistics;
use addons\cms\model\RentalModelsInfo;
use think\Cache;
use think\Db;
use think\Config;
use addons\cms\model\Models;
use addons\cms\model\Cities;
use addons\cms\model\Collection;
use addons\cms\model\Fabulous;
use addons\cms\model\Subscribe;
use addons\cms\model\PlanAcar;
use app\common\model\Addon;
use app\common\model\User;
use addons\cms\model\SecondcarRentalModelsInfo;
use fast\Http;
use GuzzleHttp\Client;
use think\Exception;

class Share extends Base
{
    protected $noNeedLogin = '*';
    /**
     * 云之讯短信发送模板
     * @var array
     */
    protected static $Ucpass = [
        'accountsid' => 'ffc7d537e8eb86b6ffa3fab06c77fc02',
        'token' => '894cfaaf869767dce526a6eba54ffe52',
        'appid' => '33553da944fb487089dadb16a37c53cc',
        'templateid' => '405684',
    ];

    /**
     * 方案详情接口
     */
    public function plan_details()
    {
        $plan_id = $this->request->post('plan_id');                   //参数：方案ID
        $user_id = $this->request->post('user_id');                   //参数：用户ID
        $cartype = $this->request->post('cartype');                   //车辆类型

        if (!$plan_id || !$user_id || !$cartype) {
            $this->error('缺少参数,请求失败', 'error');
        }
        $data = null;
        switch ($cartype) {
            case 'new':
                $data = $this->newcar_details($plan_id, $user_id, $cartype);
                break;
            case 'used':
                $data = $this->used_details($plan_id, $user_id, $cartype);
                break;
            case 'logistics':
                $data = $this->logistics_details($plan_id, $user_id, $cartype);
                break;
            case 'rent':
                $data = $this->rent_details($plan_id, $user_id, $cartype);
                break;
            default:
                $this->error('参数错误', '');
        }

        $this->success('请求成功', $data);

    }


    /**
     * 省份-城市接口
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cityList()
    {
//        if (Cache::get('cityList')) {
//            $this->success('请求成功', Cache::get('cityList'));
//        }

        $province = self::getCityList();
//        Cache::set('cityList', $province);

        if ($province) {
            $this->success('请求成功', $province);
        } else {
            $this->error();
        }

    }

    /**
     * 模糊搜索城市接口
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function searchCity()
    {
        //搜索栏内容
        $cities_name = $this->request->post('cities_name');

        if ($cities_name == '') {
            $this->success('请求成功', ['searchCityList' => []]);
        }

        //获取搜索的数据
        $searchCityList = Cities::field('id,cities_name')
            ->where([
                'status' => 'normal',
                'pid' => ['neq', 'null'],
                'cities_name' => ['like', '%' . $cities_name . '%']
            ])
            ->select();

        $this->success('请求成功', ['searchCityList' => $searchCityList]);


    }

    /**
     * 分享接口
     * @throws \think\Exception
     */
    public function shareInterface()
    {
        $user_id = $this->request->post('user_id');

        if (!$user_id) {
            $this->error('缺少参数,请求失败', 'error');
        }

        $shareScore = intval(json_decode(self::ConfigData(['group' => 'integral'])['value'], true)['share']);

        $data = [
            'type' => 'share',
            'score' => $shareScore,
            'user_id' => $user_id
        ];

        $res = self::integral($user_id, $shareScore);

        if ($res) {
            Fabulous::create($data) ? $this->success('分享成功,积分+:' . $shareScore, $shareScore) : $this->error();

        } else {
            $this->error();
        }

    }

    /**
     * 点击点赞接口
     */
    public function fabulousInterface()
    {
        $user_id = $this->request->post('user_id');
        $plan_id = $this->request->post('plan_id');
        $cartype = $this->request->post('cartype');

        if (!$user_id || !$plan_id || !$cartype) {
            $this->error('缺少参数,请求失败', 'error');
        }
        $fabulousScore = intval(json_decode(self::ConfigData(['group' => 'integral'])['value'], true)['fabulous']);

        $res = $this->getFabulousCollection($user_id, $plan_id, $cartype, 'cms_fabulous', false, $fabulousScore);

        switch ($res['errorCode']) {
            case '1':
                $this->error('cartype参数错误');
                break;
            case '2':
                $this->error('点赞失败');
                break;
            case '0':
                break;
        }

        $tables = null;
        switch ($cartype) {
            case 'new':
                $tables = new PlanAcar();
                break;
            case 'used':
                $tables = new SecondcarRentalModelsInfo();
                break;
            case 'logistics':
                $tables = new Logistics();
                break;
            case 'rent':
                break;
            default:
                $this->error('cartype参数错误');
        }

        //人气值加1000
        if ($cartype != 'rent') {
            $tables->where('id', $plan_id)->setInc('popularity', 1000);
        }

        $integral = self::integral($user_id, $fabulousScore);

        $integral ? $this->success('点赞成功,积分+:' . $integral, $integral) : $this->error('添加积分失败');

    }


    /**
     * 点击收藏接口
     */
    public function collectionInterface()
    {
        //用户ID
        $user_id = $this->request->post('user_id');
        //方案ID
        $plan_id = $this->request->post('plan_id');
        //车辆类型
        $cartype = $this->request->post('cartype');
        //是否删除
        $identification = $this->request->post('identification');

        if (!in_array($identification, [0, 1])) {
            $this->error('identification参数缺少或错误，请传入1或者0');
        }
        if (!$user_id || !$plan_id || !$cartype) {
            $this->error('缺少参数,请求失败', 'error');
        }

        //删除该方案的收藏
        if ($identification) {
            $plan_field = $this->getQueryPlan($cartype);
            $res = Collection::destroy([
                'user_id' => $user_id,
                $plan_field => $plan_id
            ]);

            $res ? $this->success('取消收藏成功', 'success') : $this->error('取消收藏失败');
        }


        $res = $this->getFabulousCollection($user_id, $plan_id, $cartype, 'cms_collection');

        switch ($res['errorCode']) {
            case '1':
                $this->error('cartype参数错误');
                break;
            case '2':
                $this->error('收藏失败');
                break;
            case '0':
                $this->success('收藏成功', 'success');
        }
    }

    /**
     * 点击预约接口
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function clickAppointment()
    {
        //必传
        $user_id = $this->request->post('user_id');
        $plan_id = $this->request->post('plan_id');
        $cartype = $this->request->post('cartype');
        $store_id = $this->request->post('store_id');
        $models_name = $this->request->post('models_name');

        //如果是走的手机号码验证 必须传递 mobile  和code参数
        $mobile = $this->request->post('mobile');
        $code = $this->request->post('code');
        //如果是手机号码授权  必须传递 iv 、encryptedData 、 sessionKey参数
        $iv = $this->request->post('iv');
        $encryptedData = $this->request->post('encryptedData');
        $sessionKey = $this->request->post('sessionKey');
        //解密手机号
        if ($sessionKey && $iv && $sessionKey) {
            $pc = new WxBizDataCrypt('wxf789595e37da2838', $sessionKey);
            $result = $pc->decryptData($encryptedData, $iv, $data);
            if ($result == 0) {
                $mobile = json_decode($data, true)['phoneNumber'];
            } else {
                $this->error('手机号解密失败', json_decode($data, true));
            }
        }
//        || !$store_id || !$models_name
        if (!$user_id || !$plan_id || !$cartype) {
            $this->error('缺少参数,请求失败', 'error');
        }
        if ($code) {
            $userInfo = Db::name('cms_login_info')
                ->where(['user_id' => $user_id, 'login_state' => 0])->find();
            if (!$userInfo || $code != $userInfo['login_code']) {
                $this->error('验证码输入错误');
            }
//            else if (intval($userInfo['login_time ']) + 180 < time()) {
//                $this->error('验证码已过期，请重新发送');
//            }

        }

        //如果是手机授权，手机号码更新到用户表
        if ($mobile) {
            User::where('id', $user_id)->update([
                'mobile' => $mobile
            ]);
        } else {
            $mobile = User::get($user_id)->mobile;
        }

        $res = $this->getFabulousCollection($user_id, $plan_id, $cartype, 'subscribe');

        switch ($res['errorCode']) {
            case '1':
                $this->error('cartype参数错误');
                break;
            case '2':
                $this->error('预约失败');
                break;
            case '0':
                if (Cache::get('appointment')) {
                    Cache::rm('appointment');
                }
                $this->success('预约成功', 'success');
//                $url = 'https://open.ucpaas.com/ol/sms/sendsms';
//                $client = new Client();
//
//                $response = $client->request('POST', $url, [
//                    'json' => [
//                        'sid' => self::$Ucpass['accountsid'],
//                        'token' => self::$Ucpass['token'],
//                        'appid' => self::$Ucpass['appid'],
//                        'templateid' => '415120',
//                        'param' => substr($mobile, -4) . ',' . $models_name,
//                        'mobile' => CompanyStore::get($store_id)->mobile,
//                        'uid' => $user_id
//                    ]
//                ]);
//
//                if ($response) {
//                    $result = json_decode($response->getBody(), true);
//                    if ($result['code'] == '000000') {
//                        $this->success('预约成功', 'success');
//                    } else {
//                        $this->error('给该门店发送短信失败');
//                    }
//                } else {
//                    $this->error('请求短信接口失败');
//                }
        }


    }

    /**
     * 搜索车型接口
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function searchModels()
    {

        $queryModels = $this->request->post('queryModels');
        $city_id = $this->request->post('city_id');

        if (!$queryModels || !$city_id) {
            $this->error('缺少参数,请求失败', 'error');
        }

        //新车车型
        $new_models = $this->getModels($queryModels, 'planacarIndex', $city_id);
        //二手车车型
        $used_models = $this->getModels($queryModels, 'usedcarCount', $city_id);
//      //新能源车型
        $logistics = $this->getModels($queryModels, 'logisticsCount', $city_id);

        $data = ['searchModel' => ['new' => $new_models, 'used' => $used_models, 'logistics' => $logistics]];
        $this->success('', $data);

    }

    /**
     * 心愿单接口
     */
    public function wishList()
    {
        $user_id = $this->request->post('user_id');
        $fill_models = $this->request->post('fill_models');
        $expectant_city = $this->request->post('expectant_city');
        $mobile = $this->request->post('mobile');
        $code = $this->request->post('code');

        if (!$fill_models || !$expectant_city || !$mobile || !$code) {
            $this->error('缺少参数,请求失败', 'error');
        }

        if ($code) {
            $userInfo = Db::name('cms_login_info')
                ->where(['user_id' => $user_id, 'login_state' => 0])->find();
            if (!$userInfo || $code != $userInfo['login_code']) {
                $this->error('验证码输入错误');
            }

        }

        $res = Db::name('cms_wishlist')->insert([
            'user_id' => $user_id,
            'fill_models' => $fill_models,
            'expectant_city' => $expectant_city,
            'mobile' => $mobile,
            'createtime' => time()
        ]);

        $res ? $this->success('提交心愿单成功', 'success') : $this->error('提交心愿单失败');
    }


    /**
     * 新车详情
     * @param $plan_id     方案ID
     * @param $user_id     用户ID
     * @param $cartype     车辆类型
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function newcar_details($plan_id, $user_id, $cartype)
    {
        //获取该方案的详细信息
        $plans = PlanAcar::field('id,models_id,payment,monthly,nperlist,modelsimages,models_main_images,
specialimages,popularity')
            ->with(['models' => function ($models) {
                $models->withField('name,models_name,vehicle_configuration,price');
            }, 'label' => function ($label) {
                $label->withField('name,lableimages,rotation_angle');
            }, 'companystore' => function ($companystore) {
                $companystore->withField('id,city_id,store_name,store_address,phone,longitude,latitude');
            }])->find([$plan_id]);

        //用户ID
        $plans['users'] = $this->userPhone($user_id);

        //方案标签图片加入CDN
        if ($plans['label'] && $plans['label']['lableimages']) {
            $plans['label']['lableimages'] = Config::get('upload')['cdnurl'] . $plans['label']['lableimages'];
        }

        $plans['models']['vehicle_configuration'] = json_decode($plans['models']['vehicle_configuration'], true);
        $plans['models']['name'] = $plans['models']['name'] . ' ' . $plans['models']['models_name'];
        $plans['models_main_images'] = $plans['models_main_images'] ? Config::get('upload')['cdnurl'] . $plans['models_main_images'] : '';
        $plans['modelsimages'] = $plans['modelsimages'] ? Config::get('upload')['cdnurl'] . $plans['modelsimages'] : '';
        $plans['specialimages'] = $plans['specialimages'] ? Config::get('upload')['cdnurl'] . $plans['specialimages'] : '';
        //查看同城市同车型不同的方案
        $different_schemes = $this->getPlans($plans['models_id'], $plans['companystore']['city_id'], $plan_id);

        //查看其它方案的属性名
        if ($different_schemes) {
            //为其他方案封面图片加入CDN
            foreach ($different_schemes as $k => $v) {
                unset($different_schemes[$k]['models_main_images'], $different_schemes[$k]['models_name'], $different_schemes[$k]['price']);
            }
            $plans['different_schemes'] = $different_schemes;
        } else {
            $plans['different_schemes'] = null;
        }

        //获取其他方案
        $allModel = $this->getPlans('', $plans['companystore']['city_id'], $plan_id);
        unset($plans['companystore']['city_id']);
        $reallyOther = null;

        //如果有其他方案，随机得到其他的方案
        if ($allModel) {
            $reallyOther = [];

            shuffle($allModel);
            $checkOther = [];
            foreach ($allModel as $k => $v) {

                if (in_array($v['models_name'], $checkOther)) {
                    continue;
                }
                $checkOther[] = $v['models_name'];

                $v['type'] = 'new';
                $reallyOther[] = $v;
                if (count($reallyOther) >= 8) {
                    break;
                }
            }


        }

        //是否点赞丶收藏丶预约
        $collectionFabulousAppointment = $this->collectionFabulousAppointment($user_id, $plan_id, $cartype);

        $plans = array_merge($plans->toArray(), $collectionFabulousAppointment);

        return [
            'plan' => $plans,
            'guesslike' => $reallyOther
        ];

    }

    /**
     * 二手车详情
     * @param $plan_id     方案ID
     * @param $user_id     用户ID
     * @param $cartype     车辆类型
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function used_details($plan_id, $user_id, $cartype)
    {
        $info = SecondcarRentalModelsInfo::field('id,kilometres,totalprices,newpayment,monthlypaymen,periods,
       daypaymen,car_licensedate,emission_standard,emission_load,speed_changing_box,the_transfer_record,
       expirydate,annualverificationdate,businessdate,modelsimages,models_main_images')
            ->with(['models' => function ($q) {
                $q->withField('name,models_name,vehicle_configuration,price');
            }, 'companystore' => function ($q) {
                $q->withField('id,city_id,store_name,store_address,phone,longitude,latitude');
            }])->where('shelfismenu', 1)->find($plan_id);

        $info['models']['name'] = $info['models']['name'] . ' ' . $info['models']['models_name'];
        unset($info['models']['models_name']);

        //用户信息
        $info['users'] = $this->userPhone($user_id);

        $info['modelsimages'] = $info['modelsimages'] ? Config::get('upload')['cdnurl'] . $info['modelsimages'] : null;
        $info['models_main_images'] = $info['models_main_images'] ? Config::get('upload')['cdnurl'] . $info['models_main_images'] : null;

        $info['companystore']['cities_name'] = null;
        if ($info['companystore']['city_id']) {
            $info['companystore']['cities_name'] = Cities::field('cities_name')
                ->find($info['companystore']['city_id'])['cities_name'];
        }
        unset($info['companystore']['city_id']);
        //是否点赞丶收藏丶预约
        $collectionFabulousAppointment = $this->collectionFabulousAppointment($user_id, $plan_id, $cartype);

        $info = array_merge($info->toArray(), $collectionFabulousAppointment);

        return ['plan' => $info];
    }

    public function logistics_details($plan_id, $user_id, $cartype)
    {
        $info = Logistics::field('id,payment,monthly,nperlist,models_main_images,modelsimages')
            ->with(['companystore' => function ($store) {
                $store->withField('id,store_name,store_address,phone,store_img,longitude,latitude,city_id');
            }, 'label' => function ($label) {
                $label->withField('id,name,lableimages,rotation_angle');
            }, 'models' => function ($models) {
                $models->withField('id,name,vehicle_configuration,price,models_name');
            }])->find($plan_id);

        if ($info['models']['vehicle_configuration']) {
            $info['models']['vehicle_configuration'] = json_decode($info['models']['vehicle_configuration'], true);
        }

        //用户ID
        $info['users'] = $this->userPhone($user_id);

        $info['models_main_images'] = $info['models_main_images'] ? Config::get('upload')['cdnurl'] . $info['models_main_images'] : '';
        $info['modelsimages'] = $info['modelsimages'] ? Config::get('upload')['cdnurl'] . $info['modelsimages'] : '';
        $info['label']['lableimages'] = $info['label']['lableimages'] ? Config::get('upload')['cdnurl'] . $info['label']['lableimages'] : '';


        $info['different_chemes'] = $this->logisticsPlans($info['models']['id'], $plan_id, $info['companystore']['city_id']);

        //是否点赞丶收藏丶预约
        $collectionFabulousAppointment = $this->collectionFabulousAppointment($user_id, $plan_id, $cartype);

        $guessLike = $this->logisticsPlans('', $plan_id, $info['companystore']['city_id']);

        if ($guessLike) {
            $guess = [];
            shuffle($guessLike);

            foreach ($guessLike as $k => $v) {
                if ($k > 7) {
                    break;
                }

                $guess[] = $v;
            }
            $guessLike = $guess;
        }

        unset($info['companystore']['city_id']);

        $info = array_merge($info->toArray(), $collectionFabulousAppointment);
        return ['plan' => $info, 'guesslike' => $guessLike];

    }

    public function rent_details($plan_id, $user_id, $cartype)
    {
        $info = RentalModelsInfo::field('id,modelsimages,models_main_images,manysixmonths')
            ->with(['models' => function ($models) {
                $models->withField('id,name,models_name,vehicle_configuration');
            }, 'companystore' => function ($companystore) {
                $companystore->withField('id,city_id,store_name,store_address,phone,longitude,latitude');
            }])->find($plan_id);

        //用户ID
        $info['users'] = $this->userPhone($user_id);
        $info['models']['name'] = $info['models']['name'] . ' ' . $info['models']['models_name'];
        unset($info['models']['models_name']);

        $info['models']['vehicle_configuration'] = json_decode($info['models']['vehicle_configuration'], true);

        $info['models']['vehicle_configuration'] = '省油丨舒适丨' . $info['models']['vehicle_configuration']['变速箱']['abbreviation'] . '丨'
            . $info['models']['vehicle_configuration']['车身']['numberOfDoors'] . '门' . $info['models']['vehicle_configuration']['车身']['numberOfSeats'] . '座' . $info['models']['vehicle_configuration']['车身']['bodyStructure'];

        //是否点赞丶收藏丶预约
        $collectionFabulousAppointment = $this->collectionFabulousAppointment($user_id, $plan_id, $cartype);

        $info = array_merge($info->toArray(), $collectionFabulousAppointment);

        return ['plan' => $info];
    }

    /**
     * 用户电话
     * @param $user_id
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function userPhone($user_id)
    {
        $users = User::get(function ($query) use ($user_id) {
            $query->where('id', $user_id)->field('id,mobile');
        })->toArray();
        unset($users['url']);
        return $users;
    }

    /**
     * 新能源的其他方案
     * @param $models_id      车型ID
     * @param $plan_id        方案ID
     * @param $city_id        城市ID
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function logisticsPlans($models_id, $plan_id, $city_id)
    {
        $plans = Logistics::field('id,payment,monthly,models_main_images')
            ->with(['models' => function ($q) use ($models_id) {
                $q->where(['models.id' => $models_id ? $models_id : ['neq', 'null']])->withField('id,name,price');
            }, 'companystore' => function ($store) use ($city_id) {
                $store->where('companystore.city_id', $city_id)->withField('id');
            }])->where([
                'ismenu' => 1,
                'logistics.id' => ['neq', $plan_id]
            ])->select();

        foreach ($plans as $k => $v) {
            unset($v['companystore']);
        }

        return $plans;

    }


    /**
     * 判断用户是否点赞丶收藏丶预约该方案
     * @param $user_id      用户ID
     * @param $plan_id      方案ID
     * @param $cartype      车辆类型
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function collectionFabulousAppointment($user_id, $plan_id, $cartype)
    {
        $plans = [];
        //判断用户是否点赞该方案
        $collection = $this->getFabulousCollection($user_id, $plan_id, $cartype, 'cms_collection', true);
        //判断用户是否收藏该方案
        $fabulous = $this->getFabulousCollection($user_id, $plan_id, $cartype, 'cms_fabulous', true);
        //判断用户是否预约该方案
        $appointment = $this->getFabulousCollection($user_id, $plan_id, $cartype, 'subscribe', true);
        $plans['collection'] = $collection ? 1 : 0;
        $plans['fabulous'] = $fabulous ? 1 : 0;
        $plans['appointment'] = $appointment ? 1 : 0;

        return $plans;
    }

    /**
     * 查询或者新增点赞丶收藏丶预约
     * @param $user_id
     * @param $plan_id
     * @param $cartype
     * @param $tableName
     * @param bool $getData
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getFabulousCollection($user_id, $plan_id, $cartype, $tableName, $getData = false, $score = null)
    {

        $plan_field = $this->getQueryPlan($cartype);

        if (!$plan_field) {
            return ['errorCode' => 1];
        }

        $tables = null;
        switch ($tableName) {
            case 'cms_fabulous':
                $tables = new Fabulous();
                break;
            case 'cms_collection':
                $tables = new Collection();
                break;
            case 'subscribe':
                $tables = new Subscribe();
        }
        $data = [
            'user_id' => $user_id,
            $plan_field => $plan_id,
        ];

        if ($getData) {
            return Db::name($tableName)
                ->where($data)
                ->find();
        }

        if ($tableName == 'subscribe') {
            $data['cartype'] = $cartype;

            switch ($cartype) {
                case 'new':
                    $planTables = 'plan_acar';
                    break;
                case 'used':
                    $planTables = 'secondcar_rental_models_info';
                    break;
                case 'logistics':
                    $planTables = 'cms_logistics_project';
                    break;
                case 'rent':
                    $planTables = 'car_rental_models_info';
                    break;
            }

            $data['store_ids'] = Db::name($planTables)
                ->where('id', $plan_id)
                ->value('store_id');

        }
        if ($score) {
            $data['score'] = intval($score);
        }

        return $tables->create($data) ? ['errorCode' => 0] : ['errorCode' => 2];
    }

    /**
     *添加积分
     * @param $user_id        用户ID
     * @param $style          增加积分参数，仅支持['fabulous','share','sign']
     * @return int|true
     * @throws \think\Exception
     */
    public static function integral($user_id, $score)
    {
        $res = User::where('id', $user_id)
            ->setInc('score', $score);
        return $res ? 1 : 0;

    }

    /**
     * 获取配置信息
     * @param $where
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function ConfigData($where)
    {
        return Db::name('config')
            ->where($where)
            ->find();
    }


    /**
     * 模糊查询得到新车或者二手车的车型
     * @param $queryModels            搜索内容
     * @param $withTable              关联的表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getModels($queryModels, $withTable, $city_id)
    {

        $models = Cities::field('id,cities_name')
            ->with(['storeList' => function ($store) use ($withTable, $queryModels) {
                $store->where('statuss', 'normal')->with([$withTable => function ($plan) use ($withTable, $queryModels) {
                    $where = $withTable == 'usedcarCount' ? ['shelfismenu' => 1] : ['ismenu' => 1];
                    $plan->where($where)->with(['models' => function ($models) use ($queryModels) {
                        $models->where([
                            'name' => ['like', '%' . $queryModels . '%']
                        ])->whereOr([
                                'first_word' => $queryModels
                            ]
                        )->withField('id,name,models_name');
                    }]);
                }]);
            }])->find($city_id);

        switch ($withTable) {
            case 'planacarIndex':
                $planKey = 'planacar_index';
                break;
            case 'usedcarCount':
                $planKey = 'usedcar_count';
                break;
            case 'logisticsCount':
                $planKey = 'logistics_count';
                break;
            default:
                $planKey = null;
                break;
        }

        $check = $relModels = [];

        foreach ($models['store_list'] as $k => $v) {
            if ($v[$planKey]) {
                foreach ($v[$planKey] as $key => $value) {
                    if (in_array($value['models']['id'], $check)) {
                        continue;
                    } else {
                        $check[] = $value['models']['id'];
                    }
                    $value['models']['name'] = $value['models']['name'] . ' ' . $value['models']['models_name'];
                    unset($value['models']['models_name']);
                    $value['models']['city'] = ['id' => $models['id'], 'cities_name' => $models['cities_name']];
                    $value['models']['type'] = $value['type'];
                    $relModels[] = $value['models'];
                }
            }
        }

        return $relModels;

    }


    /**
     * 详情方案
     * @param null $models_id 车型ID
     * @param $city_id          城市ID
     * @param $plan_id          方案ID
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPlans($models_id = null, $city_id = null, $plan_id)
    {
        return Db::name('models')
            ->alias('a')
            ->join('plan_acar b', 'b.models_id = a.id')
            ->join('cms_company_store c', 'b.store_id = c.id')
            ->where([
                'a.id' => $models_id == null ? ['neq', 'null'] : $models_id,
                'c.city_id' => $city_id == null ? ['neq', 'null'] : $city_id,
                'b.id' => ['neq', $plan_id],
                'b.sales_id' => null
            ])
            ->field('b.id,b.payment,b.monthly,b.nperlist,b.models_main_images,a.name as models_name,a.price')
            ->select();

    }


    /**
     * 返回需要类型的方案
     * @param $city_id          城市ID
     * @param bool $duplicate 是否去重
     * @param $withPlan         关联方案
     * @param $type             车辆类型
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getVariousTypePlan($city_id = null, $duplicate = false, $withPlan, $type, $store_id = null)
    {
        $info = Cities::field('id,cities_name')
            ->with(['storeList' => function ($q) use ($withPlan, $store_id) {
                $q->where([
                    'id' => $store_id ? $store_id : ['neq', 'null'],
                    'statuss' => 'normal',
                ])->with([$withPlan => function ($query) use ($withPlan) {
                    $where = $withPlan == 'usedcarCount' || $withPlan == 'rentalmodelsinfo' ? ['shelfismenu' => 1] : ['ismenu' => 1];

                    $order = $withPlan == 'logisticsCount' ? '' : 'weigh desc';

                    $query->where($where)->order($order)->with(['models' => function ($models) use ($withPlan) {
                        $field = $withPlan == 'rentalmodelsinfo' ? 'id,name,brand_id,price,models_name,vehicle_configuration' : 'id,name,brand_id,price,models_name';
                        $models->withField($field);
                    }, 'label' => function ($label) {
                        $label->withField('name,lableimages,rotation_angle');
                    }]);
                }]);
            }])->select($city_id ? $city_id : null);

        foreach ($info as $k => $v) {
            if ($v['store_list']) {
                $info = $v;
                break;
            }
        }
        return self::handleNewUsed($info, $duplicate, $type);
    }


    /**
     *新车二手车返回
     * @param $info
     * @param bool $duplicate
     * @param $type
     * @return array
     */
    public static function handleNewUsed($info, $duplicate = false, $type)
    {
        //该城市无任何门店
        if (!$info['store_list']) {
            return [];
        }

        $needArr = $checkModelId = $checkPayment = [];

        //得到所有的品牌列表
        if (Cache::get('brandList')) {
            $brand = Cache::get('brandList');
        } else {
            Cache::set('brandList', self::brandInfo());
            $brand = Cache::get('brandList');
        }

        switch ($type) {
            case 'new':
                $planKey = 'planacar_index';
                break;
            case 'used':
                $planKey = 'usedcar_count';
                break;
            case 'logistics':
                $planKey = 'logistics_count';
                break;
            case 'rent':
                $planKey = 'rentalmodelsinfo';
                break;
            default:
                $planKey = null;
                break;
        }

        foreach ($info['store_list'] as $k => $v) {
            if ($v[$planKey]) {
                foreach ($v[$planKey] as $kk => $vv) {

                    if ($duplicate) {

                        if (in_array($vv['models']['id'], $checkModelId)) {
                            if ($checkPayment[$vv['models']['id']] < $vv['payment']) {
                                continue;
                            } else {
                                $checkPayment[$vv['models']['id']] = $vv['payment'];

                                foreach ($needArr as $key => $value) {
                                    if ($vv['models']['id'] == $value['models']['id']) {
                                        unset($needArr[$key]);
                                    }
                                }
                            }

                        } else {
                            $checkModelId[$vv['models']['id']] = $vv['models']['id'];
                            $checkPayment[$vv['models']['id']] = $vv['payment'];
                        }

                    }

                    $vv['models']['name'] = $vv['models']['name'] . ' ' . $vv['models']['models_name'];
                    unset($vv['models']['models_name']);
                    $vv['city'] = ['id' => $info['id'], 'cities_name' => $info['cities_name']];
                    $data = $vv['label'];
                    $vv['label'] = $data;

                    if (!empty($vv['models']['vehicle_configuration'])) {
                        $vv['models']['vehicle_configuration'] = json_decode($vv['models']['vehicle_configuration'], true);
                        $vv['models']['vehicle_configuration'] = '省油丨舒适丨' . $vv['models']['vehicle_configuration']['变速箱']['abbreviation'] . '丨'
                            . $vv['models']['vehicle_configuration']['车身']['numberOfDoors'] . '门' . $vv['models']['vehicle_configuration']['车身']['numberOfSeats'] . '座' . $vv['models']['vehicle_configuration']['车身']['bodyStructure'];
//                        $vv['models']['vehicle_configuration'] = ['车身'=>$vv['models']['vehicle_configuration']['车身'],'变速箱'=>$vv['models']['vehicle_configuration']['变速箱']];
                    }
                    $needArr[] = $vv;

                }
            }

        }
        $needArr = array_values($needArr);

        shuffle($brand);

        foreach ($needArr as $k => $v) {
            foreach ($brand as $key => $value) {
                if (empty($value['planList'])) {
                    $brand[$key]['planList'] = array();
                }

                if ($value['id'] == $v['models']['brand_id']) {
                    $arr = $brand[$key]['planList'];
                    $arr[] = $v;
                    $brand[$key]['planList'] = $arr;
                }

            }
        }
        //如果品牌列表没有【planList】键，表示该城市门店无方案
        if (!isset($brand[0]['planList'])) {
            return [];
        }
        //去除没用的品牌
        foreach ($brand as $k => $v) {
            if (!$v['planList']) {
                unset($brand[$k]);
            }
        }

        return $brand ? array_values($brand) : [];
    }

    /**
     * 品牌
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function brandInfo()
    {
        return Brand::field('id,name,brand_initials')
            ->where([
                'status' => 'normal',
                'pid' => 0
            ])->select();
    }

    /**
     * 获取配置表信息
     * @return mixed
     */
    public static function getConfigShare()
    {
        return json_decode(Db::name('config')
            ->where('name', 'share')
            ->value('value'), true);

    }

    /**
     * 获取省份-城市
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getCityList()
    {
        $citys = Cities::where([
            'status' => 'normal',
        ])->field('id,pid,name,province_letter,cities_name')->select();

        //将省份单独拿出来
        $province = [];
        foreach ($citys as $k => $v) {
            if ($v['pid'] == 0) {
                unset($v['cities_name']);
                $province[$v['province_letter']][] = $v;

                //删除省份的数据，保留城市
                unset($citys[$k]);
            }

        }

        //省份加入对应的城市
        foreach ($province as $k => $v) {

            foreach ($v as $kk => $vv) {
                $temporary = [];

                foreach ($citys as $key => $value) {
                    if ($value['pid'] == $vv['id']) {
                        unset($value['name']);
                        unset($value['province_letter']);
                        $temporary[] = $value;
                    }
                }

                //加入满足条件的城市数据
                $province[$k][$kk]['citys'] = $temporary;
            }

        }

        return $province;
    }

    /**
     * 得到预约表满足要求的方案字段
     * @param $cartype
     * @return bool|string
     */
    public function getQueryPlan($cartype)
    {
        switch ($cartype) {
            case 'new':
                $planField = 'plan_acar_id';
                break;
            case 'used':
                $planField = 'secondcar_rental_models_info_id';
                break;
            case 'logistics':
                $planField = 'logistics_project_id';
                break;
            case 'rent':
                $planField = 'car_rental_models_info_id';
                break;
            default:
                return false;

        }

        return $planField;
    }


    /**
     *  发送验证码
     * @return mixed
     */
    public function sendMessage()
    {
        $mobile = $this->request->post('mobile');
        $user_id = $this->request->post('user_id');
        if (!$mobile || !$user_id) $this->error('参数缺失或格式错误');
        if (!checkPhoneNumberValidate($mobile)) $this->error('手机号格式错误', $mobile);
        $authnum = '';
        //随机生成四位数验证码
        $list = explode(",", "0,1,2,3,4,5,6,7,8,9");
        for ($i = 0; $i < 4; $i++) {
            $randnum = rand(0, 9);
            $authnum .= $list[$randnum];
        }

        $url = 'http://open.ucpaas.com/ol/sms/sendsms';
        $client = new Client();
        $response = $client->request('POST', $url, [
            'json' => [
                'sid' => self::$Ucpass['accountsid'],
                'token' => self::$Ucpass['token'],
                'appid' => self::$Ucpass['appid'],
                'templateid' => self::$Ucpass['templateid'],
                'param' => $authnum,
                'mobile' => $mobile,
                'uid' => $user_id
            ]
        ]);
        if ($response) {
            $result = json_decode($response->getBody(), true);
            $num = '';
            if ($result['code'] == '000000') {
                //查询当前手机号，如果存在更新他的的请求次数与 请求时间
                $getPhone = Db::name('cms_login_info')->where(['login_phone' => $mobile])->find();
                if ($getPhone) {
                    $num = $getPhone['login_num'];
                    ++$num;
                    Db::name('cms_login_info')->update([
                        'login_time' => strtotime($result['create_date']),
                        'login_code' => $authnum,
                        'login_num' => $num,
                        'login_phone' => $mobile,
                        'id' => $getPhone['id'],
                        'login_state' => 0,
                        'user_id' => $user_id
                    ]) ? $this->success('发送成功') : $this->error('发送失败');

                } else {
                    //否则新增当前用户到登陆表
                    Db::name('cms_login_info')->insert([
                        'login_time' => strtotime($result['create_date']),
                        'login_code' => $authnum,
                        'login_num' => 1,
                        'login_phone' => $mobile,
                        'login_state' => 0,
                        'user_id' => $user_id
                    ]) ? $this->success('发送成功') : $this->error('发送失败');
                }
            } else {
                $this->error($result['msg'], $result);
            }
        } else {
            $err = json_decode($response->getBody(), true);
            $this->error($err['msg'], $err);
        }


    }

    public static function object_to_array($obj)
    {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)object_to_array($v);
            }
        }

        return $obj;
    }


}
<?php

namespace addons\cms\controller\wxapp;

use addons\cms\model\Block;
use addons\cms\model\Channel;
use app\common\model\Addon;
use think\Config;
use think\Db;
use think\Cache;

/**
 * 公共
 */
class Common extends Base
{

    protected $noNeedLogin = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 初始化
     */
    public function init()
    {
        $city_id = $this->request->post('city_id');                              //参数：城市ID
        if (!$city_id) {
            $this->error('缺少参数,请求失败', 'error');
        }
        //预约缓存
        if (!Cache::get('appointment')) {
            Cache::set('appointment', Index::appointment());
        }

        //返回所有类型的方案
        $useful = Index::getAllStylePlan($city_id);

        //焦点图
        $bannerList = [];
//        $list = Block::getBlockList(['name' => 'focus', 'row' => 5]);
        $list = Db::name('cms_block')->where(['name' => 'focus', 'status' => 'normal'])->select();
        foreach ($list as $index => $item) {
            $bannerList[] = ['id'=>$item['id'],'image' => $item['image'], 'url' => $item['url'], 'title' => $item['title']];
        }


        //配置信息
        $upload = Config::get('upload');
        $upload['cdnurl'] = $upload['cdnurl'] ? $upload['cdnurl'] : cdnurl('', true);
        $upload['uploadurl'] = $upload['uploadurl'] == 'ajax/upload' ? cdnurl('/ajax/upload', true) : $upload['cdnurl'];
        $config = [
            'upload' => $upload
        ];

        $shares = Db::name('config')
            ->where('group','share')
            ->field('name,value')
            ->select();

        $sharesAll = [];
        $shares[0]['value'] = json_decode($shares[0]['value'],true);
        $sharesAll['index_share_title'] = $shares[0]['value']['index_share_title'];
        $sharesAll['index_share_img'] = $shares[1]['value'];
        $sharesAll['share_moments_bk_img'] = $shares[2]['value'];
        $sharesAll['index_share_bk_qrcode'] = $shares[3]['value'];
        $data = [
            'carType' => [
                'new' => [
                    //为你推荐
                    'recommendList' => $useful['recommendList'],
                    //专题
                    'specialList' => $useful['specialList'],
                    //专场
                    'specialfieldList' => $useful['specialfieldList']
                ],
            ],
            'bannerList' => $bannerList,
            'config' => $config,
            //品牌
            'brandList' => Index::getBrand(),
            //分享
            'shares' => $sharesAll,
            //预约
            'appointment' => Cache::get('appointment')
        ];
        $this->success('', $data);

    }


}

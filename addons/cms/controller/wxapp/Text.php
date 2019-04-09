<?php

namespace addons\cms\controller\wxapp;

use think\Db;
use think\Config;

/**
 * 首页
 */
class Text extends Base
{

    protected $noNeedLogin = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 首页
     */
    public function index()
    {
        $plan = Db::name('car_rental_models_info')->select();

        foreach ($plan as $key => $value){
            $plan[$key]['drivinglicenseimages'] = Config::get('upload')['cdnurl'] . $value['drivinglicenseimages'];
        }

        $data = [
            'plan'  => $plan,
        ];
        $this->success('', $data);

    }


}

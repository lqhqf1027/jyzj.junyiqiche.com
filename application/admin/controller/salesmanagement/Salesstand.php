<?php

namespace app\admin\controller\salesmanagement;

use app\common\controller\Backend;
// use app\admin\controller\salesmanagement\Customerlisttabs;
use think\Config;
use think\Db;
use think\Cache;

use app\admin\model\Salesstand as SalesstandModel;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Salesstand extends Backend
{
        protected $noNeedRight = ['index'];


        /**
         * 查看
         */
        public function index()
        {
                //以租代购（新车）成交量
                $newcount = SalesstandModel::getOrderCount('sales_order','the_car',null,null);
                //租车成交量
                $rentalcount = SalesstandModel::getOrderCount('rental_order','for_the_car',null,null);
                //以租代购（二手车）成交量
                $secondcount = SalesstandModel::getOrderCount('second_sales_order','the_car',null,null);
                //全款新车成交量
                $fullcount = SalesstandModel::getOrderCount('full_parment_order','for_the_car',null,null);
                //全款新车成交量
                $fullsecondcount = SalesstandModel::getOrderCount('second_full_order','for_the_car',null,null);

                $seventtime = \fast\Date::unixtime('month', -6);
                
                $newonesales = $rentalonesales = $secondonesales = $fullonesales = [];

                $month = date("Y-m", $seventtime);
                $day = date('t', strtotime("$month +1 month -1 day"));

                //销售一部
                $one_sales = SalesstandModel::getAdmin('auth_group_access','18');
                foreach($one_sales as $k => $v){
                        $one_admin[] = $v['uid'];
                }
                //以租代购（新车）历史成交数
                $newonecount = SalesstandModel::getOrderCount('sales_order','the_car',$one_admin,null);
                //租车历史出租数
                $rentalonecount = SalesstandModel::getOrderCount('rental_order','for_the_car',$one_admin,null);
                //以租代购（二手车）历史成交数
                $secondonecount = SalesstandModel::getOrderCount('second_sales_order','the_car',$one_admin,null);
                //全款历史成交数
                $fullonecount = SalesstandModel::getOrderCount('full_parment_order','for_the_car',$one_admin,null);

                //销售一部的销售情况    
                for ($i = 0; $i < 8; $i++)
                {
                        $months = date("Y-m", $seventtime + (($i+1) * 86400 * $day));
                        $firstday = strtotime(date('Y-m-01', strtotime($month)));
                        $secondday = strtotime(date('Y-m-01', strtotime($months)));
                        
                        //以租代购（新车）
                        $newonetake = SalesstandModel::getOrderCount('sales_order','the_car',$one_admin,[$firstday, $secondday]);
                        //租车 
                        $rentalonetake = SalesstandModel::getOrderCount('rental_order','for_the_car',$one_admin,[$firstday, $secondday]);
                        //以租代购（二手车）
                        $secondonetake = SalesstandModel::getOrderCount('second_sales_order','the_car',$one_admin,[$firstday, $secondday]);
                        //全款车
                        $fullonetake = SalesstandModel::getOrderCount('full_parment_order','for_the_car',$one_admin,[$firstday, $secondday]);

                        //以租代购（新车）
                        $newonesales[$month . '(月)'] = $newonetake;
                        //租车
                        $rentalonesales[$month . '(月)'] = $rentalonetake;
                        //以租代购（二手车）
                        $secondonesales[$month . '(月)'] = $secondonetake;
                        //全款车
                        $fullonesales[$month . '(月)'] = $fullonetake;

                        $month = date("Y-m", $seventtime + (($i+1) * 86400 * $day));
                        
                        $day = date('t', strtotime("$months +1 month -1 day"));
                }
                
                $newsecondsales = $rentalsecondsales = $secondsecondsales = $fullsecondsales = [];

                $month = date("Y-m", $seventtime);
                $day = date('t', strtotime("$month +1 month -1 day"));

                //销售二部
                $two_sales = SalesstandModel::getAdmin('auth_group_access','22');
                foreach($two_sales as $k => $v){
                        $two_admin[] = $v['uid'];
                }

                //以租代购（新车）历史成交数
                $newtwocount = SalesstandModel::getOrderCount('sales_order','the_car',$two_admin,null);
                // pr($newonecount);
                //租车历史出租数
                $rentaltwocount = SalesstandModel::getOrderCount('rental_order','for_the_car',$two_admin,null);
                //以租代购（二手车）历史成交数
                $secondtwocount = SalesstandModel::getOrderCount('second_sales_order','the_car',$two_admin,null);
                //全款历史成交数
                $fulltwocount = SalesstandModel::getOrderCount('full_parment_order','for_the_car',$two_admin,null);

                //销售二部的销售情况    
                for ($i = 0; $i < 8; $i++)
                {
                        $months = date("Y-m", $seventtime + (($i+1) * 86400 * $day));
                        $firstday = strtotime(date('Y-m-01', strtotime($month)));
                        $secondday = strtotime(date('Y-m-01', strtotime($months)));
                        

                        //以租代购（新车）
                        $newtwotake = SalesstandModel::getOrderCount('sales_order','the_car',$two_admin,[$firstday, $secondday]);
                        //租车 
                        $rentaltwotake = SalesstandModel::getOrderCount('rental_order','for_the_car',$two_admin,[$firstday, $secondday]);
                        //以租代购（二手车）
                        $secondtwotake = SalesstandModel::getOrderCount('second_sales_order','the_car',$two_admin,[$firstday, $secondday]);
                        //全款车
                        $fulltwotake = SalesstandModel::getOrderCount('full_parment_order','for_the_car',$two_admin,[$firstday, $secondday]);

                        //以租代购（新车）
                        $newsecondsales[$month . '(月)'] = $newtwotake;
                        //租车
                        $rentalsecondsales[$month . '(月)'] = $rentaltwotake;
                        //以租代购（二手车）
                        $secondsecondsales[$month . '(月)'] = $secondtwotake;
                        //全款车
                        $fullsecondsales[$month . '(月)'] = $fulltwotake;

                        $month = date("Y-m", $seventtime + (($i+1) * 86400 * $day));
                        
                        $day = date('t', strtotime("$months +1 month -1 day"));
                
                } 
                
                $newthreesales = $rentalthreesales = $secondthreesales = $fullthreesales = [];

                $month = date("Y-m", $seventtime);
                $day = date('t', strtotime("$month +1 month -1 day"));

                //销售三部。。。l
                $three_sales = SalesstandModel::getAdmin('auth_group_access','37');
                foreach($three_sales as $k => $v){
                        $three_admin[] = $v['uid'];
                }

                //以租代购（新车）历史成交数
                $newthreecount = SalesstandModel::getOrderCount('sales_order','the_car',$three_admin,null);
                //租车历史出租数
                $rentalthreecount = SalesstandModel::getOrderCount('rental_order','for_the_car',$three_admin,null);
                //以租代购（二手车）历史成交数
                $secondthreecount = SalesstandModel::getOrderCount('second_sales_order','the_car',$three_admin,null);
                //全款历史成交数
                $fullthreecount = SalesstandModel::getOrderCount('full_parment_order','for_the_car',$three_admin,null);

                //销售三部的销售情况 
                for ($i = 0; $i < 8; $i++)
                {
                        $months = date("Y-m", $seventtime + (($i+1) * 86400 * $day));
                        $firstday = strtotime(date('Y-m-01', strtotime($month)));
                        $secondday = strtotime(date('Y-m-01', strtotime($months)));
                        

                        //以租代购（新车）
                        $newthreetake = SalesstandModel::getOrderCount('sales_order','the_car',$three_admin,[$firstday, $secondday]);
                        //租车 
                        $rentalthreetake = SalesstandModel::getOrderCount('rental_order','for_the_car',$three_admin,[$firstday, $secondday]);
                        //以租代购（二手车）
                        $secondthreetake = SalesstandModel::getOrderCount('second_sales_order','the_car',$three_admin,[$firstday, $secondday]);
                        //全款车
                        $fullthreetake = SalesstandModel::getOrderCount('full_parment_order','for_the_car',$three_admin,[$firstday, $secondday]);

                        //以租代购（新车）
                        $newthreesales[$month . '(月)'] = $newthreetake;
                        //租车
                        $rentalthreesales[$month . '(月)'] = $rentalthreetake;
                        //以租代购（二手车）
                        $secondthreesales[$month . '(月)'] = $secondthreetake;
                        //全款车
                        $fullthreesales[$month . '(月)'] = $fullthreetake;

                        $month = date("Y-m", $seventtime + (($i+1) * 86400 * $day));
                        
                        $day = date('t', strtotime("$months +1 month -1 day"));

                }

                $hooks = config('addons.hooks');
                $uploadmode = isset($hooks['upload_config_init']) && $hooks['upload_config_init'] ? implode(',', $hooks['upload_config_init']) : 'local';
                $addonComposerCfg = ROOT_PATH . '/vendor/karsonzhang/fastadmin-addons/composer.json';
                Config::parse($addonComposerCfg, "json", "composer");
                $config = Config::get("composer");
                $addonVersion = isset($config['version']) ? $config['version'] : __('Unknown');
                $this->view->assign([

                //销售情况 --- 一部
                'newonesales'           => $newonesales,
                'rentalonesales'        => $rentalonesales,
                'secondonesales'        => $secondonesales,
                'fullonesales'          => $fullonesales,
                
                //销售情况 --- 二部
                'newsecondsales'        => $newsecondsales,
                'rentalsecondsales'     => $rentalsecondsales,
                'secondsecondsales'     => $secondsecondsales,
                'fullsecondsales'       => $fullsecondsales,

                //总共成交量
                'newcount'              => $newcount,
                'rentalcount'           => $rentalcount,
                'secondcount'           => $secondcount,
                'fullcount'             => $fullcount,
                'fullsecondcount'       => $fullsecondcount,
                
                //销售情况 --- 三部
                'newthreesales'         => $newthreesales,
                'rentalthreesales'      => $rentalthreesales,
                'secondthreesales'      => $secondthreesales,
                'fullthreesales'        => $fullthreesales,

                //历史成交数---一部
                'newonecount'           => $newonecount,
                'rentalonecount'        => $rentalonecount,
                'secondonecount'        => $secondonecount,
                'fullonecount'          => $fullonecount,

                //历史成交数---二部
                'newtwocount'        => $newtwocount,
                'rentaltwocount'     => $rentaltwocount,
                'secondtwocount'     => $secondtwocount,
                'fulltwocount'       => $fulltwocount,

                //历史成交数---三部
                'newthreecount'         => $newthreecount,
                'rentalthreecount'      => $rentalthreecount,
                'secondthreecount'      => $secondthreecount,
                'fullthreecount'        => $fullthreecount

                ]);

                return $this->view->fetch();
        }

}

<?php

namespace app\admin\controller\secondfullcar;

use app\common\controller\Backend;
use think\DB;
use think\Config;
use app\common\library\Email;
use think\Cache;

/**
 * 全款进件列管理
 *
 * @icon fa fa-circle-o
 */
class Secondfullcustomer extends Backend
{

    /**
     * Fullpeople模型对象
     * @var \app\admin\model\Fullpeople
     */
    protected $model = null;
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 查看
     */
    public function index()
    {

        $this->loadlang('order/Secondfullorder');

        return $this->view->fetch();
    }


    /**待提车
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function prepare_lift_car()
    {
        $this->model = new \app\admin\model\SecondFullOrder;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username', true);
            $total = $this->model
                ->with(['plansecondfull' => function ($query) {
                    $query->withField(['totalprices', 'licenseplatenumber']);
                }, 'admin' => function ($query) {
                    $query->withField(['nickname', 'id', 'avatar']);
                }, 'models' => function ($query) {
                    $query->withField('name,models_name');
                }])
                ->where($where)
                ->where(function ($query) {
                    $query->where("review_the_data", "is_reviewing_true");
                })
                ->order($sort, $order)
                ->count();


            $list = $this->model
                ->with(['plansecondfull' => function ($query) {
                    $query->withField(['totalprices', 'licenseplatenumber']);
                }, 'admin' => function ($query) {
                    $query->withField(['nickname', 'id', 'avatar']);
                }, 'models' => function ($query) {
                    $query->withField('name,models_name');
                }])
                ->where($where)
                ->where(function ($query) {
                    $query->where("review_the_data", "is_reviewing_true");
                })
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $row) {
                $row->visible(['id', 'order_no', 'username', 'createtime', 'phone', 'id_card', 'amount_collected', 'review_the_data']);
                $row->visible(['plansecondfull']);
                $row->getRelation('plansecondfull')->visible(['totalprices', 'licenseplatenumber']);
                $row->visible(['admin']);
                $row->getRelation('admin')->visible(['nickname', 'id', 'avatar']);
                $row->visible(['models']);
                $row->getRelation('models')->visible(['name', 'models_name']);

                if ($list[$k]['models']['models_name']) {
                    $list[$k]['models']['name'] = $list[$k]['models']['name'] . " " . $list[$k]['models']['models_name'];
                }

            }


            $list = collection($list)->toArray();
            foreach ($list as $k => $v) {
                $department = Db::name('auth_group_access')
                    ->alias('a')
                    ->join('auth_group b', 'a.group_id = b.id')
                    ->where('a.uid', $v['admin']['id'])
                    ->value('b.name');
                $list[$k]['admin']['department'] = $department;
            }
            $result = array('total' => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();

    }


    /**已提车
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function already_lift_car()
    {

        $this->model = model('second_full_order');
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username', true);
            $total = $this->model
                ->with(['plansecondfull' => function ($query) {
                    $query->withField(['totalprices', 'licenseplatenumber']);
                }, 'admin' => function ($query) {
                    $query->withField(['nickname', 'id', 'avatar']);
                }, 'models' => function ($query) {
                    $query->withField('name,models_name');
                }])
                ->where($where)
                ->where(function ($query) {
                    $query->where("review_the_data", "for_the_car");
                })
                ->order($sort, $order)
                ->count();


            $list = $this->model
                ->with(['plansecondfull' => function ($query) {
                    $query->withField(['totalprices', 'licenseplatenumber']);
                }, 'admin' => function ($query) {
                    $query->withField(['nickname', 'id', 'avatar']);
                }, 'models' => function ($query) {
                    $query->withField('name,models_name');
                }])
                ->where($where)
                ->where(function ($query) {
                    $query->where("review_the_data", "for_the_car");
                })
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $row) {
                $row->visible(['id', 'order_no', 'username', 'createtime', 'phone', 'id_card', 'amount_collected', 'review_the_data','delivery_datetime']);
                $row->visible(['plansecondfull']);
                $row->getRelation('plansecondfull')->visible(['totalprices', 'licenseplatenumber']);
                $row->visible(['admin']);
                $row->getRelation('admin')->visible(['nickname', 'id', 'avatar']);
                $row->visible(['models']);
                $row->getRelation('models')->visible(['name', 'models_name']);

                if ($list[$k]['models']['models_name']) {
                    $list[$k]['models']['name'] = $list[$k]['models']['name'] . " " . $list[$k]['models']['models_name'];
                }

            }


            $list = collection($list)->toArray();
            foreach ($list as $k => $v) {
                $department = Db::name('auth_group_access')
                    ->alias('a')
                    ->join('auth_group b', 'a.group_id = b.id')
                    ->where('a.uid', $v['admin']['id'])
                    ->value('b.name');
                $list[$k]['admin']['department'] = $department;
            }
            $result = array('total' => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();

    }


    /**选择库存车
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function choose_stock($ids = null)
    {
        $stock = Db::name("secondcar_rental_models_info")
            ->alias("i")
            ->join("models m", "i.models_id=m.id")
            ->where("status_data", ['=', 'send_the_car'], ['=', ''], 'or')
            ->field("i.id,m.name,i.licenseplatenumber,i.vin,i.engine_number,i.companyaccount,i.totalprices,i.Parkingposition,i.note")
            ->select();

        $this->view->assign([
            'stock' => $stock
        ]);

        if ($this->request->isPost()) {

            $id = input("post.id");

            $result = Db::name("second_full_order")
                ->where("id", $ids)
                ->update([
                    'second_car_id' => $id,
                    'review_the_data' => "for_the_car",
//                    'delivery_datetime' => time()
                ]);

            if($result){

                $source = Db::name('second_full_order')
                    ->where('id', $ids)
                    ->value('customer_source');

                if($source == 'introduce'){
                    $useful_info = Db::name('second_full_order')
                        ->where('id', $ids)
                        ->field('models_id,admin_id,introduce_name as referee_name,introduce_phone as referee_phone,introduce_card as referee_idcard,username as customer_name,phone as customer_phone')
                        ->find();
                    $useful_info['buy_way'] = '全款(二手车)';

                    Db::name('referee')->insert($useful_info);

                    $last_id = Db::name('referee')->getLastInsID();

                    Db::name('second_full_order')
                        ->where('id', $ids)
                        ->setField('referee_id', $last_id);
        
                }

                Db::name("secondcar_rental_models_info")
                    ->where("id", $id)
                    ->setField("status_data", 'the_car');

                $seventtime = \fast\Date::unixtime('month', -2);
                $fullsecondsales = [];
                
                $month = date("Y-m", $seventtime);
                $day = date('t', strtotime("$month +1 month -1 day"));
                for ($i = 0; $i < 4; $i++)
                {
                        $months = date("Y-m", $seventtime + (($i+1) * 86400 * $day));
                        $firstday = strtotime(date('Y-m-01', strtotime($month)));
                        $secondday = strtotime(date('Y-m-01', strtotime($months)));
                        
                        $fullsecondtake = Db::name('second_full_order')
                            ->where('review_the_data', 'for_the_car')
                            ->where('delivery_datetime', 'between', [$firstday, $secondday])
                            ->count();
                        
                        //销售一部
                        $fullsecondsales[$month] = $fullsecondtake;
                        //销售二部
                        
                        $month = date("Y-m", $seventtime + (($i+1) * 86400 * $day));
                                
                        $day = date('t', strtotime("$months +1 month -1 day"));
                
                
                }
                Cache::set('fullsecondsales', $fullsecondsales);
                

                //添加到违章表
                $peccancy = Db::name('second_full_order')
                    ->alias('po')
                    ->join('models m', 'po.models_id = m.id')
                    ->join('secondcar_rental_models_info ni', 'po.second_car_id = ni.id')
                    ->where('po.id', $ids)
                    ->field('po.username,po.phone,m.name as models,ni.licenseplatenumber as license_plate_number,ni.vin as frame_number,ni.engine_number')
                    ->find();

                $peccancy['car_type'] = 5;

                //检查是否存在
                $check_real = Db::name('violation_inquiry')
                    ->where('license_plate_number', $peccancy['license_plate_number'])
                    ->where('username', $peccancy['username'])
                    ->find();

                if(!$check_real){
                    $last_id = Db::name('violation_inquiry')->insertGetId($peccancy);

                    $result_peccancy = Db::name("second_full_order")
                        ->where('id', $ids)
                        ->setField('violation_inquiry_id', $last_id);
                }

                if ($result_peccancy) {
                    $this->success('', '', $ids);
                } else {
                    $this->error('违章信息添加失败');
                }

                $secondfull_info = Db::name('second_full_order')->where('id', $ids)->find();

                $models_name = Db::name('models')->where('id', $secondfull_info['models_id'])->value('name');
                $admin_name = Db::name('admin')->where('id', $secondfull_info['admin_id'])->value('nickname');

                //goeasy推送
//                $channel = "demo-secondfull_takecar";
//                $content = "你发起的客户：" . $secondfull_info['username'] . "对车型：" . $models_name . "的购买，已经提车";
//                goeary_push($channel, $content. "|" . $admin_id);

                //邮箱通知
                $data = secondfullsales_inform($models_name, $admin_name, $secondfull_info['username']);

                $email = new Email();

                $receiver = Db::name('admin')->where('id', $secondfull_info['admin_id'])->value('email');

                $result_s = $email
                    ->to($receiver)
                    ->subject($data['subject'])
                    ->message($data['message'])
                    ->send();

                if ($result_s) {
                    $this->success();
                } else {
                    $this->error('邮箱发送失败');
                }
            }
        }
        
        return $this->view->fetch();
    }


    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {



        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                try {
                    $params['delivery_datetime'] = strtotime($params['delivery_datetime']);

//                    pr($params);die();
                    $result = DB::name('second_full_order')
                    ->where('id',$ids)
                    ->update($params);
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                } catch (\think\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

}

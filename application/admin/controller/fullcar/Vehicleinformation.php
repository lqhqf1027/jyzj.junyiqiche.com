<?php

namespace app\admin\controller\fullcar;

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
class Vehicleinformation extends Backend
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

        $this->model = model('full_parment_order');

    }

    /**
     * 查看
     */
    public function index()
    {

        $this->loadlang('order/fullparmentorder');
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
        $this->model = model('full_parment_order');
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username', true);
            $total = $this->model
                ->with(['planfull' => function ($query) {
                    $query->withField('full_total_price');
                }, 'admin' => function ($query) {
                    $query->withField(['nickname', 'id', 'avatar']);
                }, 'models' => function ($query) {
                    $query->withField('name,models_name');
                }])
                ->where($where)
                ->where(function ($query) {
                    $query->where("car_new_inventory_id", null)
                        ->where("review_the_data", "is_reviewing_true");
                })
                ->order($sort, $order)
                ->count();


            $list = $this->model
                ->with(['planfull' => function ($query) {
                    $query->withField('full_total_price');
                }, 'admin' => function ($query) {
                    $query->withField(['nickname', 'id', 'avatar']);
                }, 'models' => function ($query) {
                    $query->withField('name,models_name');
                }])
                ->where($where)
                ->where(function ($query) {
                    $query->where("car_new_inventory_id", null)
                        ->where("review_the_data", "is_reviewing_true");
                })
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $row) {
                $row->visible(['id', 'order_no', 'username', 'createtime', 'phone', 'id_card', 'amount_collected', 'review_the_data']);
                $row->visible(['planfull']);
                $row->getRelation('planfull')->visible(['full_total_price']);
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

        $this->model = model('full_parment_order');
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username', true);
            $total = $this->model
                ->with(['planfull' => function ($query) {
                    $query->withField('full_total_price');
                }, 'admin' => function ($query) {
                    $query->withField('nickname');
                }, 'models' => function ($query) {
                    $query->withField('name,models_name');
                }, 'carnewinventory' => function ($query) {
                    $query->withField('frame_number,engine_number,licensenumber,household,4s_shop');
                }])
                ->where($where)
                ->where(function ($query) {
                    $query->where("car_new_inventory_id", "not null")
                        ->where("review_the_data", "for_the_car");
                })
                ->order($sort, $order)
                ->count();


            $list = $this->model
                ->with(['planfull' => function ($query) {
                    $query->withField('full_total_price');
                }, 'admin' => function ($query) {
                    $query->withField('nickname');
                }, 'models' => function ($query) {
                    $query->withField('name,models_name');
                }, 'carnewinventory' => function ($query) {
                    $query->withField('frame_number,engine_number,licensenumber,household,4s_shop');
                }])
                ->where($where)
                ->where(function ($query) {
                    $query->where("car_new_inventory_id", "not null")
                        ->where("review_the_data", "for_the_car");
                })
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $row) {
                $row->visible(['id', 'order_no', 'username', 'createtime','delivery_datetime', 'phone', 'id_card', 'amount_collected', 'review_the_data']);
                $row->visible(['planfull']);
                $row->getRelation('planfull')->visible(['full_total_price']);
                $row->visible(['admin']);
                $row->getRelation('admin')->visible(['nickname']);
                $row->visible(['models']);
                $row->getRelation('models')->visible(['name', 'models_name']);
                $row->visible(['carnewinventory']);
                $row->getRelation('carnewinventory')->visible(['frame_number', 'licensenumber', 'engine_number', 'household', '4s_shop']);

                if ($list[$k]['models']['models_name']) {
                    $list[$k]['models']['name'] = $list[$k]['models']['name'] . " " . $list[$k]['models']['models_name'];
                }

            }


            $list = collection($list)->toArray();

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
        $stock = Db::name("car_new_inventory")
            ->alias("i")
            ->join("crm_models m", "i.models_id=m.id")
            ->where("statuss", 1)
            ->field("i.id,m.name,i.licensenumber,i.frame_number,i.engine_number,i.household,i.4s_shop,i.note")
            ->select();

        $this->view->assign([
            'stock' => $stock
        ]);

        if ($this->request->isPost()) {

            $id = input("post.id");

            $result = Db::name("full_parment_order")
                ->where("id", $ids)
                ->update([
                    'car_new_inventory_id' => $id,
                    'review_the_data' => "for_the_car",
                ]);

            if($result){
                $source = Db::name('full_parment_order')
                    ->where('id', $ids)
                    ->value('customer_source');

                if($source == 'introduce'){
                    $useful_info = Db::name('full_parment_order')
                        ->where('id', $ids)
                        ->field('models_id,admin_id,introduce_name as referee_name,introduce_phone as referee_phone,introduce_card as referee_idcard,username as customer_name,phone as customer_phone')
                        ->find();
                    $useful_info['buy_way'] = '全款车';

                    Db::name('referee')->insert($useful_info);

                    $last_id = Db::name('referee')->getLastInsID();

                    Db::name('full_parment_order')
                        ->where('id', $ids)
                        ->setField('referee_id', $last_id);
                }

                Db::name("car_new_inventory")
                    ->where("id", $id)
                    ->setField("statuss", 0);

                $seventtime = \fast\Date::unixtime('month', -2);
                $fullsales = [];

                $month = date("Y-m", $seventtime);
                $day = date('t', strtotime("$month +1 month -1 day"));
                for ($i = 0; $i < 4; $i++)
                {
                    $months = date("Y-m", $seventtime + (($i+1) * 86400 * $day));
                    $firstday = strtotime(date('Y-m-01', strtotime($month)));
                    $secondday = strtotime(date('Y-m-01', strtotime($months)));
                    
                    $fulltake = Db::name('full_parment_order')
                        ->where('review_the_data', 'for_the_car')
                        ->where('delivery_datetime', 'between', [$firstday, $secondday])
                        ->count();
                    
                    //全款寻车销售情况
                    $fullsales[$month] = $fulltake;

                    $month = date("Y-m", $seventtime + (($i+1) * 86400 * $day));
                        
                    $day = date('t', strtotime("$months +1 month -1 day"));


                }
                Cache::set('fullsales', $fullsales);

                Db::name("car_new_inventory")
                    ->where("id", $id)
                    ->setField("statuss", 0);

                // 添加到违章表
                // $peccancy = DB::name('full_parment_order')
                //     ->alias('po')
                //     ->join('models m', 'po.models_id = m.id')
                //     ->join('car_new_inventory ni', 'po.car_new_inventory_id = ni.id')
                //     ->where('po.id', $ids)
                //     ->field('po.username,po.phone,m.name as models,ni.licensenumber as license_plate_number,ni.frame_number,ni.engine_number')
                //     ->find();

                // $peccancy['car_type'] = 3;

                // $result_peccancy = DB::name('violation_inquiry')->insert($peccancy);
                // if ($result_peccancy) {
                //     $this->success('', '', $ids);
                // } else {
                //     $this->error('违章信息添加失败');
                // }

                $full_info = Db::name('full_parment_order')->where('id', $ids)->find();

                $models_name = Db::name('models')->where('id', $full_info['models_id'])->value('name');
                $admin_name = Db::name('admin')->where('id', $full_info['admin_id'])->value('nickname');
                
                //goeasy推送
//                $channel = "demo-full_takecar";
//                $content = "你发起的客户：" . $full_info['username'] . "对车型：" . $models_name . "的购买，已经提车";
//                goeary_push($channel, $content. "|" . $admin_id);
                
                //邮箱通知
                $data = fullsales_inform($models_name, $admin_name, $full_info['usename']);

                $email = new Email();

                $receiver = Db::name('admin')->where('id', $full_info['admin_id'])->value('email');

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

            

            if ($result) {

                
            }


        }

        


        return $this->view->fetch();
    }

    /**查看详细资料 */
    public function show_order($ids = null)
    {
        $this->model = new \app\admin\model\FullParmentOrder;
        $row = $this->model->get($ids);

        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($row['admin_id']) {
            $row['sales_name'] = DB::name('admin')
                ->where('id', $row['admin_id'])
                ->value('nickname');
        }

        //身份证正反面（多图）
        $id_cardimages = explode(',', $row['id_cardimages']);

        foreach ($id_cardimages as $k => $v) {
            $id_cardimages[$k] = Config::get('upload')['cdnurl'] . $v;
        }
        //驾照正副页（多图）
        $drivers_licenseimages = explode(',', $row['drivers_licenseimages']);
        foreach ($drivers_licenseimages as $k => $v) {
            $drivers_licenseimages[$k] = Config::get('upload')['cdnurl'] . $v;
        }
        //申请表（多图）
        $application_formimages = explode(',', $row['application_formimages']);
        foreach ($application_formimages as $k => $v) {
            $application_formimages[$k] = Config::get('upload')['cdnurl'] . $v;
        }
        /**不必填 */
        //银行卡照（可多图）
        $bank_cardimages = $row['bank_cardimages'] == '' ? [] : explode(',', $row['bank_cardimages']);
        if ($bank_cardimages) {
            foreach ($bank_cardimages as $k => $v) {
                $bank_cardimages[$k] = Config::get('upload')['cdnurl'] . $v;
            }
        }
        //通话清单（文件上传）
        $call_listfiles = $row['call_listfiles'] == '' ? [] : explode(',', $row['call_listfiles']);
        foreach ($call_listfiles as $k => $v) {
            $call_listfiles[$k] = Config::get('upload')['cdnurl'] . $v;
        }

        $this->view->assign(
            [
                'row' => $row,
                'cdn' => Config::get('upload')['cdnurl'],
                'id_cardimages_arr' => $id_cardimages,
                'drivers_licenseimages_arr' => $drivers_licenseimages,
                'application_formimages_arr' => $application_formimages,
                'bank_cardimages_arr' => $bank_cardimages,
                'call_listfiles_arr' => $call_listfiles,
            ]
        );
        return $this->view->fetch();
    }


    /**查看订单表和库存表所有信息
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function show_order_and_stock($ids = null)
    {
        $this->model = new \app\admin\model\FullParmentOrder;
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }

        if ($row['admin_id']) {
            $row['sales_name'] = DB::name('admin')
                ->where('id', $row['admin_id'])
                ->value('nickname');
        }

        $data = DB::name('car_new_inventory')
            ->where('id', $row['car_new_inventory_id'])
            ->find();

        //身份证正反面（多图）
        $id_cardimages = explode(',', $row['id_cardimages']);
        foreach ($id_cardimages as $k => $v) {
            $id_cardimages[$k] = Config::get('upload')['cdnurl'] . $v;
        }
        //驾照正副页（多图）
        $drivers_licenseimages = explode(',', $row['drivers_licenseimages']);
        foreach ($drivers_licenseimages as $k => $v) {
            $drivers_licenseimages[$k] = Config::get('upload')['cdnurl'] . $v;
        }
        //申请表（多图）
        $application_formimages = explode(',', $row['application_formimages']);
        foreach ($application_formimages as $k => $v) {
            $application_formimages[$k] = Config::get('upload')['cdnurl'] . $v;
        }
        /**不必填 */
        //银行卡照（可多图）
        $bank_cardimages = $row['bank_cardimages'] == '' ? [] : explode(',', $row['bank_cardimages']);
        if ($bank_cardimages) {
            foreach ($bank_cardimages as $k => $v) {
                $bank_cardimages[$k] = Config::get('upload')['cdnurl'] . $v;
            }
        }
        //通话清单（文件上传）
        $call_listfiles = $row['call_listfiles'] == '' ? [] : explode(',', $row['call_listfiles']);
        foreach ($call_listfiles as $k => $v) {
            $call_listfiles[$k] = Config::get('upload')['cdnurl'] . $v;
        }
        $this->view->assign(
            [
                'row' => $row,
                'cdn' => Config::get('upload')['cdnurl'],
                'id_cardimages_arr' => $id_cardimages,
                'drivers_licenseimages_arr' => $drivers_licenseimages,
                'application_formimages_arr' => $application_formimages,
                'bank_cardimages_arr' => $bank_cardimages,
                'call_listfiles_arr' => $call_listfiles,
            ]
        );

//        $row['createtime'] = date("Y-m-d", $row['createtime']);
//        $row['delivery_datetime'] = date("Y-m-d", $row['delivery_datetime']);

        $this->view->assign($data);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);

        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
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

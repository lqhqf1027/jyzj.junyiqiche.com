<?php

namespace app\admin\controller\riskcontrol;

use app\common\controller\Backend;
use app\common\model\Config;
use think\Cache;
use think\Db;
use app\common\library\Email;
use app\admin\model\SecondcarRentalModelsInfo;

/**
 * 违章信息管理
 *
 * @icon fa fa-circle-o
 */
class Peccancy extends Backend
{

    /**
     * Inquiry模型对象
     * @var \app\admin\model\violation\Inquiry
     */
    protected $model = null;

    protected $noNeedRight = ['*'];

    protected static $keys = '217fb8552303cb6074f88dbbb5329be7';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\violation\Inquiry;
        $this->view->assign("carTypeList", $this->model->getCarTypeList());
    }

    /**
     * 查看
     */
    public function index()
    {

        return $this->view->fetch();
    }

    /**
     * 待发送给客服
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function prepare_send()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username,license_plate_number');
            $total = $this->model
                ->where($where)
                ->where(function ($query) {
                    $query->where('customer_status', 0);
                })
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->where(function ($query) {
                    $query->where('customer_status', 0);
                })
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();

            $real_list = array();

            foreach ($list as $k => $v) {
                if ($v['car_type'] == 4) {
                    $retiring = Db::name('rental_order')
                        ->where('violation_inquiry_id', $v['id'])
                        ->value('review_the_data');

                    if ($retiring && $retiring == 'retiring') {
                        continue;
                    }
                }

                $data = SecondcarRentalModelsInfo::select();
                foreach ( $data as $key => $value) {
                    if ($v['license_plate_number'] == $value['licenseplatenumber']) {
                        $v['strong_deadtime'] = strtotime($value['expirydate']);
                        $v['business_deadtime'] = strtotime($value['businessdate']);
                        $v['year_checktime'] = strtotime($value['annualverificationdate']);
                    }
                }

                $real_list[] = $v;
            }


            foreach ($real_list as $k => $v) {
                  switch ($v['car_type']){
                      case 1:
                         $order = Db::name('sales_order')
                          ->where('violation_inquiry_id',$v['id'])
                          ->value('id');

                         $real_list[$k]['order_id'] = $order;
                         break;
                      case 2:
                          $order = Db::name('second_sales_order')
                          ->where('violation_inquiry_id',$v['id'])
                          ->value('id');

                          $real_list[$k]['order_id'] = $order;
                          break;
                      case 3:

                          $order = Db::name('full_parment_order')
                          ->where('violation_inquiry_id',$v['id'])
                          ->value('id');

                          $real_list[$k]['order_id'] = $order;
                          break;
                      case 4:

                          $order = Db::name('rental_order')
                          ->where('violation_inquiry_id',$v['id'])
                          ->value('id');

                          $real_list[$k]['order_id'] = $order;
                          break;
                      case 5:
                          $order = Db::name('second_full_order')
                          ->where('violation_inquiry_id',$v['id'])
                          ->value('id');

                          $real_list[$k]['order_id'] = $order;
                          break;
                  }
            }



//            $result = array("total" => count($real_list), "rows" => $real_list);
            $result = array("total" => $total, "rows" => $real_list);

            return json($result);
        }
    }

    /**
     * 已发送给客服
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function already_send()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username,license_plate_number');
            $total = $this->model
                ->where($where)
                ->where(function ($query) {
                    $query->where('customer_status', 1);
                })
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->where(function ($query) {
                    $query->where('customer_status', 1);
                })
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $real_list = array();

            foreach ($list as $k => $v) {
                if ($v['car_type'] == 4) {
                    $retiring = Db::name('rental_order')
                        ->where('violation_inquiry_id', $v['id'])
                        ->value('review_the_data');

                    if ($retiring && $retiring == 'retiring') {
                        continue;
                    }
                }

                $real_list[] = $v;
            }


            $result = array("total" => count($real_list), "rows" => $real_list);

            return json($result);
        }
    }

    /**
     * 请求第三方接口获取违章信息
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function sendMessage()
    {
        if ($this->request->isAjax()) {

            $params = $this->request->post()['ids'];

            $finals = [];
            $keys = self::$keys;
            foreach ($params as $k => $v) {
                //获取城市前缀接口
                $result = gets("http://v.juhe.cn/sweizhang/carPre.php?key=" . $keys . "&hphm=" . urlencode($v['hphm']));

                if ($result['error_code'] == 0) {

                    $field = array();

                    $data = gets("http://v.juhe.cn/sweizhang/query?city=" . $result['result']['city_code'] . "&hphm=" . urlencode($v['hphms']) . "&engineno=" . $v['engineno'] . "&classno=" . $v['classno'] . "&key=" . $keys);

                    if ($data['error_code'] == 0) {

                        $total_fraction = 0;     //总扣分
                        $total_money = 0;        //总罚款
                        $flag = -1;
                        if ($data['result']['lists']) {
                            foreach ($data['result']['lists'] as $key => $value) {
                                if ($value['fen']) {
                                    $value['fen'] = floatval($value['fen']);

                                    $total_fraction += $value['fen'];
                                }

                                if ($value['money']) {
                                    $value['money'] = floatval($value['money']);

                                    $total_money += $value['money'];
                                }

                                if ($value['handled'] == 0) {
                                    $flag = -2;
                                }

                            }
                            $field['peccancy_detail'] = json_encode($data['result']['lists']);
                        }

                        $flag == -2 ? $field['peccancy_status'] = 2 : $field['peccancy_status'] = 1;

                        $field['total_deduction'] = $total_fraction;
                        $field['total_fine'] = $total_money;
                        $field['final_time'] = time();


                        Db::name('violation_inquiry')
                            ->where('license_plate_number', $v['hphms'])
                            ->setInc('query_times');


                        Db::name('violation_inquiry')
                            ->where('license_plate_number', $v['hphms'])
                            ->update($field);

                        array_push($finals, $data);
                    } else {
                        $this->error($data['reason'], '', $data);
                    }
                } else {
                    $this->error($result['reason'], '', $result);
                }

            }

            $this->success('', '', $finals);
        }
    }


    /**
     * 单个请求第三方接口获取违章信息
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function sendMessagePerson()
    {
        if ($this->request->isAjax()) {

            $params = $this->request->post()['ids'];
            $params = $params[0];
            $finals = [];
            $keys = self::$keys;


            //获取城市前缀接口
            $result = gets("http://v.juhe.cn/sweizhang/carPre.php?key=" . $keys . "&hphm=" . urlencode($params['hphm']));

            if ($result['error_code'] == 0) {

                $field = array();


                $data = gets("http://v.juhe.cn/sweizhang/query?city=" . $result['result']['city_code'] . "&hphm=" . urlencode($params['hphms']) . "&engineno=" . $params['engineno'] . "&classno=" . $params['classno'] . "&key=" . $keys);


                if ($data['error_code'] == 0) {

                    $total_fraction = 0;     //总扣分
                    $total_money = 0;        //总罚款
                    $flag = -1;
                    if ($data['result']['lists']) {
                        foreach ($data['result']['lists'] as $key => $value) {
                            if ($value['fen']) {
                                $value['fen'] = floatval($value['fen']);

                                $total_fraction += $value['fen'];
                            }

                            if ($value['money']) {
                                $value['money'] = floatval($value['money']);

                                $total_money += $value['money'];
                            }

                            if ($value['handled'] == 0) {
                                $flag = -2;
                            }

                        }
                        $field['peccancy_detail'] = json_encode($data['result']['lists']);
                    }

                    $flag == -2 ? $field['peccancy_status'] = 2 : $field['peccancy_status'] = 1;

                    $field['total_deduction'] = $total_fraction;
                    $field['total_fine'] = $total_money;
                    $field['final_time'] = time();


                    Db::name('violation_inquiry')
                        ->where('license_plate_number', $params['hphms'])
                        ->setInc('query_times');


                    Db::name('violation_inquiry')
                        ->where('license_plate_number', $params['hphms'])
                        ->update($field);

                    array_push($finals, $data);
                } else {
                    $this->error($data['reason'], '', $data);
                }
            } else {
                $this->error($result['reason'], '', $result);
            }


            $this->success('', '', $finals);
        }
    }

    /**
     * 查看违章详情
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function details($ids = null)
    {
        $detail = Db::name('violation_inquiry')
            ->where('id', $ids)
            ->field('username,phone,peccancy_detail,total_deduction,total_fine')
            ->find();

        $details = json_decode($detail['peccancy_detail'], true);


        $score = 0;
        $money = 0;
        $no_handle = array();
        $handle = array();
        foreach ($details as $k => $v) {
            if ($v['handled'] == 0) {
                $score += floatval($v['fen']);
                $money += floatval($v['money']);
                array_push($no_handle, $v);
            } else {
                array_push($handle, $v);
            }
        }


        $details = array_merge($no_handle, $handle);

        $this->view->assign([
            'detail' => $details,
            'phone' => $detail['phone'],
            'username' => $detail['username'],
            'total_fine' => $money,
            'total_deduction' => $score
        ]);

        return $this->view->fetch();
    }

    public function note()
    {
        if ($this->request->isAjax()) {
            $content = input('content');
            $ids = input('ids');

            $res = Db::name('violation_inquiry')
                ->where('id', $ids)
                ->setField('inquiry_note', $content);

            if ($res) {
                $this->success('', '', 'success');
            } else {
                $this->error('备注失败');
            }
        }
    }

    /**
     * 发送给客服
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function sendCustomer()
    {
        if ($this->request->isAjax()) {

            $ids = input("ids");
            $ids = json_decode($ids, true);
            $res = Db::name('violation_inquiry')
                ->where('id', 'in', $ids)
                ->update([
                    'customer_status' => 1,
                    'customer_time' => time()

                ]);

            if ($res) {

//                $channel = 'send_peccancy';
//                $content = '有新的客户违章信息进入，请注意查看';
//                goeary_push($channel, $content);

                $data = send_peccancy();

                $address = Db::name('admin')
                    ->where('rule_message', 'message25')
                    ->field('email')
                    ->find()['email'];

                $email = new Email;
                $send = $email
                    ->to($address)
                    ->subject($data['subject'])
                    ->message($data['message'])
                    ->send();

                if ($send) {
                    $this->success('', '', $res);

                } else {
                    $this->error('邮箱发送失败');
                }


            } else {
                $this->error();
            }

        }
    }


    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);
        $data = SecondcarRentalModelsInfo::select();
        foreach ( $data as $key => $value) {
            if ($row['license_plate_number'] == $value['licenseplatenumber']) {
                $row['strong_deadtime'] = strtotime($value['expirydate']);
                $row['business_deadtime'] = strtotime($value['businessdate']);
                $row['year_checktime'] = strtotime($value['annualverificationdate']);
            }
        }
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

            // pr($params);
            // die;
            if ($params['strong_deadtime']) {

                SecondcarRentalModelsInfo::where('licenseplatenumber', $params['license_plate_number'])->setField(['expirydate' => $params['strong_deadtime']]);

                $params['strong_deadtime'] = strtotime($params['strong_deadtime']);

            }

            if ($params['business_deadtime']) {
            
                SecondcarRentalModelsInfo::where('licenseplatenumber', $params['license_plate_number'])->setField(['businessdate' => $params['business_deadtime']]);
                
                $params['business_deadtime'] = strtotime($params['business_deadtime']);
            }

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
                        $this->success('','',5);
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
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }


    /**
     * 更改保险状态
     */
    public function insurance()
    {

        if ($this->request->isAjax()) {


            $status = $this->request->post('status');

            $ids = $this->request->post('id');

//            if (Cache::get('insurance_flag' . $ids)) {
//                return;
//            } else {
//                Cache::set('insurance_flag' . $ids, 1, 3600 * 6);
//            }

            $status = json_decode($status, true);


            if ($status[0] == -1 && $status[1] == -1) {
                $this->modify($ids, [0, 0]);
            } else if ($status[0] == -1 && $status[1] == -2) {
                $this->modify($ids, [0, 1]);
            } else if ($status[0] == -2 && $status[1] == -2) {
                $this->modify($ids, [1, 1]);
            } else if ($status[0] == -2 && $status[1] == -1) {
                $this->modify($ids, [1, 0]);
            } else if ($status[0] == -3 && $status[1] == -1) {
                $this->modify($ids, [2, 0]);
            } else if ($status[0] == -3 && $status[1] == -2) {
                $this->modify($ids, [2, 1]);
            } else if ($status[0] == -3 && $status[1] == -3) {
                $this->modify($ids, [2, 2]);
            } else if ($status[0] == -1 && $status[1] == -3) {
                $this->modify($ids, [0, 2]);
            } else if ($status[0] == -2 && $status[1] == -3) {
                $this->modify($ids, [1, 2]);
            }

        }
    }

    /**
     * 更改
     * @param $id
     * @param $num
     */
    public function modify($id, $num = array())
    {
        Db::name('violation_inquiry')
            ->where('id', $id)
            ->update([
                'strong_status' => $num[0],
                'business_status' => $num[1]
            ]);
    }


    /**
     * 编辑年检时间
     */
    public function check_year()
    {
        if ($this->request->isAjax()) {
            $date = input('date');
            
            $id = input('id');

            $licenseplatenumber = $this->model->where('id', $id)->value('license_plate_number');
            
            $result = SecondcarRentalModelsInfo::where('licenseplatenumber', $licenseplatenumber)->setField(['annualverificationdate' => $date]);

            $date = strtotime($date);
            $res = $this->model ->where('id', $id)->update(['year_checktime' => $date]);

            // pr($res);
            // pr($result);
            // die;

            if ($res !== false && $result !== false) {
                $this->success();
            } else {
                $this->error('年检更新失败');

            }

        }
    }

    /**
     * 存入年检状态
     */
    public function year_status()
    {
        if ($this->request->isAjax()) {
            $status = $this->request->post('status');

            $ids = $this->request->post('id');

//            if (Cache::get('year' . $ids)) {
//                return;
//            } else {
//                Cache::set('year' . $ids, '1', 3600 * 6);
//            }

            Db::name('violation_inquiry')
                ->where('id', $ids)
                ->setField('year_status', $status);

        }
    }

    /**
     * 统计
     * @return array
     */
    public function totals()
    {

            $peccancy = $this->model->where('peccancy_status', 2)->count();    //有违章

            $year_inspect = $this->model->where('year_status', -2)->count();   //即将年检

            $year_overdue = $this->model->where('year_status', -3)->count();   //年检已过期

            $strong = $this->model->where('strong_status', 1)->count();        //交强险即需续保

            $strong_overdue = $this->model->where('strong_status', 2)->count();//交强险续保过期

            $business = $this->model->where('business_status', 1)->count();    //商业险即需续保

            $business_overdue = $this->model->where('business_status', 2)->count();//商业险续保过期

            return [
                'peccancy'=>$peccancy,
                'year_inspect'=>$year_inspect,
                'year_overdue'=>$year_overdue,
                'strong'=>$strong,
                'strong_overdue'=>$strong_overdue,
                'business'=>$business,
                'business_overdue'=>$business_overdue
            ];

    }

}

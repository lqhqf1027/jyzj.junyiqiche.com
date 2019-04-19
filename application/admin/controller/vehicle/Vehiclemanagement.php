<?php

namespace app\admin\controller\vehicle;

use app\admin\model\Order;
use app\admin\model\OrderDetails;
use app\common\controller\Backend;
use fast\Date;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;
use think\Session;
use Endroid\QrCode\QrCode;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class Vehiclemanagement extends Backend
{

    /**
     * Vehiclemanagement模型对象
     * @var \app\admin\model\vehicle\Vehiclemanagement
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Order();
        $this->view->assign("customerSourceList", $this->model->getCustomerSourceList());
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("liftCarStatusList", $this->model->getLiftCarStatusList());

        $this->view->assign([
            'total_violation' => OrderDetails::where('is_it_illegal', 'violation_of_regulations')->count('id')
        ]);

//        $query_today = OrderDetails::whereTime('last_update_check_time', 'today')->find();

        $check_time = collection(OrderDetails::field('id,annual_inspection_time,traffic_force_insurance_time,business_insurance_time,annual_inspection_status,traffic_force_insurance_status,business_insurance_status')->where('annual_inspection_status|traffic_force_insurance_status|business_insurance_status', 'neq', 'no_queries')->select())->toArray();

//        pr($check_time);die;
        $times = time();

//        $config_time = strtotime(Db::name('config')->where('name', 'last_update_time')->value('value'));
//
//        if ($config_time > strtotime(date('Ymd')) && $config_time < (strtotime(date('Ymd')) + 86400)) {
//
//        }


        foreach ($check_time as $value) {

            $year_status = $traffic_force_status = $business_status = 'no_queries';
            if ($value['annual_inspection_status'] != 'no_queries') {
                $year_status = self::check_state($value['annual_inspection_time']);
            }

            if ($value['traffic_force_insurance_status'] != 'no_queries') {
                $traffic_force_status = self::check_state($value['traffic_force_insurance_time']);
            }

            if ($value['business_insurance_status'] != 'no_queries') {
                $business_status = self::check_state($value['business_insurance_time']);
            }

            OrderDetails::update([
                'id' => $value['id'],
                'annual_inspection_status' => $year_status,
                'traffic_force_insurance_status' => $traffic_force_status,
                'business_insurance_status' => $business_status,
//                'last_update_check_time' => $times
            ]);
        }

    }

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;

        $this->searchFields = 'username';
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with(['orderdetails', 'admin'])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['orderdetails', 'admin'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $row) {
                $row->visible(['id', 'username', 'avatar', 'phone', 'id_card', 'models_name', 'payment', 'monthly', 'nperlist', 'end_money', 'tail_money', 'margin', 'createtime', 'type', 'lift_car_status', 'user_id']);
                $row->visible(['orderdetails']);
                $row->getRelation('orderdetails')->visible(['file_coding', 'signdate', 'total_contract', 'hostdate', 'licensenumber', 'frame_number', 'engine_number', 'is_mortgage', 'mortgage_people', 'ticketdate', 'supplier', 'tax_amount', 'no_tax_amount', 'pay_taxesdate', 'purchase_of_taxes', 'house_fee', 'luqiao_fee', 'insurance_buydate', 'insurance_policy', 'insurance', 'car_boat_tax', 'commercial_insurance_policy', 'business_risks', 'subordinate_branch', 'transfer_time', 'is_it_illegal', 'annual_inspection_time', 'traffic_force_insurance_time', 'business_insurance_time', 'annual_inspection_status', 'traffic_force_insurance_status', 'business_insurance_status']);
                $row->visible(['admin']);
                $row->getRelation('admin')->visible(['nickname', 'avatar']);
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }

        return $this->view->fetch();
    }

    /**
     * 确认提车
     * @param null $ids
     * @return string
     * @throws Exception
     * @throws \think\exception\DbException
     */


    /**
     * 生成二维码,后台展示授权码
     * @throws \Endroid\QrCode\Exceptions\ImageTypeInvalidException
     */
    public function setqrcode()
    {

        if ($this->request->isAjax()) {
            $params = $this->request->post();
            $authorization_img = Order::get($params['order_id'])->authorization_img;
            if ($authorization_img) $this->success('创建成功', '', $authorization_img);
            $time = date('YmdHis');
            $qrCode = new QrCode();
            $qrCode->setText($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/?order_id=' . $params['order_id'])
                ->setSize(150)
                ->setPadding(10)
                ->setErrorCorrection('high')
                ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
                ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
                ->setLabel('需由客户 ' . $params['username'] . ' 扫码授权')
                ->setLabelFontPath(VENDOR_PATH . 'endroid/qr-code/assets/font/MSYHBD.TTC')
                ->setLabelFontSize(10)
                ->setImageType(\Endroid\QrCode\QrCode::IMAGE_TYPE_PNG);
            $fileName = DS . 'uploads' . DS . 'qrcode' . DS . $time . '_' . 'o_id' . $params['order_id'] . '.png';
            $qrCode->save(ROOT_PATH . 'public' . $fileName);
            if ($qrCode) {
                Order::update(['id' => $params['order_id'], 'authorization_img' => $fileName]) ? $this->success('创建成功', '', $fileName) : $this->error('创建失败');
            }
            $this->error('未知错误');
        }
    }

    public function edit($ids = null)
    {

        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $order_details = new OrderDetails();
                    $order_details->allowField(true)->save($params, ['id' => OrderDetails::getByOrder_id($ids)->id]);
                    $row->lift_car_status = 'yes';
                    $result = $row->save();
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result) {
                    $this->success();
                } else {
                    $this->error();
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign('type', $row->type);
        return $this->view->fetch();
    }


    /**
     * 修改资料
     * @param null $ids
     * @return string
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function modifying_data($ids = null)
    {
        $row = OrderDetails::getByOrder_id($ids);

        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $params['annual_inspection_time'] = $params['annual_inspection_time'] ? strtotime($params['annual_inspection_time']) : null;
                    $params['traffic_force_insurance_time'] = $params['traffic_force_insurance_time'] ? strtotime($params['traffic_force_insurance_time']) : null;
                    $params['business_insurance_time'] = $params['business_insurance_time'] ? strtotime($params['business_insurance_time']) : null;
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }

                //修改各种日期的状态
                $year_status = $traffic_force_status = $business_status = 'no_queries';
                if ($params['annual_inspection_time']) {
                    $year_status = self::check_state($params['annual_inspection_time']);
                }
                if ($params['traffic_force_insurance_time']) {
                    $traffic_force_status = self::check_state($params['traffic_force_insurance_time']);
                }
                if ($params['business_insurance_time']) {
                    $business_status = self::check_state($params['business_insurance_time']);
                }
                $row->annual_inspection_status = $year_status;
                $row->traffic_force_insurance_status = $traffic_force_status;
                $row->business_insurance_status = $business_status;
                $row->save();
                $this->success();

            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign([
            "row" => $row,
            'type' => Order::get($ids)->type,
            'mortgageList' => ['是' => '是', '否' => '否']
        ]);
        return $this->view->fetch();
    }

    /**
     * 检查状态
     * @param $checktime
     * @return string
     */
    public static function check_state($checktime)
    {
        $time = time();
        $last_month = date('Y-m-d', $checktime);
        $last_month = strtotime("{$last_month} -1 month");
        $status_year = '';
        if ($time < $last_month) {
            $status_year = 'normal';
        } else if ($time > $last_month && $time < $checktime) {
            $status_year = 'soon';
        } else if ($time > $checktime) {
            $status_year = 'overdue';
        }

        return $status_year;
    }

    /**
     * 查看客户信息
     * @param null $ids
     * @return string
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function view_information($ids = null)
    {
        $row = $this->model->with(['orderimg'])->find($ids);

        $order = new \app\admin\model\Order;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $this->success();
        }
        $car_type_list = $order->getTypeList();

        $car_type = '';
        foreach ($car_type_list as $k => $v) {
            if ($row['type'] == $k) {
                $car_type = $v;
                break;
            }
        }
        $this->view->assign([
            "row" => $row,
            'customerSourceList' => $order->getCustomerSourceList(),
            "genderdataList" => $order->getGenderdataList(),
            "nperlistList" => $this->getNperlistList(),
            'car_type' => $car_type
        ]);
        return $this->view->fetch();
    }

    public function getNperlistList()
    {
        return ['12' => __('12'), '24' => __('24'), '36' => __('36'), '48' => __('48'), '60' => __('60')];
    }

    /**
     * 请求第三方接口获取违章信息
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
            $keys = '217fb8552303cb6074f88dbbb5329be7';
            $order_details = new OrderDetails();
            $query_record = [];
            $error_num = $success_num = 0;
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
                            $record = [];
                            foreach ($data['result']['lists'] as $key => $value) {
                                if ($value['handled'] == 0) {
                                    $flag = -2;
                                } else if ($value['handled'] == 1) {
                                    continue;
                                }
                                if ($value['fen']) {
                                    $value['fen'] = floatval($value['fen']);

                                    $total_fraction += $value['fen'];
                                }

                                if ($value['money']) {
                                    $value['money'] = floatval($value['money']);

                                    $total_money += $value['money'];
                                }

                                $record[] = $value;

                            }
                            $field['violation_details'] = $record ? json_encode($record) : null;

                            $field['is_it_illegal'] = $flag == -2 ? 'violation_of_regulations' : 'no_violation';

                        } else {
                            $field['is_it_illegal'] = 'no_violation';
                        }

                        $field['total_deduction'] = $total_fraction;
                        $field['total_fine'] = $total_money;

                        $order_details->allowField(true)->save($field, ['id' => OrderDetails::getByOrder_id($v['order_id'])->id]);
                        $query_record[] = ['username' => $v['username'], 'license_plate_number' => $v['hphms'], 'status' => 'success', 'msg' => '-', 'is_it_illegal' => $field['is_it_illegal'] == 'violation_of_regulations' ? '有' : '无', 'total_deduction' => $total_fraction, 'total_fine' => $total_money];
                        $success_num++;
                    } else {
//                        $this->error("客户姓名为<b>{$v['username']}</b>的用户：".$data['reason'], '', $data);
                        $query_record[] = ['username' => $v['username'], 'license_plate_number' => $v['hphms'], 'status' => 'error', 'msg' => $data['reason'], 'is_it_illegal' => '-', 'total_deduction' => '-', 'total_fine' => '-'];
                        $error_num++;
                    }
                } else {
                    $query_record[] = ['username' => $v['username'], 'license_plate_number' => $v['hphms'], 'status' => 'error', 'msg' => $result['reason'], 'is_it_illegal' => '-', 'total_deduction' => '-', 'total_fine' => '-'];
                    $error_num++;
                }
//                else {
//                    $this->error("客户姓名为<b>{$v['username']}</b>的用户：".$result['reason'], '', $result);
//                }
            }


            $this->success('', '', ['error_num' => $error_num, 'success_num' => $success_num, 'query_record' => $query_record]);
        }
    }


    public function violation_details($ids = null)
    {
//        $detail = Db::name('violation_inquiry')
//            ->where('id', $ids)
//            ->field('username,phone,peccancy_detail,total_deduction,total_fine')
//            ->find();

        $detail = $this->model->field('username,phone')
            ->with(['orderdetails' => function ($q) {
                $q->withField('total_deduction,total_fine,violation_details');
            }])->find($ids);

        $details = json_decode($detail['orderdetails']['violation_details'], true);


//        $score = 0;
//        $money = 0;
//        $no_handle = array();
//        $handle = array();
//        foreach ($details as $k => $v) {
//            if ($v['handled'] == 0) {
//                $score += floatval($v['fen']);
//                $money += floatval($v['money']);
//                array_push($no_handle, $v);
//            } else {
//                array_push($handle, $v);
//            }
//        }
//
//
//        $details = array_merge($no_handle, $handle);

        $this->view->assign([
            'detail' => $details,
            'phone' => $detail['phone'],
            'username' => $detail['username'],
            'total_fine' => $detail['orderdetails']['total_fine'],
            'total_deduction' => $detail['orderdetails']['total_deduction']
        ]);

        return $this->view->fetch();
    }


    public function accredit()
    {
        return 123;
    }

}

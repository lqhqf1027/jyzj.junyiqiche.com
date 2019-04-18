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
                $row->visible(['id', 'username', 'phone', 'id_card', 'models_name', 'payment', 'monthly', 'nperlist', 'end_money', 'tail_money', 'margin', 'createtime', 'type', 'lift_car_status']);
                $row->visible(['orderdetails']);
                $row->getRelation('orderdetails')->visible(['file_coding', 'signdate', 'total_contract', 'hostdate', 'licensenumber', 'frame_number', 'engine_number', 'is_mortgage', 'mortgage_people', 'ticketdate', 'supplier', 'tax_amount', 'no_tax_amount', 'pay_taxesdate', 'purchase_of_taxes', 'house_fee', 'luqiao_fee', 'insurance_buydate', 'insurance_policy', 'insurance', 'car_boat_tax', 'commercial_insurance_policy', 'business_risks', 'subordinate_branch', 'transfer_time', 'is_it_illegal']);
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

                    $time = time();
                    if($params['annual_inspection_time']){
                        $last_month = date('Y-m-d',$params['annual_inspection_time']);
                        $last_month = strtotime("{$last_month} -1 month");
                        $status_year = '';
                        if($time<$last_month){
                            $status_year = 'normal';
                        }else if($time>$last_month && $time<$params['annual_inspection_time']){
                            $status_year = 'soon';
                        }else if($time>$params['annual_inspection_time']){
                            $status_year = 'overdue';
                        }
                    }

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


    public static function check_state($checktime)
    {
        
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
                        $query_record[] = ['username' => $v['username'], 'license_plate_number' => $v['hphms'], 'status' => 'success', 'msg' => ''];
                        $success_num++;
                    } else {
//                        $this->error("客户姓名为<b>{$v['username']}</b>的用户：".$data['reason'], '', $data);
                        $query_record[] = ['username' => $v['username'], 'license_plate_number' => $v['hphms'], 'status' => 'error', 'msg' => $data['reason']];
                        $error_num++;
                    }
                } else {
                    $query_record[] = ['username' => $v['username'], 'license_plate_number' => $v['hphms'], 'status' => 'error', 'msg' => $result['reason']];
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
}

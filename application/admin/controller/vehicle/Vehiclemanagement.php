<?php

namespace app\admin\controller\vehicle;

use app\admin\model\Order;
use app\admin\model\OrderDetails;
use app\common\controller\Backend;
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
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


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
            $a = null;
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with(['orderdetails'])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['orderdetails'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $row) {
                $row->visible(['id', 'username','avatar', 'phone', 'id_card', 'models_name', 'payment', 'monthly', 'nperlist', 'end_money', 'tail_money', 'margin', 'createtime', 'type', 'lift_car_status']);
                $row->visible(['orderdetails']);
                $row->getRelation('orderdetails')->visible(['file_coding', 'signdate', 'total_contract', 'hostdate', 'licensenumber', 'frame_number', 'engine_number', 'is_mortgage', 'mortgage_people', 'ticketdate', 'supplier', 'tax_amount', 'no_tax_amount', 'pay_taxesdate', 'purchase_of_taxes', 'house_fee', 'luqiao_fee', 'insurance_buydate', 'insurance_policy', 'insurance', 'car_boat_tax', 'commercial_insurance_policy', 'business_risks', 'subordinate_branch', 'transfer_time','is_it_illegal']);
$a = $row['type'];
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list,'types'=>$a);
Session::set('types',$a);

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

        if($this->request->isAjax()){
            $params = $this->request->post();
//            pr(VENDOR_PATH);DIE;
//            pr(ROOT_PATH.'vendor/endroid'.DS.'qr-code'.DS.'assets'.DS.'font'.DS.'MSYHBD.TTC');die;
//            return ROOT_PATH.DS.'endroid'.DS.'qr-code'.DS.'assets'.DS.'font'.DS.'MSYHBD.TTC';
            $time = date('YmdHis');
            $qrCode = new QrCode();
            $qrCode->setText($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/?order_id=' . $params['order_id'] )
                ->setSize(150)
                ->setPadding(10)
                ->setErrorCorrection('high')
                ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
                ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
                ->setLabel('需由客户 '.$params['username'].' 扫码授权')
                ->setLabelFontPath(VENDOR_PATH.'endroid/qr-code/assets/font/MSYHBD.TTC')
                ->setLabelFontSize(10)
                ->setImageType(\Endroid\QrCode\QrCode::IMAGE_TYPE_PNG);
            $fileName = DS . 'uploads' . DS . 'qrcode' . DS . $time . '_' . 'o_id'.$params['order_id'] . '.png';
            $qrCode->save(ROOT_PATH . 'public' . $fileName);
            if ($qrCode) {
                Order::update(['id' => $params['order_id'], 'authorization_img' => $fileName]) ? $this->success('创建成功', $fileName) : $this->error('创建失败');
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
//        pr($row->toArray());die;
//            $this->model->get($ids);
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
                if ($result) {
                    $this->success();
                } else {
                    $this->error();
                }
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
            $keys = '217fb8552303cb6074f88dbbb5329be7';

            //获取城市前缀接口
            $result = gets("http://v.juhe.cn/sweizhang/carPre.php?key=" . $keys . "&hphm=" . urlencode($params['hphm']));
//pr($result);die;
            if ($result['error_code'] == 0) {

                $field = array();


                $data = gets("http://v.juhe.cn/sweizhang/query?city=" . $result['result']['city_code'] . "&hphm=" . urlencode($params['hphms']) . "&engineno=" . $params['engineno'] . "&classno=" . $params['classno'] . "&key=" . $keys);

//pr($data);die;
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
                        $field['violation_details'] = $record?json_encode($record):null;

                        $field['is_it_illegal'] = $flag == -2 ? 'violation_of_regulations' : 'no_violation';

                    } else {
                        $field['is_it_illegal'] = 'no_violation';
                    }

                    $field['total_deduction'] = $total_fraction;
                    $field['total_fine'] = $total_money;

                    $order_details = new OrderDetails();

                    $order_details->allowField(true)->save($field,['id'=>OrderDetails::getByOrder_id($params['order_id'])->id]);
                } else {
                    $this->error($data['reason'], '', $data);
                }
            } else {
                $this->error($result['reason'], '', $result);
            }


            $this->success('', '', $finals);
        }
    }

    public function violation_details($ids = null)
    {
//        $detail = Db::name('violation_inquiry')
//            ->where('id', $ids)
//            ->field('username,phone,peccancy_detail,total_deduction,total_fine')
//            ->find();

        $detail = $this->model->field('username,phone')
            ->with(['orderdetails'=>function ($q){
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

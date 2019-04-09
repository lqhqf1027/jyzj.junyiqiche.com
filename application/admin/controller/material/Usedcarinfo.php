<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/8/20
 * Time: 11:53
 */

namespace app\admin\controller\material;


use app\common\controller\Backend;
use think\Db;
use think\Config;

class Usedcarinfo extends Backend
{
    /**
     * DriverInfo模型对象
     * @var \app\admin\model\DriverInfo
     */
    protected $model = null;
    protected $noNeedRight = ['index','car_information','edit','details','data_warehousing','edit_dataware'];

    public function _initialize()
    {
        parent::_initialize();

        $this->loadlang('material/mortgageregistration');
        $this->loadlang('newcars/newcarscustomer');
        $this->loadlang('order/salesorder');

        $this->model = new \app\admin\model\SecondSalesOrder();
    }

    public function index()
    {
        return $this->view->fetch();
    }


    /**二手车购车信息登记表
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function car_information()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {

            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams("secondcarrentalmodelsinfo.vin", true);
            $total = $this->model
                ->with(['mortgageregistration',
                    'admin' => function ($query) {
                        $query->withField(['nickname','id','avatar']);
                    }, 'secondcarrentalmodelsinfo' => function ($query) {
                        $query->withField('newpayment,monthlypaymen,periods,bond,tailmoney,licenseplatenumber,vin');
                    }, 'models' => function ($query) {
                        $query->withField('name');
                    }])
                ->where(function ($query){
                    $query->where('mortgage_registration_id','not null');
                })
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['mortgageregistration',
                    'admin' => function ($query) {
                        $query->withField(['nickname','id','avatar']);
                    }, 'secondcarrentalmodelsinfo' => function ($query) {
                        $query->withField('newpayment,monthlypaymen,periods,bond,tailmoney,licenseplatenumber,vin');
                    }, 'models' => function ($query) {
                        $query->withField('name');
                    }])
                ->where(function ($query){
                    $query->where('mortgage_registration_id','not null');
                })
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            foreach ($list as $k=>$v){
                $department = Db::name('auth_group_access')
                    ->alias('a')
                    ->join('auth_group b','a.group_id = b.id')
                    ->where('a.uid',$v['admin']['id'])
                    ->value('b.name');
                $list[$k]['admin']['department'] = $department;
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {

        $helpful = Db::name("second_sales_order")
            ->where("id", $ids)
            ->field("mortgage_registration_id,credit_reviewimages,plan_car_second_name")
            ->find();

        $row = array();


        if ($helpful['mortgage_registration_id']) {
            $row = Db::name("mortgage_registration")
                ->where("id", $helpful['mortgage_registration_id'])
                ->find();
        }

        if ($helpful['credit_reviewimages']) {
            $row['credit_reviewimages'] = $helpful['credit_reviewimages'];
        }

        if ($helpful['plan_car_second_name']) {
            $row['plan_car_second_name'] = Db::name("secondcar_rental_models_info")
                ->where("id", $helpful['plan_car_second_name'])
                ->find();
        }


        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");

            $mortgage = $this->request->post("mortgage");

            $credit = $this->request->post("credit_reviewimages");

            $info = $this->request->post("info/a");

            if(!$params['hostdate']){
                $params['hostdate'] = null;
            }

            if(!$params['ticketdate']){
                $params['ticketdate'] = null;
            }


            if(!$info['lending_date']){
                $info['lending_date'] = null;
            }

            if(!$params['pay_taxesdate']){
                $params['pay_taxesdate'] = null;
            }

            if(!$params['insurance_buydate']){
                $params['insurance_buydate'] = null;
            }

            if(!$params['next_inspection']){
                $params['next_inspection'] = null;
            }



            Db::name("second_sales_order")
                ->where("id", $ids)
                ->setField("credit_reviewimages", $credit);

            Db::name("secondcar_rental_models_info")
                ->where("id", $helpful['plan_car_second_name'])
                ->update($info);

            if (!$mortgage) {
                $params['mortgage_people'] = null;
            }

            if (!$params['transfer']) {
                $params['transferdate'] = null;
            }

            if ($params['next_inspection']) {

                //自动根据年检日期得到年检的时间段
                $date = $params['next_inspection'];

                $first_day = date("Y-m-01",strtotime("-1 month",strtotime($date)));

                $last_date = date("Y-m-01",strtotime($date));

                $last_date = date("Y-m-d",strtotime("-1 day",strtotime($last_date)));

                $params['year_range'] = $first_day . ";" . $last_date;
            }

            if ($params) {
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }

                    if ($helpful['mortgage_registration_id']) {
                        $result = Db::name("mortgage_registration")
                            ->where("id", $helpful['mortgage_registration_id'])
                            ->update($params);
                    } else {
                        Db::name("mortgage_registration")->insert($params);

                        $last_id = Db::name("mortgage_registration")->getLastInsID();

                        $result = Db::name("second_sales_order")
                            ->where("id", $ids)
                            ->setField("mortgage_registration_id", $last_id);
                    }


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
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 检查年检
     */
    public function check_year()
    {
        if ($this->request->isAjax()) {

            $id = $this->request->post("id");

            $status = $this->request->post("status");

            if ($status == -1) {
                $status = 0;
            }

            $res = Db::name("mortgage_registration")
                ->where("id", $id)
                ->setField("year_status", $status);

            if ($res) {
                echo json_encode("success");
            } else {
                echo json_encode("error");
            }

        }
    }


    /**资料入库登记表
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function data_warehousing()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams("secondcarrentalmodelsinfo.vin", true);
            $total = $this->model
                ->with(['mortgageregistration' => function ($query) {
                    $query->withField('archival_coding');
                }, 'secondcarrentalmodelsinfo' => function ($query) {
                    $query->withField('licenseplatenumber,vin,companyaccount');
                }, 'admin' => function ($query) {
                    $query->withField(['nickname','id','avatar']);
                }, 'registryregistration'])
                ->where(function ($query){
                    $query->where('registry_registration_id','not null');
                })
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['mortgageregistration' => function ($query) {
                    $query->withField('archival_coding');
                }, 'secondcarrentalmodelsinfo' => function ($query) {
                    $query->withField('licenseplatenumber,vin,companyaccount');
                }, 'admin' => function ($query) {
                    $query->withField(['nickname','id','avatar']);
                }, 'registryregistration'])
                ->where(function ($query){
                    $query->where('registry_registration_id','not null');
                })
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            foreach ($list as $k=>$v){
                $department = Db::name('auth_group_access')
                    ->alias('a')
                    ->join('auth_group b','a.group_id = b.id')
                    ->where('a.uid',$v['admin']['id'])
                    ->value('b.name');
                $list[$k]['admin']['department'] = $department;
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**编辑
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit_dataware($ids = null)
    {
        $registry_registration_id = Db::name("second_sales_order")
            ->where("id", $ids)
            ->value("registry_registration_id");

        if ($registry_registration_id) {
            $row = Db::name("registry_registration")
                ->where("id", $registry_registration_id)
                ->find();

            $this->view->assign("row", $row);
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

                    if ($registry_registration_id) {
                        $result = Db::name("registry_registration")
                            ->where("id", $registry_registration_id)
                            ->update($params);
                    } else {
                        Db::name("registry_registration")->insert($params);

                        $last_id = Db::name("registry_registration")->getLastInsID();

                        $result = Db::name("second_sales_order")
                            ->where("id", $ids)
                            ->setField("registry_registration_id", $last_id);
                    }


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
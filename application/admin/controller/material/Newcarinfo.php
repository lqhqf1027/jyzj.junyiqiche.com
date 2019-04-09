<?php

namespace app\admin\controller\material;

use app\common\controller\Backend;
use think\Db;
use think\Config;


/**
 * 司机信息
 *
 * @icon fa fa-circle-o
 */
class Newcarinfo extends Backend
{

    /**
     * DriverInfo模型对象
     * @var \app\admin\model\DriverInfo
     */
    protected $model = null;
//    protected $searchFields = 'id,username';
    protected $multiFields = 'shelfismenu';
    protected $noNeedRight = ['index','new_customer','data_warehousing','edit','warehousing','detail'];

    public function _initialize()
    {
        $data = array(
            'aa车型'=>['shoufu 000','月供 111'],
            'bb车型'=>['shoufu 000','月供 111','额外方案'=>[
                ['a'=>'月供 111','b'=>'shoufu 111']
            ]]
            
        );


        parent::_initialize();

        $this->loadlang('material/mortgageregistration');
        $this->loadlang('newcars/newcarscustomer');
        $this->loadlang('order/salesorder');

        $this->model = new \app\admin\model\SalesOrder;
    }

    public function index()
    {


        return $this->view->fetch();
    }

    /**
     * 按揭客户购车信息
     * @return string|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function new_customer()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('newinventory.frame_number', true);
            $total = $this->model
                ->with(['admin' => function ($query) {
                    $query->withField('nickname,id,avatar');
                }, 'models' => function ($query) {
                    $query->withField('name');
                }, 'newinventory' => function ($query) {
                    $query->withField('licensenumber,frame_number');
                }, 'planacar' => function ($query) {
                    $query->withField('payment,monthly,nperlist,tail_section,margin');
                }, 'mortgageregistration' => function ($query) {
                    $query->withField('archival_coding,signdate,end_money,hostdate,mortgage_people,transfer,transferdate,registry_remark,yearly_inspection,year_range,year_status,next_inspection');
                }])
                ->where(function ($query){
                    $query->where('mortgage_registration_id','not null');
                })
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['admin' => function ($query) {
                    $query->withField('nickname,id,avatar');
                }, 'models' => function ($query) {
                    $query->withField('name');
                }, 'newinventory' => function ($query) {
                    $query->withField('licensenumber,frame_number');
                }, 'planacar' => function ($query) {
                    $query->withField('payment,monthly,nperlist,tail_section,margin');
                }, 'mortgageregistration' => function ($query) {
                    $query->withField('archival_coding,signdate,end_money,hostdate,mortgage_people,transfer,transferdate,registry_remark,yearly_inspection,year_range,year_status,next_inspection');
                }])
                ->where(function ($query){
                    $query->where('mortgage_registration_id','not null');
                })
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $used = new \app\admin\model\SecondcarRentalModelsInfo();
            $used = $used->column('licenseplatenumber');


            foreach ($list as $k => $v) {
                $list[$k]['used_car'] = $used;
            }
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
     * 按揭客户资料入库表
     * @return string|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function data_warehousing()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username', true);
            $total = $this->model
                ->with(['admin' => function ($query) {
                    $query->withField('nickname,id,avatar');
                }, 'newinventory' => function ($query) {
                    $query->withField('licensenumber,frame_number');
                }, 'planacar' => function ($query) {
                    $query->withField('payment,monthly,nperlist,tail_section,margin');
                }, 'mortgageregistration' => function ($query) {
                    $query->withField('archival_coding,signdate,end_money,hostdate,mortgage_people');
                }, 'registryregistration'])
                ->where(function ($query){
                    $query->where('registry_registration_id','not null');
                })
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['admin' => function ($query) {
                    $query->withField(['nickname','id','avatar']);
                }, 'newinventory' => function ($query) {
                    $query->withField('licensenumber,frame_number');
                }, 'planacar' => function ($query) {
                    $query->withField('payment,monthly,nperlist,tail_section,margin');
                }, 'mortgageregistration' => function ($query) {
                    $query->withField('archival_coding,signdate,end_money,hostdate,mortgage_people');
                }, 'registryregistration'])
                ->where(function ($query){
                    $query->where('registry_registration_id','not null');
                })
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

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

        $gage = Db::name("sales_order")
            ->where("id", $ids)
            ->field("mortgage_registration_id,createtime")
            ->find();


        if ($gage['mortgage_registration_id']) {
            $row = Db::name("mortgage_registration")
                ->where("id", $gage['mortgage_registration_id'])
                ->find();

            $this->view->assign("row", $row);
        }


        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");

            $check_mortgage = $this->request->post("mortgage");

            if ($gage['createtime']) {
                $params['signdate'] = date('Y-m-d', $gage['createtime']);
            }

            if ($params) {
                if (!$check_mortgage) {
                    $params['mortgage_people'] = null;
                }

                if (!$params['transfer']) {
                    $params['transferdate'] = null;
                }

                if ($params['next_inspection']) {

                    //自动根据年检日期得到即将年检的时间段
                    $date = $params['next_inspection'];

                    $first_day = date("Y-m-01", strtotime("-1 month", strtotime($date)));

                    $last_date = date("Y-m-01", strtotime($date));

                    $last_date = date("Y-m-d", strtotime("-1 day", strtotime($last_date)));

                    $now_month = $this->getMonth($params['next_inspection']);
                    $params['year_range'] = $first_day . ";" . $last_date."|".$now_month[0].";".$now_month[1];
                }


                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }

                    if ($gage['mortgage_registration_id']) {
                        $result = Db::name("mortgage_registration")
                            ->where("id", $gage['mortgage_registration_id'])
                            ->update($params);
                    } else {
                        Db::name("mortgage_registration")->insert($params);

                        $lastId = Db::name("mortgage_registration")->getLastInsID();

                        $result = Db::name("sales_order")
                            ->where("id", $ids)
                            ->setField("mortgage_registration_id", $lastId);
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

    public function getMonth($date){
        $firstday = date("Y-m-01",strtotime($date));
        $lastday = date("Y-m-d",strtotime("$firstday +1 month -1 day"));
        return array($firstday,$lastday);
    }


    /**
     * 编辑
     */
    public function warehousing($ids = NULL)
    {


        $registr = Db::name("sales_order")
            ->where("id", $ids)
            ->find()['registry_registration_id'];

        if ($registr) {
            $row = Db::name("registry_registration")
                ->where("id", $registr)
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

                    if ($registr) {
                        $result = Db::name("registry_registration")
                            ->where("id", $registr)
                            ->update($params);
                    } else {
                        Db::name("registry_registration")->insert($params);

                        $last_id = Db::name("registry_registration")->getLastInsID();

                        $result = Db::name("sales_order")
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

        $this->view->assign("keylist", $this->keylist());
        return $this->view->fetch();
    }

    public function keylist()
    {
        return ['yes' => '有', 'no' => '无'];
    }

    /**
     * 检查年检
     */
    public function check_year()
    {
        if ($this->request->isAjax()) {
            $num = input("status");

            $id = input("id");


            $res = Db::name("mortgage_registration")
                ->where("id", $id)
                ->setField("year_status", $num);

            if ($res) {
                echo json_encode("yes");
            } else {
                echo json_encode("no");
            }


        }
    }



}

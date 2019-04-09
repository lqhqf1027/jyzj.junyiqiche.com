<?php

namespace app\admin\controller\store;

use app\common\controller\Backend;
use think\Db;
use app\admin\model\CompanyStore;
use app\admin\model\PlanAcar;
use app\admin\model\Cities;

/**
 * 公司门店
 *
 * @icon fa fa-circle-o
 */
class Shop extends Backend
{

    /**
     * Store模型对象
     * @var \app\admin\model\cms\company\Store
     */
    protected $model = null;
    protected static $key = "7cd2a3dc6f7174c013693bc9d7c24e58";
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\CompanyStore;
        $this->view->assign("statussList", $this->model->getStatussList());
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
//        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('store_name',true);
            $total = $this->model
                ->with(['city'])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['city'])
                ->with(['planacar' => function ($query) {
                    $query->where(['ismenu' => 1, 'acar_status' => 1]);
                }])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            $list = collection($list)->toArray();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * @param $shopID
     */
    public  static function getPlan_num($shopID){

    }

    //获取城市名称
    public function getCity()
    {
        $result = Db::name('cms_cities')->where('pid', 'NEQ', 0)->select();

        return $result;
    }

    /**
     * 添加
     */
    public function add()
    {
        $key = self::$key;
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $result = gets("https://restapi.amap.com/v3/geocode/geo?key=" . $key . "&address=" . $params['store_address']);
            if ($result['status']) {

                $data = explode(",", $result['geocodes'][0]['location']);
                $params['longitude'] = $data[0];
                $params['latitude'] = $data[1];
            }
            // pr($params);
            // die;
            if ($params) {
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($this->model->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                } catch (\think\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign([
            'city' => $this->getCity()
        ]);
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $key = self::$key;

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
            $result = gets("https://restapi.amap.com/v3/geocode/geo?key=" . $key . "&address=" . $params['store_address']);
            if ($result['status']) {

                $data = explode(",", $result['geocodes'][0]['location']);
                $params['longitude'] = $data[0];
                $params['latitude'] = $data[1];
            }
            // pr($params);
            // die;
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
                        //城市与门店的开启与关闭
                        $this->getStore($ids);

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
        $this->view->assign([
            'row'  => $row,
            'city' => $this->getCity()
        ]);
        return $this->view->fetch();
    }

    //关闭或开启门店
    public function getStore($id)
    {
        $result = CompanyStore::get($id);
        // pr($result->statuss);
        // die;
        //关闭门店
        if ($result->statuss == 'hidden') {

            $data = CompanyStore::where(['city_id' => $result->city_id, 'statuss' => 'normal'])->select();
            if (!$data) {
                Cities::update(['id' => $result->city_id, 'status' => 'hidden']);
            }
            //关闭门店下的方案
            PlanAcar::where('store_id', $result->id)->setField(['acar_status' => 2, 'ismenu' => 0]);
        }
        //开启门店
        else {
            $status = Cities::where('id', $result->city_id)->value('status');
            if ($status == 'hidden') {
                Cities::update(['id' => $result->city_id, 'status' => 'normal']);
            }
            //开启门店下的方案
            PlanAcar::where('store_id', $result->id)->setField(['acar_status' => 1, 'ismenu' => 1]);
        }

    }

    public function selectpage()
    {
        return parent::selectpage();
    }
}

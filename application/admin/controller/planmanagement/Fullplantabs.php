<?php

namespace app\admin\controller\planmanagement;

use app\common\controller\Backend;
use app\common\library\Email;
//use app\common\model\Config;
use think\Config;
use think\Db;
use think\Session;
use think\Cache;
/**
 * 多表格示例
 *
 * @icon fa fa-table
 * @remark 当一个页面上存在多个Bootstrap-table时该如何控制按钮和表格
 */
class Fullplantabs extends Backend
{

    protected $model = null;
    protected $multiFields = 'ismenu';

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
        $this->loadlang('plan/planacar');
        $this->loadlang('plan/planusedcar');
        $this->loadlang('plan/planfull');
        return $this->view->fetch();
    }

    /**
     * Notes:获取已签单的全款方案id
     * User: glen9
     * Date: 2018/10/11
     * Time: 23:43
     * @return array
     */
    public static function matchingFullParmentOrder()
    {
        return array_unique(Db::name('full_parment_order')->column('plan_plan_full_name'));
    }

    /**
     * 关联品牌名称
     * @param $plan_id 方案id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getBrandName($plan_id = null)
    {
        return Db::name('plan_acar')->alias('a')
            ->join('models b', 'a.models_id = b.id')
            ->join('brand c', 'b.brand_id=c.id')
            ->where('a.id', $plan_id)
//            ->field('c.name,c.brand_logoimage')
            ->column('c.name,c.brand_logoimage');
    }


    public function getFullBrandName($plan_id)
    {
        return Db::name('plan_full')->alias('a')
            ->join('models b', 'a.models_id = b.id')
            ->join('brand c', 'b.brand_id=c.id')
            ->where('a.id', $plan_id)
            ->value('c.name');
    }

    /**
     * Notes:全款
     * User: glen9
     * Date: 2018/9/6
     * Time: 22:00
     * @return string|\think\response\Json
     * @throws \think\Exception
     */
    public function table3()
    {
        $this->model = model('PlanFull');
        $this->view->assign("ismenuList", $this->model->getIsmenuList());
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with(['models'])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['models'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $row) {
                $row->visible(['id', 'models_id', 'full_total_price', 'margin', 'ismenu', 'createtime', 'updatetime']);
                $row->visible(['models']);
                $row->getRelation('models')->visible(['name']);
            }
            $full_order_data = self::matchingFullParmentOrder();
            $list = collection($list)->toArray();
            foreach ((array)$list as $key => $value) {
                $list[$key]['brand_name'] = $this->getFullBrandName($value['id']);

                   $list[$key]['match_plan'] = in_array($value['id'], $full_order_data) == $value['id'] ? 'match_success' : 'match_error'; //返回是否与方案id匹配
            }

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch('index');
    }

    public function working_insurance()
    {
        return ['yes' => '有', 'no' => '无'];
    }


    /**得到销售员信息
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSales()
    {
        $sales = Db::name("admin")
            ->where("rule_message", "in", ['message8', 'message9', 'message23'])
            ->where('status','normal')
            ->field("id,nickname,rule_message")
            ->select();


        $arr = array(['id' => 1, 'name' => '销售1部', 'message' => array()], ['id' => 2, 'name' => '销售2部', 'message' => array()], ['id' => 3, 'name' => '销售3部', 'message' => array()]);


        foreach ($sales as $value) {
            if ($value['rule_message'] == 'message8') {
                array_push($arr[0]['message'], $value);
            } else if ($value['rule_message'] == 'message9') {
                array_push($arr[1]['message'], $value);
            } else if ($value['rule_message'] == 'message23') {
                array_push($arr[2]['message'], $value);
            }
        }

        return $arr;

    }


    /**得到销售方案类别信息
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCategory()
    {
        $res = Db::name("scheme_category")->select();

        return $res;
    }

    /**得到门店
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getStore()
    {
        $res = Db::name("cms_company_store")->where('statuss', 'normal')->select();

        return $res;
    }


    /**
     * 全款编辑
     */
    public function fulledit($ids = NULL)
    {
        $this->model = model('PlanFull');
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
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 全款车删除
     */
    public function fulldel($ids = "")
    {
        $this->model = model('PlanFull');
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $count = $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();
            $count = 0;
            foreach ($list as $k => $v) {
                $count += $v->delete();
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    /**
     * 批量更新
     */
    public function fullmulti($ids = "")
    {
        $this->model = model('PlanFull');
        $ids = $ids ? $ids : $this->request->param("ids");
        if ($ids) {
            if ($this->request->has('params')) {
                parse_str($this->request->post("params"), $values);
                $values = array_intersect_key($values, array_flip(is_array($this->multiFields) ? $this->multiFields : explode(',', $this->multiFields)));
                if ($values) {
                    $adminIds = $this->getDataLimitAdminIds();
                    if (is_array($adminIds)) {
                        $this->model->where($this->dataLimitField, 'in', $adminIds);
                    }
                    $count = 0;
                    $list = $this->model->where($this->model->getPk(), 'in', $ids)->select();
                    foreach ($list as $index => $item) {
                        $count += $item->allowField(true)->isUpdate(true)->save($values);
                    }
                    if ($count) {
                        $this->success();
                    } else {
                        $this->error(__('No rows were updated'));
                    }
                } else {
                    $this->error(__('You have no permission'));
                }
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }


    /**车型对应车辆
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getInfo()
    {
        // $keys = self::$keys;
        
        // $data = Cache::get('data123');
        // // dump($data);
        // // die;
        
        // if ($data['error_code'] == 0) {
        //     // pr($data['result']);
        //     // die;
        //     foreach ($data['result'] as $key => $value) {
                
                
        //         if( $key <= 210 && $key > 180){

        //             $brand[$key]['id'] = $value['I'];
        //             $brand[$key]['brand_name'] = $value['N'];

        //             Db::name('brand')->insert(['pid' => '0', 'level' => '0', 'name' =>$value['N'], 'brand_initials' => $value['L']]);
        //             $userId = Db::name('brand')->getLastInsID();
        //             foreach ($value['List'] as $k => $v) {
                        
        //                 $brand[$key]['category_list'][$k]['category_id'] = $v['I'];
        //                 $brand[$key]['category_list'][$k]['category_name'] = $v['N'];

                        
        //                 foreach ($v['List'] as $kk => $vv) {
                            
        //                     $brand[$key]['category_list'][$k]['car_series'][$kk]['series_id'] = $vv['I'];
        //                     $brand[$key]['category_list'][$k]['car_series'][$kk]['series_name'] = $vv['N'];
        //                     Db::name('brand')->insert(['pid' => $userId, 'level' => '1', 'name' => $vv['N'], 'series_id' => $vv['I']]);
                            
        //                 }
        //             }
        //         }    
        //     }                      
        // }
        //     pr($brand);
        //     die;

        $models = Db::name("models")
            ->field("id as models_id,models_name as model_name,name as models_name,brand_id")
            ->select();
            
        //品牌下没有车型，就不显示在下拉列表
        foreach ($models as $key => $value) {
            $ids[] = $value['brand_id'];
        }
        
        $brand = Db::name("brand")
            ->where('pid', '0')
            ->where('id', 'in', $ids)
            ->field("id,name")
            ->select();

        // pr($brand);
        // die;

        foreach ($brand as $k => $v) {
            $brand[$k]['models'] = array();
            foreach ($models as $key => $value) {

                if ($v['id'] == $value['brand_id']) {
                    $value['models_name'] = $value['models_name'] . " " . $value['model_name'];
                    array_push($brand[$k]['models'], $value);
                }
            }

        }

        return $brand;

    }

    /**
     * 全款车添加
     */
    public function fulladd()
    {
        $this->model = model("PlanFull");
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
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
        return $this->view->fetch();
    }


}

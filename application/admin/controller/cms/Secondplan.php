<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;

use think\Db;
use think\Cache;
use app\admin\model\CompanyStore;
use app\admin\model\Cities;
use think\Config;
use think\console\output\descriptor\Console;
use think\Model;
use think\Session;
use fast\Tree;
use think\db\Query;
use app\admin\model\CarRentalModelsInfo;


/**
 * 二手车管理车辆信息
 *
 * @icon fa fa-circle-o
 */
class Secondplan extends Backend
{

    /**
     * SecondcarRentalModelsInfo模型对象
     * @var \app\admin\model\SecondcarRentalModelsInfo
     */
    protected $model = null;
    protected $multiFields = 'shelfismenu';
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('SecondcarRentalModelsInfo');

        $storeList = [];
        $disabledIds = [];
        $cities_all = collection(Cities::where('pid', 'NEQ', '0')->order("id desc")->field(['id,cities_name as name'])->select())->toArray();
        $store_all = collection(CompanyStore::order("id desc")->field(['id, city_id, store_name as name'])->select())->toArray();
        $all = array_merge($cities_all, $store_all);
        // pr($all);
        // die;
        foreach ($all as $k => $v) {

            $state = ['opened' => true];

            if (!$v['city_id']) {
            
                $disabledIds[] = $v['id'];
                $storeList[] = [
                    'id'     => $v['id'],
                    'parent' => '#',
                    'text'   => __($v['name']),
                    'state'  => $state
                ];
            }

            foreach ($cities_all as $key => $value) {
                
                if ($v['city_id'] == $value['id']) {
                    
                    $storeList[] = [
                        'id'     => $v['id'],
                        'parent' => $value['id'],
                        'text'   => __($v['name']),
                        'state'  => $state
                    ];
                }
                   
            }
            
        }
        // pr($storeList);
        // die;
        // $tree = Tree::instance()->init($all, 'city_id');
        // $storeOptions = $tree->getTree(0, "<option value=@id @selected @disabled>@spacer@name</option>", '', $disabledIds);
        // pr($storeOptions);
        // die;
        // $this->view->assign('storeOptions', $storeOptions);
        $this->assignconfig('storeList', $storeList);

        $this->view->assign("shelfismenuList", $this->model->getShelfismenuList());
    }

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('licenseplatenumber',true);
            $total = $this->model
                ->with(['models' => function ($query) {
                    $query->withField('name,models_name');
                },'label'=>function($query){
                    $query->withField('name,lableimages');
                },'companystore'=>function($query){
                    $query->withField('store_name');
                }])
                ->where($where)
                ->where(['status_data' => "", 'shelfismenu' => 1])
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['models' => function ($query) {
                    $query->withField('name,models_name');
                },'label'=>function($query){
                    $query->withField('name,lableimages');
                },'companystore'=>function($query){
                    $query->withField('store_name');
                }])
                ->where($where)
                ->where(['status_data' => "", 'shelfismenu' => 1])
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            
            foreach ($list as $k=>$row){
                $data = [
                    CarRentalModelsInfo::where(['status_data' => '', 'shelfismenu' => 1])->select()
                ];

                $row->visible(['id', 'licenseplatenumber','bond', 'kilometres', 'companyaccount', 'newpayment', 'monthlypaymen', 'periods', 'totalprices', 'drivinglicenseimages', 'vin',
                    'engine_number', 'expirydate', 'annualverificationdate', 'carcolor', 'aeratedcard', 'volumekeys', 'Parkingposition', 'shelfismenu', 'vehiclestate', 'note',
                    'createtime', 'updatetime', 'status_data', 'department', 'admin_name', 'modelsimages', 'models_main_images','daypaymen','weigh','store_name']);
                $row->visible(['models']);
                $row->getRelation('models')->visible(['name', 'models_name']);
                $row->visible(['label']);
                $row->getRelation('label')->visible(['name', 'lableimages']);
                $row->visible(['companystore']);
                $row->getRelation('companystore')->visible(['store_name']);
                $list[$k]['store_name'] = $this->getStoreName($row['store_id']); //获取门店
                foreach ((array)$data as $key => $value){
                    foreach ($value as $v){
                        if($v['licenseplatenumber'] == $row['licenseplatenumber']){
                            $daypaymen = round($v['manysixmonths'] / 30);
                            // pr($daypaymen);
                            // die;
                            $list[$k]['daypaymen'] = $daypaymen;

                            $this->model->where('licenseplatenumber', $v['licenseplatenumber'])->setField(['daypaymen' => $daypaymen]);
                        }
                    }   
                }

                if ($list[$k]['models']['models_name']) {
                    $list[$k]['models']['name'] = $list[$k]['models']['name'] . " " . $list[$k]['models']['models_name'];
                }

            }

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 关联城市省份
     * @param $store_id 门店id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getStoreName($store_id)
    {
        $store = CompanyStore::where('id', $store_id)->find();
        $cities = Cities::where('id', $store['city_id'])->find();
        $provice = Cities::where('id', $cities['pid'])->value('name');
        return $provice . $cities['cities_name'] . "---" . $store['store_name'];
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
        $this->view->assign([
            "row" => $row,
            "store" => $this->getStore()
            ]);
        return $this->view->fetch();
    }

    //门店名称
    public function getStore()
    {
        $result = Db::name('cms_company_store')->select();

        return $result;
    }

    //拖拽排序---改变权重
    public function dragsort()
    {
        //排序的数组
        $ids = $this->request->post("ids");
        //拖动的记录ID
        $changeid = $this->request->post("changeid");
        //操作字段
        $field = $this->request->post("field");
        //操作的数据表
        $table = $this->request->post("table");
        //排序的方式
        $orderway = $this->request->post("orderway", 'strtolower');
        $orderway = $orderway == 'asc' ? 'ASC' : 'DESC';
        $sour = $weighdata = [];
        $ids = explode(',', $ids);
        $prikey = 'id';
        $pid = $this->request->post("pid");
        
        //限制更新的字段
        $field = in_array($field, ['weigh']) ? $field : 'weigh';

        // 如果设定了pid的值,此时只匹配满足条件的ID,其它忽略
        if ($pid !== '') {
            $hasids = [];
            $list = Db::name($table)->where($prikey, 'in', $ids)->where('pid', 'in', $pid)->field('id,pid')->select();
            foreach ($list as $k => $v) {
                $hasids[] = $v['id'];
            }
            $ids = array_values(array_intersect($ids, $hasids));
        }

        $list = Db::name($table)->field("$prikey,$field")->where($prikey, 'in', $ids)->order($field, $orderway)->select();
       
        foreach ($list as $k => $v) {
            $sour[] = $v[$prikey];
            $weighdata[$v[$prikey]] = $v[$field];
        }
        $position = array_search($changeid, $ids);
        $desc_id = $sour[$position];    //移动到目标的ID值,取出所处改变前位置的值
        $sour_id = $changeid;
        $weighids = array();
        $temp = array_values(array_diff_assoc($ids, $sour));
        foreach ($temp as $m => $n) {
            if ($n == $sour_id) {
                $offset = $desc_id;
            } else {
                if ($sour_id == $temp[0]) {
                    $offset = isset($temp[$m + 1]) ? $temp[$m + 1] : $sour_id;
                } else {
                    $offset = isset($temp[$m - 1]) ? $temp[$m - 1] : $sour_id;
                }
            }
            $weighids[$n] = $weighdata[$offset];
            Db::name($table)->where($prikey, $n)->update([$field => $weighdata[$offset]]);
        }
        $this->success();
    }


}

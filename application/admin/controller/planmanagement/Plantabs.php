<?php

namespace app\admin\controller\planmanagement;

use app\common\controller\Backend;
use app\common\library\Email;
//use app\common\model\Config;
use think\Config;
use think\Db;
use think\Session;
use think\Cache;

use app\admin\model\CompanyStore;
use app\admin\model\Cities;
/**
 * 多表格示例
 *
 * @icon fa fa-table
 * @remark 当一个页面上存在多个Bootstrap-table时该如何控制按钮和表格
 */
class Plantabs extends Backend
{

    protected $model = null;
    protected $multiFields = 'ismenu';

    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();

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

    }

    /**
     * 查看
     */
    public function index()
    {
        $this->loadlang('plan/planacar');
        $this->loadlang('plan/planusedcar');
        return $this->view->fetch();
    }

    /**
     * Notes:新车方案
     * User: glen9
     * Date: 2018/9/6
     * Time: 21:47
     * @return string|\think\response\Json
     * @throws \think\Exception
     */
    public function table1()
    {
        $this->model = model('PlanAcar');
        $this->view->assign("nperlistList", $this->model->getNperlistList());
        $this->view->assign("ismenuList", $this->model->getIsmenuList());
        //当前是否为关联查询
        // $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('models.name', true);
            $total = $this->model
                ->with(['models' => function ($query) {
                    $query->withField('name,models_name');
                }, 'admin' => function ($query) {
                    $query->withField('nickname');
                },'financialplatform'=>function($query){
                    $query->withField('name');
                }])
                ->where($where)
                ->order($sort, $order)
                ->order('payment')
                ->count();

            $list = $this->model
                ->with(['models' => function ($query) {
                    $query->withField('name,models_name');
                }, 'admin' => function ($query) {
                    $query->withField('nickname');
                }, 'schemecategory' => function ($query) {
                    $query->withField('name,category_note');
                },'financialplatform'=>function($query){
                    $query->withField('name');
                }])
                ->order('category_id')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            $sales_order_data = self::matchingSalesOrder();

            foreach ($list as $key => $row) {

                $row->visible(['id', 'payment', 'monthly', 'brand_name','brand_log', 'match_plan', 'nperlist', 'margin', 'tail_section', 'gps', 'note', 'ismenu', 'createtime', 'updatetime', 'working_insurance', 'category_id','sales_id','store_id','city_store']);
                $row->visible(['models']);
                $row->getRelation('models')->visible(['name', 'models_name']);
                $row->visible(['admin']);
                $row->getRelation('admin')->visible(['nickname']);
                $row->visible(['schemecategory']);
                $row->getRelation('schemecategory')->visible(['name', 'category_note']);
                $row->visible(['financialplatform']);
                $row->getRelation('financialplatform')->visible(['name']);
                $list[$key]['brand_name'] = array_keys(self::getBrandName($row['id'])); //获取品牌
                $list[$key]['brand_log'] =Config::get('upload')['cdnurl'].array_values(self::getBrandName($row['id']))[0]; //获取logo图片
                $list[$key]['match_plan'] = in_array($row['id'], $sales_order_data) == $row['id'] ? 'match_success' : 'match_error'; //返回是否与方案id匹配

                //城市门店
                if ($row['store_id']) {
                    $CompanyStore = CompanyStore::where('id', $row['store_id'])->find();
                    $city_name = Cities::where('id', $CompanyStore['city_id'])->value('cities_name');
                    $list[$key]['city_store'] = $city_name . "+" . $CompanyStore['store_name'];
                }
                else{
                    $list[$key]['city_store'] = "暂无";
                }
                //车型
                if ($list[$key]['models']['models_name']) {
                    $list[$key]['models']['name'] = $list[$key]['models']['name'] . " " . $list[$key]['models']['models_name'];
                }

            }
            $list = collection($list)->toArray();

            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch('index');
    }

    /**
     * Notes:获取已签单的方案id
     * User: glen9
     * Date: 2018/10/11
     * Time: 23:43
     * @return array
     */
    public static function matchingSalesOrder()
    {
        return array_unique(Db::name('sales_order')->column('plan_acar_name'));

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
           // ->field('c.name,c.brand_logoimage')
            ->column('c.name,c.brand_logoimage');
    }

    /**
     * 新车编辑
     */
    public function firstedit($ids = NULL)
    {
        $this->model = model('PlanAcar');
        $row = $this->model->get($ids);

        $financial = Db::name("financial_platform")->where('status', 'normal')->select();
        $this->view->assign([
            'row' => $row,
            'working_insurance_list' => $this->working_insurance(),
            'sales' => $this->getSales(),
            'category' => $this->getCategory(),
            'store' => $this->getStore(),
            'car_models' => $this->getInfo(),
            'financial' => $financial,
            'nperlistList' => $this->getNperlistList()
        ]);

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
            if ($params['sales_id'] == " ") {
                $params['sales_id'] = null;
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

    //期数
    public function getNperlistList()
    {
        return ['12' => __('Nperlist 12'),'24' => __('Nperlist 24'),'36' => __('Nperlist 36'),'48' => __('Nperlist 48'),'60' => __('Nperlist 60')];
    }   

    //运营险
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

    /**得到门店
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getStore()
    {
        $companyStore = Db::name("cms_company_store")
            ->field("id as store_id,store_name,city_id")
            ->select();
            
        //城市下没有门店，就不显示在下拉列表
        foreach ($companyStore as $key => $value) {
            $ids[] = $value['city_id'];
        }
        
        $cities = Db::name("cms_cities")
            ->where('pid', 'NEQ', '0')
            ->where('id', 'in', $ids)
            ->field("id,cities_name")
            ->select();

        foreach ($cities as $k => $v) {
            $cities[$k]['store'] = array();
            foreach ($companyStore as $key => $value) {

                if ($v['id'] == $value['city_id']) {
                    array_push($cities[$k]['store'], $value);
                }
            }

        }

        return $cities;

    }

    /**
     * 新车删除
     */
    public function firstdel($ids = "")
    {
        $this->model = model('PlanAcar');
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
    public function firstmulti($ids = "")
    {
        $this->model = model('PlanAcar');
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

    /**门店下的车型
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getModels()
    {
        $this->model = model('Models');
        // //当前是否为关联查询
        // $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
           
        }

    }

    /**门店下的方案类别
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCategory()
    {
        $this->model = model('Schemecategory');
        // //当前是否为关联查询
        // $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
           
        }

    }

    /**
     * 新车添加
     */
    public function firstadd()
    {
        $this->model = model("PlanAcar");
        $financial = Db::name("financial_platform")->where('status', 'normal')->select();

        $this->view->assign([
            'sales' => $this->getSales(),
            'store' => $this->getStore(),
            'financial' => $financial,
            'car_models' => $this->getInfo(),
            'nperlistList' => $this->model->getNperlistList()
        ]);
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            //人气值--随机
            $params['popularity'] = rand(1000,9999);
            
            if (empty($params['working_insurance'])) {
                $params['working_insurance'] = "no";
            }
            if ($params['sales_id'] == " ") {
                $params['sales_id'] = null;
            }
            if ($params) {
                $params['acar_status'] = 1;
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
                    $planId = Db::name('plan_acar')->getLastInsID();
                   
                    $result_ss = Db::name('plan_acar')->where('id', $planId)->update(['weigh' => $planId]);
                    
                    if ($result !== false && $result_ss) {

                        $models_name = Db::name('models')
                            ->where('id', $params['models_id'])
                            ->value('name');

                        if ($params['liu'] == 'yes' && $params['sales_id']) {

                            $results = array();

                            $datas = send_newmodels_to_sales($models_name, $params['payment'], $params['monthly']);

                            $email = new Email;

                            $receiver = Db::name('admin')->where('id', $params['sales_id'])->value('email');

                            $result_sss = $email
                                ->to($receiver)
                                ->subject($datas['subject'])
                                ->message($datas['message'])
                                ->send();

                            if ($result_sss) {
                                $this->success();
                            } else {
                                $this->error($this->model->getError());
                            }
                        }

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

    public function import_first_plan()
    {

        $this->model = model("PlanAcar");
        $financial = Db::name("financial_platform")->where('status', 'normal')->select();

        $this->view->assign([
            'sales' => $this->getSales(),
            'category' => $this->getCategory(),
            'financial' => $financial,
            'car_models' => $this->getInfo(),
            'nperlistList' => $this->model->getNperlistList()
        ]);
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if (empty($params['working_insurance'])) {
                $params['working_insurance'] = "no";
            }
            if ($params['sales_id'] == " ") {
                $params['sales_id'] = null;
            }
            if ($params) {
                $params['acar_status'] = 1;
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

                        $models_name = Db::name('models')
                            ->where('id', $params['models_id'])
                            ->value('name');

                        if ($params['liu'] == 'yes' && $params['sales_id']) {

                            $results = array();

                            $datas = send_newmodels_to_sales($models_name, $params['payment'], $params['monthly']);

                            $email = new Email;

                            $receiver = Db::name('admin')->where('id', $params['sales_id'])->value('email');

                            $result_sss = $email
                                ->to($receiver)
                                ->subject($datas['subject'])
                                ->message($datas['message'])
                                ->send();

                            if ($result_sss) {
                                $this->success();
                            } else {
                                $this->error($this->model->getError());
                            }
                        }

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
        return $this->view->fetch();
    }


    /**导入销售方案
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     */
    public function import()
    {
        $this->model = model('PlanAcar');
        $file = $this->request->request('file');
        if (!$file) {
            $this->error(__('Parameter %s can not be empty', 'file'));
        }
        $filePath = ROOT_PATH . DS . 'public' . DS . $file;
        if (!is_file($filePath)) {
            $this->error(__('No results were found'));
        }
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                $PHPReader = new \PHPExcel_Reader_CSV();
                if (!$PHPReader->canRead($filePath)) {
                    $this->error(__('Unknown data format'));
                }
            }
        }

        //导入文件首行类型,默认是注释,如果需要使用字段名称请使用name
        $importHeadType = isset($this->importHeadType) ? $this->importHeadType : 'comment';

        $table = $this->model->getQuery()->getTable();
        $database = \think\Config::get('database.database');
        $fieldArr = [];
        $list = db()->query("SELECT COLUMN_NAME,COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?", [$table, $database]);
        // pr($list);
        // die;
        foreach ($list as $k => $v) {
            if ($importHeadType == 'comment') {
                $fieldArr[$v['COLUMN_COMMENT']] = $v['COLUMN_NAME'];
            } else {
                $fieldArr[$v['COLUMN_NAME']] = $v['COLUMN_NAME'];
            }
        }
        // pr($fieldArr);
        // die;
        $PHPExcel = $PHPReader->load($filePath); //加载文件
        $currentSheet = $PHPExcel->getSheet(0);  //读取文件中的第一个工作表
        $allColumn = $currentSheet->getHighestDataColumn(); //取得最大的列号
        $allRow = $currentSheet->getHighestRow(); //取得一共有多少行
        $maxColumnNumber = \PHPExcel_Cell::columnIndexFromString($allColumn);
        for ($currentRow = 1; $currentRow <= 1; $currentRow++) {
            for ($currentColumn = 0; $currentColumn < $maxColumnNumber; $currentColumn++) {
                $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                $fields[] = $val;
            }
        }
        $insert = [];
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            $values = [];
            for ($currentColumn = 0; $currentColumn < $maxColumnNumber; $currentColumn++) {
                $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                $values[] = is_null($val) ? '' : $val;
            }
            $row = [];
            $temp = array_combine($fields, $values);
            // pr($temp);
            // die;
            foreach ($temp as $k => $v) {
                if (isset($fieldArr[$k]) && $k !== '') {
                    $row[$fieldArr[$k]] = $v;
                    $row['popularity'] = rand(1000,9999);
                }
            }

            if ($row) {

                $insert[] = $row;
            }
        }
        if (!$insert) {
            $this->error(__('No rows were updated'));
        }
        try {
            $this->model->saveAll($insert);

        } catch (\think\exception\PDOException $exception) {
            $this->error($exception->getMessage());
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success();
    }


}

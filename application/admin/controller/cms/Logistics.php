<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use think\Db;

use app\admin\model\CompanyStore;
use app\admin\model\Cities;

/**
 * 物流车方案
 *
 * @icon fa fa-circle-o
 */
class Logistics extends Backend
{
    
    /**
     * Project模型对象
     * @var \app\admin\model\cms\logistics\Project
     */
    protected $model = null;
    protected $multiFields = ['recommendismenu','flashviewismenu','specialismenu','subjectismenu','ismenu'];

    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Logistics;
        $this->view->assign("nperlistList", $this->model->getNperlistList());
        $this->view->assign("acarStatusList", $this->model->getAcarStatusList());

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
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    ->with(['subject','label','store','models'])
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with(['subject','label','store','models'])
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            foreach ($list as $key => $row) {
                
                if ($list[$key]['models']['models_name']) {
                    $list[$key]['models']['name'] = $list[$key]['models']['name'] . " " . $list[$key]['models']['models_name'];
                }
                
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            // pr($params);
            // die;
            //车型名称
            $params['name'] = Db::name('brand')->where('id', $params['series_name'])->value('name');
            
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
            'subject' => $this->getSubject(),
            'store'  => $this->getStore()
        ]);

        return $this->view->fetch();
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
            //车型名称
            $params['name'] = Db::name('brand')->where('id', $params['series_name'])->value('name');
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
            'subject' => $this->getSubject(),
            'store'  => $this->getStore()
        ]);
        return $this->view->fetch();
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


    //专题标题
    public function getSubject()
    {
        $result = Db::name('cms_subject')->select();

        return $result;
    }

}

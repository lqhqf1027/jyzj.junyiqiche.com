<?php

namespace app\admin\controller\customer;

use app\common\controller\Backend;
use think\Db;
/**
 * 客户资源列管理
 *
 * @icon fa fa-circle-o
 */
class Customerresource extends Backend
{
    
    /**
     * CustomerResource模型对象
     * @var \app\admin\model\CustomerResource
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('CustomerResource');
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
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
        
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    ->with(['platform'])
                    ->where($where)
                    ->where('backoffice_id','neq','')
                   
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with(['platform'])
                    ->where($where)
                    ->where('backoffice_id','neq','')
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            foreach ($list as $row) {
                
                $row->getRelation('platform')->visible(['name']);
            }
            
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
         
        return $this->view->fetch();
    }
    
    //添加
    public function add()
    {
       
        $this->model = model('CustomerResource');
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $platform = collection( model('Platform')->all(['id'=>array('not in','5,6,7')]))->toArray();
        // var_dump($platform);
        // die;
        if ($this->request->isPost()) {

            $params = $this->request->post("row/a");

            if ($params) {
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
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
                }
                $result = $this->model->allowField(true)->save($params);


                if($result){
                    $this->success();
                }else{
                    $this->error();
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $arr = array();
        foreach ($platform as $value){
           $arr[$value['id']]=$value['name'];
        }
        // var_dump($arr);
        // die;
        $this->assign('platform',$arr);
        return $this->view->fetch();
    }
    //导入客户信息
    public function import () {
        return parent::import();
    }
   
}

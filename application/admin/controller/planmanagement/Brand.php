<?php

namespace app\admin\controller\planmanagement;

use app\common\controller\Backend;
use app\admin\model\Brand as brandModel;
/**
 * 品牌列管理
 *
 * @icon fa fa-circle-o
 */
class Brand extends Backend
{
    
    /**
     * Brand模型对象
     * @var \app\admin\model\Brand
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Brand');
        $this->view->assign("statusList", $this->model->getStatusList());
        
    }
    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->where('pid', '0')
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->where('pid', '0')
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
//            $co = brandModel::withCount('models')->select([1,2,3]);
//            pr(collection(brandModel::withCount('models')->select([1,2,3]))->toArray());
//            foreach($co as $key=>$value){
//                echo $value->models_count .'<br>';
//
//            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
    


}

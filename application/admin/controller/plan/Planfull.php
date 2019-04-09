<?php

namespace app\admin\controller\plan;

use app\common\controller\Backend;

/**
 * 全款方案
 *
 * @icon fa fa-circle-o
 */
class Planfull extends Backend
{
    
    /**
     * PlanFull模型对象
     * @var \app\admin\model\PlanFull
     */
    protected $model = null;
    protected $multiFields = 'ismenu';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('PlanFull');
        $this->view->assign("ismenuList", $this->model->getIsmenuList());
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
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
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
                $row->visible(['id','models_id','full_total_price','ismenu','createtime','updatetime']);
                $row->visible(['models']);
				$row->getRelation('models')->visible(['name']);
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
}

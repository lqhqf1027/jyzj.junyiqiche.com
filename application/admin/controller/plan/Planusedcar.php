<?php

namespace app\admin\controller\plan;

use app\common\controller\Backend;

/**
 * 二手车
 *
 * @icon fa fa-circle-o
 */
class Planusedcar extends Backend
{
    
    /**
     * PlanUsedCar模型对象
     * @var \app\admin\model\PlanUsedCar
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('PlanUsedCar');
        $this->view->assign("statusdataList", $this->model->getStatusdataList());
        $this->view->assign("nperlistList", $this->model->getNperlistList());
        $this->view->assign("contrarytodataList", $this->model->getContrarytodataList());
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
                    ->with(['models','financialplatform'])
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with(['models','financialplatform'])
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            foreach ($list as $row) {
                $row->visible(['id','statusdata','the_door','new_payment','new_monthly','nperlist','new_total_price','mileage','contrarytodata','createtime','updatetime']);
                $row->visible(['models']);
				$row->getRelation('models')->visible(['name']);
				$row->visible(['financialplatform']);
				$row->getRelation('financialplatform')->visible(['name']);
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
}

<?php

namespace app\admin\controller\promote;

use app\common\controller\Backend;

/**
 * 推广平台列管理
 *
 * @icon fa fa-circle-o
 */
class Platform extends Backend
{
    
    /**
     * Platform模型对象
     * @var \app\admin\model\Platform
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Platform');
        $this->view->assign("statusList", $this->model->getStatusList());
       
    }
    public function index()
    {
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
                    ->where($where)
                    ->where('id', 'not in', [5,6,7])
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->where($where)
                    ->order($sort, $order)
                    ->where('id', 'not in', [5,6,7])
                    ->limit($offset, $limit)
                    ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
             
            return json($result);
        }
        return $this->view->fetch();
    }


}

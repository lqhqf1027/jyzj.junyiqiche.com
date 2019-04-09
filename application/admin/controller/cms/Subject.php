<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;

/**
 * 专题管理
 *
 * @icon fa fa-circle-o
 */
class Subject extends Backend
{

    /**
     * Subject模型对象
     * @var \app\admin\model\cms\Subject
     */
    protected $model = null;
    protected $multiFields = 'shelfismenu';

    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Subject;
        $this->view->assign("statusList", $this->model->getStatusList());


    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 城市
     */
    public function city()
    {
        $this->model = new \app\admin\model\Cities;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {

            //主键
            $primarykey = $this->request->request('keyField');
            //主键值
            $primaryvalue = $this->request->request('keyValue');

            // pr($primaryvalue);
            // die;
            if ($primaryvalue) {
                $list = $this->model->where('pid', 'NEQ', 0)->where('id', $primaryvalue)->where('status', 'normal')->field('id, cities_name as name')->select();

                $result = array("list" => $list);

                return json($result);
            }
            else{
                $list = $this->model->where('pid', 'NEQ', 0)->where('status', 'normal')->field('id, cities_name as name')->select();

                $result = array("list" => $list);

                return json($result);
            }

        }

    }


}

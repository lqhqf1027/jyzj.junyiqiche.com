<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;

use addons\cms\controller\wxapp\User as AddonsUser;

/**
 * 奖品领取记录管理
 *
 * @icon fa fa-circle-o
 */
class Prizerecord extends Backend
{
    
    /**
     * Record模型对象
     * @var \app\admin\model\cms\prize\Record
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Prizerecord;

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
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('user.mobile', true);
            $total = $this->model
                    ->with(['prize','user'])
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with(['prize','user'])
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();
            
            foreach ($list as $k => $v)
            {
                $list[$k]['user']['nickname'] = AddonsUser::emoji_decode($list[$k]['user']['nickname']);
            }

            foreach ($list as $row) {
                
                
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
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
            // pr($params);
            // die;
            if ($params['conversion_code'] == $row['conversion_code']) {

                $result = $this->model->where('conversion_code', $params['conversion_code'])->setField(['is_use' => 1, 'accepttime' => time()]);

                if ($result) {
                    $this->success('领取奖品成功');
                }
                else {
                    $this->error('领取奖品失败');
                }
            }
            else {
                $this->error('奖品兑换码不正确，请重新输入');
            }
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
}

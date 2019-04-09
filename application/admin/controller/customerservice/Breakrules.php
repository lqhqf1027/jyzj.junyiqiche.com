<?php

namespace app\admin\controller\customerservice;

use app\common\controller\Backend;
use think\Db;

/**
 * 违章信息管理
 *
 * @icon fa fa-circle-o
 */
class Breakrules extends Backend
{

    /**
     * Inquiry模型对象
     * @var \app\admin\model\violation\Inquiry
     */
    protected $model = null;
    protected $noNeedRight = ['index','prepare_feedback','already_feedback','handle_feedback','handle_feedback_lots'];
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\violation\Inquiry;
        $this->view->assign("carTypeList", $this->model->getCarTypeList());
        $this->view->assign("customerStatusList", $this->model->getCustomerStatusList());
    }


    public function index()
    {
        return $this->view->fetch();
    }

    /**
     * 待反馈
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function prepare_feedback()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username,license_plate_number');
            $total = $this->model
                ->where($where)
                ->where([
                    'feedback' => null,
                    'customer_status' => 1
                ])
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->where([
                    'customer_status' => 1,
                    'feedback' => null
                ])
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
    }

    /**
     * 已反馈
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function already_feedback()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username,license_plate_number');
            $total = $this->model
                ->where($where)
                ->where(function ($query){
                    $query->where('feedback','not null')
                        ->where('customer_status', 1);
                })
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where(function ($query){
                    $query->where('feedback','not null')
                    ->where('customer_status', 1);
                })
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
    }

    /**
     * 反馈
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function handle_feedback()
    {
        if ($this->request->isAjax()) {
            $content = input('content');

            $ids = input('ids');

            $res = Db::name('violation_inquiry')
                ->where('id', $ids)
                ->update([
                    'feedback' => $content,
                    'feedbacktime' => time()
                ]);

            if ($res) {
                $this->success('', '', 'success');
            } else {
                $this->error('反馈失败');
            }
        }
    }

    /**
     * 批量反馈
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function handle_feedback_lots()
    {
        if ($this->request->isAjax()) {
            $content = input('content');

            $ids = input('ids');

            $ids = json_decode($ids,true);

            $res = Db::name('violation_inquiry')
                ->where('id', 'in',$ids)
                ->update([
                    'feedback' => $content,
                    'feedbacktime' => time()
                ]);

            if ($res) {
                $this->success('', '', $res);
            } else {
                $this->error('反馈失败');
            }
        }
    }


}

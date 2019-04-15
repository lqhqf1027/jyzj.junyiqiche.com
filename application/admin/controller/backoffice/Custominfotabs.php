<?php

namespace app\admin\controller\backoffice;

use app\admin\model\Admin;
use app\common\controller\Backend;
// use app\admin\controller\wechat\WechatMessage;
use app\admin\model\Admin as adminModel;
use think\Config;
use think\Db;
use app\common\library\Email;

/**
 * 多表格示例
 *
 * @icon fa fa-table
 * @remark 当一个页面上存在多个Bootstrap-table时该如何控制按钮和表格
 */
class Custominfotabs extends Backend
{

    protected $model = null;
    protected $noNeedRight = ['*'];
    protected $dataLimit = false; //表示不启用，显示所有数据
    static protected $token = null;

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 查看
     */
    public function index()
    {
        $this->model = model('CustomerResource');
        $this->loadlang('backoffice/custominfotabs');

        return $this->view->fetch();
    }

    /**
     * 新客户
     * @return string|\think\response\Json
     * @throws \think\Exception
     */
    public function newCustomer()
    {
        $this->model = model('CustomerResource');

        $this->view->assign("genderdataList", $this->model->getGenderdataList());

        //当前是否为关联查询
        $this->relationSearch = true;

        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();


            $total = $this->model
                ->with(['backoffice' => function ($query) {
                    $query->withField(['nickname', 'avatar']);
                }])
                ->where($where)
                ->where('backoffice_id', 'not null')
                ->where('sales_id', null)
                ->order($sort, $order)
                ->count();


            $list = $this->model
                ->with(['backoffice' => function ($query) {
                    $query->withField(['nickname', 'avatar']);
                }])
                ->where($where)
                ->where('backoffice_id', 'not null')
                ->where('sales_id', null)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $k => $v) {

                $list[$k]['avatar_url'] = Config::get('upload')['cdnurl'];
            }


            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch('index');
    }


    /**
     * 已分配给销售的用户
     * @return string|\think\response\Json
     * @throws \think\Exception
     */
    public function assignedCustomers()
    {
        $this->model = model('CustomerResource');

        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $total = $this->model
                ->with(['backoffice' => function ($query) {
                    $query->withField(['nickname', 'avatar']);
                }, 'admin' => function ($query) {
                    $query->withField(['id', 'nickname', 'avatar']);
                }])
                ->where($where)
                ->where(['sales_id' => ['neq', 'null']])
                ->order($sort, $order)
                ->count();


            $list = $this->model
                ->with(['backoffice' => function ($query) {
                    $query->withField(['nickname', 'avatar']);
                }, 'admin' => function ($query) {
                    $query->withField(['id', 'nickname', 'avatar']);
                }])
                ->where($where)
                ->where(['sales_id' => ['neq', 'null']])
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();

            foreach ($list as $k => $v) {
                $department = Db::name('auth_group_access')
                    ->alias('a')
                    ->join('auth_group b', 'a.group_id = b.id')
                    ->where('a.uid', $v['admin']['id'])
                    ->value('b.name');
                $list[$k]['admin']['department'] = $department;
                $list[$k]['avatar_url'] = Config::get('upload')['cdnurl'];
            }

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }

        return $this->view->fetch('index');
    }


    /**单个分配客户给销售
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function admeasure($ids = NULL)
    {
        $this->model = model('CustomerResource');
        $id = $this->model->get(['id' => $ids]);
        //销售顾问
        $saleList = $this->salesList();
        // pr($saleList);
        // die;
        $this->view->assign([
            'Sale' => $saleList
        ]);
        // $this->assignconfig('id', $id->id);

        if ($this->request->isPost()) {

            $params = $this->request->post('row/a');

            $result = $this->model->save([
                'sales_id' => $params['id'],
                'distributsaletime' => time()
            ], function ($query) use ($id) {
                $query->where('id', $id->id);
            });
            if ($result) {

                // $data = sales_inform();

                // $email = new Email;
                // // $receiver = "haoqifei@cdjycra.club";
                // $receiver = DB::name('admin')->where('id', $params['id'])->value('email');
                // $result_s = $email
                //     ->to($receiver)
                //     ->subject($data['subject'])
                //     ->message($data['message'])
                //     ->send();
                // if ($result_s) {
                    $this->success('', '', 3);
                // } else {
                //     $this->error('邮箱发送失败');
                // }

            } else {
                $this->error();
            }
        }

        return $this->view->fetch();

    }


    /**批量分配
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function batch($ids = null)
    {
        $this->model = model('CustomerResource');
        //销售顾问
        $saleList = $this->salesList();
        $this->view->assign([
            'Sale' => $saleList
        ]);

        if ($this->request->isPost()) {

            $params = $this->request->post('row/a');

            $result = $this->model->save(['sales_id' => $params['id'], 'distributsaletime' => time()], function ($query) use ($ids) {
                $query->where('id', 'in', $ids);
            });
            if ($result) {

                // $data = sales_inform();

                // $email = new Email;
                // $receiver = DB::name('admin')->where('id', $params['id'])->value('email');
                // $result_s = $email
                //     ->to($receiver)
                //     ->subject($data['subject'])
                //     ->message($data['message'])
                //     ->send();
                // if ($result_s) {
                    $this->success();
                // } else {
                //     $this->error('邮箱发送失败');
                // }

            } else {

                $this->error(__('Parameter %s can not be empty', ''));
            }
        }
        return $this->view->fetch();
    }

    //得到销售顾问
    public function salesList()
    {
        $sale = Admin::field('id,nickname,rule_message')->where(function ($query) {
            $query->where([
                'rule_message' => 'message6',
                'status' => 'normal'
            ]);
        })->select();

        $saleList = array();

        if (count($sale) > 0) {

            foreach ($sale as $k => $v) {
                $saleList[] = ['id' => $v['id'], 'nickname' => $v['nickname']];
            }

        }

        return $saleList;
    }


}


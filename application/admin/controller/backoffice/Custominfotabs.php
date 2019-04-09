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
     * 得到可行管理员ID
     * @return array
     */
    public static function getUserId()
    {
        $back = Admin::where("rule_message", 'in', ['message13', 'message20', 'message24'])
            ->field("id")
            ->select();

        $backArray = array();
        $backArray['back'] = array();
        $backArray['admin'] = array();
        $backArray['manager'] = Db::name('admin')
            ->where('rule_message', 'in', ['message3', 'message4', 'message22'])
            ->column('id');

        foreach ($back as $value) {
            array_push($backArray['back'], $value['id']);
        }

        $superAdmin = Admin::where("rule_message", "message21")
            ->field("id")
            ->select();

        foreach ($superAdmin as $value) {
            array_push($backArray['admin'], $value['id']);
        }

        return $backArray;
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
        $canUseId = $this->getUserId();
        $this->model = model('CustomerResource');

        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $get_id = null;
        if (in_array($this->auth->id, $canUseId['manager'])) {
            $get_id = $this->get_manager();

        }

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
                ->with(['platform', 'backoffice' => function ($query) {
                    $query->withField(['nickname', 'avatar']);
                }])
                ->where($where)
                ->where(function ($query) use ($canUseId, $get_id) {
                    if (in_array($this->auth->id, $canUseId['back'])) {
                        $query->where('backoffice_id', $this->auth->id);

                    } else if (in_array($this->auth->id, $canUseId['manager'])) {
                        $query->where('backoffice_id', 'in', $get_id);
                    } else if (in_array($this->auth->id, $canUseId['admin'])) {
                        $query->where('backoffice_id', 'not null');

                    }
                    $query->where('sales_id', 'null')
                        ->where('platform_id', 'in', [2, 3, 4, 8]);

                })
                ->order($sort, $order)
                ->count();


            $list = $this->model
                ->with(['platform', 'backoffice' => function ($query) {
                    $query->withField(['nickname', 'avatar']);
                }])
                ->where($where)
                ->order($sort, $order)
                ->where(function ($query) use ($canUseId, $get_id) {
                    if (in_array($this->auth->id, $canUseId['back'])) {
                        $query->where('backoffice_id', $this->auth->id);

                    } else if (in_array($this->auth->id, $canUseId['manager'])) {
                        $query->where('backoffice_id', 'in', $get_id);
                    } else if (in_array($this->auth->id, $canUseId['admin'])) {
                        $query->where('backoffice_id', 'not null');

                    }
                    $query->where('sales_id', 'null')
                        ->where('platform_id', 'in', [2, 3, 4, 8]);

                })
                ->limit($offset, $limit)
                ->select();


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
        $canUseId = $this->getUserId();
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
                ->with(['platform', 'backoffice' => function ($query) {
                    $query->withField(['nickname', 'avatar']);
                }, 'admin' => function ($query) {
                    $query->withField(['id', 'nickname', 'avatar']);
                }])
                ->where($where)
                ->where(function ($query) use ($canUseId) {
                    $where = [
                        'sales_id' => ['neq', 'null'],
                        'platform_id' => ['in', [2, 3, 4, 8]]
                    ];
                    if (in_array($this->auth->id, $canUseId['back'])) {
                        $where['backoffice_id'] = $this->auth->id;
                    } else if (in_array($this->auth->id, $canUseId['manager'])) {
                        $where['backoffice_id'] = ['in', $this->get_manager()];
                    } else if (in_array($this->auth->id, $canUseId['admin'])) {
                        $where['backoffice_id'] = ['neq', 'null'];
                    }

                    $query->where($where);
                })
                ->order($sort, $order)
                ->count();


            $list = $this->model
                ->with(['platform', 'backoffice' => function ($query) {
                    $query->withField(['nickname', 'avatar']);
                }, 'admin' => function ($query) {
                    $query->withField(['id', 'nickname', 'avatar']);
                }])
                ->where($where)
                ->order($sort, $order)
                ->where(function ($query) use ($canUseId) {
                    $where = [
                        'sales_id' => ['neq', 'null'],
                        'platform_id' => ['in', [2, 3, 4, 8]]
                    ];
                    if (in_array($this->auth->id, $canUseId['back'])) {
                        $where['backoffice_id'] = $this->auth->id;
                    } else if (in_array($this->auth->id, $canUseId['manager'])) {
                        $where['backoffice_id'] = ['in', $this->get_manager()];
                    } else if (in_array($this->auth->id, $canUseId['admin'])) {
                        $where['backoffice_id'] = ['neq', 'null'];
                    }

                    $query->where($where);

                })
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
        $saleList = $this->salesList();
        $this->view->assign([
            'firstSale' => $saleList['message8'] ? $saleList['message8'] : null,
            'secondSale' => $saleList['message9'] ? $saleList['message9'] : null,
            'thirdSale' => $saleList['message23'] ? $saleList['message23'] : null
        ]);


        $this->assignconfig('id', $id->id);

        if ($this->request->isPost()) {


            $params = $this->request->post('row/a');

            $result = $this->model->save([
                'sales_id' => $params['id'],
                'distributsaletime' => time()
            ], function ($query) use ($id) {
                $query->where('id', $id->id);
            });
            if ($result) {

                $data = sales_inform();

                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = DB::name('admin')->where('id', $params['id'])->value('email');
                $result_s = $email
                    ->to($receiver)
                    ->subject($data['subject'])
                    ->message($data['message'])
                    ->send();
                if ($result_s) {
                    $this->success('', '', 3);
                } else {
                    $this->error('邮箱发送失败');
                }

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

        $saleList = $this->salesList();

        $this->view->assign([
            'firstSale' => $saleList['message8'] ? $saleList['message8'] : null,
            'secondSale' => $saleList['message9'] ? $saleList['message9'] : null,
            'thirdSale' => $saleList['message23'] ? $saleList['message23'] : null
        ]);

        if ($this->request->isPost()) {

            $params = $this->request->post('row/a');

            $result = $this->model->save(['sales_id' => $params['id'], 'distributsaletime' => time()], function ($query) use ($ids) {
                $query->where('id', 'in', $ids);
            });
            if ($result) {

                $data = sales_inform();

                $email = new Email;
                $receiver = DB::name('admin')->where('id', $params['id'])->value('email');
                $result_s = $email
                    ->to($receiver)
                    ->subject($data['subject'])
                    ->message($data['message'])
                    ->send();
                if ($result_s) {
                    $this->success();
                } else {
                    $this->error('邮箱发送失败');
                }

            } else {

                $this->error(__('Parameter %s can not be empty', ''));
            }
        }
        return $this->view->fetch();
    }

    public function salesList()
    {
        $sale = Admin::field('id,nickname,rule_message')->where(function ($query) {
            $query->where([
                'rule_message' => ['in', ['message8', 'message9', 'message23']],
                'status' => 'normal'
            ]);
        })->select();

        $saleList = array();

        if (count($sale) > 0) {

            foreach ($sale as $k => $v) {
                $saleList[$v['rule_message']][] = ['id' => $v['id'], 'nickname' => $v['nickname']];
            }

        }

        return $saleList;
    }


    /**
     * 销售经理获取自己部门内勤
     * @return array
     */
    public function get_manager()
    {
        $message = Db::name('admin')
            ->where('id', $this->auth->id)
            ->value('rule_message');

        return $this->getAdmin([
            'rule_message' => $message,
        ]);

    }

    public static function getAdmin($where)
    {
        return Db::name('admin')
            ->where($where)
            ->where('status', 'normal')
            ->column('id');
    }


}


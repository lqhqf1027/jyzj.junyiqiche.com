<?php

namespace app\admin\controller\salesmanagement;

use app\common\controller\Backend;
use think\Db;

/**
 * 渠道管理列管理
 *
 * @icon fa fa-circle-o
 */
class Channellisttabs extends Backend
{

    /**
     * Channeltabs模型对象
     * @var \app\admin\model\Channeltabs
     */
    protected $model = null;

    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Referee;

    }

    /**
     * 如登录的是销售或者销售经理,得到满足条件的数据进行显示
     * @param $login
     * @return array
     *
     */
    public function satisfy_id()
    {
        $canUseId = $this->getUserId();

        $message = null;

        //得到当前销售所在部门
        if (in_array($this->auth->id, $canUseId['sales'])) {


            $all_sales = [$this->auth->id];
        } else if (in_array($this->auth->id, $canUseId['manager'])) {

            $manager = Db::name('admin')
                ->where('id', $this->auth->id)
                ->value('rule_message');

            switch ($manager) {
                case 'message3':
                    $message = 'message8';
                    break;
                case 'message4':
                    $message = 'message9';
                    break;
                case 'message22':
                    $message = 'message23';
                    break;
            }

            //得到该部门所有销售
            $all_sales = Db::name('admin')
                ->where('rule_message', $message)
                ->where('status','normal')
                ->column('id');
        }




        $new_car = Db::name("sales_order")
            ->where("admin_id", 'in', $all_sales)
            ->where("referee_id", "not null")
            ->column("referee_id");

        $used_car = Db::name("second_sales_order")
            ->where("admin_id", 'in', $all_sales)
            ->where("referee_id", "not null")
            ->column("referee_id");

        $full_car = Db::name("full_parment_order")
            ->where("admin_id", 'in', $all_sales)
            ->where("referee_id", "not null")
            ->column("referee_id");

        $satisfy = array_merge($new_car, $used_car, $full_car);


        return $satisfy;

    }

    public function index()
    {
        $login = $this->auth->id;

        $canUseId = $this->getUserId();


        $this->model = new \app\admin\model\Referee;

        $referee = null;
        $phone = null;

        //如果操作员是销售,得到对应销售的客户电话
        if (in_array($login, $canUseId['sales'])) {

            $referee = $this->satisfy_id();
            $phone = Db::name("referee")
                ->where("id", 'in', $referee)
                ->column("customer_phone");
        } else if (in_array($login, $canUseId['manager'])) {

            $referee = $this->satisfy_id();
            $phone = Db::name("referee")
                ->where("id", 'in', $referee)
                ->column("customer_phone");

        }

        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username', true);
            $total = $this->model
                ->with(['models' => function ($query) {
                    $query->withField('name');
                }, 'admin' => function ($query) {
                    $query->withField('nickname');
                }])
                ->where($where)
                ->where(function ($query) use ($login, $canUseId, $referee, $phone) {
                    if (in_array($login, $canUseId['sales']) || in_array($login, $canUseId['manager'])) {
                        $query->where('customer_phone', 'in', $phone);
                    }


                })
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['models' => function ($query) {
                    $query->withField('name');
                }, 'admin' => function ($query) {
                    $query->withField('nickname');
                }])
                ->where($where)
                ->where(function ($query) use ($login, $canUseId, $referee, $phone) {
                    if (in_array($login, $canUseId['sales']) || in_array($login, $canUseId['manager'])) {
                        $query->where('customer_phone', 'in', $phone);
                    }
                })
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }




    /**得到可行管理员ID
     * @return array
     */
    public function getUserId()
    {
        $result = array();
        $result['sales'] = Db::name('admin')
            ->where('rule_message', 'in', ['message8', 'message9', 'message23'])
            ->column('id');

        $result['admin'] = Db::name('admin')
            ->where('rule_message', 'message21')
            ->column('id');

        $result['manager'] = Db::name('admin')
            ->where('rule_message', 'in', ['message3', 'message4', 'message22'])
            ->column('id');


        return $result;
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
            if ($params) {
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                } catch (\think\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }


    /**
     * 删除
     */
    public function del($ids = "")
    {
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $count = $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();
            $count = 0;
            foreach ($list as $k => $v) {
                $count += $v->delete();
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

}

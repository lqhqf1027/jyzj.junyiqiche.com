<?php

namespace app\admin\controller\salesmanagement;

use app\admin\model\CustomerResource;
use app\common\controller\Backend;
use think\Db;
use think\Model;
use think\Config;

/**
 * 客户列管理
 *
 * @icon fa fa-circle-o
 */
class Customerlisttabs extends Backend
{

    /**
     * Customertabs模型对象
     * @var \app\admin\model\Customertabs
     */
    protected $model = null;
//    protected $searchFields = 'id,username';
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('CustomerResource');

    }

    /**得到可操作的管理员ID
     * @return array
     */
    public function getUserId()
    {
        $this->model = model("Admin");
        $back = $this->model->where("rule_message", 'in', ["message8", 'message9', 'message23'])
            ->field("id")
            ->select();

        $backArray = array();
        $backArray['sale'] = array();
        $backArray['admin'] = array();
        $backArray['backoffice'] = array();
        foreach ($back as $value) {
            array_push($backArray['sale'], $value['id']);
        }

        $superAdmin = $this->model->where("rule_message", 'in', ['message1', 'message21','message6'])
            ->field("id")
            ->select();

        $backoffice = $this->model->where("rule_message", 'in', ['message13', 'message20','message24'])
            ->field("id")
            ->select();

        foreach ($superAdmin as $value) {
            array_push($backArray['admin'], $value['id']);
        }

        foreach ($backoffice as $value) {
            array_push($backArray['backoffice'], $value['id']);
        }

        return $backArray;
    }

    /**
     * @return array|string、
     */
    public function special()
    {
        $message = Db::name('admin')
            ->where('id', $this->auth->id)
            ->value('rule_message');
        switch ($message) {
            case 'message3':
            case 'message13':
                return Db::name('admin')
                    ->where('rule_message', 'message8')
                    ->where('status','normal')
                    ->column('id');
            case 'message4':
            case 'message20':
                return Db::name('admin')
                    ->where('rule_message', 'message9')
                    ->where('status','normal')
                    ->column('id');
            case 'message22':
            case 'message24':
                return Db::name('admin')
                    ->where('rule_message', 'message23')
                    ->where('status','normal')
                    ->column('id');
            default:
                return '没有该角色功能';
        }
    }

    /**
     * 排除销售列表电话号码用户
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function noPhone()
    {
        $phone = Db::table("crm_sales_order")
            ->field("phone")
            ->select();

        $noPhone = array();

        if (count($phone) > 0) {
            foreach ($phone as $value) {
                array_push($noPhone, $value['phone']);
            }
        } else {
            $noPhone[0] = -1;
        }

        return $noPhone;
    }

    public function index()
    {

        $this->loadlang('salesmanagement/customerlisttabs');


        return $this->view->fetch();
    }



    /**
     * 新客户
     * @return string|\think\response\Json
     * @throws \think\Exception
     */
    public function newCustomer()
    {


        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {

            $result = $this->encapsulationSelect();


            return json($result);
        }

        return $this->view->fetch("index");

    }


    /**
     * 查询封装
     *
     * @param $where
     * @param null $customerlevel 客户等级
     * @param $sort
     * @param $order
     * @param $offset
     * @param $limit
     * @return array
     */
    public function encapsulationSelect($customerlevel = null)
    {

        //如果发送的来源是Selectpage，则转发到Selectpage

        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        list($where, $sort, $order, $offset, $limit) = $this->buildparams('username', true);
        $authId = $this->auth->id; // 当前操作员id
        $noPhone = $this->noPhone(); //判断销售单里的电话没有和客户池电话相同的数据
        $getUserId = $this->getUserId();//获取当前可操作权限的id
        $id_sale = $this->special();      //判断是否为销售经理
        $total = model('CustomerResource')
            ->where($where)
            ->with(['platform' => function ($query) {
                $query->withField('name');
            }, 'admin' => function ($query) {
                $query->withField(['id','nickname', 'avatar']);
            }])
            ->where(function ($query) use ($noPhone, $authId, $getUserId, $customerlevel, $id_sale) {

                if ($customerlevel == "overdue") {
                    //超级管理员
                    if (in_array($authId, $getUserId['admin'])) {
                        $query->where(['phone' => ['not in', $noPhone], 'followuptimestamp' => ['<', time()], 'customerlevel' => ['neq', 'giveup']]);
                    } //当前销售
                    else if (in_array($authId, $getUserId['sale'])) {
                        $query->where(['phone' => ['not in', $noPhone], 'followuptimestamp' => ['<', time()], 'sales_id' => $authId, 'customerlevel' => ['neq', 'giveup']]);

                    } else {

                        $query->where(['phone' => ['not in', $noPhone], 'followuptimestamp' => ['<', time()], 'sales_id' => ['in', $id_sale], 'customerlevel' => ['neq', 'giveup']]);
                    }
                } else if ($customerlevel == null) {
                    //超级管理员
                    if (in_array($authId, $getUserId['admin'])) {
                        $query->where(['phone' => ['not in', $noPhone], 'backoffice_id' => ['neq',''], 'sales_id' => ['neq',''], 'customerlevel' => null]);
                    } //当前销售
                    else if (in_array($authId, $getUserId['sale'])) {
                        $query->where(['phone' => ['not in', $noPhone], 'sales_id' => $authId, 'customerlevel' => null]);

                    } else {
                        $query->where(['phone' => ['not in', $noPhone], 'sales_id' => ['in', $id_sale], 'customerlevel' => null]);

                    }
                } else if ($customerlevel == "giveup") {
                    //超级管理员
                    if (in_array($authId, $getUserId['admin'])) {
                        $query->where(['customerlevel' => $customerlevel]);
                    } //当前销售
                    else if (in_array($authId, $getUserId['sale'])) {
                        $query->where(['sales_id' => $authId, 'customerlevel' => $customerlevel]);

                    } else {
                        $query->where(['sales_id' => ['in', $id_sale], 'customerlevel' => $customerlevel]);

                    }
                } else {
                    //超级管理员
                    if (in_array($authId, $getUserId['admin'])) {

                        $query->where(['customerlevel' => $customerlevel, 'phone' => ['not in', $noPhone], 'followuptimestamp' => ['>', time()]]);
                    } //当前销售
                    else if (in_array($authId, $getUserId['sale'])) {
                        $query->where(['customerlevel' => $customerlevel, 'phone' => ['not in', $noPhone], 'sales_id' => $authId, 'followuptimestamp' => ['>', time()]]);
                    } else {
                        $query->where(['customerlevel' => $customerlevel, 'phone' => ['not in', $noPhone], 'sales_id' => ['in', $id_sale], 'followuptimestamp' => ['>', time()]]);

                    }
                }

            })
            ->order($sort, $order)
            ->count();


        $list = model('CustomerResource')
            ->where($where)
            ->with(['platform' => function ($query) {
                $query->withField('name');
            }, 'admin' => function ($query) {
                $query->withField(['id','nickname', 'avatar']);
            }])
            ->where(function ($query) use ($noPhone, $authId, $getUserId, $customerlevel, $id_sale) {

                if ($customerlevel == "overdue") {
                    //超级管理员
                    if (in_array($authId, $getUserId['admin'])) {
                        $query->where(['phone' => ['not in', $noPhone], 'followuptimestamp' => ['<', time()], 'customerlevel' => ['neq', 'giveup']]);
                    } //当前销售
                    else if (in_array($authId, $getUserId['sale'])) {
                        $query->where(['phone' => ['not in', $noPhone], 'followuptimestamp' => ['<', time()], 'sales_id' => $authId, 'customerlevel' => ['neq', 'giveup']]);

                    } else {

                        $query->where(['phone' => ['not in', $noPhone], 'followuptimestamp' => ['<', time()], 'sales_id' => ['in', $id_sale], 'customerlevel' => ['neq', 'giveup']]);
                    }
                } else if ($customerlevel == null) {
                    //超级管理员
                    if (in_array($authId, $getUserId['admin'])) {
                        $query->where(['phone' => ['not in', $noPhone], 'backoffice_id' => ['neq',''], 'sales_id' => ['neq',''],  'customerlevel' => null]);
                    } //当前销售
                    else if (in_array($authId, $getUserId['sale'])) {
                        $query->where(['phone' => ['not in', $noPhone], 'sales_id' => $authId, 'customerlevel' => null]);

                    } else {
                        $query->where(['phone' => ['not in', $noPhone], 'sales_id' => ['in', $id_sale], 'customerlevel' => null]);

                    }
                } else if ($customerlevel == "giveup") {
                    //超级管理员
                    if (in_array($authId, $getUserId['admin'])) {
                        $query->where(['customerlevel' => $customerlevel]);
                    } //当前销售
                    else if (in_array($authId, $getUserId['sale'])) {
                        $query->where(['sales_id' => $authId, 'customerlevel' => $customerlevel]);

                    } else {
                        $query->where(['sales_id' => ['in', $id_sale], 'customerlevel' => $customerlevel]);

                    }
                } else {
                    //超级管理员
                    if (in_array($authId, $getUserId['admin'])) {

                        $query->where(['customerlevel' => $customerlevel, 'phone' => ['not in', $noPhone], 'followuptimestamp' => ['>', time()]]);
                    } //当前销售
                    else if (in_array($authId, $getUserId['sale'])) {
                        $query->where(['customerlevel' => $customerlevel, 'phone' => ['not in', $noPhone], 'sales_id' => $authId, 'followuptimestamp' => ['>', time()]]);
                    } else {
                        $query->where(['customerlevel' => $customerlevel, 'phone' => ['not in', $noPhone], 'sales_id' => ['in', $id_sale], 'followuptimestamp' => ['>', time()]]);

                    }
                }
            })
            ->order($sort, $order)
            ->limit($offset, $limit)
            ->select();
        foreach ($list as $k => $row) {

            $row->visible(['id', 'username', 'jobs','phone', 'age', 'genderdata', 'customerlevel', 'sales_id', 'followupdate', 'feedbacktime', 'distributsaletime', 'reason', 'giveup_time','invalidtime']);
            $row->visible(['platform']);
            $row->getRelation('platform')->visible(['name']);
            $row->visible(['admin']);
            $row->getRelation('admin')->visible(['id','nickname', 'avatar']);
            //转化头像
            $list[$k]['admin']['avatar'] = Config::get('upload')['cdnurl'] . $row['admin']['avatar'];

        }
        $list = collection($list)->toArray();

        foreach ($list as $k=>$v){
            $department = Db::name('auth_group_access')
                ->alias('a')
                ->join('auth_group b','a.group_id = b.id')
                ->where('a.uid',$v['admin']['id'])
                ->value('b.name');
            $list[$k]['admin']['department'] = $department;
        }

        return array('total' => $total, "rows" => $list);
    }

    /**
     * 待联系
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function relation()
    {

        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {

            $result = $this->encapsulationSelect('relation');
            //关联反馈表内容
            foreach ($result['rows'] as $key => $value) {
                $result['rows'][$key]['feedbackContent'] = $this->tableShowF_d($value['id']);
            }
            return json($result);
        }

        return $this->view->fetch("index");

    }


    /**
     * 有意向
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function intention()
    {

        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {

            $result = $this->encapsulationSelect('intention');
            //关联反馈表内容
            foreach ($result['rows'] as $key => $value) {
                $result['rows'][$key]['feedbackContent'] = $this->tableShowF_d($value['id']);
            }

            return json($result);
        }

        return $this->view->fetch("index");

    }


    /**
     * 暂无意向
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function nointention()
    {

        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $result = $this->encapsulationSelect('nointention');
            //关联反馈表内容
            foreach ($result['rows'] as $key => $value) {
                $result['rows'][$key]['feedbackContent'] = $this->tableShowF_d($value['id']);
            }

            return json($result);
        }

        return $this->view->fetch("index");

    }


    /**
     * 已放弃
     * @return string|\think\response\Json
     * @throws \think\Exception
     */
    public function giveup()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
            if ($this->request->isAjax()) {

            $result = $this->encapsulationSelect('giveup');

            return json($result);
        }

        return $this->view->fetch("index");

    }


    /**
     * 跟进时间过期用户
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function overdue()
    {

        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {

            $result = $this->encapsulationSelect('overdue');
            //关联反馈表内容
            foreach ($result['rows'] as $key => $value) {
                $result['rows'][$key]['feedbackContent'] = $this->tableShowF_d($value['id']);
            }

            return json($result);
        }

        return $this->view->fetch("index");

    }

    /**
     *添加
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function add()
    {

        $this->model = model('CustomerResource');
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $platform = collection(model('Platform')->all(['id' => array('in', '5,6,7')]))->toArray();


        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");


            if ($params) {
                $params['sales_id'] = $this->auth->id;

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($this->model->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }


                if ($result) {
                    $this->success();
                } else {
                    $this->error();
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $arr = array();
        foreach ($platform as $value) {
            $arr[$value['id']] = $value['name'];
        }
        $this->assign('platform', $arr);
        return $this->view->fetch();
    }


    /**
     * 编辑 单个反馈
     */
    public function edit($ids = NULL)
    {
        $this->model = model('CustomerResource');
        $row = $this->model->get($ids);
        if (empty($row['followupdate'])) {
            $this->view->assign("default_date", date("Y-m-d", time()));
        }
        $this->view->assign("costomlevelList", $this->model->getCustomerlevelList());

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
            //转换为数组，截取等级
            $customerlevel = reset(explode(',', $params['level']));
            $params['customerlevel'] = $customerlevel;
            //获取等级内容
            $customerlevelText = end(explode(',', $params['level']));

            if ($params) {
                unset($params['level']);
                $sql1 = $this->model->where('id', $ids)->update([
                    'feedbacktime' => time(),
                    'followuptimestamp' => strtotime($params['followupdate']),
                    'customerlevel' => $params['customerlevel'],
                    'followupdate' => $params['followupdate'],
                    'feedback' => $params['feedback']
                ]);

                $cnlevel = "";
                switch ($params['customerlevel']) {
                    case "relation":
                        $cnlevel = "待联系";
                        break;
                    case "intention":
                        $cnlevel = "有意向";
                        break;
                    case "nointention":
                        $cnlevel = "暂无意向";
                        break;
                    case "giveup":
                        $cnlevel = "已放弃";
                        break;
                }

                $data = [
                    'feedbackcontent' => $params['feedback'],
                    'feedbacktime' => time(),
                    'customer_id' => $ids,
                    'customerlevel' => $cnlevel,
                    'followupdate' => $params['followupdate']
                ];
                $sql2 = Db::table("crm_feedback_info")->insert($data);


                if ($sql1 && $sql2) {
                    $this->success('操作成功', null, $customerlevelText);
                } else {
                    $this->error();
                }

            }
            $this->error(__('Parameter %s can not be empty', ''));
        }


        $this->view->assign("row", $row);


        return $this->view->fetch();
    }

    /**
     *放弃
     */
    public function ajaxGiveup()
    {
        if ($this->request->isAjax()) {
            $id = input("id");
            $text = input("text"); //放弃原因

            $this->model = model('CustomerResource');


            $result = $this->model
                ->where("id", $id)
                ->update([
                    "customerlevel" => "giveup",
                    'reason' => $text,
                    'giveup_time' => time()
                ]);
            //如果放弃的客户，更新字段到反馈表  feedback_info 中
            $feedback = Db::name('feedback_info')->insert(['feedbackcontent'=>$text,'feedbacktime'=>time(),'customerlevel'=>'已放弃','customer_id'=>$id]);
            if ($result && $feedback) {
                $this->success();
            }
            else{
                $this->error('放弃失败',null,[$result,$feedback]);
            }

        }

    }

    /**
     * 批量反馈
     * @param null $ids
     * @return string
     * @throws \think\Exception
     */
    public function batchfeedback($ids = NULL)
    {
        $this->model = model('CustomerResource');
        $this->view->assign("costomlevelList", $this->model->getCustomerlevelList());
        $row = $this->model->get($ids);
        $this->view->assign("row", $row);
        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $ids = explode(',', $ids);

            $params = $this->request->post("row/a");
            foreach ($ids as $value) {
                $params_new[] = ['id' => $value, 'customerlevel' => $params['customerlevel'], 'followupdate' => $params['followupdate'], 'feedback' => $params['feedback']];

            }

            if ($params_new) {
                try {
                    //是否采用模型验证
                    $result = $this->model->isUpdate()->saveAll($params_new);
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

        return $this->view->fetch();
    }

    /**
     * 批量放弃
     */
    public function ajaxBatchGiveup()
    {

        if ($this->request->isAjax()) {
            $this->model = model('CustomerResource');
            $id = input("id");
            $text = input("text");
            $id = json_decode($id, true);

            
            $result = $this->model
                ->where('id', 'in', $id)
                ->update([
                    'customerlevel' => 'giveup',
                    'reason' => $text,
                    'giveup_time'=>time()
                ]);
            foreach ($id as $k=>$v) {
                $data[] = [
                    'feedbackcontent'=>$text,
                    'feedbacktime'=>time(),
                    'customerlevel'=>'已放弃',
                    'customer_id'=> $v
                ];
            }
            // pr($data);
            // die;
            $feedback = Db::name('feedback_info')->insertAll($data);

            if ($result && $feedback) {
                $this->success();
            } else {
                $this->error('批量放弃失败',null,[$result,$feedback]);
            }
        }


    }


    /**
     * Notes:查看跟进结果
     * User: glen9
     * Date: 2018/9/9
     * Time: 12:16
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function showFeedback($ids = NULL)
    {

        $data = Db::table("crm_feedback_info")
            ->where("customer_id", $ids)
            ->order("feedbacktime")
            ->select();

        foreach ($data as $key => $value) {
            $data[$key]['feedbacktime'] = date("Y-m-d H:i:s", intval($value['feedbacktime']));
            $data[$key]['count'] = count($data);
        }
        $this->view->assign([
            'feedback_data' => $data
        ]);
        return $this->view->fetch();
    }

    /**
     * Notes:表格列的反馈展示
     * User: glen9
     * Date: 2018/9/9
     * Time: 12:32
     * @param $cusId  客户池表的主键id
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function tableShowF_d($cusId)
    {
        return Db::name('feedback_info')->where("customer_id", $cusId)->field(['feedbackcontent', 'feedbacktime', 'customerlevel'])->select();
    }

}

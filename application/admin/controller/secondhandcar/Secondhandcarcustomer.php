<?php

namespace app\admin\controller\secondhandcar;

use app\common\controller\Backend;
use think\Db;
use think\Config;

/**
 * 二手车客户信息
 *
 * @icon fa fa-circle-o
 */
class Secondhandcarcustomer extends Backend
{
    
    /**
     * Secondpeople模型对象
     * @var \app\admin\model\Secondpeople
     */
    protected $model = null;
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();

    }
    

    /**二手车 */
    public function index()
    {
        $this->model = new \app\admin\model\SecondSalesOrder;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username,plansecond.licenseplatenumber', true);
            $total = $this->model
                    ->with(['plansecond' => function ($query) {
                        $query->withField('licenseplatenumber,newpayment,monthlypaymen,periods,totalprices,bond,tailmoney');
                    }, 'admin' => function ($query) {
                        $query->withField('nickname');
                    }, 'models' => function ($query) {
                        $query->withField('name,models_name');
                    }])
                    ->where($where)
                    ->where('review_the_data', 'the_car')
                    ->order($sort, $order)
                    ->count();


            $list = $this->model
                    ->with(['plansecond' => function ($query) {
                        $query->withField('licenseplatenumber,newpayment,monthlypaymen,periods,totalprices,bond,tailmoney,vin,engine_number');
                    }, 'admin' => function ($query) {
                        $query->withField(['nickname','id','avatar']);
                    }, 'models' => function ($query) {
                        $query->withField('name,models_name');
                    }])
                    ->where($where)
                    ->where('review_the_data', 'the_car')
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();
            foreach ($list as $k => $row) {
                    $row->visible(['id','financial_name', 'order_no', 'username', 'genderdata', 'createtime', 'delivery_datetime', 'phone', 'id_card', 'amount_collected', 'downpayment', 'review_the_data']);
                    $row->visible(['plansecond']);
                    $row->getRelation('plansecond')->visible(['newpayment', 'licenseplatenumber','vin','engine_number', 'monthlypaymen', 'periods', 'totalprices', 'bond', 'tailmoney',]);
                    $row->visible(['admin']);
                    $row->getRelation('admin')->visible(['id','avatar','nickname']);
                    $row->visible(['models']);
                    $row->getRelation('models')->visible(['name', 'models_name']);

                    if ($list[$k]['models']['models_name']) {
                        $list[$k]['models']['name'] = $list[$k]['models']['name'] . " " . $list[$k]['models']['models_name'];
                    }
    
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


            $result = array('total' => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();

    }


}

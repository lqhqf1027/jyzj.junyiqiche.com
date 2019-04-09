<?php

namespace app\admin\controller\vehiclemanagement;

use app\common\controller\Backend;
use think\Db;

/**
 * 新车管理库存
 *
 * @icon fa fa-circle-o
 */
class Newnventory extends Backend
{

    /**
     * CarNewInventory模型对象
     * @var \app\common\model\CarNewInventory
     */
    protected $model = null;
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('CarNewInventory');
        $this->view->assign("carprocessList", $this->model->getCarprocessList());
        $this->view->assign("pledgeList", $this->model->getPledgeList());
        $this->loadlang("newcars/newnventory");
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
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with(['models' => function ($query) {
                    $query->withField('name,models_name');
                }])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['models' => function ($query) {
                    $query->withField('name,models_name');
                }])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $key=>$row) {
                // $list[$key]['aaa'] = 11;
                $row->visible(['id', 'carnumber', 'reservecar','open_fare', 'licensenumber', 'presentationcondition', 'note', 'frame_number', 'engine_number', 'household', '4s_shop', 'createtime', 'updatetime','the_car_username']);
                $row->visible(['models']);
                $row->getRelation('models')->visible(['name', 'models_name']); 

                if ($list[$key]['models']['models_name']) {
                    $list[$key]['models']['name'] = $list[$key]['models']['name'] . " " . $list[$key]['models']['models_name'];
                }

                foreach((array)$this->getOrderName($row['id']) as $k=>$v){
                    foreach($v as $rows){
                        if($rows['licensenumber']==$row['licensenumber']){
                           
                            $list[$key]['the_car_username']= $rows['username'];
                        }
                    }
                }
            }
            $list = collection($list)->toArray();
            
            // pr($this->getOrderName());
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    public function getOrderName($NewnventoryId = null){
      
        $name= [
            Db::name('sales_order')->alias('a')
                ->join('car_new_inventory b','a.car_new_inventory_id = b.id')
                ->field('a.username,a.car_new_inventory_id,b.id,b.licensenumber')
                ->where(['a.car_new_inventory_id'=>$NewnventoryId,'a.review_the_data'=>'the_car'])
                ->select(),
            Db::name('full_parment_order')->alias('a')
                ->join('car_new_inventory b','a.car_new_inventory_id = b.id')
                ->field('a.username,a.car_new_inventory_id,b.id,b.licensenumber')
                ->where(['a.car_new_inventory_id'=>$NewnventoryId,'a.review_the_data'=>'for_the_car'])
                ->select()
        ]; 
       
        return $name;
    }

    /**添加
     * @return string
     * @throws \think\Exception
     */
    public function add()
    {
        $this->view->assign("car_models", $this->getInfo());

        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");


            if ($params) {
                if (empty($params['carprocess'])) {
                    $params['carprocess'] = 0;
                }

                if (empty($params['pledge'])) {
                    $params['pledge'] = 0;
                }
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
//                    $resultMortgage = model('Mortgage')->allowField(true)->save(['id'=>self::getInvoiceMoney($this->model->id),'invoice_monney'=>$params['invoice_monney']]);
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($this->model->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    /**编辑
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function edit($ids = NULL)
    {

        $validate = $this->getReally($ids);

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
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
//        $row['invoice_monney'] =self::getInvoiceMoney($row['id'])['invoice_monney'];
        $this->view->assign("row", $row);
//        pr(collection($row)->toArray());die;
        $this->view->assign("car_models", $this->getInfo());
        $this->view->assign("validate_models_id", $validate['models_id']);

        return $this->view->fetch();
    }

    /**
     * 获取按揭列表里的开票价
     * @param $inventoryId 新车库存id
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
 /*   public static function getInvoiceMoney($inventoryId){
        return Db::name('sales_order')->alias('a')
                ->join('car_new_inventory b','a.car_new_inventory_id=b.id')
                ->join('mortgage c','a.mortgage_id = c.id')
                ->field('a.id as sales_id,c.invoice_monney,a.id as mortgage_id')
                ->where(['b.id'=>$inventoryId])
                ->find();
    }*/

    /**
     * 得到车型信息
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getReally($id)
    {
        $result = Db::name("car_new_inventory")
            ->alias("i")
            ->join("models m", "i.models_id=m.id")
            ->field("i.id,i.models_id,m.brand_id,m.name")
            ->where("i.id", $id)
            ->select();

        return $result[0];


    }


    public function getInfo()
    {

        $models = Db::name("models")
            ->field("id as models_id,models_name as model_name,name as models_name,brand_id")
            ->select();
            
        //品牌下没有车型，就不显示在下拉列表
        foreach ($models as $key => $value) {
            $ids[] = $value['brand_id'];
        }
        
        $brand = Db::name("brand")
            ->where('pid', '0')
            ->where('id', 'in', $ids)
            ->field("id,name")
            ->select();

        foreach ($brand as $k => $v) {
            $brand[$k]['models'] = array();
            foreach ($models as $key => $value) {

                if ($v['id'] == $value['brand_id']) {
                    $value['models_name'] = $value['models_name'] . " " . $value['model_name'];
                    array_push($brand[$k]['models'], $value);
                }
            }

        }

        return $brand;

    }
}

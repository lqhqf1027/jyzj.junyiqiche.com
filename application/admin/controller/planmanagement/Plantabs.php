<?php

namespace app\admin\controller\planmanagement;

use app\common\controller\Backend;
use think\Db;

/**
 * 新车（按揭）方案
 *
 * @icon fa fa-circle-o
 */
class Plantabs extends Backend
{
    
    /**
     * Acar模型对象
     * @var \app\admin\model\plan\Acar
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\PlanAcar;
        $this->view->assign("nperlistList", $this->model->getNperlistList());
        $this->view->assign("workingInsuranceList", $this->model->getWorkingInsuranceList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("schemecategory", self::schemecategory());
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
        // $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('models.name', true);
            $total = $this->model
                    ->with(['models','schemecategory', 'admin'])
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with(['models','schemecategory', 'admin'])
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();


            $sales_order_data = self::matchingSalesOrder();
            foreach ($list as $key =>  $row) {
                
                $list[$key]['match_plan'] = in_array($row['id'], $sales_order_data) == $row['id'] ? 'match_success' : 'match_error'; //返回是否与方案id匹配
                
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * Notes:获取已签单的方案id
     * User: glen9
     * Date: 2018/10/11
     * Time: 23:43
     * @return array
     */
    public static function matchingSalesOrder()
    {
        return array_unique(Db::name('sales_order')->column('plan_acar_name'));

    }

    /**
     * 获取方案类型
     * @return array
     */
    public static function schemecategory()
    {
        return Db::name('scheme_category')->field(['name','category_note'])->select();

    }

    /**
     * 新车方案添加
     */
    public function add()
    {
        $this->model = model("PlanAcar");
    
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            
            if (empty($params['working_insurance'])) {
                $params['working_insurance'] = "no";
            }
            if ($params['sales_id'] == " ") {
                $params['sales_id'] = null;
            }
            if ($params) {
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    
                    if ($result !== false) {

                        // $models_name = Db::name('models')
                        //     ->where('id', $params['models_id'])
                        //     ->value('name');

                        // if ($params['liu'] == 'yes' && $params['sales_id']) {

                        //     $results = array();

                        //     $datas = send_newmodels_to_sales($models_name, $params['payment'], $params['monthly']);

                        //     $email = new Email;

                        //     $receiver = Db::name('admin')->where('id', $params['sales_id'])->value('email');

                        //     $result_sss = $email
                        //         ->to($receiver)
                        //         ->subject($datas['subject'])
                        //         ->message($datas['message'])
                        //         ->send();

                        //     if ($result_sss) {
                                // $this->success();
                            // } else {
                            //     $this->error($this->model->getError());
                            // }
                        // }

                        $this->success();
                    } else {
                        $this->error($this->model->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                } catch (\think\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $this->view->assign([
            'sales' => $this->getSales()
        ]);
        return $this->view->fetch();
    }

    /**
     * 新车方案编辑
     */
    public function edit($ids = NULL)
    {
        $this->model = model('PlanAcar');

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
            if ($params['sales_id'] == " ") {
                $params['sales_id'] = null;
            }
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

        $this->view->assign([
            'row' => $row,
            'sales' => $this->getSales(),
        ]);
        return $this->view->fetch();
    }

    /**得到销售员信息
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSales()
    {
        $sales = Db::name("admin")
            ->where("rule_message", "=", 'message5')
            ->where('status','normal')
            ->field("id,nickname,rule_message")
            ->select();

        return $sales;

    }

    /**导入销售方案
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     */
    public function import()
    {
        $this->model = model('PlanAcar');
        $file = $this->request->request('file');
        if (!$file) {
            $this->error(__('Parameter %s can not be empty', 'file'));
        }
        $filePath = ROOT_PATH . DS . 'public' . DS . $file;
        if (!is_file($filePath)) {
            $this->error(__('No results were found'));
        }
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                $PHPReader = new \PHPExcel_Reader_CSV();
                if (!$PHPReader->canRead($filePath)) {
                    $this->error(__('Unknown data format'));
                }
            }
        }

        //导入文件首行类型,默认是注释,如果需要使用字段名称请使用name
        $importHeadType = isset($this->importHeadType) ? $this->importHeadType : 'comment';

        $table = $this->model->getQuery()->getTable();
        $database = \think\Config::get('database.database');
        $fieldArr = [];
        $list = db()->query("SELECT COLUMN_NAME,COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?", [$table, $database]);
        // pr($list);
        // die;
        foreach ($list as $k => $v) {
            if ($importHeadType == 'comment') {
                $fieldArr[$v['COLUMN_COMMENT']] = $v['COLUMN_NAME'];
            } else {
                $fieldArr[$v['COLUMN_NAME']] = $v['COLUMN_NAME'];
            }
        }
        // pr($fieldArr);
        // die;
        $PHPExcel = $PHPReader->load($filePath); //加载文件
        $currentSheet = $PHPExcel->getSheet(0);  //读取文件中的第一个工作表
        $allColumn = $currentSheet->getHighestDataColumn(); //取得最大的列号
        $allRow = $currentSheet->getHighestRow(); //取得一共有多少行
        $maxColumnNumber = \PHPExcel_Cell::columnIndexFromString($allColumn);
        for ($currentRow = 1; $currentRow <= 1; $currentRow++) {
            for ($currentColumn = 0; $currentColumn < $maxColumnNumber; $currentColumn++) {
                $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                $fields[] = $val;
            }
        }
        $insert = [];
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            $values = [];
            for ($currentColumn = 0; $currentColumn < $maxColumnNumber; $currentColumn++) {
                $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                $values[] = is_null($val) ? '' : $val;
            }
            $row = [];
            $temp = array_combine($fields, $values);
            // pr($temp);
            // die;
            foreach ($temp as $k => $v) {
                if (isset($fieldArr[$k]) && $k !== '') {
                    $row[$fieldArr[$k]] = $v;
                }
            }

            if ($row) {

                $insert[] = $row;
            }
        }
        if (!$insert) {
            $this->error(__('No rows were updated'));
        }
        try {
            $this->model->saveAll($insert);

        } catch (\think\exception\PDOException $exception) {
            $this->error($exception->getMessage());
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success();
    }




}

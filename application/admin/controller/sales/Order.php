<?php

namespace app\admin\controller\sales;

use app\admin\library\Auth;
use app\admin\model\OrderDetails;
use app\common\controller\Backend;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use think\Db;
use think\exception\PDOException;


/**
 *
 *
 * @icon fa fa-circle-o
 */
class Order extends Backend
{

    /**
     * Order模型对象
     * @var \app\admin\model\Order
     */
    protected $model = null;
    protected $dataLimit = 'auth'; //表示显示当前自己和所有子级管理员的所有数据
    protected $dataLimitField = 'admin_id';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Order;
        $this->view->assign("customerSourceList", $this->model->getCustomerSourceList());
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $this->view->assign("nperlistList", $this->model->getNperlistList());
        $this->view->assign("typeList", $this->model->getTypeList());
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
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with(['orderdetails', 'orderimg'])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['orderdetails', 'orderimg','admin'])

                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $key=>$row) {
                $row->getRelation('orderdetails')->visible(['admin_id', 'licensenumber']);
                $row->getRelation('orderimg')->visible(['id_cardimages', 'driving_licenseimages']);
                $row->getRelation('admin')->visible(['avatar','username']);
            }

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $params['order_createtime'] = strtotime($params['order_createtime']);
            // pr($params);
            // die;
            if ($params) {
                $params = $this->preExcludeFields($params);
//                if ($params['customer_source'] === 'turn_to_introduce') {
//                    if (!trim($params['turn_to_introduce_name']) || !trim($params['turn_to_introduce_phone'])) {
//                        $this->error('转介绍人信息不能为空');
//                    }
//                }
                pr($params);die;
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
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
        return $this->view->fetch();
    }



    /**
     * 导入
     */
    public function import()
    {
        $file = $this->request->request('file');
        if (!$file) {
            $this->error(__('Parameter %s can not be empty', 'file'));
        }
        $filePath = ROOT_PATH . DS . 'public' . DS . $file;
        if (!is_file($filePath)) {
            $this->error(__('No results were found'));
        }
        //实例化reader
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
            $this->error(__('Unknown data format'));
        }
        if ($ext === 'csv') {
            $file = fopen($filePath, 'r');
            $filePath = tempnam(sys_get_temp_dir(), 'import_csv');
            $fp = fopen($filePath, "w");
            $n = 0;
            while ($line = fgets($file)) {
                $line = rtrim($line, "\n\r\0");
                $encoding = mb_detect_encoding($line, ['utf-8', 'gbk', 'latin1', 'big5']);
                if ($encoding != 'utf-8') {
                    $line = mb_convert_encoding($line, 'utf-8', $encoding);
                }
                if ($n == 0 || preg_match('/^".*"$/', $line)) {
                    fwrite($fp, $line . "\n");
                } else {
                    fwrite($fp, '"' . str_replace(['"', ','], ['""', '","'], $line) . "\"\n");
                }
                $n++;
            }
            fclose($file) || fclose($fp);

            $reader = new Csv();
        } elseif ($ext === 'xls') {
            $reader = new Xls();
        } else {
            $reader = new Xlsx();
        }

        //导入文件首行类型,默认是注释,如果需要使用字段名称请使用name
        $importHeadType = isset($this->importHeadType) ? $this->importHeadType : 'comment';

        $table = $this->model->getQuery()->getTable();

        $database = \think\Config::get('database.database');
        $fieldArr = [];
        $list = db()->query("SELECT COLUMN_NAME,COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?", [$table, $database]);
        $list2 = db()->query("SELECT COLUMN_NAME,COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?", ['jyzj_order_details', $database]);

        $list = array_unique(array_merge($list, $list2), SORT_REGULAR);
        foreach ($list as $k => $v) {
            if ($importHeadType == 'comment') {
                $fieldArr[$v['COLUMN_COMMENT']] = $v['COLUMN_NAME'];
            } else {
                $fieldArr[$v['COLUMN_NAME']] = $v['COLUMN_NAME'];
            }
        }
        //加载文件
        $insert = $insertOrder = [];
        try {
            if (!$PHPExcel = $reader->load($filePath)) {
                $this->error(__('Unknown data format'));
            }
            $currentSheet = $PHPExcel->getSheet(0);  //读取文件中的第一个工作表
            $allColumn = $currentSheet->getHighestDataColumn(); //取得最大的列号
            $allRow = $currentSheet->getHighestRow(); //取得一共有多少行
            $maxColumnNumber = Coordinate::columnIndexFromString($allColumn);
            $fields = [];
            for ($currentRow = 1; $currentRow <= 1; $currentRow++) {
                for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
                    $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                    $fields[] = $val;
                }
            }

            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                $values = [];
                for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
                    $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                    $values[] = is_null($val) ? '' : $val;
                }

                $row = [];
                $temp = array_combine($fields, $values);

                foreach ($temp as $k => $v) {
                    if (isset($fieldArr[$k]) && $k !== '') {
                        $row[$fieldArr[$k]] = $v;
                    }
                }
                if ($row && $row['username']) {
                    $row['total_contract'] = $row['total_contract'] ? $row['total_contract'] : 0;
                    if (isset($row['payment'])) {
                        $row['payment'] = $row['payment'] ? $row['payment'] : 0;
                    }
                    if (isset($row['monthly'])) {
                        $row['monthly'] = $row['monthly'] ? $row['monthly'] : 0;
                    }
                    if (isset($row['nperlist'])) {
                        $row['nperlist'] = $row['nperlist'] ? $row['nperlist'] : 0;
                    }
                    if (isset($row['end_money'])) {
                        $row['end_money'] = $row['end_money'] ? $row['end_money'] : 0;
                    }

                    if (isset($row['tail_money'])) {
                        $row['tail_money'] = $row['tail_money'] ? $row['tail_money'] : 0;
                    }

                    if (isset($row['margin'])) {
                        $row['margin'] = $row['margin'] ? $row['margin'] : 0;
                    }

                    $row['tax_amount'] = $row['tax_amount'] ? $row['tax_amount'] : 0;
                    $row['no_tax_amount'] = $row['no_tax_amount'] ? $row['no_tax_amount'] : 0;
                    $row['purchase_of_taxes'] = $row['purchase_of_taxes'] ? $row['purchase_of_taxes'] : 0;
                    $row['house_fee'] = $row['house_fee'] ? $row['house_fee'] : 0;
                    $row['luqiao_fee'] = $row['luqiao_fee'] ? $row['luqiao_fee'] : 0;
                    $row['insurance'] = $row['insurance'] ? $row['insurance'] : 0;
                    $row['car_boat_tax'] = $row['car_boat_tax'] ? $row['car_boat_tax'] : 0;
                    $row['business_risks'] = $row['business_risks'] ? $row['business_risks'] : 0;
                    $row['is_mortgage'] = $row['is_mortgage'] == '是' ? '是' : '否';

                    if ($row['type'] == 'mortgage') {
                        $row['total_contract'] = floatval($row['payment']) + floatval($row['monthly'] * intval($row['nperlist'])) + floatval($row['end_money']) + floatval($row['tail_money']);
                    }
                    $insert[] = $row;

                }
            }
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
        if (!$insert) {
            $this->error(__('No rows were updated'));
        }
        Db::startTrans();
        try {
            //是否包含admin_id字段
            $has_admin_id = false;
            foreach ($fieldArr as $name => $key) {
                if ($key == 'admin_id') {
                    $has_admin_id = true;
                    break;
                }
            }
            if ($has_admin_id) {
                $auth = Auth::instance();
                foreach ($insert as &$val) {
                    if (!isset($val['admin_id']) || empty($val['admin_id'])) {
                        $val['admin_id'] = $auth->isLogin() ? $auth->id : 0;
                    }
                }
            }
            $results = collection($this->model->allowField(true)->saveAll($insert))->toArray();

            foreach ($results as $k => $v) {
                $insert[$k]['order_id'] = $v['id'];
            }

            $order_details = new OrderDetails();

            $order_details->allowField(true)->saveAll($insert);

            Db::commit();
        } catch (PDOException $exception) {
            Db::rollback();
            $msg = $exception->getMessage();
            if (preg_match("/.+Integrity constraint violation: 1062 Duplicate entry '(.+)' for key '(.+)'/is", $msg, $matches)) {
                $msg = "导入失败，包含【{$matches[1]}】的记录已存在";
            };
            $this->error($msg);
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        $this->success();
    }

}

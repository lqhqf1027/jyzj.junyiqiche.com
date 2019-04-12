<?php

namespace app\admin\controller\riskcontrol;

use app\common\controller\Backend;

/**
 * 以往客户
 *
 * @icon fa fa-circle-o
 */
class Previouscustomer extends Backend
{
    
    /**
     * PreviousCustomer模型对象
     * @var \app\admin\model\PreviousCustomer
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\PreviousCustomer;
        $this->view->assign("typeList", $this->model->getTypeList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

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
        foreach ($list as $k => $v) {
            if ($importHeadType == 'comment') {
                $fieldArr[$v['COLUMN_COMMENT']] = $v['COLUMN_NAME'];
            } else {
                $fieldArr[$v['COLUMN_NAME']] = $v['COLUMN_NAME'];
            }
        }

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
            foreach ($temp as $k => $v) {
                if (isset($fieldArr[$k]) && $k !== '') {
                    $row[$fieldArr[$k]] = $v;
                }
            }
            if ($row) {
                $row['payment'] = $row['payment']?$row['payment']:0;
                $row['monthly'] = $row['monthly']?$row['monthly']:0;
                $row['end_money'] = $row['end_money']?$row['end_money']:0;
                $row['tail_money'] = $row['tail_money']?$row['tail_money']:0;
                $row['margin'] = $row['margin']?$row['margin']:0;
                $row['tax_amount'] = $row['tax_amount']?$row['tax_amount']:0;
                $row['no_tax_amount'] = $row['no_tax_amount']?$row['no_tax_amount']:0;
                $row['purchase_of_taxes'] = $row['purchase_of_taxes']?$row['purchase_of_taxes']:0;
                $row['house_fee'] = $row['house_fee']?$row['house_fee']:0;
                $row['luqiao_fee'] = $row['luqiao_fee']?$row['luqiao_fee']:0;
                $row['house_fee'] = $row['house_fee']?$row['house_fee']:0;
                $row['insurance'] = $row['insurance']?$row['insurance']:0;
                $row['car_boat_tax'] = $row['car_boat_tax']?$row['car_boat_tax']:0;
                $row['business_risks'] = $row['business_risks']?$row['business_risks']:0;
                $row['type'] = trim($row['type']);
                if($row['type']!='full_amount') $row['contract_total'] = floatval($row['payment'])+floatval($row['monthly'])*floatval($row['nperlist'])+floatval($row['end_money'])+floatval($row['tail_money']);
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

<?php

namespace app\admin\controller\pastcustomers;

use app\common\controller\Backend;

use app\admin\model\PastInformation as PastInformationModel;
use think\Cache;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class Pastinformation extends Backend
{

    /**
     * PastInformation模型对象
     * @var \app\admin\model\PastInformation
     */
    protected $model = null;

    protected $noNeedLogin = '*';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\PastInformation;
        $this->view->assign("typesList", $this->model->getTypesList());

    }

    /**
     * 二维码
     * @param int $ids
     * @return string
     * @throws \Endroid\QrCode\Exceptions\ImageTypeInvalidException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function grant_authorization(int $ids)
    {
        $models = PastInformationModel::get($ids)->visible(['username', 'qr_code']);

        if (!$models['qr_code']) {

           $result = setQrcode('new', $ids, $models['username']);
        };

        $this->view->assign('qrcode',!empty($result)?$result:$models['qr_code']);

        return $this->view->fetch();
    }


    //添加转介绍
    public function referral($ids = null)
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");

            $referral_id = $this->model->where(['username' => $params['username'], 'id_card' => $params['id_card']])->value('id');
            $referral_ids = $this->model->where('id', $ids)->value('referral_ids');

            if($referral_id) {
                $referral_ids = $referral_ids . $referral_id . ',';

                // pr($referral_ids);
                // die;
                $result = $this->model->where('id', $ids)->update(['referral_ids' => $referral_ids]);
                if ($result) {
                    $this->success();
                }
                else {
                    $this->error();
                }
            }
            else {
                $this->error('未匹配到用户,请重新输入');
            }
            
        }
        return $this->view->fetch();
    }


    //查看违章信息
    public function violationInformation($ids = null)
    {
        $data = $this->model->where('id', $ids)->find();
        //违章信息
        $violation_details = json_decode($data['violation_details'], true);
        foreach ($violation_details as $k => $v) {
            //总扣分
            $total_deduction += $v['fen'];
            //总扣钱
            $total_fine += $v['money'];
        }

        $this->view->assign([
            'violation_details' => $violation_details,
            'data' => $data,
            'total_deduction' => $total_deduction,
            'total_fine' => $total_fine
            ]);

        return $this->view->fetch();
    }


    /**导入新客户信息
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     */
    public function import()
    {

//        $this->model = model('CustomerResource');
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
//pr($importHeadType);
//        pr($list);die;
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

            if(!$row['platform_id']||!$row['phone']){
                continue;
            }
            if ($row) {
                $insert[] = $row;
            }

        }
        pr($insert);die();
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
r
}

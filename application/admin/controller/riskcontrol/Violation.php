<?php

namespace app\admin\controller\riskcontrol;

use app\common\controller\Backend;

use think\Db;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Shared_Date;
use PHPExcel_Style;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_Style_NumberFormat;

/**
 * 违章信息管理
 *
 * @icon fa fa-circle-o
 */
class Violation extends Backend
{
    
    /**
     * Old模型对象
     * @var \app\admin\model\violation\inquiry\Old
     */
    protected $model = null;

    protected static $keys = '217fb8552303cb6074f88dbbb5329be7';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\violation\inquiry\Old;
        $this->view->assign("peccancyStatusList", $this->model->getPeccancyStatusList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    
    /**导入查询违章
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
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
                    $row['import_time'] = time();
                }
            }

            if ($row) {

                $insert[] = $row;
            }
        }
        // pr($insert);
        // die;
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

     /**
     * 请求第三方接口获取违章信息
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function sendMessage()
    {
        if ($this->request->isAjax()) {

            $params = $this->request->post()['ids'];

            $finals = [];
            $keys = self::$keys;
            foreach ($params as $k => $v) {
                //获取城市前缀接口
                $result = gets("http://v.juhe.cn/sweizhang/carPre.php?key=" . $keys . "&hphm=" . urlencode($v['hphm']));

                if ($result['error_code'] == 0) {

                    $field = array();

                    $data = gets("http://v.juhe.cn/sweizhang/query?city=" . $result['result']['city_code'] . "&hphm=" . urlencode($v['hphms']) . "&engineno=" . $v['engineno'] . "&classno=" . $v['classno'] . "&key=" . $keys);

                    if ($data['error_code'] == 0) {

                        $total_fraction = 0;     //总扣分
                        $total_money = 0;        //总罚款
                        $flag = -1;
                        $field['total_violation'] = 0;//违章次数
                        if ($data['result']['lists']) {
                            foreach ($data['result']['lists'] as $key => $value) {
                                if ($value['fen']) {
                                    $value['fen'] = floatval($value['fen']);

                                    $total_fraction += $value['fen'];
                                }

                                if ($value['money']) {
                                    $value['money'] = floatval($value['money']);

                                    $total_money += $value['money'];
                                }

                                if ($value['handled'] == 0) {
                                    $flag = -2;
                                }

                                //违章次数
                                $field['total_violation']++;

                            }
                            $field['peccancy_detail'] = json_encode($data['result']['lists']);
                        }

                        $flag == -2 ? $field['peccancy_status'] = 2 : $field['peccancy_status'] = 1;

                        $field['total_deduction'] = $total_fraction;
                        $field['total_fine'] = $total_money;
                        $field['final_time'] = time();


                        Db::name('violation_inquiry_old')
                            ->where('license_plate_number', $v['hphms'])
                            ->update($field);

                        array_push($finals, $data);
                    } else {
                        $this->error($data['reason'], '', $data);
                    }
                } else {
                    $this->error($result['reason'], '', $result);
                }

            }

            $this->success('', '', $finals);
        }
    }

    /**
     * 单个请求第三方接口获取违章信息
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function sendMessagePerson()
    {
        if ($this->request->isAjax()) {

            $params = $this->request->post()['ids'];
            $params = $params[0];
            $finals = [];
            $keys = self::$keys;


            //获取城市前缀接口
            $result = gets("http://v.juhe.cn/sweizhang/carPre.php?key=" . $keys . "&hphm=" . urlencode($params['hphm']));

            if ($result['error_code'] == 0) {

                $field = array();


                $data = gets("http://v.juhe.cn/sweizhang/query?city=" . $result['result']['city_code'] . "&hphm=" . urlencode($params['hphms']) . "&engineno=" . $params['engineno'] . "&classno=" . $params['classno'] . "&key=" . $keys);


                if ($data['error_code'] == 0) {

                    $total_fraction = 0;     //总扣分
                    $total_money = 0;        //总罚款
                    $flag = -1;
                    $field['total_violation'] = 0;//违章次数
                    if ($data['result']['lists']) {
                        foreach ($data['result']['lists'] as $key => $value) {
                            if ($value['fen']) {
                                $value['fen'] = floatval($value['fen']);

                                $total_fraction += $value['fen'];
                            }

                            if ($value['money']) {
                                $value['money'] = floatval($value['money']);

                                $total_money += $value['money'];
                            }

                            if ($value['handled'] == 0) {
                                $flag = -2;
                            }
                            //违章次数
                            $field['total_violation']++;

                        }
                        $field['peccancy_detail'] = json_encode($data['result']['lists']);
                    }

                    $flag == -2 ? $field['peccancy_status'] = 2 : $field['peccancy_status'] = 1;

                    $field['total_deduction'] = $total_fraction;
                    $field['total_fine'] = $total_money;
                    $field['final_time'] = time();


                    Db::name('violation_inquiry_old')
                        ->where('license_plate_number', $params['hphms'])
                        ->update($field);

                    array_push($finals, $data);
                } else {
                    $this->error($data['reason'], '', $data);
                }
            } else {
                $this->error($result['reason'], '', $result);
            }


            $this->success('', '', $finals);
        }
    }

    /**
     * 查看违章详情
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function details($ids = null)
    {
        $detail = Db::name('violation_inquiry_old')
            ->where('id', $ids)
            ->field('username,phone,peccancy_detail,total_deduction,total_fine')
            ->find();

        $details = json_decode($detail['peccancy_detail'], true);


        $score = 0;
        $money = 0;
        $no_handle = array();
        $handle = array();
        foreach ($details as $k => $v) {
            if ($v['handled'] == 0) {
                $score += floatval($v['fen']);
                $money += floatval($v['money']);
                array_push($no_handle, $v);
            } else {
                array_push($handle, $v);
            }
        }


        $details = array_merge($no_handle, $handle);

        $this->view->assign([
            'detail' => $details,
            'phone' => $detail['phone'],
            'username' => $detail['username'],
            'total_fine' => $money,
            'total_deduction' => $score
        ]);

        return $this->view->fetch();
    }

    /**
     * 批量导出违章客户信息和违章详情
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function export()
    {
        $ids = $this->request->post('ids');
        $whereIds = $ids == 'all' ? '1=1' : ['id' => ['in', explode(',', $ids)]];
        $data = $this->model
            ->where($whereIds)
            ->select();
        // pr($data);
        // die;
        // 新建一个excel对象 大神已经加入了PHPExcel 不用引了 直接用！
        $objPHPExcel = new \PHPExcel();  //在vendor目录下 \不能少 否则报错
        // 设置文档的相关信息
        $objPHPExcel->getProperties()->setCreator("君忆")/*设置作者*/
        ->setLastModifiedBy("Fastadmin")/*最后修改*/
        ->setTitle("客户违章详情")/*题目*/
        ->setSubject("subject")/*主题*/
        ->setDescription("Fastadmin")/*描述*/
        ->setKeywords("Fastadmin")/*关键词*/
        ->setCategory("Fastadmin");/*类别*/
        $objPHPExcel->getDefaultStyle()->getFont()->setName('微软雅黑');//字体
       
        $myrow = 1;/*表头所需要行数的变量，方便以后修改*/
        /*表头数据填充*/
        $objPHPExcel->getActiveSheet()->getRowDimension('3')->setRowHeight(30);/*设置行高*/
        $objPHPExcel->setActiveSheetIndex(0)  //设置一张sheet为活动表 添加表头信息 
            ->setCellValue('A' . $myrow, '序号')
            ->setCellValue('B' . $myrow, '客户姓名')
            ->setCellValue('C' . $myrow, '手机号')
            ->setCellValue('D' . $myrow, '所属公司')
            ->setCellValue('E' . $myrow, '车牌号')
            ->setCellValue('F' . $myrow, '发动机号')
            ->setCellValue('G' . $myrow, '车架号')
            ->setCellValue('H' . $myrow, '起租时间')
            ->setCellValue('I' . $myrow, '退租时间')
            ->setCellValue('J' . $myrow, '购车类型')
            ->setCellValue('K' . $myrow, '导入时间')
            ->setCellValue('L' . $myrow, '总违章')
            ->setCellValue('M' . $myrow, '总扣分')
            ->setCellValue('N' . $myrow, '总罚款')
            ->setCellValue('O' . $myrow, '违章时间')
            ->setCellValue('P' . $myrow, '违章省份')
            ->setCellValue('Q' . $myrow, '违章城市')
            ->setCellValue('R' . $myrow, '违章地点')
            ->setCellValue('S' . $myrow, '违章内容')
            ->setCellValue('T' . $myrow, '扣分')
            ->setCellValue('U' . $myrow, '处罚金额')
            ->setCellValue('V' . $myrow, '违章状态');

        // 关键数据
        // $data = json_decode($data, true);
        $myrow = $myrow + 1; //刚刚设置的行变量
        $mynum = 1;//序号
         //遍历接收的数据，并写入到对应的单元格内
        foreach ($data as $key => $value) {

            if ($value['status'] == 1) {
                $status = "按揭";
            }
            if ($value['status'] == 2) {
                $status = "租车";
            }
            if ($value['status'] == 3) {
                $status = "全款车";
            }

            $details = json_decode($value['peccancy_detail'], true);

            foreach ($details as $k => $v) {
                if ($v['handled'] == 1) {
                    $handle = "已处理";
                }
                if ($v['handled'] == 0) {
                    $handle = "未处理";
                }
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $myrow, $mynum)
                    ->setCellValue('B' . $myrow, $value['username'])
                    ->setCellValue('C' . $myrow, $value['phone'])
                    ->setCellValue('D' . $myrow, $value['companyaccount'])
                    ->setCellValue('E' . $myrow, $value['license_plate_number'])
                    ->setCellValue('F' . $myrow, $value['engine_number'])
                    ->setCellValue('G' . $myrow, $value['frame_number'])
                    ->setCellValue('H' . $myrow, $value['start_renttime'])
                    ->setCellValue('I' . $myrow, $value['end_renttime'])
                    ->setCellValue('J' . $myrow, $status)
                    ->setCellValue('K' . $myrow, date("Y-m-d", $value['import_time']))
                    ->setCellValue('L' . $myrow, $value['total_violation'])
                    ->setCellValue('M' . $myrow, $value['total_deduction'])
                    ->setCellValue('N' . $myrow, $value['total_fine'])
                    ->setCellValue('O' . $myrow, $v['date'])
                    ->setCellValue('P' . $myrow, $v['wzcity'])
                    ->setCellValue('Q' . $myrow, $v['wzcity'])
                    ->setCellValue('R' . $myrow, $v['area'])
                    ->setCellValue('S' . $myrow, $v['act'])
                    ->setCellValue('T' . $myrow, $v['fen'])
                    ->setCellValue('U' . $myrow, $v['money'])
                    ->setCellValue('V' . $myrow, $handle);
                
                $objPHPExcel->getActiveSheet()->getRowDimension('' . $myrow)->setRowHeight(20);/*设置行高 不能批量的设置 这种感觉 if（has（蛋）！=0）{疼();}*/
                $myrow++;
                $mynum++;
            }
            $objPHPExcel->getActiveSheet()->getRowDimension('' . $myrow)->setRowHeight(20);/*设置行高 不能批量的设置 这种感觉 if（has（蛋）！=0）{疼();}*/
            $myrow++;
            $mynum++;
        }

        //纸张方向和大小 为A4横向
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        //浏览器交互 导出
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="客户违章详情.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

}

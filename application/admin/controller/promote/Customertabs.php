<?php

namespace app\admin\controller\promote;

use app\admin\model\Admin;
use app\common\controller\Backend;
use app\admin\controller\wechat\WechatMessage;
use app\admin\model\Admin as adminModel;
use app\common\library\Email;
// use app\admin\controller\wechat\Wechatuser;
use think\Db;
use think\Config;


/**
 * 多表格示例
 *
 * @icon fa fa-table
 * @remark 当一个页面上存在多个Bootstrap-table时该如何控制按钮和表格
 */
class Customertabs extends Backend
{

    protected $model = null;
    protected $noNeedRight = ['dstribution', 'distribution', 'download', 'import', 'export', 'allocationexport', 'feedbackexport', 'index', 'del'
        , 'headline', 'baidu', 'same_city', 'music', 'add_headline', 'add_baidu', 'add_same_city', 'add_music', 'download_same'];


    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('CustomerResource');
        //扔出角色信息 && cdn url


        $this->view->assign("genderdataList", $this->model->getGenderdataList());
    }

    /**
     * 查看
     */
    public function index()
    {
        $this->loadlang('customer/customerresource');
        $this->view->assign([
           'toutiao_total'=>$this->model->with('platform')->where(['name'=>'今日头条', 'backoffice_id' => ['neq',null]])->count(),
           'total_58'=>$this->model->with('platform')->where(['name'=>'58同城', 'backoffice_id' => ['neq',null]])->count(),
           'baidu_total'=>$this->model->with('platform')->where(['name'=>'百度', 'backoffice_id' => ['neq',null]])->count(),
           'douyin_total'=>$this->model->with('platform')->where(['name'=>'抖音', 'backoffice_id' => ['neq',null]])->count()
        ]);
        return $this->view->fetch();

    }


    /**今日头条
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function headline()
    {

        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {

            $use_id = $this->getPlatId('今日头条');

            $result = $this->getInformation($use_id);

            foreach ($result['rows'] as $k => $v) {
                $result['rows'][$k]['feedback_content'] = $this->tableShowF_d($v['id']);
            }
            return json($result);
        }
        return $this->view->fetch();
    }


    /**百度
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function baidu()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {

            $use_id = $this->getPlatId('百度');

            $result = $this->getInformation($use_id);
            foreach ($result['rows'] as $k => $v) {
                $result['rows'][$k]['feedback_content'] = $this->tableShowF_d($v['id']);
            }
            return json($result);
        }
        return $this->view->fetch();
    }


    /**58同城
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function same_city()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {

            $use_id = $this->getPlatId('58同城');;

            $result = $this->getInformation($use_id);
            foreach ($result['rows'] as $k => $v) {
                $result['rows'][$k]['feedback_content'] = $this->tableShowF_d($v['id']);
            }

            return json($result);
        }
        return $this->view->fetch();
    }


    /**抖音
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function music()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {

            $use_id = $this->getPlatId('抖音');

            $result = $this->getInformation($use_id);
            foreach ($result['rows'] as $k => $v) {
                $result['rows'][$k]['feedback_content'] = $this->tableShowF_d($v['id']);
            }
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 封装的查询方法
     * @param $platform_id 平台id
     * @return array|\think\response\Json
     */
    public function getInformation($platform_id)
    {

        //如果发送的来源是Selectpage，则转发到Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        list($where, $sort, $order, $offset, $limit) = $this->buildparams('username', true);
        $total = $this->model
            ->with(['platform', 'backoffice' => function ($query) {
                $query->withField(['nickname', 'avatar']);
            }, 'admin' => function ($quyer) {
                $quyer->withField(['nickname', 'avatar', 'rule_message']);
            }])
            ->where($where)
            ->where([
                'platform_id' => $platform_id,
            ])
            ->order($sort, $order)
            ->count();

        $list = $this->model
            ->with(['platform', 'backoffice' => function ($query) {
                $query->withField(['nickname', 'avatar']);
            }, 'admin' => function ($quyer) {
                $quyer->withField(['nickname', 'avatar', 'rule_message']);
            }])
            ->where($where)
            ->where([
                'platform_id' => $platform_id,
            ])
            ->order($sort, $order)
            ->limit($offset, $limit)
            ->select();
        $list = collection($list)->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['admin']['avatar_url'] = Config::get('upload')['cdnurl'];
        }
        $result = array("total" => $total, "rows" => $list);

        return $result;

    }

    /**今日头条添加
     * @return string
     * @throws \think\Exception
     */
    public function add_headline()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {

                $use_id = $this->getPlatId('今日头条');

                $params['platform_id'] = $use_id;
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
        return $this->view->fetch();
    }


    /**百度添加
     * @return string
     * @throws \think\Exception
     */
    public function add_baidu()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {

                $use_id = $this->getPlatId('百度');

                $params['platform_id'] = $use_id;
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
        return $this->view->fetch();
    }


    /**58同城添加
     * @return string
     * @throws \think\Exception
     */
    public function add_same_city()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {

                $use_id = $this->getPlatId('58同城');

                $params['platform_id'] = $use_id;
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
//                pr($params);die();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
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
                } catch (\think\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }


    /**抖音添加
     * @return string
     * @throws \think\Exception
     */
    public function add_music()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {

                $use_id = $this->getPlatId('抖音');

                $params['platform_id'] = $use_id;
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
        return $this->view->fetch();
    }


    /**
     * 获取平台ID
     * @param $name
     * @return mixed
     */
    public function getPlatId($name)
    {
        return Db::name('platform')
            ->where('name', $name)
            ->value('id');
    }

    /**单个分配给内勤
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function dstribution($ids = NULL)
    {
        $this->model = model('CustomerResource');

        $id = $this->model->get(['id' => $ids]);
        $backoffice = $this->getBackOffice();

        $this->view->assign([
            'backofficeList'=> $backoffice[0],
            'realList'=> $backoffice[1]
        ]);

        $this->assignconfig('id', $id->id);

        if ($this->request->isPost()) {

            $params = $this->request->post('row/a');
            $params['distributinternaltime'] = time();
            $result = $this->model->save($params, function ($query) use ($id) {
                $query->where('id', $id->id);
            });
            if ($result) {
                $data = dstribution_inform();

                $email = new Email;
                // $receiver = "haoqifei@cdjycra.club";
                $receiver = DB::name('admin')->where('id', $params['backoffice_id'])->value('email');
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
                $this->error();
            }
        }

        return $this->view->fetch();
    }


    /**批量分配给内勤
     * @param string $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function distribution($ids = '')
    {

        $this->model = model('CustomerResource');

        $backoffice = $this->getBackOffice();

        $this->view->assign('backofficeList', $backoffice[0]);

        if ($this->request->isPost()) {

            $params = $this->request->post('row/a');
            $params['distributinternaltime'] = time();
            $result = $this->model->save($params, function ($query) use ($ids) {
                $query->where('id', 'in', $ids);
            });
            if ($result) {

                $data = dstribution_inform();

                $email = new Email;
                $receiver = DB::name('admin')->where('id', $params['backoffice_id'])->value('email');
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

                $this->error();
            }
        }
        return $this->view->fetch();
    }

    /**
     * 获取所有的内勤
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getBackOffice()
    {
        $backoffice = collection(Admin::field('id,nickname,rule_message')->where(function ($query) {
            $query->where([
                'rule_message' => ['in', ['message20', 'message13', 'message24']],
                'status' => 'normal'
            ]);
        })->select())->toArray();

        $backofficeList = array();
        $realList = array();
        foreach ($backoffice as $k => $v) {
            $backofficeList[$v['rule_message']] = ['nickname' => $v['nickname'], 'id' => $v['id']];
            $realList[$v['id']] = $v['nickname'];
        }

        return [$backofficeList,$realList];
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


    /**下载导入模板
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function download()
    {
        // 新建一个excel对象 大神已经加入了PHPExcel 不用引了 直接用！
        $objPHPExcel = new \PHPExcel();  //在vendor目录下 \不能少 否则报错
        /*设置表头*/
        // $objPHPExcel->getActiveSheet()->mergeCells('A1:P1');//合并第一行的单元格
        // $objPHPExcel->getActiveSheet()->mergeCells('A2:P2');//合并第二行的单元格
        // $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        // $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '客户信息导入模板表');//标题
        // $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);      // 第一行的默认高度

        $myrow = 1;/*表头所需要行数的变量，方便以后修改*/
        /*表头数据填充*/
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);/*设置行高*/
        $objPHPExcel->setActiveSheetIndex(0)//设置一张sheet为活动表 添加表头信息
        // ->setCellValue('A' . $myrow, 'id')
        ->setCellValue('A' . $myrow, '所属平台')
            ->setCellValue('B' . $myrow, '姓名')
            ->setCellValue('C' . $myrow, '联系电话');
        // ->setCellValue('E' . $myrow, '年龄')
        // ->setCellValue('F' . $myrow, '性别');

        //浏览器交互 导出
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="头条百度抖音通用模板.xlsx"');
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


    /**下载导入模板---58同城
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function download_same()
    {
        // 新建一个excel对象 大神已经加入了PHPExcel 不用引了 直接用！
        $objPHPExcel = new \PHPExcel();  //在vendor目录下 \不能少 否则报错
        /*设置表头*/
        // $objPHPExcel->getActiveSheet()->mergeCells('A1:P1');//合并第一行的单元格
        // $objPHPExcel->getActiveSheet()->mergeCells('A2:P2');//合并第二行的单元格
        // $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        // $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '客户信息导入模板表');//标题
        // $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);      // 第一行的默认高度

        $myrow = 1;/*表头所需要行数的变量，方便以后修改*/
        /*表头数据填充*/
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);/*设置行高*/
        $objPHPExcel->setActiveSheetIndex(0)//设置一张sheet为活动表 添加表头信息
        // ->setCellValue('A' . $myrow, 'id')
        ->setCellValue('A' . $myrow, '所属平台')
            ->setCellValue('B' . $myrow, '姓名')
            ->setCellValue('C' . $myrow, '联系电话')
            ->setCellValue('D' . $myrow, '失效时间');
        // ->setCellValue('F' . $myrow, '性别');

        //浏览器交互 导出
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="58客户导入模板.xlsx"');
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


    /**导入新客户信息
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     */
    public function import()
    {
        $this->model = model('CustomerResource');
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
//             pr($temp);
//             die;
            foreach ($temp as $k => $v) {
                if (isset($fieldArr[$k]) && $k !== '') {
                    $row[$fieldArr[$k]] = $v;
                }
            }
//             pr($row);
//             die;
             if(!$row['platform_id']||!$row['phone']){
                  continue;
             }
            if ($row) {
                $insert[] = $row;
            }

        }
//        pr($insert);die();
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
     * 删除
     */
    public function del($ids = "")
    {
        $this->model = model('CustomerResource');
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

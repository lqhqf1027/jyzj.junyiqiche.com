<?php

namespace app\admin\controller\promote;

use app\common\controller\Backend;
use think\Db;
use think\Config;
use app\admin\model\Admin;
use app\common\library\Email;

/**
 * 客户资源列管理
 *
 * @icon fa fa-circle-o
 */
class Customertabs extends Backend
{
    
    /**
     * Resource模型对象
     * @var \app\admin\model\CustomerResource
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\CustomerResource;
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $this->view->assign("customerlevelList", $this->model->getCustomerlevelList());
        $this->view->assign("liftStateList", $this->model->getLiftStateList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 查询数据
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
        
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username', true);
            $total = $this->model
                ->with(['backoffice' => function ($query) {
                    $query->withField(['nickname', 'avatar']);
                }, 'admin' => function ($quyer) {
                    $quyer->withField(['nickname', 'avatar', 'rule_message']);
                }])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['backoffice' => function ($query) {
                    $query->withField(['nickname', 'avatar']);
                }, 'admin' => function ($quyer) {
                    $quyer->withField(['nickname', 'avatar', 'rule_message']);
                }])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            $list = collection($list)->toArray();

            foreach ($list as $key => $value) {
                $list[$key]['admin']['avatar_url'] = Config::get('upload')['cdnurl'];
                $list[$key]['feedback_content'] = $this->tableShowF_d($value['id']);
            }

            $result = array("total" => $total, "rows" => $list);

            return $result;
        }

        return $this->view->fetch();
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
                // $data = dstribution_inform();

                // $email = new Email;
                // // $receiver = "haoqifei@cdjycra.club";
                // $receiver = DB::name('admin')->where('id', $params['backoffice_id'])->value('email');
                // $result_s = $email
                //     ->to($receiver)
                //     ->subject($data['subject'])
                //     ->message($data['message'])
                //     ->send();
                // if ($result_s) {
                    $this->success();
                // } else {
                //     $this->error('邮箱发送失败');
                // }

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

                // $data = dstribution_inform();

                // $email = new Email;
                // $receiver = DB::name('admin')->where('id', $params['backoffice_id'])->value('email');
                // $result_s = $email
                //     ->to($receiver)
                //     ->subject($data['subject'])
                //     ->message($data['message'])
                //     ->send();
                // if ($result_s) {
                    $this->success();
                // } else {
                //     $this->error('邮箱发送失败');
                // }
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
                'rule_message' => 'message6',
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
            // pr($temp);
            // die;
            foreach ($temp as $k => $v) {
                if (isset($fieldArr[$k]) && $k !== '') {
                    $row[$fieldArr[$k]] = $v;
                }
            }
            // pr($row);
            // die;
             if(!$row['status']||!$row['phone']){
                  continue;
             }
            if ($row) {
                $insert[] = $row;
            }

        }
        // pr($insert);
        // die();
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

<?php

namespace app\admin\controller\promote;

use app\common\controller\Backend;
use app\admin\model\Admin;
use app\admin\controller\wechat\WechatMessage;
use app\admin\model\Admin as adminModel;
use app\common\library\Email;
// use app\admin\controller\wechat\Wechatuser;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use think\Db;
use think\Config;

/**
 * 客户资源列管理
 *
 * @icon fa fa-circle-o
 */
class Customertabs extends Backend
{
    
    /**
     * Customertabs模型对象
     * @var \app\admin\model\CustomerResource
     */
    protected $model = null;
    protected $noNeedRight = ['*'];

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
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    ->with(['admin' => function ($quyer) {
                        $quyer->withField(['nickname', 'avatar', 'rule_message']);
                    }, 'backoffice' => function ($quyer) {
                        $quyer->withField(['nickname', 'avatar']);
                    }])
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with(['admin' => function ($quyer) {
                        $quyer->withField(['nickname', 'avatar', 'rule_message']);
                    }, 'backoffice' => function ($quyer) {
                        $quyer->withField(['nickname', 'avatar']);
                    }])
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            foreach ($list as $key => $row) {

                $list[$key]['avatar_url'] = Config::get('upload')['cdnurl'];
                $list[$key]['feedback_content'] = $this->tableShowF_d($row['id']);

            }

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
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
                    // $this->error('邮箱发送失败');
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
     * 获取内勤
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getBackOffice()
    {
        $backoffice = collection(Admin::field('id,nickname,rule_message')->where(function ($query) {
            $query->where([
                'rule_message' => 'message5',
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
        foreach ($list as $k => $v) {
            if ($importHeadType == 'comment') {
                $fieldArr[$v['COLUMN_COMMENT']] = $v['COLUMN_NAME'];
            } else {
                $fieldArr[$v['COLUMN_NAME']] = $v['COLUMN_NAME'];
            }
        }

        //加载文件
        $insert = [];
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
                if(!$row['status']||!$row['phone']){
                    continue;
               }
                if ($row) {
                    $insert[] = $row;
                }
            }
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
        if (!$insert) {
            $this->error(__('No rows were updated'));
        }

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
            $this->model->saveAll($insert);
        } catch (PDOException $exception) {
            $msg = $exception->getMessage();
            if (preg_match("/.+Integrity constraint violation: 1062 Duplicate entry '(.+)' for key '(.+)'/is", $msg, $matches)) {
                $msg = "导入失败，包含【{$matches[1]}】的记录已存在";
            };
            $this->error($msg);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success();
    }

}

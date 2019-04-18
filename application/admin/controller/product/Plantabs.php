<?php

namespace app\admin\controller\product;

use app\common\controller\Backend;
use think\Db;

/**
 * 以租代购
 *
 * @icon fa fa-circle-o
 */
class Plantabs extends Backend
{
    
    /**
     * Plantabs模型对象
     * @var \app\admin\model\Plantabs
     */
    protected $model = null;
    protected $multiFields = 'ismenu';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Plantabs;
        $this->view->assign("nperlistList", $this->model->getNperlistList());
        $this->view->assign("workingInsuranceList", $this->model->getWorkingInsuranceList());
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
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    ->with(['schemecategory','models'])
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with(['schemecategory','models'])
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            foreach ($list as $key => $row) {
                
                $brand_name = Db::name('brand_cate')->where('id', $row['models']['brand_id'])->value('name');
                $list[$key]['models_name'] = $brand_name . ' ' . $list[$key]['models']['name'];
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function commitedit($row)
    {
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
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
    }

    /**
     * 新车方案编辑
     */
    public function newedit($ids = null)
    {
        $row = $this->model->get($ids);
        $this->commitedit($row);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 二手方案编辑
     */
    public function usedcaredit($ids = null)
    {
        $row = $this->model->get($ids);
        $this->commitedit($row);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 租车方案编辑
     */
    public function rentaledit($ids = null)
    {
        $row = $this->model->get($ids);
        $this->commitedit($row);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
}

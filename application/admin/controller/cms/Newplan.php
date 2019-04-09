<?php

namespace app\admin\controller\cms;

use app\admin\model\CompanyStore;
use app\admin\model\Cities;
use app\common\controller\Backend;
use app\common\library\Email;
//use app\common\model\Config;
use think\Config;
use think\console\output\descriptor\Console;
use think\Model;
use think\Session;
use think\Db;
use fast\Tree;
use think\db\Query;


/**
 * 多表格示例
 *
 * @icon fa fa-table
 * @remark 当一个页面上存在多个Bootstrap-table时该如何控制按钮和表格
 */
class Newplan extends Backend
{

    protected $model = null;
    protected $multiFields = ['recommendismenu', 'flashviewismenu', 'specialismenu', 'subjectismenu'];

    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('PlanAcar');

        $storeList = [];
        $disabledIds = [];
        $cities_all = collection(Cities::where('pid', 'NEQ', '0')->order("id desc")->field(['id,cities_name as name'])->select())->toArray();
        $store_all = collection(CompanyStore::order("id desc")->field(['id, city_id, store_name as name'])->select())->toArray();
        $all = array_merge($cities_all, $store_all);
        // pr($all);
        // die;
        foreach ($all as $k => $v) {

            $state = ['opened' => true];

            if (!$v['city_id']) {
            
                $disabledIds[] = $v['id'];
                $storeList[] = [
                    'id'     => $v['id'],
                    'parent' => '#',
                    'text'   => __($v['name']),
                    'state'  => $state
                ];
            }

            foreach ($cities_all as $key => $value) {
                
                if ($v['city_id'] == $value['id']) {
                    
                    $storeList[] = [
                        'id'     => $v['id'],
                        'parent' => $value['id'],
                        'text'   => __($v['name']),
                        'state'  => $state
                    ];
                }
                   
            }
            
        }
        // pr($storeList);
        // die;
        // $tree = Tree::instance()->init($all, 'city_id');
        // $storeOptions = $tree->getTree(0, "<option value=@id @selected @disabled>@spacer@name</option>", '', $disabledIds);
        // pr($storeOptions);
        // die;
        // $this->view->assign('storeOptions', $storeOptions);
        $this->assignconfig('storeList', $storeList);

    }

    /**
     * Notes:新车方案
     * User: glen9
     * Date: 2018/9/6
     * Time: 21:47
     * @return string|\think\response\Json
     * @throws \think\Exception
     */
    public function index()
    {
        $this->model = model('PlanAcar');
        $this->view->assign("nperlistList", $this->model->getNperlistList());
        $this->view->assign("ismenuList", $this->model->getIsmenuList());
        //当前是否为关联查询
        // $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('models.name', true);
            $total = $this->model
                ->with(['models' => function ($query) {
                    $query->withField('name,models_name');
                }, 'admin' => function ($query) {
                    $query->withField('nickname');
                }, 'schemecategory' => function ($query) {
                    $query->withField('name,category_note');
                }, 'financialplatform' => function ($query) {
                    $query->withField('name');
                }, 'subject' => function ($query) {
                    $query->withField('title,coverimages');
                }, 'label' => function ($query) {
                    $query->withField('name,lableimages');
                }, 'companystore' => function ($query) {
                    $query->withField('store_name');
                }])
                ->where($where)
                ->where('category_id', 'NEQ', '10')
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['models' => function ($query) {
                    $query->withField('id,name,models_name');
                }, 'admin' => function ($query) {
                    $query->withField('nickname');
                }, 'schemecategory' => function ($query) {
                    $query->withField('name,category_note');
                }, 'financialplatform' => function ($query) {
                    $query->withField('name');
                }, 'subject' => function ($query) {
                    $query->withField('title,coverimages');
                }, 'label' => function ($query) {
                    $query->withField('name,lableimages');
                }, 'companystore' => function ($query) {
                    $query->withField('store_name');
                }])
                ->where($where)
                ->where('category_id', 'NEQ', '10')
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            //去重
            $models_ids = [];
            foreach ($list as $key => $row) {

                $row->visible(['id', 'payment', 'monthly', 'brand_name', 'brand_log', 'match_plan', 'nperlist', 'margin', 'tail_section', 'gps', 'note', 'createtime', 'guide_price',
                    'updatetime', 'category_id', 'recommendismenu', 'flashviewismenu', 'specialismenu', 'subjectismenu', 'specialimages', 'models_main_images', 'modelsimages', 'weigh',
                    'store_name','models_id']);
                $row->visible(['models']);
                $row->getRelation('models')->visible(['id','name', 'models_name']);
                $row->visible(['admin']);
                $row->getRelation('admin')->visible(['nickname']);
                $row->visible(['schemecategory']);
                $row->getRelation('schemecategory')->visible(['name', 'category_note']);
                $row->visible(['financialplatform']);
                $row->getRelation('financialplatform')->visible(['name']);
                $row->visible(['subject']);
                $row->getRelation('subject')->visible(['title', 'coverimages']);
                $row->visible(['label']);
                $row->getRelation('label')->visible(['name', 'lableimages']);
                $row->visible(['companystore']);
                $row->getRelation('companystore')->visible(['store_name']);
                $list[$key]['brand_name'] = array_keys(self::getBrandName($row['id'])); //获取品牌
                $list[$key]['brand_log'] = Config::get('upload')['cdnurl'] . array_values(self::getBrandName($row['id']))[0]; //获取logo图片
                $list[$key]['store_name'] = $this->getStoreName($row['store_id']); //门店
                
                if ($list[$key]['models']['models_name']) {
                    $list[$key]['models']['name'] = $list[$key]['models']['name'] . " " . $list[$key]['models']['models_name'];
                }

                // if (in_array($list[$key]['models_id'], $models_ids)) {
                //     unset($list[$key]);
                // }
                // else{
                //     $models_ids[] = $list[$key]['models_id'];
                // }

            }
            
            $list = collection($list)->toArray();

            $result = array("total" => $total, "rows" => $list);
            Session::set('row', $list);
            return json($result);
        }

        return $this->view->fetch('index');
    }

    /**
     * 关联品牌名称
     * @param $plan_id 方案id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getBrandName($plan_id = null)
    {
        return Db::name('plan_acar')->alias('a')
            ->join('models b', 'a.models_id = b.id')
            ->join('brand c', 'b.brand_id=c.id')
            ->where('a.id', $plan_id)
            ->column('c.name,c.brand_logoimage');
    }

    /**
     * 关联城市省份
     * @param $store_id 门店id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getStoreName($store_id)
    {
        $store = CompanyStore::where('id', $store_id)->find();
        $cities = Cities::where('id', $store['city_id'])->find();
        $provice = Cities::where('id', $cities['pid'])->value('name');
        return $provice . $cities['cities_name'] . "---" . $store['store_name'];
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
            //专题
            $plan_id = Db::name('cms_subject')->where('id', $params['subject_id'])->field('plan_id')->find();
            $plan_id = json_decode($plan_id['plan_id'], true);

            if ($plan_id) {
                if (!in_array($ids, $plan_id['plan_id'])) {
                    array_push($plan_id['plan_id'], $ids);
                }
            } else {
                $plan_id['plan_id'][] = $ids;
            }

            $plan_id = json_encode($plan_id);

            $result_s = Db::name('cms_subject')->where('id', $params['subject_id'])->update(['plan_id' => $plan_id]);

            $params['subjectismenu'] = '1';
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
            "row" => $row,
            'subject'=>$this->getSubject($row['store_id'])
        ]);

        return $this->view->fetch();
    }

    //专题标题
    public function getSubject($store_id)
    {
        if ($this->request->isAjax()) {
            $store_id = $this->request->post('store_id');

            $subject = $this->getSpecials($store_id);
            $this->success('','',$subject);
       }

       if($store_id){
           return $this->getSpecials($store_id);
       }


    }

    /**
     * 根据门店id获取专题
     * @param $id
     * @return array
     */
    public function getSpecials($id)
    {
        $result = model('Cities')->field('id')
            ->with(['subject', 'companystore' => function ($query) use ($id) {
                $query->where('companystore.id', $id);
                $query->withField('id');
            }])->select();

        $subject = [];
        foreach ($result as $k=>$v){
            $subject[$v['subject']['id']] = $v['subject']['title'];
        }

        return $subject;
    }

    //门店名称
    public function getStore()
    {
        $this->model = model('CompanyStore');
        // //当前是否为关联查询
        // $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
        }
    }

    /**
     * 批量更新
     */
    public function multi($ids = "")
    {
        $ids = $ids ? $ids : $this->request->param("ids");
        if ($ids) {
            if ($this->request->has('params')) {
                parse_str($this->request->post("params"), $values);
                $values = array_intersect_key($values, array_flip(is_array($this->multiFields) ? $this->multiFields : explode(',', $this->multiFields)));
                if ($values) {
                    $data = $this->model->where('id', $ids)->field('subject_id')->find();
                    if ($values['subjectismenu'] == '0') {

                        $plan_id = Db::name('cms_subject')->where('id', $data['subject_id'])->field('plan_id')->find();
                        $plan_id = json_decode($plan_id['plan_id'], true);
                        // pr($plan_id);
                        // die;
                        if (in_array($ids, $plan_id['plan_id'])) {

                            foreach ($plan_id['plan_id'] as $k => $v) {
                                if ($v === $ids)
                                    unset($plan_id['plan_id'][$k]);
                            }

                            $plan_id = json_encode($plan_id);
                            // pr($plan_id);
                            // die;
                            $result_s = Db::name('cms_subject')->where('id', $data['subject_id'])->setField('plan_id', $plan_id);

                        }
                    } else {
                        //专题
                        $plan_id = Db::name('cms_subject')->where('id', $data['subject_id'])->field('plan_id')->find();
                        $plan_id = json_decode($plan_id['plan_id'], true);

                        if ($plan_id) {
                            if (!in_array($ids, $plan_id['plan_id'])) {
                                array_push($plan_id['plan_id'], $ids);
                            }
                        } else {
                            $plan_id['plan_id'][] = $ids;
                        }

                        $plan_id = json_encode($plan_id);

                        $result_s = Db::name('cms_subject')->where('id', $data['subject_id'])->setField('plan_id', $plan_id);
                    }
                    $adminIds = $this->getDataLimitAdminIds();
                    if (is_array($adminIds)) {
                        $this->model->where($this->dataLimitField, 'in', $adminIds);
                    }
                    $count = 0;
                    $list = $this->model->where($this->model->getPk(), 'in', $ids)->select();
                    foreach ($list as $index => $item) {
                        $count += $item->allowField(true)->isUpdate(true)->save($values);
                    }
                    if ($count) {
                        $this->success();
                    } else {
                        $this->error(__('No rows were updated'));
                    }
                } else {
                    $this->error(__('You have no permission'));
                }
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    //拖拽排序---改变权重
    public function dragsort()
    {
        //排序的数组
        $ids = $this->request->post("ids");
        //拖动的记录ID
        $changeid = $this->request->post("changeid");
        //操作字段
        $field = $this->request->post("field");
        //操作的数据表
        $table = $this->request->post("table");
        //排序的方式
        $orderway = $this->request->post("orderway", 'strtolower');
        $orderway = $orderway == 'asc' ? 'ASC' : 'DESC';
        $sour = $weighdata = [];
        $ids = explode(',', $ids);
        $prikey = 'id';
        $pid = $this->request->post("pid");
        
        //限制更新的字段
        $field = in_array($field, ['weigh']) ? $field : 'weigh';

        // 如果设定了pid的值,此时只匹配满足条件的ID,其它忽略
        if ($pid !== '') {
            $hasids = [];
            $list = Db::name($table)->where($prikey, 'in', $ids)->where('pid', 'in', $pid)->field('id,pid')->select();
            foreach ($list as $k => $v) {
                $hasids[] = $v['id'];
            }
            $ids = array_values(array_intersect($ids, $hasids));
        }

        $list = Db::name($table)->field("$prikey,$field")->where($prikey, 'in', $ids)->order($field, $orderway)->select();
       
        foreach ($list as $k => $v) {
            $sour[] = $v[$prikey];
            $weighdata[$v[$prikey]] = $v[$field];
        }
        $position = array_search($changeid, $ids);
        $desc_id = $sour[$position];    //移动到目标的ID值,取出所处改变前位置的值
        $sour_id = $changeid;
        $weighids = array();
        $temp = array_values(array_diff_assoc($ids, $sour));
        foreach ($temp as $m => $n) {
            if ($n == $sour_id) {
                $offset = $desc_id;
            } else {
                if ($sour_id == $temp[0]) {
                    $offset = isset($temp[$m + 1]) ? $temp[$m + 1] : $sour_id;
                } else {
                    $offset = isset($temp[$m - 1]) ? $temp[$m - 1] : $sour_id;
                }
            }
            $weighids[$n] = $weighdata[$offset];
            Db::name($table)->where($prikey, $n)->update([$field => $weighdata[$offset]]);
        }
        $this->success();
    }



}

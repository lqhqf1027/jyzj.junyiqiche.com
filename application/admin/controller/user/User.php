<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use addons\cms\controller\wxapp\User as AddonsUser;
use think\Db;
/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{

    protected $relationSearch = true;
    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('User');
        //总用户数量
        $user_count = $this->model->count();

        $startday = \fast\Date::unixtime('day');
        $endday = $startday + 86400;
        //今日登陆人数
        $user_today = $this->model->where('logintime', 'between', [$startday, $endday])->count();
        //今日加入人数
        $user_join = $this->model->where('jointime', 'between', [$startday, $endday])->count();
        //连续登陆天数大于三
        $user_login = $this->model->where('successions', '>=', '2')->count();
        $this->view->assign([
            'user_count' => $user_count,
            'user_today' => $user_today,
            'user_join'  => $user_join,
            'user_login' => $user_login
        ]);
    }

    /**
     * 查看
     */
    public function index()
    {
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
                    ->with('group')
                    ->where($where)
                    ->order($sort, $order)
                    ->count();
            $list = $this->model
                    ->with('group')
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();
            foreach ($list as $k => $v)
            {
                $v->hidden(['password', 'salt']);
                $list[$k]['nickname'] = AddonsUser::emoji_decode($list[$k]['nickname']);
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), $row['group_id'], ['class' => 'form-control selectpicker']));
        return parent::edit($ids);
    }
    public  function  registerAccount(){
        if($this->request->isAjax()){
            $params = input('post.');
            //判断email是否被注册
            $email = model('Adminaccount')::where(['email'=>$params['postdata']['email']])->column('email');
            if(empty($email)&&$params){

                $params['postdata']['createtime'] = time();
                return model('Adminaccount')->allowField(true)->save($params['postdata'])?json(array('errorcode'=>'00001',msg=>'注册成功',result=>'')):json(array('errorcode'=>'00002',msg=>'注册失败',result=>''));
            }
            return json(array('errorcode'=>'00000',msg=>'此邮箱已被注册',result=>''));
        }

    }

}

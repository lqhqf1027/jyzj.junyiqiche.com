<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use think\Db;

/**
 * 奖品管理
 *
 * @icon fa fa-circle-o
 */
class Prize extends Backend
{

    /**
     * Prize模型对象
     * @var \app\admin\model\Prize
     */
    protected $model = null;

    protected $noNeedLogin = ['reset_prize'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Prize;
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 定时任务
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function reset_prize()
    {
        $time = time();
        $block = new \app\admin\model\Block();
        $endtime = strtotime(Db::name('config')
            ->where('name', 'endtime')
            ->value('value'));
        if ($time >= $endtime) {

            $block->where('title', '转盘抽奖')
                ->setField('status', 'hidden');
        }

        $blockStatus = $block->where('title', '转盘抽奖')->find();

        if($blockStatus->status=='normal'){
            $total_payment = Db::name('cms_prize')
                ->where('status', 'normal')
                ->field('id,total_payment')
                ->select();

            foreach ($total_payment as $k => $v) {
                Db::name('cms_prize')
                    ->update([
                        'id' => $v['id'],
                        'total_surplus' => $v['total_payment'],
                        'win_prize_number' => $v['total_payment'],
                    ]);
            }
        }


    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}

<?php

namespace app\admin\controller\system;

use app\common\controller\Backend;
// use app\admin\controller\salesmanagement\Customerlisttabs;
use think\Config;
use think\Db;
use think\Cache;

/**
 * 系统使用流程
 *
 * @icon fa fa-hand-o-right
 * @remark 用于展示当前系统的使用流程
 */
class Process extends Backend
{

    /**
     * 查看
     */
    public function index()
    {

        return $this->view->fetch();
    }

}

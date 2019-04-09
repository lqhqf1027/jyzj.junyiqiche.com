<?php

namespace app\admin\controller\planmanagement;

use app\common\controller\Backend;

/**
 * 金融平台
 *
 * @icon fa fa-circle-o
 */
class Financialplatform extends Backend
{
    
    /**
     * FinancialPlatform模型对象
     * @var \app\admin\model\FinancialPlatform
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('FinancialPlatform');
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    


}

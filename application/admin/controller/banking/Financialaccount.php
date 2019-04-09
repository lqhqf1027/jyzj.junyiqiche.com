<?php

namespace app\admin\controller\banking;

use app\common\controller\Backend;

/**
 * 金融办抵押台账
 *
 * @icon fa fa-circle-o
 */
class Financialaccount extends Backend
{
    
    /**
     * Account模型对象
     * @var \app\admin\model\FinancialAccount
     */
    protected $model = null;
    protected $noNeedRight =['index','edit','del','add'];
    protected $searchFields='full_name,framenumber';
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\FinancialAccount();

    }

}

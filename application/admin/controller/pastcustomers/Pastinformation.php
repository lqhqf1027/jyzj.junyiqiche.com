<?php

namespace app\admin\controller\pastcustomers;

use app\common\controller\Backend;

use app\admin\model\PastInformation as PastInformationModel;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class Pastinformation extends Backend
{

    /**
     * PastInformation模型对象
     * @var \app\admin\model\PastInformation
     */
    protected $model = null;

    protected $noNeedLogin = '*';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\PastInformation;
        $this->view->assign("typesList", $this->model->getTypesList());

    }

    public function grant_authorization($ids = null)
    {
        $models = PastInformationModel::get($ids)->visible(['username', 'qr_code']);

        if (!$models['qr_code']) {

           $result = setQrcode('new', $ids, $models['username']);
        };

        $this->view->assign('qrcode',!empty($result)?$result:$models['qr_code']);

        return $this->view->fetch();
    }


}

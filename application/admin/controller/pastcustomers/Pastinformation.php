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


    //添加转介绍
    public function referral($ids = null)
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");

            $referral_id = $this->model->where(['username' => $params['username'], 'id_card' => $params['id_card']])->value('id');
            $referral_ids = $this->model->where('id', $ids)->value('referral_ids');

            if($referral_id) {
                $referral_ids = $referral_ids . $referral_id . ',';

                // pr($referral_ids);
                // die;
                $result = $this->model->where('id', $ids)->update(['referral_ids' => $referral_ids]);
                if ($result) {
                    $this->success();
                }
                else {
                    $this->error();
                }
            }
            else {
                $this->error('未匹配到用户,请重新输入');
            }
            
        }
        return $this->view->fetch();
    }


    //查看违章信息
    public function violationInformation($ids = null)
    {
        $data = $this->model->where('id', $ids)->find();
        //违章信息
        $violation_details = json_decode($data['violation_details'], true);
        foreach ($violation_details as $k => $v) {
            //总扣分
            $total_deduction += $v['fen'];
            //总扣钱
            $total_fine += $v['money'];
        }

        $this->view->assign([
            'violation_details' => $violation_details,
            'data' => $data,
            'total_deduction' => $total_deduction,
            'total_fine' => $total_fine
            ]);

        return $this->view->fetch();
    }


}

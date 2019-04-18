<?php

namespace addons\cms\controller\wxapp;
use addons\cms\model\Config;
use addons\cms\model\User;
use fast\Auth;
use think\Db;
use Endroid\QrCode\QrCode;
use fast\Random;
use think\Exception;
/**
 * 我的
 */
class My extends Base
{
    protected $noNeedLogin = ['*'];
    protected $uid = '';
    public function _initialize()
    {
        parent::_initialize();
//        $auth = Auth::instance();
//        $this->uid = $auth->id;
    }
    public function index(){
        $user_id = $this->request->post('user_id');
        if (!(int)$user_id) $this->error('参数错误');
        try {
            $userInfo = User::field('id,nickname,avatar,invite_code,invitation_code_img,level')
                ->find($user_id);
            if (!$userInfo) $this->error('未查询到用户信息');
            //查询邀请码背景图片
            $userInfo['invite_bg_img'] = Config::get(['name' => 'invite_bg_img'])->value;

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('请求成功', ['userInfo' => $userInfo]);

    }
    /**
     * 生成二维码
     * @throws \Endroid\QrCode\Exceptions\ImageTypeInvalidException
     */
    public function setQrcode()
    {
        $user_id = $this->request->post('user_id');
        if (!(int)$user_id) $this->error('参数错误');
        $time = date('YmdHis');
        $qrCode = new QrCode();
        $qrCode->setText($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/?user_id=' . $user_id )
            ->setSize(150)
            ->setPadding(10)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabel(' ')
            ->setLabelFontSize(10)
            ->setImageType(\Endroid\QrCode\QrCode::IMAGE_TYPE_PNG);
        $fileName = DS . 'uploads' . DS . 'qrcode' . DS . $time . '_' . $user_id . '.png';
        $qrCode->save(ROOT_PATH . 'public' . $fileName);
        if ($qrCode) {
            User::update(['id' => $user_id, 'invitation_code_img' => $fileName]) ? $this->success('创建成功', $fileName) : $this->error('创建失败');
        }
        $this->error('未知错误');
    }

}

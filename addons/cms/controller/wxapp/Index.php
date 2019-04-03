<?php

namespace addons\cms\controller\wxapp;

use addons\cms\model\Archives;
use addons\cms\model\Block;
use addons\cms\model\Channel;
use app\common\model\Addon;
use addons\cms\model\User;
use addons\cms\model\PastInformation;
use addons\cms\model\Config as ConfigModel;
use think\Exception;
use Endroid\QrCode\QrCode;

/**
 * 首页
 */
class Index extends Base
{

    protected $noNeedLogin = '*';

    protected static $keys = '217fb8552303cb6074f88dbbb5329be7';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 首页
     */
    public function index()
    {
        $bannerList = [];
        $list = Block::getBlockList(['name' => 'focus', 'row' => 5]);
        foreach ($list as $index => $item) {
            $bannerList[] = ['image' => cdnurl($item['image'], true), 'url' => '/', 'title' => $item['title']];
        }

        $tabList = [
            ['id' => 0, 'title' => '全部'],
        ];
        $channelList = Channel::where('status', 'normal')
            ->where('type', 'in', ['list'])
            ->field('id,parent_id,name,diyname')
            ->order('weigh desc,id desc')
            ->cache(false)
            ->select();
        foreach ($channelList as $index => $item) {
            $tabList[] = ['id' => $item['id'], 'title' => $item['name']];
        }
        $archivesList = Archives::getArchivesList([]);
        $data = [
            'bannerList' => $bannerList,
            'tabList' => $tabList,
            'archivesList' => $archivesList,
        ];
        $this->success('', $data);

    }

    /**
     * 进入小程序调用接口
     */
    public function past_information()
    {
        $user_id = $this->request->post('user_id');

        $pastInformation = $this->request->post('pastInformation_id');

        if(!$user_id || !$pastInformation){
            $this->error('缺少参数');
        }

        PastInformation::where('id',$pastInformation)->setField('user_ud',$user_id);

        $this->success('请求成功', PastInformation::getByUser_id($user_id));

    }

    /**
     * 查询违章
     */
    public function query_violation()
    {
        $license_plate = $this->request->post('license_plate');

        $engine_number = $this->request->post('engine_number');

        $frame_number = $this->request->post('frame_number');

        $user_id = $this->request->post('user_id');

        try {
            $details = PastInformation::getByUser_id($user_id)->violation_details;

            if (!$details) {
                $result = gets("http://v.juhe.cn/sweizhang/carPre.php?key=" . self::$keys . "&hphm=" . urlencode($license_plate));

                if ($result['error_code'] != 0) throw new Exception($result['reason']);

                $data = gets("http://v.juhe.cn/sweizhang/query?city=" . $result['result']['city_code'] . "&hphm=" . urlencode($license_plate) . '&engineno=' . $engine_number . '&classno=' . $frame_number . "&key=" . self::$keys);

                if ($data['error_code'] != 0) throw new Exception($data['reason']);

                PastInformation::where('user_id', $user_id)->update([
                    'violation_details' => json_encode($data['result']['lists']),
                    'query_times' => 1
                ]);

                $results = $data['result']['lists'];
            } else {
                $results = json_decode($details);
            }

        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success($results);

    }

    /**
     * 更新违章
     */
    public function update_violation()
    {
        $license_plate = $this->request->post('license_plate');

        $engine_number = $this->request->post('engine_number');

        $frame_number = $this->request->post('frame_number');

        $user_id = $this->request->post('user_id');

        try {
            $query_times = PastInformation::getByUser_id($user_id)->query_times;

            if ($query_times >= 2) throw new Exception('更新违章次数已超限制');

            $result = gets("http://v.juhe.cn/sweizhang/carPre.php?key=" . self::$keys . "&hphm=" . urlencode($license_plate));

            if ($result['error_code'] != 0) throw new Exception($result['reason']);

            $data = gets("http://v.juhe.cn/sweizhang/query?city=" . $result['result']['city_code'] . "&hphm=" . urlencode($license_plate) . '&engineno=' . $engine_number . '&classno=' . $frame_number . "&key=" . self::$keys);

            if ($data['error_code'] != 0) throw new Exception($data['reason']);

            PastInformation::where('user_id', $user_id)->update([
                'violation_details' => json_encode($data['result']['lists']),
                'query_times' => $query_times + 1
            ]);

            $this->success('更新违章成功');

        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 生成二维码
     * @throws \Endroid\QrCode\Exceptions\ImageTypeInvalidException
     */
    public function setQrcode()
    {
//        $user_id = $this->request->post('user_id');
//        if (!(int)$user_id) $this->error('参数错误');
        $time = date('Ymd');
        $qrCode = new QrCode();
        $qrCode->setText($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/?user_id=2' )
            ->setSize(150)
            ->setPadding(10)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabel(' ')
            ->setLabelFontSize(10)
            ->setImageType(\Endroid\QrCode\QrCode::IMAGE_TYPE_PNG);
        $fileName = DS . 'uploads' . DS .'qrcode'.DS. $time . '_' . 2 . '.png';
        $qrCode->save(ROOT_PATH . 'public' . $fileName);
        if ($qrCode) {
            User::update(['id' => 2, 'invitation_code_img' => $fileName]) ? $this->success('创建成功', $fileName) : $this->error('创建失败');
        }
        $this->error('未知错误');
    }


}

<?php

namespace addons\cms\controller\wxapp;

use addons\cms\model\Block;
use addons\cms\model\Channel;
use app\common\model\Addon;
use think\Config as ThinkConfig;
use fast\Random;
use think\Env;
use think\Request;
use Upyun\Upyun;
use Upyun\Config;

/**
 * 公共
 */
class Common extends Base
{
    protected $noNeedLogin = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 上传文件到u拍云
     * @ApiMethod (POST)
     * @param File $file 文件流
     */
    public function upUpyun()
    {
        $file = $this->request->file('file');

        $is_verify_idcard = $this->request->post('is_verify_idcard', false);

//        $this->success($is_verify_idcard);

        if (empty($file)) {
            $this->error(__('No file upload or server upload limit exceeded'));
        }

        //判断是否已经存在附件
        $sha1 = $file->hash();

        $upload = ThinkConfig::get('upload');

        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int)$upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix ? $suffix : 'file';

        $mimetypeArr = explode(',', strtolower($upload['mimetype']));
        $typeArr = explode('/', $fileInfo['type']);

        //验证文件后缀
        if ($upload['mimetype'] !== '*' &&
            (
                !in_array($suffix, $mimetypeArr)
                || (stripos($typeArr[0] . '/', $upload['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)))
            )
        ) {
            $this->error(__('Uploaded file format is limited'));
        }
        $replaceArr = [
            '{year}' => date("Y"),
            '{mon}' => date("m"),
            '{day}' => date("d"),
            '{hour}' => date("H"),
            '{min}' => date("i"),
            '{sec}' => date("s"),
            '{random}' => Random::alnum(16),
            '{random32}' => Random::alnum(32),
            '{filename}' => $suffix ? substr($fileInfo['name'], 0, strripos($fileInfo['name'], '.')) : $fileInfo['name'],
            '{suffix}' => $suffix,
            '{.suffix}' => $suffix ? '.' . $suffix : '',
            '{filemd5}' => md5_file($fileInfo['tmp_name']),
        ];
        $savekey = $upload['savekey'];
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
        $fileName = substr($savekey, strripos($savekey, '/') + 1);
        //
        $splInfo = $file->validate(['size' => $size])->move(ROOT_PATH . '/public' . $uploadDir, $fileName);
        if ($splInfo) {
            $imagewidth = $imageheight = 0;
            if (in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf'])) {
                $imgInfo = getimagesize($splInfo->getPathname());
                $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
                $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
            }

            $serviceConfig = new Config(Env::get('upyun.serviceName'), Env::get('upyun.operatorName'), Env::get('upyun.operatorPassword'));
            $client = new Upyun($serviceConfig);
            try {
                $fileName = $uploadDir . $splInfo->getSaveName();
                $files = fopen(ROOT_PATH . '/public' . $fileName, 'r');
                $res = $client->write('/jyzj.junyiqiche.com' . $fileName, $files); //上传到u拍云

                if ($is_verify_idcard) {
                    $identify_result = posts('https://api-cn.faceplusplus.com/cardpp/v1/ocridcard', [
                        'api_key' => '6YWpf8Xx8g1Ll2F5w8bNOpNkmOby1Sdh',
                        'api_secret' => 'BV_r5bgSN3DY9SELbKpmVUZ52hI-GCPp',
//                        'image_url'=>ROOT_PATH . '/public' . $fileName
                        'image_url' => 'https://static.junyiqiche.com/jyzj.junyiqiche.com' . $fileName
                    ]);
                }
                if ($res['x-upyun-content-length']) unlink(ROOT_PATH . '/public' . $fileName);  //删除本地服务器
            } catch (\Exception $e) {

                $this->error($e->getMessage());
            }

            $datas = [
                'url' => '/jyzj.junyiqiche.com' . $fileName
            ];

            if (!empty($identify_result)) {
                $datas['card_info'] = $identify_result;
            }

            $this->success('上传成功', $datas);
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
    }

}

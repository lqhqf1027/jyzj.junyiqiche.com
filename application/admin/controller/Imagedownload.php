<?php

namespace app\admin\controller;

use app\admin\controller\material\Zipfile;
use app\common\controller\Backend;

/**
 * 测试管理
 *
 * @icon fa fa-circle-o
 */
class Imagedownload extends Backend
{

    /**
     * Test模型对象
     * @var \app\admin\model\Test
     */
    protected $model = null;
    protected $noNeedRight = ['pack'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Test');
        $this->view->assign("weekList", $this->model->getWeekList());
        $this->view->assign("flagList", $this->model->getFlagList());
        $this->view->assign("genderdataList", $this->model->getGenderdataList());
        $this->view->assign("hobbydataList", $this->model->getHobbydataList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("stateList", $this->model->getStateList());
    }


    /**
     * 批量下载图片，生成ZIP文件
     */
    public function pack()
    {


        $dfile = tempnam('/tmp', 'tmp');//产生一个临时文件，用于缓存下载文件
        $zip = new Zipfile();

        $all_img = input("post.all");

        $all_font = input("post.all_font");

        $username = input("post.username");


        $all_font = explode(",", $all_font);

        $all_img = explode(",", $all_img);

        $image = array();


        foreach ($all_img as $k => $v) {
            array_push($image, ['image_src' => $v, 'image_name' => trim($all_font[$k]) . ".jpg"]);
        }


//----------------------
        $filename = $username . '.zip'; //下载的默认文件名
        $filename = $filename;
        $host = 'http://test.love11.com';


        foreach ($image as $v) {
            $zip->add_file(file_get_contents($v['image_src']), $v['image_name']);
            // 添加打包的图片，第一个参数是图片内容，第二个参数是压缩包里面的显示的名称, 可包含路径
            // 或是想打包整个目录 用 $zip->add_path($image_path);
        }
//----------------------
        $zip->output($dfile);
// 下载文件
        ob_clean();
        header('Pragma: public');
        header('Last-Modified:' . gmdate('D, d M Y H:i:s') . 'GMT');
        header('Cache-Control:no-store, no-cache, must-revalidate');
        header('Cache-Control:pre-check=0, post-check=0, max-age=0');
        header('Content-Transfer-Encoding:binary');
        header('Content-Encoding:none');
        header('Content-type:multipart/form-data');
        header('Content-Disposition:attachment; filename="' . $filename . '"'); //设置下载的默认文件名
        header('Content-length:' . filesize($dfile));
        $fp = fopen($dfile, 'r');
        while (connection_status() == 0 && $buf = @fread($fp, 8192)) {
            echo $buf;
        }
        fclose($fp);
        @unlink($dfile);
        @flush();
        @ob_flush();
        exit();
    }

    public function getRepeat($arr)
    {

        // 获取去掉重复数据的数组
        $unique_arr = array_unique($arr);
        // 获取重复数据的数组
        $repeat_arr = array_diff_assoc($arr, $unique_arr);

        return $repeat_arr;
    }

}

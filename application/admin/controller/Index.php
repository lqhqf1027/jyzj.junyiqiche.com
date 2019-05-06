<?php

namespace app\admin\controller;

use app\admin\model\AdminLog;
use app\common\controller\Backend;
use think\Config;
use think\Hook;
use think\Validate;

/**
 * 后台首页
 * @internal
 */
class Index extends Backend
{

    protected $noNeedLogin = ['login'];
    protected $noNeedRight = ['index', 'logout'];
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 后台首页
     */
    public function index()
    {
        //左侧菜单
        list($menulist, $navlist, $fixedmenu, $referermenu) = $this->auth->getSidebar([
            'dashboard' => 'hot',
            'addon'     => ['new', 'red', 'badge'],
            'auth/rule' => __('Menu'),
            'general'   => ['new', 'purple'],
        ], $this->view->site['fixedpage']);
        $action = $this->request->request('action');
        if ($this->request->isPost()) {
            if ($action == 'refreshmenu') {
                $this->success('', null, ['menulist' => $menulist, 'navlist' => $navlist]);
            }
        }
        $this->view->assign('menulist', $menulist);
        $this->view->assign('navlist', $navlist);
        $this->view->assign('fixedmenu', $fixedmenu);
        $this->view->assign('referermenu', $referermenu);
        $this->view->assign('title', __('Home'));
        return $this->view->fetch();
    }


    /**
     * 管理员登录
     */
    public function login()
    {

        $url = $this->request->get('url', 'index/index');
        if ($this->auth->isLogin()) {

            $this->success(__("You've logged in, do not login again"), $url);
        }
        if ($this->request->isPost()) {

            $username = $this->request->post('username');
            $password = $this->request->post('password');
            $keeplogin = $this->request->post('keeplogin');
            $token = $this->request->post('__token__');
            $rule = [
                'username'  => 'require|length:3,30',
                'password'  => 'require|length:3,30',
                '__token__' => 'token',
            ];
            $data = [
                'username'  => $username,
                'password'  => $password,
                '__token__' => $token,
            ];
            if (Config::get('fastadmin.login_captcha')) {
                $rule['captcha'] = 'require|captcha';
                $data['captcha'] = $this->request->post('captcha');
            }
            $validate = new Validate($rule, [], ['username' => __('Username'), 'password' => __('Password'), 'captcha' => __('Captcha')]);
            $result = $validate->check($data);
            if (!$result) {
                $this->error($validate->getError(), $url, ['token' => $this->request->token()]);
            }
            AdminLog::setTitle(__('Login'));
            $result = $this->auth->login($username, $password, $keeplogin ? 86400 : 0);
            if ($result === true) {
                Hook::listen("admin_login_after", $this->request);
//                $this->redirect('index/index');
                $this->success(__('Login successful'), $url, ['url' => $url, 'id' => $this->auth->id, 'username' => $username, 'avatar' => $this->auth->avatar]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('Username or password is incorrect');
                $this->error($msg, $url, ['token' => $this->request->token()]);
            }
        }

        // 根据客户端的cookie,判断是否可以自动登录
        if ($this->auth->autologin()) {

            $this->redirect($url);
        }

        $background = Config::get('fastadmin.login_background');

        $background = stripos($background, 'http') === 0 ? $background : config('site.cdnurl') . $background;

        $this->view->assign('background', $background);
        $this->view->assign('title', __('Login'));
//        Hook::listen("admin_login_init", $this->request);


        return $this->view->fetch();
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        $this->auth->logout();
        Hook::listen("admin_logout_after", $this->request);
        $this->redirect('index/login');
//        $this->success(__('Logout successful'), 'index/login');
    }

    public function exportOrderExcel($data)
    {

        // 新建一个excel对象 大神已经加入了PHPExcel 不用引了 直接用！
        $objPHPExcel = new \PHPExcel();  //在vendor目录下 \不能少 否则报错
        // 设置文档的相关信息
        $objPHPExcel->getProperties()->setCreator("楼主666")/*设置作者*/
        ->setLastModifiedBy("楼主666")/*最后修改*/
        ->setTitle("楼主666")/*题目*/
        ->setSubject("楼主666")/*主题*/
        ->setDescription("楼主666")/*描述*/
        ->setKeywords("楼主666")/*关键词*/
        ->setCategory("楼主666");/*类别*/
        $objPHPExcel->getDefaultStyle()->getFont()->setName('微软雅黑');//字体
        /*设置表头*/
        $objPHPExcel->getActiveSheet()->mergeCells('A1:P1');//合并第一行的单元格
        $objPHPExcel->getActiveSheet()->mergeCells('A2:P2');//合并第二行的单元格
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '楼主教我导出EXcel666');//标题
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);      // 第一行的默认高度
        //第二行的内容和格式
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', '楼主真的666');
        $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(20);/*设置行高*/
        $myrow = 3;/*表头所需要行数的变量，方便以后修改*/
        /*表头数据填充*/
        $objPHPExcel->getActiveSheet()->getRowDimension('3')->setRowHeight(30);/*设置行高*/
        $objPHPExcel->setActiveSheetIndex(0)  //设置一张sheet为活动表 添加表头信息
        ->setCellValue('A' . $myrow, '序号')
            ->setCellValue('B' . $myrow, '日期')
            ->setCellValue('C' . $myrow, '物资名称')
            ->setCellValue('D' . $myrow, '规格')
            ->setCellValue('E' . $myrow, '单位')
            ->setCellValue('F' . $myrow, '数量')
            ->setCellValue('G' . $myrow, '出厂单价(元)')
            ->setCellValue('H' . $myrow, '运杂费(元)')
            ->setCellValue('I' . $myrow, '税率')
            ->setCellValue('J' . $myrow, '税额(元)')
            ->setCellValue('K' . $myrow, '价税合计(元)')
            ->setCellValue('L' . $myrow, '金额(元)')
            ->setCellValue('M' . $myrow, '使用单位')
            ->setCellValue('N' . $myrow, '卸车地点')
            ->setCellValue('O' . $myrow, '生产厂家')
            ->setCellValue('P' . $myrow, '备注');
        // 关键数据
        $data = json_decode($data, true);
        $myrow = $myrow + 1; //刚刚设置的行变量
        $mynum = 1;//序号
        //遍历接收的数据，并写入到对应的单元格内
        foreach ($data as $key => $value) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $myrow, $mynum)
                ->setCellValue('B' . $myrow, $value['buyTime'])
                ->setCellValue('C' . $myrow, $value['proName'])
                ->setCellValue('D' . $myrow, $value['spec'])
                ->setCellValue('E' . $myrow, $value['unit'])
                ->setCellValue('F' . $myrow, $value['num'])
                ->setCellValue('G' . $myrow, $value['sellPrice'])
                ->setCellValue('H' . $myrow, '0')
                ->setCellValue('I' . $myrow, $value['sellTaxRate'])
                ->setCellValue('J' . $myrow, $value['sellTax'])
                ->setCellValue('K' . $myrow, $value['sellSumTax'])
                ->setCellValue('L' . $myrow, $value['sellSum'])
                ->setCellValue('M' . $myrow, '')
                ->setCellValue('N' . $myrow, '')
                ->setCellValue('O' . $myrow, '')
                ->setCellValue('P' . $myrow, '');
            $objPHPExcel->getActiveSheet()->getRowDimension('' . $myrow)->setRowHeight(20);/*设置行高 不能批量的设置 这种感觉 if（has（蛋）！=0）{疼();}*/
            $myrow++;
            $mynum++;
        }
        $mynumdata=$myrow-1; //获取主要数据结束的行号
        $objPHPExcel->setActiveSheetIndex(0)->getstyle('A3:P' . $mynumdata)->getAlignment()->setHorizontal(\PHPExcel_style_Alignment::HORIZONTAL_CENTER);/*设置格式 水平居中*/
        /*设置数据的边框 手册上写的方法只显示竖线 非常坑爹 所以采用网上搜来的方法*/
        $style_array = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                )
            ));
        $objPHPExcel->getActiveSheet()->getStyle('A3:P' . $mynumdata)->applyFromArray($style_array);
        /*设置数据的格式*/
        $objPHPExcel->getActiveSheet()->getRowDimension('' . $myrow)->setRowHeight(20);/*设置行高*/
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$myrow.':P'.$myrow);//合并下一行的单元格
        $objPHPExcel->setActiveSheetIndex(0) ->setCellValue('A' . $myrow,'供应单位：'.$name);
        $myrow++; $objPHPExcel->getActiveSheet()->getRowDimension('' . $myrow)->setRowHeight(20);/*设置行高*/
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$myrow.':C'.$myrow);//合并下一行的单元格
        $objPHPExcel->setActiveSheetIndex(0) ->setCellValue('A' . $myrow,'收料员：');
        $objPHPExcel->getActiveSheet()->mergeCells('D'.$myrow.':J'.$myrow);//合并下一行的单元格
        $objPHPExcel->setActiveSheetIndex(0) ->setCellValue('D' . $myrow,'复核：');
        $objPHPExcel->getActiveSheet()->mergeCells('K'.$myrow.':P'.$myrow);//合并下一行的单元格
        $objPHPExcel->setActiveSheetIndex(0) ->setCellValue('K' . $myrow,'确认签字：');
        $myrow++; $objPHPExcel->getActiveSheet()->getRowDimension('' . $myrow)->setRowHeight(20);/*设置行高*/
        $objPHPExcel->getActiveSheet()->mergeCells('I'.$myrow.':K'.$myrow);//合并下一行的单元格
        $objPHPExcel->setActiveSheetIndex(0) ->setCellValue('I' . $myrow,'日期：');
        $objPHPExcel->getActiveSheet()->getStyle('A1:P' . $myrow)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);/*垂直居中*/
        //关键数据结束


        //设置宽width 由于自适应宽度对中文的支持是个BUG因此坑爹的手动设置了每一列的宽度 这种感觉 if（has（蛋）！=0）{碎();}
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(8.5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(8);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(8);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(8.5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getStyle('A3:P' . $myrow)->getAlignment()->setWrapText(true);//设置单元格允许自动换行
        /*设置表相关的信息*/
        $objPHPExcel->getActiveSheet()->setTitle($buytime); //活动表的名称
        $objPHPExcel->setActiveSheetIndex(0);//设置第一张表为活动表
        //纸张方向和大小 为A4横向
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        //浏览器交互 导出
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="楼主教我的导出excel这弄得真是666.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;

    }

}

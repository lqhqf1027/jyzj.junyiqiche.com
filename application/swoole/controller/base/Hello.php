<?php
/**
 * Created by PhpStorm.
 * User: glen9
 * Date: 2019/5/14
 * Time: 18:50
 */

namespace app\swoole\controller\base;

use EasySwoole\Core\Http\AbstractInterface\Controller;

class Hello extends Controller
{

    function index()
    {
        $this->response()->write('Hello easySwoole1!');
    }
}
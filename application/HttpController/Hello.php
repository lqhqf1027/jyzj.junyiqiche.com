<?php
/**
 * Created by PhpStorm.
 * User: glen9
 * Date: 2019/5/14
 * Time: 19:11
 */

namespace app\HttpController;
use EasySwoole\Core\Http\AbstractInterface\Controller;

class Hello  extends Controller
{
    function index()
    {
        $this->response()->write('Hello easySwoole!');
    }
}
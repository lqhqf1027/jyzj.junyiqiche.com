<?php
/**
 * Created by PhpStorm.
 * User: glen9
 * Date: 2019/5/8
 * Time: 17:55
 */

namespace wechat;


class ErrorCode
{
    public static $OK = 0;
    public static $IllegalAesKey = -41001;
    public static $IllegalIv = -41002;
    public static $IllegalBuffer = -41003;
    public static $DecodeBase64Error = -41004;
}
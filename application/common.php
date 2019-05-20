<?php
// 公共助手函数
error_reporting(E_PARSE | E_ERROR | E_WARNING);

// 公共助手函数

use think\Request;
use think\Config;
use think\Cache;
use think\Env;
use fast\Http;

if (!function_exists('__')) {

    /**
     * 获取语言变量值
     * @param string $name 语言变量名
     * @param array $vars 动态变量值
     * @param string $lang 语言
     * @return mixed
     */
    function __($name, $vars = [], $lang = '')
    {
        if (is_numeric($name) || !$name)
            return $name;
        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }
        return \think\Lang::get($name, $vars, $lang);
    }

}
if (!function_exists('pr')) {
    /**
     * 打印变量
     * @param $var
     */
    function pr($var)
    {
        $template = PHP_SAPI !== 'cli' ? '<pre>%s</pre>' : "\n%s\n";
        printf($template, print_r($var, true));
    }

}
if (!function_exists('wx_public_token')) {
    /**
     * 打印变量
     * @param $var
     */
    function wx_public_token()
    {
        $appid = \think\Env::get('wx_public.appid');
        $secret = \think\Env::get('wx_public.secret');
        $token = cache('Token');
        if (!$token['access_token'] || $token['expires_in'] <= time()) {
            $rslt = gets("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}");
            if ($rslt) {
                $accessArr = array(
                    'access_token' => $rslt['access_token'],
                    'expires_in' => time() + $rslt['expires_in'] - 200
                );
                cache('Token', $accessArr);
                $token = $rslt;
            }
        }
    }

}


function ihttp_request($url, $post = '', $extra = array(), $timeout = 60)
{
    $urlset = parse_url($url);
    if (empty($urlset['path'])) {
        $urlset['path'] = '/';
    }
    if (!empty($urlset['query'])) {
        $urlset['query'] = "?{$urlset['query']}";
    }
    if (empty($urlset['port'])) {
        $urlset['port'] = $urlset['scheme'] == 'https' ? '443' : '80';
    }
    if (strexists($url, 'https://') && !extension_loaded('openssl')) {
        if (!extension_loaded("openssl")) {
            //die('请开启您PHP环境的openssl');
        }
    }
    if (function_exists('curl_init') && function_exists('curl_exec')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlset['scheme'] . '://' . $urlset['host'] . ($urlset['port'] == '80' ? '' : ':' . $urlset['port']) . $urlset['path'] . $urlset['query']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            if (is_array($post)) {
                $post = http_build_query($post);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        if (defined('CURL_SSLVERSION_TLSv1')) {
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1');
        if (!empty($extra) && is_array($extra)) {
            $headers = array();
            foreach ($extra as $opt => $value) {
                if (strexists($opt, 'CURLOPT_')) {
                    curl_setopt($ch, constant($opt), $value);
                } elseif (is_numeric($opt)) {
                    curl_setopt($ch, $opt, $value);
                } else {
                    $headers[] = "{$opt}: {$value}";
                }
            }
            if (!empty($headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
        }
        $data = curl_exec($ch);
        $status = curl_getinfo($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($errno || empty($data)) {
            //return error(1, $error);
        } else {
            return ihttp_response_parse($data);
        }
    }
    $method = empty($post) ? 'GET' : 'POST';
    $fdata = "{$method} {$urlset['path']}{$urlset['query']} HTTP/1.1\r\n";
    $fdata .= "Host: {$urlset['host']}\r\n";
    if (function_exists('gzdecode')) {
        $fdata .= "Accept-Encoding: gzip, deflate\r\n";
    }
    $fdata .= "Connection: close\r\n";
    if (!empty($extra) && is_array($extra)) {
        foreach ($extra as $opt => $value) {
            if (!strexists($opt, 'CURLOPT_')) {
                $fdata .= "{$opt}: {$value}\r\n";
            }
        }
    }
    $body = '';
    if ($post) {
        if (is_array($post)) {
            $body = http_build_query($post);
        } else {
            $body = urlencode($post);
        }
        $fdata .= 'Content-Length: ' . strlen($body) . "\r\n\r\n{$body}";
    } else {
        $fdata .= "\r\n";
    }
    if ($urlset['scheme'] == 'https') {
        $fp = fsockopen('ssl://' . $urlset['host'], $urlset['port'], $errno, $error);
    } else {
        $fp = fsockopen($urlset['host'], $urlset['port'], $errno, $error);
    }
    stream_set_blocking($fp, true);
    stream_set_timeout($fp, $timeout);
    if (!$fp) {
        //return error(1, $error);
    } else {
        fwrite($fp, $fdata);
        $content = '';
        while (!feof($fp))
            $content .= fgets($fp, 512);
        fclose($fp);
        return ihttp_response_parse($content, true);
    }
}

function ihttp_response_parse($data, $chunked = false)
{
    $rlt = array();
    $pos = strpos($data, "\r\n\r\n");
    $split1[0] = substr($data, 0, $pos);
    $split1[1] = substr($data, $pos + 4, strlen($data));

    $split2 = explode("\r\n", $split1[0], 2);
    preg_match('/^(\S+) (\S+) (\S+)$/', $split2[0], $matches);
    $rlt['code'] = $matches[2];
    $rlt['status'] = $matches[3];
    $rlt['responseline'] = $split2[0];
    $header = explode("\r\n", $split2[1]);
    $isgzip = false;
    $ischunk = false;
    foreach ($header as $v) {
        $row = explode(':', $v);
        $key = trim($row[0]);
        $value = trim($row[1]);
        if (is_array($rlt['headers'][$key])) {
            $rlt['headers'][$key][] = $value;
        } elseif (!empty($rlt['headers'][$key])) {
            $temp = $rlt['headers'][$key];
            unset($rlt['headers'][$key]);
            $rlt['headers'][$key][] = $temp;
            $rlt['headers'][$key][] = $value;
        } else {
            $rlt['headers'][$key] = $value;
        }
        if (!$isgzip && strtolower($key) == 'content-encoding' && strtolower($value) == 'gzip') {
            $isgzip = true;
        }
        if (!$ischunk && strtolower($key) == 'transfer-encoding' && strtolower($value) == 'chunked') {
            $ischunk = true;
        }
    }
    if ($chunked && $ischunk) {
        $rlt['content'] = ihttp_response_parse_unchunk($split1[1]);
    } else {
        $rlt['content'] = $split1[1];
    }
    if ($isgzip && function_exists('gzdecode')) {
        $rlt['content'] = gzdecode($rlt['content']);
    }

    //$rlt['meta'] = $data;
    if ($rlt['code'] == '100') {
        return ihttp_response_parse($rlt['content']);
    }
    return $rlt;
}

function ihttp_response_parse_unchunk($str = null)
{
    if (!is_string($str) or strlen($str) < 1) {
        return false;
    }
    $eol = "\r\n";
    $add = strlen($eol);
    $tmp = $str;
    $str = '';
    do {
        $tmp = ltrim($tmp);
        $pos = strpos($tmp, $eol);
        if ($pos === false) {
            return false;
        }
        $len = hexdec(substr($tmp, 0, $pos));
        if (!is_numeric($len) or $len < 0) {
            return false;
        }
        $str .= substr($tmp, ($pos + $add), $len);
        $tmp = substr($tmp, ($len + $pos + $add));
        $check = trim($tmp);
    } while (!empty($check));
    unset($tmp);
    return $str;
}


function ihttp_get($url)
{
    return ihttp_request($url);
}

function ihttp_post($url, $data)
{
    $headers = array('Content-Type' => 'application/x-www-form-urlencoded');
    return ihttp_request($url, $data, $headers);
}

function gets($url = NULL)
{
    if ($url) {
        $rslt = ihttp_get($url);
        if (strtolower(trim($rslt['status'])) == 'ok') {
            //pr($rslt) ;exit;
            if (is_json($rslt['content'])) { //返回格式是json 直接返回数组
                $return = json_decode($rslt['content'], true);
                if ($return['errcode']) //有错误
                    exit('Error:<br>Api:' . $url . '  <br>errcode:' . $return['errcode'] . '<br>errmsg:' . $return['errmsg']);
                return $return;
            } else {  //先暂时直接返回，以后其它格式再增加
                return $rslt['content'];
            }
        }
        exit('远程请求失败：' . $url);
    }
    exit('未发现远程请求地址');
}

/**
 * 远程post请求
 */
function posts($url = NULL, $data = NULL)
{
    if ($url && $data) {
        $rslt = ihttp_post($url, $data);
        if (strtolower(trim($rslt['status'])) == 'ok') {
            //pr($rslt) ;
            if (is_json($rslt['content'])) { //返回格式是json 直接返回数组
                $return = json_decode($rslt['content'], true);
                if ($return['errcode']) //有错误
                    exit('Error:<br>Api:' . $url . '  <br>errcode:' . $return['errcode'] . '<br>errmsg:' . $return['errmsg']);
                return $return;
            } else {  //先暂时直接返回，以后其它格式再增加
                return $rslt['content'];
            }
        }
        exit('远程请求失败：' . $url);
    }
    exit('post远程请求，参数错误');
}

if (!function_exists('emoji_encode')) {
    /**
     * emoji 表情转义
     * @param $nickname
     * @return string
     */
    function emoji_encode($nickname)
    {
        $strEncode = '';
        $length = mb_strlen($nickname, 'utf-8');
        for ($i = 0; $i < $length; $i++) {
            $_tmpStr = mb_substr($nickname, $i, 1, 'utf-8');
            if (strlen($_tmpStr) >= 4) {
                $strEncode .= '[[EMOJI:' . rawurlencode($_tmpStr) . ']]';
            } else {
                $strEncode .= $_tmpStr;
            }
        }
        return $strEncode;
    }
}
if (!function_exists('emoji_decode')) {
    /**
     * emoji 表情解密
     * @param $nickname
     * @return string
     */
    function emoji_decode($str)
    {
        $strDecode = preg_replace_callback('|\[\[EMOJI:(.*?)\]\]|', function ($matches) {
            return rawurldecode($matches[1]);
        }, $str);
        return $strDecode;
    }
}


if (!function_exists('__')) {

    /**
     * 获取语言变量值
     * @param string $name 语言变量名
     * @param array $vars 动态变量值
     * @param string $lang 语言
     * @return mixed
     */
    function __($name, $vars = [], $lang = '')
    {
        if (is_numeric($name) || !$name) {
            return $name;
        }
        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }
        return \think\Lang::get($name, $vars, $lang);
    }
}

if (!function_exists('format_bytes')) {

    /**
     * 将字节转换为可读文本
     * @param int $size 大小
     * @param string $delimiter 分隔符
     * @return string
     */
    function format_bytes($size, $delimiter = '')
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 6; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . $delimiter . $units[$i];
    }
}

if (!function_exists('datetime')) {

    /**
     * 将时间戳转换为日期时间
     * @param int $time 时间戳
     * @param string $format 日期时间格式
     * @return string
     */
    function datetime($time, $format = 'Y-m-d H:i:s')
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        return date($format, $time);
    }
}

if (!function_exists('human_date')) {

    /**
     * 获取语义化时间
     * @param int $time 时间
     * @param int $local 本地时间
     * @return string
     */
    function human_date($time, $local = null)
    {
        return \fast\Date::human($time, $local);
    }
}

if (!function_exists('cdnurl')) {

    /**
     * 获取上传资源的CDN的地址
     * @param string $url 资源相对地址
     * @param boolean $domain 是否显示域名 或者直接传入域名
     * @return string
     */
    function cdnurl($url, $domain = false)
    {
        $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        $url = preg_match($regex, $url) ? $url : \think\Config::get('upload.cdnurl') . $url;
        if ($domain && !preg_match($regex, $url)) {
            $domain = is_bool($domain) ? request()->domain() : $domain;
            $url = $domain . $url;
        }
        return $url;
    }
}
if (!function_exists('alert')) {
    /**
     * JS提示跳转
     * @param  $tip  弹窗口提示信息(为空没有提示)
     * @param  $type 设置类型 close = 关闭 ，back=返回 ，refresh=提示重载，jump提示并跳转url
     * @param  $url  跳转url
     */
    function alert($tip = "", $type = "", $url = "")
    {
        $js = "<script>";
        if ($tip)
            $js .= "alert('" . $tip . "');";
        switch ($type) {
            case "close" : //关闭页面
                $js .= "window.close();";
                break;
            case "back" : //返回
                $js .= "history.back(-1);";
                break;
            case "refresh" : //刷新
                $js .= "parent.location.reload();";
                break;
            case "top" : //框架退出
                if ($url)
                    $js .= "top.location.href='" . $url . "';";
                break;
            case "jump" : //跳转
                if ($url)
                    $js .= "window.location.href='" . $url . "';";
                break;
            default :
                break;
        }
        $js .= "</script>";
        echo $js;
        if ($type) {
            exit();
        }
    }
}
if (!function_exists('is_weixin')) {

    /**
     * 是否为微信浏览器访问
     * @return bool
     */
    function is_weixin()
    {
        return strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ? true : false;
    }
}
if (!function_exists('is_really_writable')) {

    /**
     * 判断文件或文件夹是否可写
     * @param    string $file 文件或目录
     * @return    bool
     */
    function is_really_writable($file)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return is_writable($file);
        }
        if (is_dir($file)) {
            $file = rtrim($file, '/') . '/' . md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === false) {
                return false;
            }
            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return true;
        } elseif (!is_file($file) or ($fp = @fopen($file, 'ab')) === false) {
            return false;
        }
        fclose($fp);
        return true;
    }
}

if (!function_exists('rmdirs')) {

    /**
     * 删除文件夹
     * @param string $dirname 目录
     * @param bool $withself 是否删除自身
     * @return boolean
     */
    function rmdirs($dirname, $withself = true)
    {
        if (!is_dir($dirname)) {
            return false;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        if ($withself) {
            @rmdir($dirname);
        }
        return true;
    }
}

if (!function_exists('copydirs')) {

    /**
     * 复制文件夹
     * @param string $source 源文件夹
     * @param string $dest 目标文件夹
     */
    function copydirs($source, $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            ) as $item
        ) {
            if ($item->isDir()) {
                $sontDir = $dest . DS . $iterator->getSubPathName();
                if (!is_dir($sontDir)) {
                    mkdir($sontDir, 0755, true);
                }
            } else {
                copy($item, $dest . DS . $iterator->getSubPathName());
            }
        }
    }
}

if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_strtolower(mb_substr($string, 1));
    }
}

if (!function_exists('addtion')) {

    /**
     * 附加关联字段数据
     * @param array $items 数据列表
     * @param mixed $fields 渲染的来源字段
     * @return array
     */
    function addtion($items, $fields)
    {
        if (!$items || !$fields) {
            return $items;
        }
        $fieldsArr = [];
        if (!is_array($fields)) {
            $arr = explode(',', $fields);
            foreach ($arr as $k => $v) {
                $fieldsArr[$v] = ['field' => $v];
            }
        } else {
            foreach ($fields as $k => $v) {
                if (is_array($v)) {
                    $v['field'] = isset($v['field']) ? $v['field'] : $k;
                } else {
                    $v = ['field' => $v];
                }
                $fieldsArr[$v['field']] = $v;
            }
        }
        foreach ($fieldsArr as $k => &$v) {
            $v = is_array($v) ? $v : ['field' => $v];
            $v['display'] = isset($v['display']) ? $v['display'] : str_replace(['_ids', '_id'], ['_names', '_name'], $v['field']);
            $v['primary'] = isset($v['primary']) ? $v['primary'] : '';
            $v['column'] = isset($v['column']) ? $v['column'] : 'name';
            $v['model'] = isset($v['model']) ? $v['model'] : '';
            $v['table'] = isset($v['table']) ? $v['table'] : '';
            $v['name'] = isset($v['name']) ? $v['name'] : str_replace(['_ids', '_id'], '', $v['field']);
        }
        unset($v);
        $ids = [];
        $fields = array_keys($fieldsArr);
        foreach ($items as $k => $v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $ids[$n] = array_merge(isset($ids[$n]) && is_array($ids[$n]) ? $ids[$n] : [], explode(',', $v[$n]));
                }
            }
        }
        $result = [];
        foreach ($fieldsArr as $k => $v) {
            if ($v['model']) {
                $model = new $v['model'];
            } else {
                $model = $v['name'] ? \think\Db::name($v['name']) : \think\Db::table($v['table']);
            }
            $primary = $v['primary'] ? $v['primary'] : $model->getPk();
            $result[$v['field']] = $model->where($primary, 'in', $ids[$v['field']])->column("{$primary},{$v['column']}");
        }

        foreach ($items as $k => &$v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $curr = array_flip(explode(',', $v[$n]));

                    $v[$fieldsArr[$n]['display']] = implode(',', array_intersect_key($result[$n], $curr));
                }
            }
        }
        return $items;
    }
}

if (!function_exists('var_export_short')) {

    /**
     * 返回打印数组结构
     * @param string $var 数组
     * @param string $indent 缩进字符
     * @return string
     */
    function var_export_short($var, $indent = "")
    {
        switch (gettype($var)) {
            case "string":
                return '"' . addcslashes($var, "\\\$\"\r\n\t\v\f") . '"';
            case "array":
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = "$indent    "
                        . ($indexed ? "" : var_export_short($key) . " => ")
                        . var_export_short($value, "$indent    ");
                }
                return "[\n" . implode(",\n", $r) . "\n" . $indent . "]";
            case "boolean":
                return $var ? "TRUE" : "FALSE";
            default:
                return var_export($var, true);
        }
    }
}

if (!function_exists('letter_avatar')) {
    /**
     * 首字母头像
     * @param $text
     * @return string
     */
    function letter_avatar($text)
    {
        $total = unpack('L', hash('adler32', $text, true))[1];
        $hue = $total % 360;
        list($r, $g, $b) = hsv2rgb($hue / 360, 0.3, 0.9);

        $bg = "rgb({$r},{$g},{$b})";
        $color = "#ffffff";
        $first = mb_strtoupper(mb_substr($text, 0, 1));
        $src = base64_encode('<svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="100" width="100"><rect fill="' . $bg . '" x="0" y="0" width="100" height="100"></rect><text x="50" y="50" font-size="50" text-copy="fast" fill="' . $color . '" text-anchor="middle" text-rights="admin" alignment-baseline="central">' . $first . '</text></svg>');
        $value = 'data:image/svg+xml;base64,' . $src;
        return $value;
    }
}

if (!function_exists('hsv2rgb')) {
    function hsv2rgb($h, $s, $v)
    {
        $r = $g = $b = 0;

        $i = floor($h * 6);
        $f = $h * 6 - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);

        switch ($i % 6) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            case 5:
                $r = $v;
                $g = $p;
                $b = $q;
                break;
        }

        return [
            floor($r * 255),
            floor($g * 255),
            floor($b * 255)
        ];
    }

    if (!function_exists('strexists')) {
        function is_json($string)
        {
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        }

    }


    if (!function_exists('strexists')) {
        function strexists($string, $find)
        {
            return !(strpos($string, $find) === false);
        }
    }
    /**
     *
     */
    if (!function_exists('ihttp_request')) {
        function ihttp_request($url, $post = '', $extra = array(), $timeout = 3000)
        {
            $urlset = parse_url($url);
            if (empty($urlset['path'])) {
                $urlset['path'] = '/';
            }
            if (!empty($urlset['query'])) {
                $urlset['query'] = "?{$urlset['query']}";
            }
            if (empty($urlset['port'])) {
                $urlset['port'] = $urlset['scheme'] == 'https' ? '443' : '80';
            }
            if (strexists($url, 'https://') && !extension_loaded('openssl')) {
                if (!extension_loaded("openssl")) {
                    //die('请开启您PHP环境的openssl');
                }
            }
            if (function_exists('curl_init') && function_exists('curl_exec')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $urlset['scheme'] . '://' . $urlset['host'] . ($urlset['port'] == '80' ? '' : ':' . $urlset['port']) . $urlset['path'] . $urlset['query']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 1);
                if ($post) {
                    curl_setopt($ch, CURLOPT_POST, 1);
                    if (is_array($post)) {
                        $post = http_build_query($post);
                    }
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                }
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSLVERSION, 1);
                if (defined('CURL_SSLVERSION_TLSv1')) {
                    curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
                }
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1');
                if (!empty($extra) && is_array($extra)) {
                    $headers = array();
                    foreach ($extra as $opt => $value) {
                        if (strexists($opt, 'CURLOPT_')) {
                            curl_setopt($ch, constant($opt), $value);
                        } elseif (is_numeric($opt)) {
                            curl_setopt($ch, $opt, $value);
                        } else {
                            $headers[] = "{$opt}: {$value}";
                        }
                    }
                    if (!empty($headers)) {
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    }
                }
                $data = curl_exec($ch);
                $status = curl_getinfo($ch);
                $errno = curl_errno($ch);
                $error = curl_error($ch);
                curl_close($ch);
                if ($errno || empty($data)) {
                    //return error(1, $error);
                } else {
                    return ihttp_response_parse($data);
                }
            }
            $method = empty($post) ? 'GET' : 'POST';
            $fdata = "{$method} {$urlset['path']}{$urlset['query']} HTTP/1.1\r\n";
            $fdata .= "Host: {$urlset['host']}\r\n";
            if (function_exists('gzdecode')) {
                $fdata .= "Accept-Encoding: gzip, deflate\r\n";
            }
            $fdata .= "Connection: close\r\n";
            if (!empty($extra) && is_array($extra)) {
                foreach ($extra as $opt => $value) {
                    if (!strexists($opt, 'CURLOPT_')) {
                        $fdata .= "{$opt}: {$value}\r\n";
                    }
                }
            }
            $body = '';
            if ($post) {
                if (is_array($post)) {
                    $body = http_build_query($post);
                } else {
                    $body = urlencode($post);
                }
                $fdata .= 'Content-Length: ' . strlen($body) . "\r\n\r\n{$body}";
            } else {
                $fdata .= "\r\n";
            }
            if ($urlset['scheme'] == 'https') {
                $fp = fsockopen('ssl://' . $urlset['host'], $urlset['port'], $errno, $error);
            } else {
                $fp = fsockopen($urlset['host'], $urlset['port'], $errno, $error);
            }
            stream_set_blocking($fp, true);
            stream_set_timeout($fp, $timeout);
            if (!$fp) {
                //return error(1, $error);
            } else {
                fwrite($fp, $fdata);
                $content = '';
                while (!feof($fp))
                    $content .= fgets($fp, 512);
                fclose($fp);
                return ihttp_response_parse($content, true);
            }
        }
    }
    if (!function_exists('ihttp_response_parse')) {
        function ihttp_response_parse($data, $chunked = false)
        {
            $rlt = array();
            $pos = strpos($data, "\r\n\r\n");
            $split1[0] = substr($data, 0, $pos);
            $split1[1] = substr($data, $pos + 4, strlen($data));

            $split2 = explode("\r\n", $split1[0], 2);
            preg_match('/^(\S+) (\S+) (\S+)$/', $split2[0], $matches);
            $rlt['code'] = $matches[2];
            $rlt['status'] = $matches[3];
            $rlt['responseline'] = $split2[0];
            $header = explode("\r\n", $split2[1]);
            $isgzip = false;
            $ischunk = false;
            foreach ($header as $v) {
                $row = explode(':', $v);
                $key = trim($row[0]);
                $value = trim($row[1]);
                if (is_array($rlt['headers'][$key])) {
                    $rlt['headers'][$key][] = $value;
                } elseif (!empty($rlt['headers'][$key])) {
                    $temp = $rlt['headers'][$key];
                    unset($rlt['headers'][$key]);
                    $rlt['headers'][$key][] = $temp;
                    $rlt['headers'][$key][] = $value;
                } else {
                    $rlt['headers'][$key] = $value;
                }
                if (!$isgzip && strtolower($key) == 'content-encoding' && strtolower($value) == 'gzip') {
                    $isgzip = true;
                }
                if (!$ischunk && strtolower($key) == 'transfer-encoding' && strtolower($value) == 'chunked') {
                    $ischunk = true;
                }
            }
            if ($chunked && $ischunk) {
                $rlt['content'] = ihttp_response_parse_unchunk($split1[1]);
            } else {
                $rlt['content'] = $split1[1];
            }
            if ($isgzip && function_exists('gzdecode')) {
                $rlt['content'] = gzdecode($rlt['content']);
            }

            //$rlt['meta'] = $data;
            if ($rlt['code'] == '100') {
                return ihttp_response_parse($rlt['content']);
            }
            return $rlt;
        }
    }
    if (!function_exists('ihttp_response_parse_unchunk')) {
        function ihttp_response_parse_unchunk($str = null)
        {
            if (!is_string($str) or strlen($str) < 1) {
                return false;
            }
            $eol = "\r\n";
            $add = strlen($eol);
            $tmp = $str;
            $str = '';
            do {
                $tmp = ltrim($tmp);
                $pos = strpos($tmp, $eol);
                if ($pos === false) {
                    return false;
                }
                $len = hexdec(substr($tmp, 0, $pos));
                if (!is_numeric($len) or $len < 0) {
                    return false;
                }
                $str .= substr($tmp, ($pos + $add), $len);
                $tmp = substr($tmp, ($len + $pos + $add));
                $check = trim($tmp);
            } while (!empty($check));
            unset($tmp);
            return $str;
        }
    }
    if (!function_exists('ihttp_get')) {
        function ihttp_get($url)
        {
            return ihttp_request($url);
        }
    }

    if (!function_exists('ihttp_post')) {
        function ihttp_post($url, $data)
        {
            $headers = array('Content-Type' => 'application/x-www-form-urlencoded');
            return ihttp_request($url, $data, $headers);
        }
    }

    /**
     * 远程GET请求
     */
    if (!function_exists('gets')) {
        function gets($url = null)
        {
            if ($url) {
                $rslt = ihttp_get($url);
                if (strtolower(trim($rslt['status'])) == 'ok') {
                    //pr($rslt) ;exit;
                    if (is_json($rslt['content'])) { //返回格式是json 直接返回数组
                        $return = json_decode($rslt['content'], true);
                        if ($return['errcode']) //有错误
                            exit('Error:<br>Api:' . $url . '  <br>errcode:' . $return['errcode'] . '<br>errmsg:' . $return['errmsg']);
                        return $return;
                    } else {  //先暂时直接返回，以后其它格式再增加
                        return $rslt['content'];
                    }
                }
                exit('远程请求失败：' . $url);
            }
            exit('未发现远程请求地址');
        }
    }
    /**
     * 远程post请求
     */
    if (!function_exists('posts')) {

        function posts($url = null, $data = null)
        {
            if ($url && $data) {
                $rslt = ihttp_post($url, $data);
                if (strtolower(trim($rslt['status'])) == 'ok') {
                    //pr($rslt) ;
                    if (is_json($rslt['content'])) { //返回格式是json 直接返回数组
                        $return = json_decode($rslt['content'], true);
                        if ($return['errcode']) //有错误
                            exit('Error:<br>Api:' . $url . '  <br>errcode:' . $return['errcode'] . '<br>errmsg:' . $return['errmsg']);
                        return $return;
                    } else {  //先暂时直接返回，以后其它格式再增加
                        return $rslt['content'];
                    }
                }
                exit('远程请求失败：' . $url);
            }
            exit('post远程请求，参数错误');
        }
    }
    /**
     * 获取access_token
     */
    if (!function_exists('getWxAccessToken')) {
        /**
         * 该公共方法获取和全局缓存js-sdk需要使用的access_token
         * @param $appid
         * @param $secret
         * @return mixed
         */
        function getWxAccessToken()
        {

            $config = get_addon_config('cms');

            $appid = $config['wxappid'];
            $secret = $config['wxappsecret'];
            //我们将access_token全局缓存在文件中,每次获取的时候,先判断是否过期,如果过期重新获取再全局缓存
            //我们缓存的在文件中的数据，包括access_token和该access_token的过期时间戳.
            //获取缓存的access_token
            $access_token_data = json_decode(Cache::get('access_token'), true);

            //判断缓存的access_token是否存在和过期，如果不存在和过期则重新获取.
            if ($access_token_data !== null && $access_token_data['access_token'] && $access_token_data['expires_in'] > time()) {

                return $access_token_data['access_token'];
            } else {
                //重新获取access_token,并全局缓存
                $result = Http::sendRequest("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}", 'GET');
                if ($result['ret']) {
                    $data = (array)json_decode($result['msg'], true);
                    //获取access_token
                    if ($data != null && $data['access_token']) {
                        //设置access_token的过期时间,有效期是7200s
                        $data['expires_in'] = $data['expires_in'] + time();
                        //将access_token全局缓存，快速缓存到文件中.
                        Cache::set('access_token', json_encode($data));

                        //返回access_token
                        return $data['access_token'];
                    }
                } else {
                    exit('微信获取access_token失败');
                }
            }
        }
    }

    if (!function_exists('illegal')) {
        /**
         * 查询违章
         * @param array $params
         * @return array
         */
        function illegal(array $params)
        {
            $order_details = new \app\admin\model\OrderDetails();
            $query_record = [];
            $error_num = $success_num = 0;
            foreach ($params as $k => $v) {
                $order_details_id = \app\admin\model\OrderDetails::getByOrder_id($v['order_id'])->id;

                $details = \app\admin\model\OrderDetails::get($order_details_id);

                $field = array();

                $data = Http::sendRequest('http://v.juhe.cn/wz/query', [
                    'hphm' => urlencode($v['hphms']),
                    'engineno' => $v['engineno'],
                    'classno' => $v['classno'],
                    'hpzl' => 02,
                    'key' => 'd91da2fdf9834922d28a10565afef31a'
                ],'GET');
//
                $data = json_decode($data['msg'],true);

//                $data = gets("http://v.juhe.cn/wz/query?hphm=" . urlencode($v['hphms']) . "&engineno=" . $v['engineno'] . "&classno=" . $v['classno'] . "&hpzl=02&key=d91da2fdf9834922d28a10565afef31a");

                if ($data['error_code'] == 0) {

                    $total_fraction = 0;     //总扣分
                    $total_money = 0;        //总罚款
                    $flag = -1;

                    if ($data['result']['lists']) {
                        $record = [];

                        foreach ($data['result']['lists'] as $key => $value) {
                            if ($value['handled'] == 0) {
                                $flag = -2;
                            } else if ($value['handled'] == 1) {
                                continue;
                            }
                            if ($value['fen']) {
                                $value['fen'] = floatval($value['fen']);

                                $total_fraction += $value['fen'];
                            }

                            if ($value['money']) {
                                $value['money'] = floatval($value['money']);

                                $total_money += $value['money'];
                            }

                            $record[] = $value;

                        }

                        $details->violation_details = $record ? json_encode($record) : null;
                    }

                    $details->is_it_illegal = $flag == -1 ? 'no_violation' : 'violation_of_regulations';
                    $details->total_deduction = $total_fraction;
                    $details->total_fine = $total_money;
                    $details->update_violation_time = time();

                    $change_num = \app\admin\model\OrderDetails::whereTime('update_violation_time', 'w')->where('id', $order_details_id)->value('id');

                    if (!$change_num) {
                        $details->number_of_queries = 0;
                    }

                    $details->save();

                    $query_record[] = ['username' => $v['username'], 'license_plate_number' => $v['hphms'], 'status' => 'success', 'msg' => '-', 'is_it_illegal' => $details->is_it_illegal == 'violation_of_regulations' ? '有' : '无', 'total_deduction' => $total_fraction, 'total_fine' => $total_money];
                    $success_num++;

                } else {
                    $order_details->allowField(true)->save(['is_it_illegal' => 'query_failed', 'reson_query_fail' => $data['reason'],'update_violation_time'=>time()], ['id' => $order_details_id]);
                    $query_record[] = ['username' => $v['username'], 'license_plate_number' => $v['hphms'], 'status' => 'error', 'msg' => $data['reason'], 'is_it_illegal' => '-', 'total_deduction' => '-', 'total_fine' => '-'];
                    $error_num++;
                }

            }

            Cache::rm('statistics_total_violation');
            Cache::set('statistics_total_violation', \app\admin\model\OrderDetails::where('is_it_illegal', 'violation_of_regulations')->count('id'), 43200);

            return [
                'error_num' => $error_num,
                'success_num' => $success_num,
                'query_record' => $query_record,
                'lists' => $record
            ];
        }
    }


    /**
     * 某个时间戳在当前时间的多久前
     */
    if (!function_exists('format_date')) {
        function format_date($time)
        {
            $nowtime = time();
            $difference = $nowtime - $time;
            switch ($difference) {
                case $difference <= '60' :
                    $msg = '刚刚';
                    break;
                case $difference > '60' && $difference <= '3600' :
                    $msg = floor($difference / 60) . '分钟前';
                    break;
                case $difference > '3600' && $difference <= '86400' :
                    $msg = floor($difference / 3600) . '小时前';
                    break;
                case $difference > '86400' && $difference <= '2592000' :
                    $msg = floor($difference / 86400) . '天前';
                    break;
                case $difference > '2592000' && $difference <= '31536000':
                    $msg = floor($difference / 2592000) . '个月前';
                    break;
                case $difference > '31536000':
                    $msg = floor($difference / 31104000) . '年前';
                    break;
            }
            return $msg;
        }
    }


}

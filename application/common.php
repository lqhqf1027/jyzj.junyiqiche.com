<?php

// 公共助手函数
error_reporting(E_PARSE | E_ERROR | E_WARNING);

use think\Request;
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
        for ($i = 0; $size >= 1024 && $i < 6; $i++)
            $size /= 1024;
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
        $url = preg_match("/^https?:\/\/(.*)/i", $url) ? $url : \think\Config::get('upload.cdnurl') . $url;
        if ($domain && !preg_match("/^(http:\/\/|https:\/\/)/i", $url)) {
            if (is_bool($domain)) {
                $public = \think\Config::get('view_replace_str.__PUBLIC__');
                $url = rtrim($public, '/') . $url;
                if (!preg_match("/^(http:\/\/|https:\/\/)/i", $url)) {
                    $url = request()->domain() . $url;
                }
            } else {
                $url = $domain . $url;
            }
        }
        return $url;
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
            if (($fp = @fopen($file, 'ab')) === FALSE) {
                return FALSE;
            }
            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return TRUE;
        } elseif (!is_file($file) OR ($fp = @fopen($file, 'ab')) === FALSE) {
            return FALSE;
        }
        fclose($fp);
        return TRUE;
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
        if (!is_dir($dirname))
            return false;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
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
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item
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
        if (!$items || !$fields)
            return $items;
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
/**
 * 对象转数组
 *
 */
if (!function_exists('object_to_array')) {
    function object_to_array($obj)
    {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)object_to_array($v);
            }
        }

        return $obj;
    }
}
/**************************************************************
 *
 *  将数组转换为JSON字符串（兼容中文）
 * @param  array $array 要转换的数组
 * @return string      转换得到的json字符串
 * @access public
 *
 *************************************************************/
if (!function_exists('ARRAY_TO_JSON')) {
    function ARRAY_TO_JSON($array)
    {
        arrayRecursive($array, 'urlencode', true);

        $json = json_encode($array);

        return urldecode($json);

    }
}

/**************************************************************
 *
 *  使用特定function对数组中所有元素做处理
 * @param  string &$array 要处理的字符串
 * @param  string $function 要执行的函数
 * @return boolean $apply_to_keys_also     是否也应用到key上
 * @access public
 *
 *************************************************************/
if (!function_exists('arrayRecursive')) {
    function arrayRecursive(&$array, $function, $apply_to_keys_also = false)


    {

        static $recursive_counter = 0;

        if (++$recursive_counter > 1000) {

            die('possible deep recursion attack');

        }

        foreach ($array as $key => $value) {

            if (is_array($value)) {

                arrayRecursive($array[$key], $function, $apply_to_keys_also);

            } else {

                $array[$key] = $function($value);

            }


            if ($apply_to_keys_also && is_string($key)) {

                $new_key = $function($key);

                if ($new_key != $key) {

                    $array[$new_key] = $array[$key];

                    unset($array[$key]);

                }

            }

        }

        $recursive_counter--;

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


if (!function_exists('pr')) {
    function pr($var)
    {
        if (config('app_debug')) {
            $template = PHP_SAPI !== 'cli' ? '<pre>%s</pre>' : "\n%s\n";
            printf($template, print_r($var, true));
        }
    }

}

if (!function_exists('ismobile')) {
    // 查看是否为手机端的方法  
    //判断是手机登录还是电脑登录  
    function ismobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备  
        if (isset($_SERVER['HTTP_X_WAP_PROFILE']))
            return true;

        //此条摘自TPM智能切换模板引擎，适合TPM开发  
        if (isset($_SERVER['HTTP_CLIENT']) && 'PhoneClient' == $_SERVER['HTTP_CLIENT'])
            return true;
        //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息  
        if (isset($_SERVER['HTTP_VIA']))
            //找不到为flase,否则为true  
            return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
        //判断手机发送的客户端标志,兼容性有待提高  
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array(
                'nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile'
            );
            //从HTTP_USER_AGENT中查找手机浏览器的关键字  
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        //协议法，因为有可能不准确，放到最后判断  
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备  
            // 如果支持wml和html但是wml在html之前则是移动设备  
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }
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
 * 推广到内勤分配客户
 */
if (!function_exists('dstribution_inform')) {


    function dstribution_inform()
    {
        $arr = [
            'subject' => "新客户通知：",
            'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 你有推广分配过来的新客户，请及时登录后台进行处理 . '</div>'
        ];

        return $arr;
    }
}
/**
 * 内勤到销售分配客户
 */
if (!function_exists('sales_inform')) {


    function sales_inform()
    {
        $arr = [
            'subject' => "新客户通知：",
            'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 你有内勤分配过来的新客户，请及时登录后台进行处理 . '</div>'
        ];

        return $arr;
    }
}
/**
 * /**
 * 以租代购（新车）发送给内勤
 */
if (!function_exists('newinternal_inform')) {


    function newinternal_inform($models_name = NULL, $admin_name = NULL, $username = NULL)
    {
        if ($models_name && $admin_name && $username) {
            $arr = [
                'subject' => "待录入实收定金、装饰通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 销售员： . $admin_name . 发起了客户： . $username . 对车型： . $models_name . 的预定，请及时登录后台进行金额、装饰录入 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（新车）内勤发送给车管
 */
if (!function_exists('newcar_inform')) {
    function newcar_inform($models_name = NULL, $admin_name = NULLL, $username = NULL)
    {
        if ($models_name && $admin_name && $username) {
            $arr = [
                'subject' => "新增订车通知",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 销售员： . $admin_name . 发起了客户： . $username . 对车型： . $models_name . 的预定，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（新车）车管发送给匹配金融
 */
if (!function_exists('newfinance_inform')) {


    function newfinance_inform($models_name = NULL, $admin_name = NULLL, $username = NULL)
    {
        if ($models_name && $admin_name && $username) {
            $arr = [
                'subject' => "金融待匹配通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 销售员： . $admin_name . 发起了客户： . $username . 对车型： . $models_name . 的购买，请及时登录后台进行金融匹配 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（新车）匹配金融发送给风控审核
 */
if (!function_exists('newcontrol_inform')) {


    function newcontrol_inform($models_name = NULL, $admin_name = NULLL, $username = NULL)
    {
        if ($models_name && $admin_name && $username) {
            $arr = [
                'subject' => "征信待审核通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 销售员： . $admin_name . 发起了客户： . $username . 对车型： . $models_name . 的购买，请及时登录后台进行风控审核 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（新车）风控审核发送给销售，审核通过
 */
if (!function_exists('newpass_inform')) {


    function newpass_inform($models_name = NULL, $username = NULL)
    {
        if ($models_name && $username) {
            $arr = [
                'subject' => "征信审核结果通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;color: green">' . 客户： . $username . 已经通过风控审核，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（新车）风控审核发送给销售，签订金融合同 --- 走一汽金平台，先签合同再订车
 */
if (!function_exists('newpass_finance')) {


    function newpass_finance($models_name = NULL, $username = NULL)
    {
        if ($models_name && $username) {
            $arr = [
                'subject' => "待签订金融合同通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 你的客户： . $username . 已经通过风控审核，请通知客户进行签订金融合同 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（新车）风控审核发送给车管，进行录入库存  ---   一汽租赁
 */
if (!function_exists('newcontrol_tube')) {


    function newcontrol_tube($models_name = NULL, $username = NULL)
    {
        if ($models_name && $username) {
            $arr = [
                'subject' => "新车金融合同已签订，可以进行录入库存通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 客户： . $username . 对车型： . $models_name . 的购买，已经签订金融合同，可以进行录入库存，请及时登陆后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（新车）风控审核发送给车管，进行录入库存----其他金融
 */
if (!function_exists('newcontrol_tube_finance')) {


    function newcontrol_tube_finance($models_name = NULL, $username = NULL)
    {
        if ($models_name && $username) {
            $arr = [
                'subject' => "新车审核已通过，可以进行录入库存通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 客户： . $username . 对车型： . $models_name . 的购买，审核已通过，可以进行录入库存，请及时登陆后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（新车）风控审核选择库存，发送给车管
 */
if (!function_exists('newchoose_stock')) {


    function newchoose_stock($models_name = NULL, $username = NULL)
    {
        if ($models_name && $username) {
            $arr = [
                'subject' => "新车已经选择完库存通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 客户： . $username . 对车型： . $models_name . 的购买，已经匹配完库存车，请及时登陆后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（新车）车管发送销售，补全提车资料
 */
if (!function_exists('newtake_car')) {


    function newtake_car($models_name = NULL, $username = NULL)
    {
        if ($models_name && $username) {
            $arr = [
                'subject' => "客户提车资料待补全通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 客户： . $username . 对车型： . $models_name . 的购买，已经可以进行提车，请补全提车资料，请及时登陆后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（新车）销售发送车管，资料补全，可以提车
 */
if (!function_exists('newsend_car')) {


    function newsend_car($models_name = NULL, $username = NULL)
    {
        if ($models_name && $username) {
            $arr = [
                'subject' => "客户提车资料补全，可以进行提车通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 客户： . $username . 对车型： . $models_name . 的购买，资料已经补全，可以进行提车，请及时登陆后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（新车）车管发送销售，已提车
 */
if (!function_exists('sales_takecar')) {


    function sales_takecar($models_name = NULL, $username = NULL)
    {
        if ($models_name && $username) {
            $arr = [
                'subject' => "客户提车成功通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 客户： . $username . 对车型： . $models_name . 的购买，已经提车，请悉知 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（新车）车管发送金融按揭专员，已提车
 */
if (!function_exists('financial_takecar')) {


    function financial_takecar($models_name = NULL, $username = NULL)
    {
        if ($models_name && $username) {
            $arr = [
                'subject' => "客户提车成功通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 客户： . $username . 对车型： . $models_name . 的购买，已经提车，请悉知 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（新车）风控审核发送给销售，审核需要保证金
 */
if (!function_exists('newdata_inform')) {


    function newdata_inform($models_name = NULL, $username = NULL)
    {
        if ($models_name && $username) {
            $arr = [
                'subject' => "新车风控审核通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 你发起的客户： . $username . 对车型： . $models_name . 的购买，风控需要你提交保证金收据，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（新车）销售发送给风控审核，保证金上传
 */
if (!function_exists('newdata_cash')) {


    function newdata_cash($models_name = NULL, $username = NULL)
    {
        if ($models_name && $username) {
            $arr = [
                'subject' => "新车销售已上传保证金收据通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 客户： . $username . 对车型： . $models_name . 的购买，保证金收据已经上传，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（新车）风控审核发送给销售，审核不通过
 */
if (!function_exists('newnopass_inform')) {


    function newnopass_inform($models_name = NULL, $username = NULL)
    {
        if ($models_name && $username) {
            $arr = [
                'subject' => "新车风控审核不通过通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 你发起的客户： . $username . 对车型： . $models_name . 的购买，没有通过风控审核，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（新车）风控审核发送给销售，审核不通过，待补录资料
 */
if (!function_exists('new_information')) {


    function new_information($models_name = NULL, $username = NULL, $text = NULL)
    {
        if ($models_name && $username && $text) {
            $arr = [
                'subject' => "新车风控审核不通过,待补录资料通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 你发起的客户： . $username . 对车型： . $models_name . 的购买，没有通过风控审核，需补录. $text .资料，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（新车）销售发送给风控，资料补录完成
 */
if (!function_exists('new_collection_data')) {


    function new_collection_data($models_name = NULL, $username = NULL, $text = NULL)
    {
        if ($models_name && $username && $text) {
            $arr = [
                'subject' => "新车资料补录完成通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 客户： . $username . 对车型： . $models_name . 的购买，补录：. $text . 资料已完成，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 租车 发送给车管进行预定
 */
if (!function_exists('rentalcar_inform')) {


    function rentalcar_inform($models_name = NULL, $admin_name = NULL, $username = NULL)
    {
        if ($models_name && $admin_name && $username) {
            $arr = [
                'subject' => "租车预定通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 销售员： . $admin_name . 发起了客户： . $username . 对车型： . $models_name . 的租车预定，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 租车 车管同意预定，发送给销售员，可补全客户信息
 */
if (!function_exists('rentalsales_inform')) {


    function rentalsales_inform($models_name = NULL, $username = NULL)
    {
        if ($models_name && $username) {
            $arr = [
                'subject' => "车管租车预定成功通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 你发起的客户： . $username . 对车型： . $models_name . 的租车预定，车管已经同意，请及时登录后台进行客户信息的补全 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 租车 销售员补全客户信息后，发送给风控审核
 */
if (!function_exists('rentalcontrol_inform')) {


    function rentalcontrol_inform($models_name = NULL, $admin_name = NULL, $username = NULL)
    {
        if ($models_name && $admin_name && $username) {
            $arr = [
                'subject' => "租车风控审核通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 销售员： . $admin_name . 发起了客户： . $username . 对车型： . $models_name . 的租车请求，车管已经同意，客户信息已补全，请及时登录后台进行风控审核 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 租车  风控审核发送给销售，审核通过
 */
if (!function_exists('rentalpass_inform')) {


    function rentalpass_inform($models_name = NULL, $username = NULL)
    {
        if ($models_name && $username) {
            $arr = [
                'subject' => "租车风控审核通过通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 你发起的客户： . $username . 对车型： . $models_name . 的租车请求，已经通过风控审核，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 租车  风控审核发送给销售，审核不通过
 */
if (!function_exists('rentalnopass_inform')) {


    function rentalnopass_inform($models_name = NULL, $username = NULL)
    {
        if ($models_name && $username) {
            $arr = [
                'subject' => "租车风控审核不通过通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 你发起的客户： . $username . 对车型： . $models_name . 的租车请求，没有通过风控审核，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 租车 风控审核发送给销售，审核不通过，待补录资料
 */
if (!function_exists('rental_information')) {


    function rental_information($models_name = NULL, $username = NULL, $text = NULL)
    {
        if ($models_name && $username && $text) {
            $arr = [
                'subject' => "租车风控审核不通过,待补录资料通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 你发起的客户： . $username . 对车型： . $models_name . 的购买，没有通过风控审核，需补录. $text .资料，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 租车 销售发送给风控，资料补录完成
 */
if (!function_exists('rental_collection_data')) {


    function rental_collection_data($models_name = NULL, $username = NULL, $text = NULL)
    {
        if ($models_name && $username && $text) {
            $arr = [
                'subject' => "租车资料补录完成通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 客户： . $username . 对车型： . $models_name . 的购买，补录：. $text . 资料已完成，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（二手车）发送给内勤
 */
if (!function_exists('secondinternal_inform')) {


    function secondinternal_inform($models_name = NULL, $admin_name = NULL, $username = NULL)
    {
        if ($models_name && $admin_name && $username) {
            $arr = [
                'subject' => "二手车待录入金额通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 销售员： . $admin_name . 发起了客户： . $username . 对车型： . $models_name . 的购买，请及时登录后台进行金额录入 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（二手车）内勤发送给车管
 */
if (!function_exists('secondcar_inform')) {


    function secondcar_inform($models_name = NULL, $admin_name = NULL, $username = NULL)
    {
        if ($models_name && $admin_name && $username) {
            $arr = [
                'subject' => "二手车待车管确认通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 销售员： . $admin_name . 发起了客户： . $username . 对车型： . $models_name . 的购买，内勤已录入金额，待车管确认，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（二手车）车管发送给金融匹配
 */
if (!function_exists('secondfinance_inform')) {


    function secondfinance_inform($models_name = NULL, $admin_name = NULL, $username = NULL)
    {
        if ($models_name && $admin_name && $username) {
            $arr = [
                'subject' => "二手车待匹配金融通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 销售员： . $admin_name . 发起了客户： . $username . 对车型： . $models_name . 的购买，内勤已录入金额，车管也已确认，请及时登录后台进行金融匹配 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（二手车）金融发送给风控
 */
if (!function_exists('secondcontrol_inform')) {


    function secondcontrol_inform($models_name = NULL, $admin_name = NULL, $username = NULL)
    {
        if ($models_name && $admin_name && $username) {
            $arr = [
                'subject' => "二手车待风控审核通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 销售员： . $admin_name . 发起了客户： . $username . 对车型： . $models_name . 的购买，内勤已录入金额，车管也已确认，金融已匹配，请及时登录后台进行风控审核 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（二手车）风控审核匹配车辆，发送给销售，补全客户提车资料，通知客户提车
 */
if (!function_exists('secondpass_inform')) {


    function secondpass_inform($models_name = NULL, $username = NULL)
    {
        if ($models_name && $username) {
            $arr = [
                'subject' => "风控匹配车辆成功通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 客户： . $username . 对车型： . $models_name . 的购买，已经通过风控审核，匹配完车辆，请及时登录后台进行处理，通知客户进行提车 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（二手车）风控审核匹配车辆，发送给车管，进行备车
 */
if (!function_exists('secondpass_tubeinform')) {


    function secondpass_tubeinform($models_name = NULL, $username = NULL)
    {
        if ($models_name && $username) {
            $arr = [
                'subject' => "风控匹配车辆成功通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 客户： . $username . 对车型： . $models_name . 的购买，已经通过风控审核，匹配完车辆，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（二手车）风控审核发送给销售，审核需要保证金
 */
if (!function_exists('seconddata_inform')) {


    function seconddata_inform($models_name = NULL, $username = NULL)
    {
        if ($models_name && $username) {
            $arr = [
                'subject' => "二手车风控审核通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 你发起的客户： . $username . 对车型： . $models_name . 的购买，风控需要你提交保证金收据，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（二手车）风控审核发送给销售，审核不通过
 */
if (!function_exists('secondnopass_inform')) {


    function secondnopass_inform($models_name = NULL, $username = NULL)
    {
        if ($models_name && $username) {
            $arr = [
                'subject' => "二手车风控审核不通过通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 你发起的客户： . $username . 对车型： . $models_name . 的购买，没有通过风控审核，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（二手车）风控审核发送给销售，审核不通过，待补录资料
 */
if (!function_exists('second_information')) {


    function second_information($models_name = NULL, $username = NULL, $text = NULL)
    {
        if ($models_name && $username && $text) {
            $arr = [
                'subject' => "二手车风控审核不通过,待补录资料通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 你发起的客户： . $username . 对车型： . $models_name . 的购买，没有通过风控审核，需补录：. $text . 资料，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 以租代购（二手车）销售发送给风控，资料补录完成
 */
if (!function_exists('second_collection_data')) {


    function second_collection_data($models_name = NULL, $username = NULL, $text = NULL)
    {
        if ($models_name && $username && $text) {
            $arr = [
                'subject' => "二手车资料补录完成通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 客户： . $username . 对车型： . $models_name . 的购买，补录：. $text . 资料已完成，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}

/**
 * 全款车 销售发送给内勤
 */
if (!function_exists('fullinternal_inform')) {

    
    function fullinternal_inform($models_name = NULL, $admin_name = NULL, $username = NULL)
    {
        if ($models_name && $admin_name && $username) {
            $arr = [
                'subject' => "全款车待录入金额通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 销售员： . $admin_name . 发起了客户： . $username . 对车型： . $models_name . 的购买，请及时登录后台进行金额录入 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 全款车 内勤发送给车管
 */
if (!function_exists('fullcar_inform')) {


    function fullcar_inform($models_name = NULL, $admin_name = NULL, $username = NULL)
    {
        if ($models_name && $admin_name && $username) {
            $arr = [
                'subject' => "全款车待车管确认通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 销售员： . $admin_name . 发起了客户： . $username . 对车型： . $models_name . 的购买，内勤已录入金额，待车管确认，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 全款车 车管发送给销售， 可以提车
 */
if (!function_exists('fullsales_inform')) {


    function fullsales_inform($models_name = NULL, $admin_name = NULL, $username = NULL)
    {
        if ($models_name && $admin_name && $username) {
            $arr = [
                'subject' => "全款车待提车通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 销售员： . $admin_name . 发起了客户： . $username . 对车型： . $models_name . 的购买，内勤已录入金额，车管也已确认，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 全款二手车 销售发送给内勤
 */
if (!function_exists('second_full_backoffice')) {

    
    function second_full_backoffice($models_name = NULL, $admin_name = NULL, $username = NULL)
    {
        if ($models_name && $admin_name && $username) {
            $arr = [
                'subject' => "全款二手车待录入金额通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 销售员： . $admin_name . 发起了客户： . $username . 对车型： . $models_name . 的购买，请及时登录后台进行金额录入 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 全款二手车 内勤发送给车管
 */
if (!function_exists('secondfullcar_amount')) {


    function secondfullcar_amount($models_name = NULL, $admin_name = NULL, $username = NULL)
    {
        if ($models_name && $admin_name && $username) {
            $arr = [
                'subject' => "全款二手车待车管确认通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 销售员： . $admin_name . 发起了客户： . $username . 对车型： . $models_name . 的购买，内勤已录入金额，待车管确认，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 全款车 车管发送给销售， 可以提车
 */
if (!function_exists('secondfullsales_inform')) {


    function secondfullsales_inform($models_name = NULL, $admin_name = NULL, $username = NULL)
    {
        if ($models_name && $admin_name && $username) {
            $arr = [
                'subject' => "全款二手车待提车通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 销售员： . $admin_name . 发起了客户： . $username . 对车型： . $models_name . 的购买，内勤已录入金额，车管也已确认，请及时登录后台进行处理 . '</div>'
            ];

            return $arr;
        }
        exit('参数错误');

    }
}
/**
 * 新车月供扣款不成功通知 ，财务出纳发送给风控
 */
if (!function_exists('send_monthly_to_risk')) {
    function send_monthly_to_risk()
    {
        $arr = [
            'subject' => "月供代扣失败通知：",
            'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 你有客户月供扣款不成功数据，请登陆系统查看 . '</div>'
        ];
        return $arr;
    }
}


if (!function_exists('send_newmodels_to_sales')) {
    function send_newmodels_to_sales($model = null, $payment = null, $monthly = null)
    {
        if ($model && $payment && $monthly) {
            $arr = [
                'subject' => "定制方案审核结果通知：",
                'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 您需要的车型： . $model . 首付： . $payment . 元， . 月供： . $monthly . 元，该方案已添加成功，请注意查看 . '</div>'
            ];
            return $arr;
        } else {
            exit('参数错误');
        }

    }
}

if (!function_exists('send_peccancy')) {
    function send_peccancy()
    {
        $arr = [
            'subject' => "新违章客户进入通知：",
            'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 您有新的违章客户进入，请注意查看 . '</div>'
        ];
        return $arr;


    }
}
if (!function_exists('lift_car')) {
    function lift_car($customer=null)
    {
        $arr = [
            'subject' => "客户提车成功通知：",
            'message' => '<div style="min-height:550px; padding: 100px 55px 200px;">' . 您的客户：.$customer.已成功提车 . '</div>'
        ];
        return $arr;
    }
}

/**数组去重
 * @param $arr
 * @param $key
 * @return mixed
 */
if (!function_exists('assoc_unique')) {


    function assoc_unique($arr, $key)
    {
        $tmp_arr = array();
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $tmp_arr))//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
            {
                unset($arr[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }
        }
        sort($arr); //sort函数对数组进行排序
        return $arr;
    }
}
if (!function_exists('checkPhoneNumberValidate')) {
    function checkPhoneNumberValidate($phone_number){
        //@2017-11-25 14:25:45 https://zhidao.baidu.com/question/1822455991691849548.html
        //中国联通号码：130、131、132、145（无线上网卡）、155、156、185（iPhone5上市后开放）、186、176（4G号段）、175（2015年9月10日正式启用，暂只对北京、上海和广东投放办理）,166,146
        //中国移动号码：134、135、136、137、138、139、147（无线上网卡）、148、150、151、152、157、158、159、178、182、183、184、187、188、198
        //中国电信号码：133、153、180、181、189、177、173、149、199
        $g = "/^1[34578]\d{9}$/";
        $g2 = "/^19[89]\d{8}$/";
        $g3 = "/^166\d{8}$/";
        if(preg_match($g, $phone_number)){
            return true;
        }else  if(preg_match($g2, $phone_number)){
            return true;
        }else if(preg_match($g3, $phone_number)){
            return true;
        }

        return false;

    }
}

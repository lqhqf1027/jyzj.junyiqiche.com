<?php

return [
    [
        //配置唯一标识
        'name'    => 'driver',
        //显示的标题
        'title'   => '驱动',
        //类型
        'type'    => 'select',
        //数据字典
        'content' => [
            'LocalFileSystem' => '本地文件系统'
        ],
        //值
        'value'   => 'LocalFileSystem',
        //验证规则
        'rule'    => 'required',
        //错误消息
        'msg'     => '',
        //提示消息
        'tip'     => '',
        //成功消息
        'ok'      => '',
        //扩展信息
        'extend'  => ''
    ],
    [
        'name'    => 'path',
        'title'   => '根目录',
        'type'    => 'string',
        'content' => [
        ],
        'value'   => ROOT_PATH . 'public',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '文件浏览器的根目录',
        'ok'      => '',
        'extend'  => ''
    ],
    [
        'name'    => 'url',
        'title'   => '根目录访问链接',
        'type'    => 'string',
        'content' => [
        ],
        'value'   => request()->domain(),
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '与上面目录对应,建议看下教程再配',
        'ok'      => '',
        'extend'  => ''
    ],
    [
        'name'    => 'allow_upload',
        'title'   => '允许的上传类型',
        'type'    => 'text',
        'content' => []
        ,
        'value'   =>'image,text/plain,application/vnd.ms-excel,application/vnd.ms-office,mp4,m4v,gif, jpg, jpeg, png, bmp, swf, flv, mp3, wav, wma, wmv, mid, avi, mpg, asf, rm, rmvb, doc, docx, xls, xlsx, ppt, htm, html, txt, zip, rar, gz, bz2,pdf,js,md',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '支持的文件上传类型',
        'ok'      => '',
        'extend'  => ''
    ],
    [
        'name'    => 'allow_write',
        'title'   => '可写的用户ID',
        'type'    => 'string',
        'content' => []
        ,
        'value'   =>'1',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '多个英文逗号分隔开,未设置的只能只读',
        'ok'      => '',
        'extend'  => ''
    ],

];
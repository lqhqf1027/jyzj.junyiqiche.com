define([], function () {
    require.config({
    paths: {
        'jquery-colorpicker': '../addons/cms/js/jquery.colorpicker.min',
        'jquery-autocomplete': '../addons/cms/js/jquery.autocomplete',
        'jquery-tagsinput': '../addons/cms/js/jquery.tagsinput',
    },
    shim: {
        'jquery-colorpicker': {
            deps: ['jquery'],
            exports: '$.fn.extend'
        },
        'jquery-autocomplete': {
            deps: ['jquery'],
            exports: '$.fn.extend'
        },
        'jquery-tagsinput': {
            deps: ['jquery', 'jquery-autocomplete', 'css!../addons/cms/css/jquery.tagsinput.min.css'],
            exports: '$.fn.extend'
        }
    }
});
//修改上传的接口调用
require(['upload'], function (Upload) {
    var _onUploadResponse = Upload.events.onUploadResponse;
    Upload.events.onUploadResponse = function (response) {
        try {
            var ret = typeof response === 'object' ? response : JSON.parse(response);
            if (ret.hasOwnProperty("code") && ret.hasOwnProperty("data")) {
                return _onUploadResponse.call(this, response);
            } else if (ret.hasOwnProperty("code") && ret.hasOwnProperty("url")) {
                ret.code = ret.code === 200 ? 1 : ret.code;
                ret.data = {
                    url: ret.url
                };
                return _onUploadResponse.call(this, JSON.stringify(ret));
            }
        } catch (e) {
        }
        return _onUploadResponse.call(this, response);

    };
});
});
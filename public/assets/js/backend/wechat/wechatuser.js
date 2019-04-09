define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'wechat/wechatuser/index',
                    add_url: 'wechat/wechatuser/add',
                    edit_url: 'wechat/wechatuser/edit',
                    // del_url: 'wechat/wechatuser/del',
                    multi_url: 'wechat/wechatuser/multi',
                    table: 'wechat_user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'subscribe', title: __('Subscribe'), visible:false, searchList: {"0":__('Subscribe 0'),"1":__('Subscribe 1')}},
                        {field: 'subscribe_text', title: __('Subscribe'), operate:false},
                        {field: 'openid', title: __('Openid')},
                        {field: 'nickname', title: __('Nickname')},
                        {field: 'remark', title: __('Remark')},
                        {field: 'sex', title: __('Sex'), visible:false, searchList: {"0":__('Sex 0'),"1":__('Sex 1'),"2":__('Sex 2')}},
                        {field: 'sex_text', title: __('Sex'), operate:false},
                        {field: 'city', title: __('City')},
                        {field: 'province', title: __('Province')},
                        {field: 'headimgurl', title: __('Headimgurl'), formatter: Table.api.formatter.image},
                        {field: 'subscribe_time', title: __('Subscribe_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'subscribe_scene', title: __('Subscribe_scene'),formatter:function(value,row,index){
                             
                            switch(value){
                                case  'ADD_SCENE_SEARCH':
                                return  value='公众号搜索';
                                break;
                                case  'ADD_SCENE_ACCOUNT_MIGRATION':
                                return  value='公众号迁移';
                                break;
                                case  'ADD_SCENE_PROFILE_CARD':
                                return  value='名片分享'; 
                                break;
                                case  'ADD_SCENE_QR_CODE ':
                                return  value='扫描二维码';
                                break;
                                case  'ADD_SCENEPROFILE':
                                return  value='图文页内名称点击';
                                break;
                                case  'ADD_SCENE_PROFILE_ITEM':
                                return  value='图文页右上角菜单';
                                break;
                                case  'ADD_SCENE_PAID':
                                return  value='支付后关注';
                                break;
                                case 'ADD_SCENE_OTHERS':
                                return  value='其他';
                                break;
                            }
                        }},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            
            $(document).on('click','.btn-pull-user',function(){ 
                Fast.api.ajax({
                    url:'wechat/wechatuser/pullNewUser' 
                },function(data,ret){
                    console.log(data);
                    console.log(ret.msg);
                    // return false;
                },function(data,ret){
                    console.log(data);
                    console.log(ret.msg);
                    // alert(ret);
                    // return false;
                })
            })
            // 为表格绑定事件
            Table.api.bindevent(table);

        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
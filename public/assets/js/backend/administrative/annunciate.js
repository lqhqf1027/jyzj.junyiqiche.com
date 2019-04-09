define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'administrative/annunciate/index',
                    add_url: 'administrative/annunciate/add',
                    // edit_url: 'administrative/annunciate/edit',
                    del_url: 'administrative/annunciate/del',
                    multi_url: 'administrative/annunciate/multi',
                    table: 'annunciate',
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
                        {field: 'name', title: __('Name')},
                        {field: 'admin.nickname', title: __('发布人员昵称')},
                        {field: 'content', title: __('Content'), table: table, buttons: [
                            {
                                name: 'fulldetails', text: '查看公告内容', title: '查看公告内容', icon: 'fa fa-eye', classname: 'btn btn-xs btn-primary btn-dialog btn-fulldetails',
                                url: 'administrative/annunciate/details', callback: function (data) {

                                }
                            }
                        ],
                        operate: false, formatter: Table.api.formatter.buttons
                        },
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            

            // 为表格绑定事件
            Table.api.bindevent(table);

            table.on('post-body.bs.table', function (e, settings, json, xhr) {
                $(".btn-add").data("area", ["50%", "60%"]);
            });
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
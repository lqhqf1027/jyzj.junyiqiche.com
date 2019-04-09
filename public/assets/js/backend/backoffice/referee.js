define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        /**
         * 推荐人
         */
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'backoffice/referee/index',
                    add_url: 'backoffice/referee/add',
                    edit_url: 'backoffice/referee/edit',
                    del_url: 'backoffice/referee/del',
                    multi_url: 'backoffice/referee/multi',
                    table: 'referee',
                }
            });

            var table = $("#table");

            table.on('post-body.bs.table', function (e, settings, json, xhr) {
                $(".btn-editone").data("area", ["80%", "80%"]);
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                searchFormVisible: true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'models.name', title: __('车型')},
                        {field: 'admin.nickname', title: __('销售员')},
                        {field: 'referee_name', title: __('Referee_name')},
                        {field: 'referee_phone', title: __('Referee_phone')},
                        {field: 'referee_idcard', title: __('Referee_idcard')},
                        {field: 'customer_name', title: __('Customer_name')},
                        {field: 'customer_phone', title: __('Customer_phone')},
                        {field: 'referee_bonus', title: __('Referee_bonus'), operate:'BETWEEN'},
                        {field: 'bank_account', title: __('Bank_account')},
                        {field: 'make_moneytime', title: __('Make_moneytime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,datetimeFormat: "YYYY-MM-DD"},
                        {field: 'request_fundstime', title: __('Request_fundstime'), operate:false, addclass:'datetimerange', formatter: Table.api.formatter.datetime,datetimeFormat: "YYYY-MM-DD"},
                        {field: 'introduction_note', title: __('Introduction_note'),operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            

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
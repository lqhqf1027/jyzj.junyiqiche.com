define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    /**
     * 推荐人列表
     * @type {{index: index, add: add, edit: edit, api: {bindevent: bindevent}}}
     */
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'salesmanagement/channellisttabs/index',
                    add_url: 'salesmanagement/channellisttabs/add',
                    edit_url: 'salesmanagement/channellisttabs/edit',
                    del_url: 'salesmanagement/channellisttabs/del',
                    multi_url: 'salesmanagement/channellisttabs/multi',
                    table: 'channeltabs',
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
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'models.name', title: __('车型')},
                        {field: 'admin.nickname', title: __('销售员')},
                        {field: 'referee_name', title: __('介绍人姓名')},
                        {field: 'referee_phone', title: __('介绍人电话')},
                        {field: 'referee_idcard', title: __('介绍人身份证')},
                        {field: 'customer_name', title: __('客户姓名')},
                        {field: 'customer_phone', title: __('客户电话')},
                        {field: 'referee_bonus', title: __('推荐人奖金额'), operate:'BETWEEN'},
                        {field: 'bank_account', title: __('推荐人银行账户')},
                        {field: 'make_moneytime', title: __('打款日期'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,datetimeFormat: "YYYY-MM-DD"},
                        {field: 'request_fundstime', title: __('请款日期'), operate:false, addclass:'datetimerange', formatter: Table.api.formatter.datetime,datetimeFormat: "YYYY-MM-DD"},
                        {field: 'introduction_note', title: __('介绍表备注'),operate:false},
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
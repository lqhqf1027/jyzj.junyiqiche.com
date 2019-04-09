define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'plan/planacar/index',
                    add_url: 'plan/planacar/add',
                    edit_url: 'plan/planacar/edit',
                    del_url: 'plan/planacar/del',
                    multi_url: 'plan/planacar/multi',
                    table: 'plan_acar',
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
                        {field: 'payment', title: __('Payment'), operate:'BETWEEN'},
                        {field: 'monthly', title: __('NewcarMonthly'), operate:'BETWEEN'},
                        {field: 'nperlist', title: __('Nperlist'), visible:false, searchList: {"12":__('Nperlist 12'),"24":__('Nperlist 24'),"36":__('Nperlist 36'),"48":__('Nperlist 48'),"60":__('Nperlist 60')}},
                        {field: 'nperlist_text', title: __('Nperlist'), operate:false},
                        {field: 'margin', title: __('Margin'), operate:'BETWEEN'},
                        {field: 'tail_section', title: __('Tail_section'), operate:'BETWEEN'},
                        {field: 'gps', title: __('Gps'), operate:'BETWEEN'},
                        {field: 'note', title: __('Note')},
                        {field: 'ismenu', title: __('Ismenu'), visible:false, searchList: {"1":__('Ismenu 1')}},
                        {field: 'ismenu_text', title: __('Ismenu'), operate:false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'models.name', title: __('Models.name')},
                        {field: 'financialplatform.name', title: __('Financialplatform.name')},
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
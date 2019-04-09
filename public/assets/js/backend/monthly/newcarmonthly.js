define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'monthly/newcarmonthly/index',
                    add_url: 'monthly/newcarmonthly/add',
                    edit_url: 'monthly/newcarmonthly/edit',
                    del_url: 'monthly/newcarmonthly/del',
                    multi_url: 'monthly/newcarmonthly/multi',
                    table: 'monthly',
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
                        {field: 'monthly_card_number', title: __('Monthly_card_number')},
                        {field: 'monthly_name', title: __('Monthly_name')},
                        {field: 'monthly_phone_number', title: __('Monthly_phone_number')},
                        {field: 'monthly_models', title: __('Monthly_models')},
                        {field: 'monthly_monney', title: __('Monthly_monney'), operate:'BETWEEN'},
                        {field: 'monthly_data', title: __('Monthly_data'), searchList: {"failure":__('Monthly_data failure'),"success":__('Monthly_data success')}, formatter: Table.api.formatter.normal},
                        {field: 'monthly_failure_why', title: __('Monthly_failure_why')},
                        {field: 'monthly_in_arrears_time', title: __('Monthly_in_arrears_time'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'monthly_company', title: __('Monthly_company')},
                        {field: 'monthly_car_number', title: __('Monthly_car_number')},
                        {field: 'monthly_arrears_months', title: __('Monthly_arrears_months')},
                        {field: 'monthly_note', title: __('Monthly_note')},
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
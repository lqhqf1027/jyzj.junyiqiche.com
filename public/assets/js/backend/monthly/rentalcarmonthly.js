define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'monthly/rentalcarmonthly/index',
                    add_url: 'monthly/rentalcarmonthly/add',
                    edit_url: 'monthly/rentalcarmonthly/edit',
                    del_url: 'monthly/rentalcarmonthly/del',
                    multi_url: 'monthly/rentalcarmonthly/multi',
                    table: 'rentalcar_monthly',
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
                        {field: 'monthly_company', title: __('Monthly_company')},
                        {field: 'monthly_username', title: __('Monthly_username')},
                        {field: 'monthly_phone_number', title: __('Monthly_phone_number')},
                        {field: 'monthly_car_from', title: __('Monthly_car_from')},
                        {field: 'monthly_models', title: __('Monthly_models')},
                        {field: 'monthly_car_number', title: __('Monthly_car_number')},
                        {field: 'monthly_deposit', title: __('Monthly_deposit'), operate:'BETWEEN'},
                        {field: 'monthly_ batches', title: __('Monthly_ batches')},
                        {field: 'monthly_monney', title: __('Monthly_monney'), operate:'BETWEEN'},
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
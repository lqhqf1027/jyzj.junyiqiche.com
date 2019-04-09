define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'xian/salesorder/index',
                    add_url: 'xian/salesorder/add',
                    edit_url: 'xian/salesorder/edit',
                    del_url: 'xian/salesorder/del',
                    multi_url: 'xian/salesorder/multi',
                    table: 'trench',
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
                        {field: 'data1', title: __('Data1')},
                        {field: 'data2', title: __('Data2')},
                        {field: 'data3', title: __('Data3')},
                        {field: 'data4', title: __('Data4')},
                        {field: 'data5', title: __('Data5'), operate:'BETWEEN'},
                        {field: 'data6', title: __('Data6'), operate:'BETWEEN'},
                        {field: 'data7', title: __('Data7'), searchList: {"12":__('Data7 12'),"24":__('Data7 24'),"36":__('Data7 36'),"48":__('Data7 48'),"60":__('Data7 60')}, formatter: Table.api.formatter.normal},
                        {field: 'data8', title: __('Data8'), operate:'BETWEEN'},
                        {field: 'data9', title: __('Data9'), operate:'BETWEEN'},
                        {field: 'data10', title: __('Data10'), operate:'BETWEEN'},
                        {field: 'data11', title: __('Data11'), operate:'BETWEEN'},
                        {field: 'data12', title: __('Data12'), operate:'BETWEEN'},
                        {field: 'data13', title: __('Data13'), operate:'BETWEEN'},
                        {field: 'data28', title: __('Data28')},
                        {field: 'data29', title: __('Data29')},
                        {field: 'salestype', title: __('Salestype'), searchList: {"new_car":__('Salestype new_car'),"rental_car":__('Salestype rental_car'),"second_car":__('Salestype second_car'),"full_car":__('Salestype full_car'),"second_full_car":__('Salestype second_full_car')}, formatter: Table.api.formatter.normal},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            
            // 绑定TAB事件
            $('.panel-heading a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var field = $(this).closest("ul").data("field");
                var value = $(this).data("value");
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                options.queryParams = function (params) {
                    var filter = {};
                    if (value !== '') {
                        filter[field] = value;
                    }
                    params.filter = JSON.stringify(filter);
                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;
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
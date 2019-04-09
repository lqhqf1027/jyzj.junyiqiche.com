define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        /**
         * 品牌
         */
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'planmanagement/brand/index',
                    add_url: 'planmanagement/brand/add',
                    edit_url: 'planmanagement/brand/edit',
                    del_url: 'planmanagement/brand/del',
                    multi_url: 'planmanagement/brand/multi',
                    table: 'brand',
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
                        {field: 'name', title: __('name')},
                        {field: 'brand_logoimage', title: __('Brand_logoimage'), formatter: Table.api.formatter.image},
                         
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status},

                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: function (value,row,index) {
                                if(row.name==='二手车专用车型'){
                                     return '';
                                }
                                else {
                                    return Table.api.formatter.operate.call(this, value, row, index);
                                }
                            }}
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
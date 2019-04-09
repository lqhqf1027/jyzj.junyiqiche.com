define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'plan/planusedcar/index',
                    add_url: 'plan/planusedcar/add',
                    edit_url: 'plan/planusedcar/edit',
                    del_url: 'plan/planusedcar/del',
                    multi_url: 'plan/planusedcar/multi',
                    table: 'plan_used_car',
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
                        {field: 'models.name', title: '销售车型'},
                        {field: 'financialplatform.name', title: '所属金融平台'},
                        
                        {field: 'the_door', title: __('The_door')},
                        {field: 'new_payment', title: __('New_payment'), operate:'BETWEEN'},
                        {field: 'new_monthly', title: __('New_monthly'), operate:'BETWEEN'},
                        {field: 'nperlist', title: __('Nperlist'), visible:false, searchList: {"12":__('Nperlist 12'),"24":__('Nperlist 24'),"36":__('Nperlist 36'),"48":__('Nperlist 48'),"60":__('Nperlist 60')}},
                        {field: 'nperlist_text', title: __('Nperlist'), operate:false},
                        {field: 'new_total_price', title: __('New_total_price'), operate:'BETWEEN'},
                        {field: 'mileage', title: __('Mileage')},
                        {field: 'contrarytodata', title: __('Contrarytodata'), visible:false, searchList: {"1":__('Contrarytodata 1'),"2":__('Contrarytodata 2')}},
                        {field: 'contrarytodata_text', title: __('Contrarytodata'), operate:false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                       {field: 'statusdata', title: __('Statusdata'), visible:false, searchList: {"0":__('Statusdata 0'),"1":__('Statusdata 1'),"2":__('Statusdata 2')}},
                        {field: 'statusdata_text', title: __('Statusdata'), operate:false,formatter: Table.api.formatter.statusdata},
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
        },
        
    };
    return Controller;
});
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    // index_url: 'order/fullparmentorder/index',
                    add_url: 'order/fullparmentorder/add',
                    edit_url: 'order/fullparmentorder/edit',
                    del_url: 'order/fullparmentorder/del',
                    multi_url: 'order/fullparmentorder/multi',
                    table: 'full_parment_order',
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
                        {field: 'plan_plan_full_name', title: __('Plan_plan_full_name')},
                        {field: 'sales_id', title: __('Sales_id')},
                        {field: 'admin_id', title: __('Admin_id')},
                        {field: 'backoffice_id', title: __('Backoffice_id')},
                        {field: 'new_car_id', title: __('New_car_id')},
                        {field: 'car_new_inventory_id', title: __('Car_new_inventory_id')},
                        {field: 'registry_registration_id', title: __('Registry_registration_id')},
                        {field: 'order_no', title: __('Order_no')},
                        {field: 'username', title: __('Username')},
                        {field: 'phone', title: __('Phone')},
                        {field: 'id_card', title: __('Id_card')},
                        {field: 'genderdata', title: __('Genderdata'), searchList: {"male":__('Genderdata male'),"female":__('Genderdata female')}, formatter: Table.api.formatter.normal},
                        {field: 'city', title: __('City')},
                        {field: 'detailed_address', title: __('Detailed_address')},
                        {field: 'id_cardimages', title: __('Id_cardimages'), formatter: Table.api.formatter.images},
                        {field: 'drivers_licenseimages', title: __('Drivers_licenseimages'), formatter: Table.api.formatter.images},
                        {field: 'bank_cardimages', title: __('Bank_cardimages'), formatter: Table.api.formatter.images},
                        {field: 'application_formimages', title: __('Application_formimages'), formatter: Table.api.formatter.images},
                        {field: 'call_listfiles', title: __('Call_listfiles')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'delivery_datetime', title: __('Delivery_datetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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
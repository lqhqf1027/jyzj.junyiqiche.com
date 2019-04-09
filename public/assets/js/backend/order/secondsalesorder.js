define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/secondsalesorder/index',
                    add_url: 'order/secondsalesorder/add',
                    edit_url: 'order/secondsalesorder/edit',
                    del_url: 'order/secondsalesorder/del',
                    multi_url: 'order/secondsalesorder/multi',
                    table: 'second_sales_order',
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
                        {field: 'plan_car_second_name', title: __('Plan_car_second_name')},
                        {field: 'sales_id', title: __('Sales_id')},
                        {field: 'admin_id', title: __('Admin_id')},
                        {field: 'backoffice_id', title: __('Backoffice_id')},
                        {field: 'control_id', title: __('Control_id')},
                        {field: 'second_car_id', title: __('Second_car_id')},
                        {field: 'mortgage_registration_id', title: __('Mortgage_registration_id')},
                        {field: 'registry_registration_id', title: __('Registry_registration_id')},
                        {field: 'order_no', title: __('Order_no')},
                        {field: 'username', title: __('Username')},
                        {field: 'phone', title: __('Phone')},
                        {field: 'id_card', title: __('Id_card')},
                        {field: 'genderdata', title: __('Genderdata'), searchList: {"male":__('Genderdata male'),"female":__('Genderdata female')}, formatter: Table.api.formatter.normal},
                        {field: 'city', title: __('City')},
                        {field: 'detailed_address', title: __('Detailed_address')},
                        {field: 'emergency_contact_1', title: __('Emergency_contact_1')},
                        {field: 'emergency_contact_2', title: __('Emergency_contact_2')},
                        {field: 'family_members', title: __('Family_members')},
                        {field: 'customer_source', title: __('Customer_source'), searchList: {"direct_the_guest":__('Customer_source direct_the_guest'),"turn_to_introduce":__('Customer_source turn_to_introduce')}, formatter: Table.api.formatter.normal},
                        {field: 'turn_to_introduce_name', title: __('Turn_to_introduce_name')},
                        {field: 'turn_to_introduce_phone', title: __('Turn_to_introduce_phone')},
                        {field: 'turn_to_introduce_card', title: __('Turn_to_introduce_card')},
                        {field: 'id_cardimages', title: __('Id_cardimages'), formatter: Table.api.formatter.images},
                        {field: 'drivers_licenseimages', title: __('Drivers_licenseimages'), formatter: Table.api.formatter.images},
                        {field: 'residence_bookletimages', title: __('Residence_bookletimages'), formatter: Table.api.formatter.images},
                        {field: 'housingimages', title: __('Housingimages'), formatter: Table.api.formatter.images},
                        {field: 'bank_cardimages', title: __('Bank_cardimages'), formatter: Table.api.formatter.images},
                        {field: 'application_formimages', title: __('Application_formimages'), formatter: Table.api.formatter.images},
                        {field: 'call_listfiles', title: __('Call_listfiles')},
                        {field: 'new_car_marginimages', title: __('New_car_marginimages'), formatter: Table.api.formatter.images},
                        {field: 'deposit_contractimages', title: __('Deposit_contractimages'), formatter: Table.api.formatter.images},
                        {field: 'deposit_receiptimages', title: __('Deposit_receiptimages'), formatter: Table.api.formatter.images},
                        {field: 'guarantee_id_cardimages', title: __('Guarantee_id_cardimages'), formatter: Table.api.formatter.images},
                        {field: 'guarantee_agreementimages', title: __('Guarantee_agreementimages'), formatter: Table.api.formatter.images},
                        {field: 'buy_insurancedata', title: __('Buy_insurancedata'), searchList: {"yes":__('Buy_insurancedata yes'),"no":__('Buy_insurancedata no')}, formatter: Table.api.formatter.normal},
                        {field: 'review_the_data', title: __('Review_the_data'), searchList: {"is_reviewing":__('Review_the_data is_reviewing'),"is_reviewing_true":__('Review_the_data is_reviewing_true'),"not_through":__('Review_the_data not_through'),"through":__('Review_the_data through'),"the_guarantor":__('Review_the_data the_guarantor'),"for_the_car":__('Review_the_data for_the_car'),"the_car":__('Review_the_data the_car')}, formatter: Table.api.formatter.normal},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'delivery_datetime', title: __('Delivery_datetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'plan_name', title: __('Plan_name')},
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
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/secondfullorder/index',
                    add_url: 'order/secondfullorder/add',
                    edit_url: 'order/secondfullorder/edit',
                    del_url: 'order/secondfullorder/del',
                    multi_url: 'order/secondfullorder/multi',
                    table: 'second_full_order',
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
                        {field: 'plan_second_full_name', title: __('Plan_second_full_name')},
                        {field: 'sales_id', title: __('Sales_id')},
                        {field: 'admin_id', title: __('Admin_id')},
                        {field: 'backoffice_id', title: __('Backoffice_id')},
                        {field: 'second_car_id', title: __('Second_car_id')},
                        {field: 'registry_registration_id', title: __('Registry_registration_id')},
                        {field: 'mortgage_registration_id', title: __('Mortgage_registration_id')},
                        {field: 'customer_downpayment_id', title: __('Customer_downpayment_id')},
                        {field: 'models_id', title: __('Models_id')},
                        {field: 'referee_id', title: __('Referee_id')},
                        {field: 'violation_inquiry_id', title: __('Violation_inquiry_id')},
                        {field: 'order_no', title: __('Order_no')},
                        {field: 'username', title: __('Username')},
                        {field: 'phone', title: __('Phone')},
                        {field: 'id_card', title: __('Id_card')},
                        {field: 'genderdata', title: __('Genderdata'), searchList: {"male":__('Genderdata male'),"female":__('Genderdata female')}, formatter: Table.api.formatter.normal},
                        {field: 'city', title: __('City')},
                        {field: 'detailed_address', title: __('Detailed_address')},
                        {field: 'customer_source', title: __('Customer_source'), searchList: {"straight":__('Customer_source straight'),"introduce":__('Customer_source introduce')}, formatter: Table.api.formatter.normal},
                        {field: 'introduce_name', title: __('Introduce_name')},
                        {field: 'introduce_phone', title: __('Introduce_phone')},
                        {field: 'introduce_card', title: __('Introduce_card')},
                        {field: 'id_cardimages', title: __('Id_cardimages'), formatter: Table.api.formatter.images},
                        {field: 'drivers_licenseimages', title: __('Drivers_licenseimages'), formatter: Table.api.formatter.images},
                        {field: 'bank_cardimages', title: __('Bank_cardimages'), formatter: Table.api.formatter.images},
                        {field: 'application_formimages', title: __('Application_formimages'), formatter: Table.api.formatter.images},
                        {field: 'call_listfiles', title: __('Call_listfiles')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'delivery_datetime', title: __('Delivery_datetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'plan_name', title: __('Plan_name')},
                        {field: 'review_the_data', title: __('Review_the_data'), searchList: {"is_reviewing_true":__('Review_the_data is_reviewing_true'),"for_the_car":__('Review_the_data for_the_car'),"send_to_internal":__('Review_the_data send_to_internal'),"inhouse_handling":__('Review_the_data inhouse_handling')}, formatter: Table.api.formatter.normal},
                        {field: 'amount_collected', title: __('Amount_collected'), operate:'BETWEEN'},
                        {field: 'decorate', title: __('Decorate')},
                        {field: 'purchase_tax', title: __('Purchase_tax'), operate:'BETWEEN'},
                        {field: 'car_images', title: __('Car_images'), formatter: Table.api.formatter.images},
                        {field: 'business_risks', title: __('Business_risks'), operate:'BETWEEN'},
                        {field: 'insurance', title: __('Insurance'), operate:'BETWEEN'},
                        {field: 'financial_name', title: __('Financial_name')},
                        {field: 'bond', title: __('Bond'), operate:'BETWEEN'},
                        {field: 'admin.id', title: __('Admin.id')},
                        {field: 'admin.username', title: __('Admin.username')},
                        {field: 'admin.nickname', title: __('Admin.nickname')},
                        {field: 'admin.password', title: __('Admin.password')},
                        {field: 'admin.salt', title: __('Admin.salt')},
                        {field: 'admin.avatar', title: __('Admin.avatar')},
                        {field: 'admin.email', title: __('Admin.email')},
                        {field: 'admin.openid', title: __('Admin.openid')},
                        {field: 'admin.rule_message', title: __('Admin.rule_message')},
                        {field: 'admin.loginfailure', title: __('Admin.loginfailure')},
                        {field: 'admin.logintime', title: __('Admin.logintime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'admin.createtime', title: __('Admin.createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'admin.updatetime', title: __('Admin.updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'admin.token', title: __('Admin.token')},
                        {field: 'admin.status', title: __('Admin.status'), formatter: Table.api.formatter.status},
                        {field: 'models.id', title: __('Models.id')},
                        {field: 'models.brand_id', title: __('Models.brand_id')},
                        {field: 'models.name', title: __('Models.name')},
                        {field: 'models.standard_price', title: __('Models.standard_price'), operate:'BETWEEN'},
                        {field: 'models.status', title: __('Models.status'), formatter: Table.api.formatter.status},
                        {field: 'models.createtime', title: __('Models.createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'models.updatetime', title: __('Models.updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'info.id', title: __('Info.id')},
                        {field: 'info.sales_id', title: __('Info.sales_id')},
                        {field: 'info.licenseplatenumber', title: __('Info.licenseplatenumber')},
                        {field: 'info.models_id', title: __('Info.models_id')},
                        {field: 'info.kilometres', title: __('Info.kilometres'), operate:'BETWEEN'},
                        {field: 'info.companyaccount', title: __('Info.companyaccount')},
                        {field: 'info.newpayment', title: __('Info.newpayment')},
                        {field: 'info.monthlypaymen', title: __('Info.monthlypaymen')},
                        {field: 'info.periods', title: __('Info.periods')},
                        {field: 'info.totalprices', title: __('Info.totalprices')},
                        {field: 'info.bond', title: __('Info.bond'), operate:'BETWEEN'},
                        {field: 'info.tailmoney', title: __('Info.tailmoney'), operate:'BETWEEN'},
                        {field: 'info.drivinglicenseimages', title: __('Info.drivinglicenseimages'), formatter: Table.api.formatter.images},
                        {field: 'info.vin', title: __('Info.vin')},
                        {field: 'info.engine_number', title: __('Info.engine_number')},
                        {field: 'info.expirydate', title: __('Info.expirydate')},
                        {field: 'info.annualverificationdate', title: __('Info.annualverificationdate')},
                        {field: 'info.carcolor', title: __('Info.carcolor')},
                        {field: 'info.aeratedcard', title: __('Info.aeratedcard')},
                        {field: 'info.volumekeys', title: __('Info.volumekeys')},
                        {field: 'info.Parkingposition', title: __('Info.parkingposition')},
                        {field: 'info.lending_date', title: __('Info.lending_date'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'info.car_images', title: __('Info.car_images'), formatter: Table.api.formatter.images},
                        {field: 'info.bank_card', title: __('Info.bank_card')},
                        {field: 'info.invoice_monney', title: __('Info.invoice_monney'), operate:'BETWEEN'},
                        {field: 'info.registration_code', title: __('Info.registration_code')},
                        {field: 'info.tax', title: __('Info.tax'), operate:'BETWEEN'},
                        {field: 'info.business_risks', title: __('Info.business_risks'), operate:'BETWEEN'},
                        {field: 'info.insurance', title: __('Info.insurance'), operate:'BETWEEN'},
                        {field: 'info.mortgage_type', title: __('Info.mortgage_type')},
                        {field: 'info.shelfismenu', title: __('Info.shelfismenu')},
                        {field: 'info.vehiclestate', title: __('Info.vehiclestate')},
                        {field: 'info.note', title: __('Info.note')},
                        {field: 'info.createtime', title: __('Info.createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'info.updatetime', title: __('Info.updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'info.status_data', title: __('Info.status_data')},
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
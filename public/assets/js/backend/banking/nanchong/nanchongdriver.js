define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'banking/nanchong/nanchongdriver/index',
                    add_url: 'banking/nanchong/nanchongdriver/add',
                    edit_url: 'banking/nanchong/nanchongdriver/edit',
                    del_url: 'banking/nanchong/nanchongdriver/del',
                    multi_url: 'banking/nanchong/nanchongdriver/multi',
                    table: 'nanchong_driver',
                }
            });

            var table = $("#table");

            table.on('post-body.bs.table', function (e, settings, json, xhr) {
                $(".btn-add").data("area", ["80%", "80%"]);

            });
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'car_model', title: __('Car_model')},
                        {field: 'licensenumber', title: __('Licensenumber')},
                        {field: 'username', title: __('Username')},
                        {field: 'phone', title: __('Phone')},
                        {field: 'id_card', title: __('Id_card')},
                        {field: 'detailed_address', title: __('Detailed_address')},
                        {field: 'household', title: __('Household')},
                        {field: 'payment', title: __('Payment')},
                        {field: 'monthly', title: __('NewcarMonthly')},
                        {field: 'nperlist', title: __('Nperlist'), searchList: {"12":__('Nperlist 12'),"24":__('Nperlist 24'),"36":__('Nperlist 36'),"48":__('Nperlist 48'),"60":__('Nperlist 60')}, formatter: Table.api.formatter.normal},
                        {field: 'car_images', title: __('Car_images'), formatter: Table.api.formatter.images},
                        {field: 'lending_date', title: __('Lending_date'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'bank_card', title: __('Bank_card')},
                        {field: 'invoice_monney', title: __('Invoice_monney'), operate:'BETWEEN'},
                        {field: 'registration_code', title: __('Registration_code')},
                        {field: 'tax', title: __('Tax'), operate:'BETWEEN'},
                        {field: 'business_risks', title: __('Business_risks'), operate:'BETWEEN'},
                        {field: 'insurance', title: __('Insurance'), operate:'BETWEEN'},
                        {field: 'booking_time', title: __('Booking_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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
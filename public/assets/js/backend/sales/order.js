define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template'], function ($, undefined, Backend, Table, Form, Template) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'sales/order/index' + location.search,
                    add_url: 'sales/order/add',
                    edit_url: 'sales/order/edit',
                    del_url: 'sales/order/del',
                    multi_url: 'sales/order/multi',
                    table: 'order',
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
                        {
                            field: 'admin_id', title: __('销售员'), formatter: function (v, r, i) {
                                return v ? '  <img src='+Config.cdn + r.admin.avatar+' alt="" width="25" height="25" >  '  + r.admin.username : '';
                            }
                        },
                        {
                            field: 'customer_source',
                            title: __('Customer_source'),
                            searchList: {
                                "direct_the_guest": __('Customer_source direct_the_guest'),
                                "turn_to_introduce": __('Customer_source turn_to_introduce')
                            },
                            formatter: Table.api.formatter.normal
                        },
                        {field: 'financial_name', title: __('Financial_name')},
                        {field: 'username', title: __('Username')},
                        {field: 'phone', title: __('Phone')},
                        {field: 'id_card', title: __('Id_card')},
                        // {
                        //     field: 'genderdata',
                        //     title: __('Genderdata'),
                        //     searchList: {"male": __('Male'), "female": __('Female')},
                        //     formatter: Table.api.formatter.normal
                        // },
                        // {field: 'city', title: __('City')},
                        {
                            field: 'type',
                            title: __('Type'),
                            searchList: {
                                "mortgage": __('Type mortgage'),
                                "used_car_mortgage": __('Type used_car_mortgage'),
                                "car_rental": __('Type car_rental'),
                                "full_new_car": __('Type full_new_car'),
                                "full_used_car": __('Type full_used_car'),
                                "sublet": __('Type sublet'),
                                "affiliated": __('Type affiliated')
                            },
                            formatter: Table.api.formatter.normal
                        },
                        {field: 'models_name', title: __('Models_name')},
                        {field: 'payment', title: __('Payment'), operate: 'BETWEEN'},
                        {field: 'monthly', title: __('Monthly'), operate: 'BETWEEN'},
                        {
                            field: 'nperlist',
                            title: __('Nperlist'),
                            searchList: {
                                "12": __('Nperlist 12'),
                                "24": __('Nperlist 24'),
                                "36": __('Nperlist 36'),
                                "48": __('Nperlist 48'),
                                "60": __('Nperlist 60')
                            },
                            formatter: Table.api.formatter.normal
                        },
                        {field: 'gps', title: __('Gps'), operate: 'BETWEEN'},
                        // {field: 'decoration', title: __('Decoration')},
                        // {field: 'rent', title: __('Rent'), operate: 'BETWEEN'},
                        // {field: 'deposit', title: __('Deposit'), operate: 'BETWEEN'},
                        // {field: 'family_members', title: __('Family_members')},
                        // {field: 'detailed_address', title: __('Detailed_address')},
                        // {field: 'family_members2', title: __('Family_members2')},
                        // {field: 'turn_to_introduce_name', title: __('Turn_to_introduce_name')},
                        // {field: 'turn_to_introduce_phone', title: __('Turn_to_introduce_phone')},
                        // {field: 'turn_to_introduce_card', title: __('Turn_to_introduce_card')},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'delivery_datetime',
                            title: __('Delivery_datetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {field: 'note_sales', title: __('Note_sales')},

                        // {field: 'orderdetails.admin_id', title: __('Orderdetails.admin_id')},
                        // {field: 'orderdetails.licensenumber', title: __('Orderdetails.licensenumber')},
                        // {
                        //     field: 'orderimg.id_cardimages',
                        //     title: __('Orderimg.id_cardimages'),
                        //     events: Table.api.events.image,
                        //     formatter: Table.api.formatter.images
                        // },
                        // {
                        //     field: 'orderimg.driving_licenseimages',
                        //     title: __('Orderimg.driving_licenseimages'),
                        //     events: Table.api.events.image,
                        //     formatter: Table.api.formatter.images
                        // },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });
            table.on('load-success.bs.table', function (e, data) {
                $(".btn-add").data("area", ["65%", "80%"]);
            })
            // 为表格绑定事件
            Table.api.bindevent(table);

        },
        add: function () {


            $('#c-customer_source').on('change', function (e) {  //客户资源
                $('.turn').toggleClass('hidden');
                // var v =  $(this).children('option:selected').val();
            })
            $('.marriage').on('change',function () {   //配偶身份证反面
                $('.marriage_block').toggleClass('hidden');
            })
            Controller.api.bindevent();

            // Form.api.bindevent($("form[role=form]"));
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
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'vehicle/vehiclemanagement/index' + location.search,
                    add_url: 'vehicle/vehiclemanagement/add',
                    // edit_url: 'vehicle/vehiclemanagement/edit',
                    del_url: 'vehicle/vehiclemanagement/del',
                    multi_url: 'vehicle/vehiclemanagement/multi',
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
                        {field: 'username', title: __('Username')},
                        {field: 'phone', title: __('Phone')},
                        {field: 'id_card', title: __('Id_card')},
                        {field: 'models_name', title: __('Models_name')},
                        {field: 'payment', title: __('Payment')},
                        {field: 'monthly', title: __('月供'), operate: 'BETWEEN'},
                        {field: 'nperlist', title: __('期数')},
                        {field: 'end_money', title: __('End_money')},
                        {field: 'tail_money', title: __('Tail_money')},
                        {field: 'margin', title: __('Margin')},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
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
                        {field: 'orderdetails.file_coding', title: __('Orderdetails.file_coding')},
                        {field: 'orderdetails.signdate', title: __('Orderdetails.signdate')},
                        {field: 'orderdetails.total_contract', title: __('Orderdetails.total_contract')},
                        {field: 'orderdetails.hostdate', title: __('Orderdetails.hostdate')},
                        {field: 'orderdetails.licensenumber', title: __('Orderdetails.licensenumber')},
                        {field: 'orderdetails.frame_number', title: __('Orderdetails.frame_number')},
                        {field: 'orderdetails.engine_number', title: __('Orderdetails.engine_number')},
                        {field: 'orderdetails.is_mortgage', title: __('Orderdetails.is_mortgage')},
                        {field: 'orderdetails.mortgage_people', title: __('Orderdetails.mortgage_people')},
                        {field: 'orderdetails.ticketdate', title: __('Orderdetails.ticketdate')},
                        {field: 'orderdetails.supplier', title: __('Orderdetails.supplier')},
                        {field: 'orderdetails.tax_amount', title: __('Orderdetails.tax_amount')},
                        {field: 'orderdetails.no_tax_amount', title: __('Orderdetails.no_tax_amount')},
                        {field: 'orderdetails.pay_taxesdate', title: __('Orderdetails.pay_taxesdate')},
                        {field: 'orderdetails.purchase_of_taxes', title: __('Orderdetails.purchase_of_taxes')},
                        {field: 'orderdetails.house_fee', title: __('Orderdetails.house_fee')},
                        {field: 'orderdetails.luqiao_fee', title: __('Orderdetails.luqiao_fee')},
                        {field: 'orderdetails.insurance_buydate', title: __('Orderdetails.insurance_buydate')},
                        {field: 'orderdetails.insurance_policy', title: __('Orderdetails.insurance_policy')},
                        {field: 'orderdetails.insurance', title: __('Orderdetails.insurance')},
                        {field: 'orderdetails.car_boat_tax', title: __('Orderdetails.car_boat_tax')},
                        {
                            field: 'orderdetails.commercial_insurance_policy',
                            title: __('Orderdetails.commercial_insurance_policy')
                        },
                        {field: 'orderdetails.business_risks', title: __('Orderdetails.business_risks')},
                        {field: 'orderdetails.subordinate_branch', title: __('Orderdetails.subordinate_branch')},
                        {field: 'orderdetails.transfer_time', title: __('Orderdetails.transfer_time')},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Controller.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'edits',
                                    icon: 'fa fa-pencil',
                                    title: __('提车'),
                                    text: '确认提车',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-info btn-lift-car',
                                    visible: function (row) {
                                        return row.lift_car_status == 'no' ? true : false;
                                    }
                                },
                                {
                                    name: 'edits',
                                    icon: 'fa fa-check',
                                    title: __('已提车'),
                                    text: '已提车',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'text-info',
                                    visible: function (row) {
                                        return row.lift_car_status == 'yes' ? true : false;
                                    }
                                }

                            ]
                        }
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
            let type = $('input[type=hidden]').val();
            let mortgage = $('#c-is_mortgage').val();
            type == 'full_new_car' || type == 'full_used_car' ? $('.full').show() : $('.full').hide();
            mortgage == 'yes' ? $('#mortgage-people').show() : $('#mortgage-people').hide();

            $('#c-is_mortgage').on('change', function () {
                $(this).val() == 'yes' ? $('#mortgage-people').show() : $('#mortgage-people').hide();
            });

            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            // 单元格元素事件
            events: {
                operate: {
                    'click .btn-lift-car': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'vehicle/vehiclemanagement/edit';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('提车'), $(this).data() || {});
                    },
                    'click .btn-delone': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var that = this;
                        var top = $(that).offset().top - $(window).scrollTop();
                        var left = $(that).offset().left - $(window).scrollLeft() - 260;
                        if (top + 154 > $(window).height()) {
                            top = top - 154;
                        }
                        if ($(window).width() < 480) {
                            top = left = undefined;
                        }
                        Layer.confirm(
                            __('Are you sure you want to delete this item?'),
                            {icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true},
                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');
                                Table.api.multi("del", row[options.pk], table, that);
                                Layer.close(index);
                            }
                        );
                    }
                }
            },
        }
    };
    return Controller;
});
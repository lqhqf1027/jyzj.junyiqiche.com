define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'publicquery/cuostomerinfo/index',
                    add_url: 'publicquery/cuostomerinfo/add',
                    edit_url: 'publicquery/cuostomerinfo/edit',
                    del_url: 'publicquery/cuostomerinfo/del',
                    multi_url: 'publicquery/cuostomerinfo/multi',
                    import_url: 'publicquery/cuostomerinfo/import',
                    table: 'past_information',
                }
            });

            var table = $("#table");

            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                return "快速搜索：客户姓名，车牌号";
            };


            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                searchFormVisible: true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('客户ID'), operate: false},
                        {field: 'archival_coding', title: __('Archival_coding'), operate: false},
                        {
                            field: 'signtime',
                            title: __('Signtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime,
                            datetimeFormat:'YYYY-MM-DD'
                        },
                        {field: 'username', title: __('Username')},
                        {field: 'id_card', title: __('Id_card')},
                        {field: 'phone', title: __('Phone')},
                        {field: 'sales', title: __('Sales')},
                        {field: 'branch_office', title: __('Branch_office')},
                        {field: 'models', title: __('Models'), operate: false},
                        {field: 'platenumber', title: __('Platenumber')},
                        {field: 'framenumber', title: __('Framenumber')},
                        {field: 'constract_total', title: __('Constract_total'), operate: false},
                        {field: 'payment', title: __('Payment'), operate: false},
                        {field: 'monthly', title: __('Monthly'), operate: false},
                        {field: 'term', title: __('Term'), operate: false},
                        {field: 'last_rent', title: __('Last_rent'), operate: false},
                        {field: 'bond', title: __('Bond'), operate: false},
                        {
                            field: 'wealthytime',
                            title: __('Wealthytime'),
                            operate: false,
                            formatter: Table.api.formatter.datetime,
                            datetimeFormat:'YYYY-MM-DD'
                        },

                        {field: 'mortgage', title: __('Mortgage'), searchList: {'是': '是', '否': '否'}},
                        {field: 'mortgage_man', title: __('Mortgage_man'), operate: false},
                        {
                            field: 'tickettime',
                            title: __('Tickettime'),
                            operate: false,
                            formatter: Table.api.formatter.datetime,
                            datetimeFormat:'YYYY-MM-DD'
                        },
                        {field: 'supplier', title: __('Supplier')},
                        {field: 'tax_amount', title: __('Tax_amount'), operate: false},
                        {field: 'no_tax', title: __('No_tax'), operate: false},
                        {
                            field: 'paymenttime',
                            title: __('Paymenttime'),
                            operate: false,
                            formatter: Table.api.formatter.datetime,
                            datetimeFormat:'YYYY-MM-DD'
                        },
                        {field: 'purchase_tax', title: __('Purchase_tax'), operate: false},
                        {field: 'house_fee', title: __('House_fee'), operate: false},
                        {field: 'road_fee', title: __('Road_fee'), operate: false},
                        {
                            field: 'buytime',
                            title: __('Buytime'),
                            operate: false,
                            formatter: Table.api.formatter.datetime,
                            datetimeFormat:'YYYY-MM-DD'
                        },
                        {field: 'strong_insurance_list', title: __('Strong_insurance_list'), operate: false},
                        {field: 'strong_insurance_money', title: __('Strong_insurance_money'), operate: false},
                        {field: 'car_money', title: __('Car_money'), operate: false},
                        {field: 'commercial_insurance_list', title: __('Commercial_insurance_list'), operate: false},
                        {field: 'commercial_insurance_money', title: __('Commercial_insurance_money'), operate: false},
                        {
                            field: 'transfertime',
                            title: __('Transfertime'),
                            operate: false,
                            formatter: Table.api.formatter.datetime,
                            datetimeFormat:'YYYY-MM-DD'
                        },


                        {field: 'rent_month', title: __('Rent_month'), operate: false},
                        {field: 'car_line', title: __('Car_line'), operate: false},
                        {field: 'deposit', title: __('Deposit'), operate: false},
                        {field: 'rent_money_month', title: __('Rent_money_month'), operate: false},
                        {field: 'renttime', title: __('Renttime'), operate: false},
                        {field: 'backtime', title: __('Backtime'), operate: false},
                        {field: 'note', title: __('Note'), operate: false},
                        {
                            field: 'types',
                            title: __('Types'),
                            searchList: {
                                "full": __('Types full'),
                                "rent": __('Types rent'),
                                "mortgage": __('Types mortgage')
                            },
                            formatter: Table.api.formatter.normal
                        },
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
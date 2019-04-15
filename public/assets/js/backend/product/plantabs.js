define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'product/plantabs/index' + location.search,
                    add_url: 'product/plantabs/add',
                    edit_url: 'product/plantabs/edit',
                    del_url: 'product/plantabs/del',
                    multi_url: 'product/plantabs/multi',
                    table: 'plan',
                }
            });

            var table = $("#table");

            table.on('load-success.bs.table', function (e, data) {
                $(".btn-add").data("area", ["65%", "80%"]);
            })

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},

                        {field: 'schemecategory.name', title: __('Schemecategory.name')},
                        {field: 'models.name', title: __('Models.name')},

                        {field: 'financial_platform_name', title: __('Financial_platform_name')},
                        {field: 'payment', title: __('Payment'), operate:'BETWEEN'},
                        {field: 'monthly', title: __('Monthly'), operate:'BETWEEN'},
                        {field: 'nperlist', title: __('Nperlist'), searchList: {"12":__('Nperlist 12'),"24":__('Nperlist 24'),"36":__('Nperlist 36'),"48":__('Nperlist 48'),"60":__('Nperlist 60')}, formatter: Table.api.formatter.normal},
                        {field: 'margin', title: __('Margin'), operate:'BETWEEN'},
                        {field: 'tail_section', title: __('Tail_section'), operate:'BETWEEN'},
                        {field: 'gps', title: __('Gps'), operate:'BETWEEN'},
                        {field: 'total_payment', title: __('Total_payment'), operate:'BETWEEN'},
                        {field: 'full_total_price', title: __('Full_total_price'), operate:'BETWEEN'},
                        {field: 'working_insurance', title: __('Working_insurance'), searchList: {"yes":__('Working_insurance yes'),"no":__('Working_insurance no')}, formatter: Table.api.formatter.normal},
                        {field: 'companyaccount', title: __('Companyaccount')},
                        {field: 'licenseplatenumber', title: __('Licenseplatenumber')},
                        {field: 'vin', title: __('Vin')},
                        {field: 'engine_no', title: __('Engine_no')},
                        {field: 'kilometres', title: __('Kilometres'), operate:'BETWEEN'},
                        {field: 'cashpledge', title: __('Cashpledge'), operate:'BETWEEN'},
                        {field: 'rent_price', title: __('Rent_price'), operate:'BETWEEN'},
                        {field: 'car_licensetime', title: __('Car_licensetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'emission_standard', title: __('Emission_standard')},
                        {field: 'emission_load', title: __('Emission_load')},
                        {field: 'speed_changing_box', title: __('Speed_changing_box')},
                        {field: 'drivinglicenseimages', title: __('Drivinglicenseimages'), events: Table.api.events.image, formatter: Table.api.formatter.images},
                        {field: 'ismenu', title: __('Ismenu')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'type', title: __('Type'), searchList: {"mortgage":__('Type mortgage'),"used_car_mortgage":__('Type used_car_mortgage'),"car_rental":__('Type car_rental'),"full_new_car":__('Type full_new_car'),"full_used_car":__('Type full_used_car')}, formatter: Table.api.formatter.normal},
          
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
            },
            formatter: {
                /**
                 * 是否上线销售
                 * @param value
                 * @param row
                 * @param index
                 * @returns {string}
                 */
                toggle: function (value, row, index) {
                    var color = typeof this.color !== 'undefined' ? this.color : 'success';
                    var yes = typeof this.yes !== 'undefined' ? this.yes : 1;
                    var no = typeof this.no !== 'undefined' ? this.no : 0;
                    // return row.match_plan == 'match_success' ? '正在销售或已出售' : "<a href='javascript:;' data-toggle='tooltip' title='" + __('Click to toggle') + "' class='btn-change' data-id='"
                    //     + row.id + "' data-params='" + this.field + "=" + (value ? no : yes) + "'><i class='fa fa-toggle-on " + (value == yes ? 'text-' + color : 'fa-flip-horizontal text-gray') + " fa-2x'></i></a>";

                    return row.sales_id?'正在销售或已出售':"<a href='javascript:;' data-toggle='tooltip' title='" + __('Click to toggle') + "' class='btn-change' data-id='"
                        + row.id + "' data-params='" + this.field + "=" + (value ? no : yes) + "'><i class='fa fa-toggle-on " + (value == yes ? 'text-' + color : 'fa-flip-horizontal text-gray') + " fa-2x'></i></a>";

                },

            },
        }
    };
    return Controller;
});
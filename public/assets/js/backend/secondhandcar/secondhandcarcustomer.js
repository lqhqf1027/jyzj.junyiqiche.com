define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    /**
     * 二手车客户信息
     * @type {{index: index, add: add, edit: edit, api: {bindevent: bindevent, formatter: {datetime: datetime}}}}
     */
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'secondhandcar/secondhandcarcustomer/index',
                    table: 'second_sales_order',
                }
            });

            var table = $("#table");
            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "快速搜索：客户姓名，车牌号";};
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                searchFormVisible: true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate: false},
                        // {field: 'order_no', title: __('订单编号')},
                        {field: 'username', title: __('客户姓名')},
                        {field: 'financial_name', title: __('金融平台')},
                        {field: 'admin.nickname', title: __('销售员'),formatter:Controller.api.formatter.sales},
                        {field: 'models.name', title: __('车型')},
                        {field: 'plansecond.licenseplatenumber', title: __('车牌号')},
                        {field: 'plansecond.vin', title: __('车架号')},
                        {field: 'plansecond.engine_number', title: __('发动机号')},
                        {field: 'plansecond.newpayment', title: __('新首付（元）'), operate: false},
                        {field: 'plansecond.monthlypaymen', title: __('月供（元）'), operate: false},
                        {field: 'plansecond.periods', title: __('期数'), operate: false},
                        {field: 'plansecond.totalprices', title: __('总价（元）'), operate: false},
                        {field: 'plansecond.bond', title: __('保证金（元）'), operate: false},
                        {field: 'plansecond.tailmoney', title: __('尾款（元）'), operate: false},
                        {
                            field: 'createtime',
                            title: __('订车时间'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Controller.api.formatter.datetime,
                            datetimeFormat: 'YYYY-MM-DD'
                        },
                        {
                            field: 'delivery_datetime',
                            title: __('提车时间'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Controller.api.formatter.datetime,
                            datetimeFormat: 'YYYY-MM-DD'
                        },

                        {
                            field: 'operate', title: __('Operate'), table: table, buttons: [
                                {
                                    name: 'the_car',
                                    icon: 'fa fa-automobile',
                                    text: '已提车',
                                    extend: 'data-toggle="tooltip"',
                                    title: __('订单已完成，客户已提车'),
                                    classname: ' text-success ',
                                },
                                {
                                    name: 'secondDetails',
                                    text: '查看详细资料',
                                    title: '查看订单详细资料',
                                    icon: 'fa fa-eye',
                                    classname: 'btn btn-xs btn-primary btn-dialog btn-secondDetails',
                                    url: 'Sharedetailsdatas/secondfull_car_share_data',
                                    callback: function (data) {
                                    }
                                }

                            ],

                            events: Table.api.events.operate,

                            formatter: Table.api.formatter.operate

                        }
                    ]
                ]
            });


            // 为表格绑定事件
            Table.api.bindevent(table);

            table.on('load-success.bs.table', function (e, data) {

                $(".btn-secondDetails").data("area", ["95%", "95%"]);
            })

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
                datetime: function (value, row, index) {

                    if (value) {
                        return timestampToTime(value);
                    }

                    function timestampToTime(timestamp) {
                        var date = new Date(timestamp * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
                        var Y = date.getFullYear() + '-';
                        var M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
                        var D = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();

                        return Y + M + D;
                    }

                },
                sales:function (value, row, index) {
                    // console.log(row);

                    return value==null?value : "<img src=" + Config.cdn_url+row.admin.avatar + " style='height:40px;width:40px;border-radius:50%'></img>" + '&nbsp;' +row.admin.department+' - '+value;
                },
            }

        }
    };
    return Controller;
});
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    // var goeasy = new GoEasy({
    //     appkey: 'BC-04084660ffb34fd692a9bd1a40d7b6c2'
    // });

    var Controller = {
        index: function () {

            // 初始化表格参数配置
            Table.api.init({});

            //绑定事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var panel = $($(this).attr("href"));
                if (panel.size() > 0) {
                    Controller.table[panel.attr("id")].call(this);
                    $(this).on('click', function (e) {
                        $($(this).attr("href")).find(".btn-refresh").trigger("click");
                    });
                }
                //移除绑定的事件
                $(this).unbind('shown.bs.tab');
            });

            //必须默认触发shown.bs.tab事件
            $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");

            $('ul.nav-tabs li a[data-toggle="tab"]').each(function () {
                $(this).trigger("shown.bs.tab");
            });
        },



        table: {

            /**
             * 新车定金
             */
            new_car: function () {
                // 表格1
                var newCar = $("#newCar");
                newCar.on('load-success.bs.table', function (e, data) {
                    $('#new-customer').text(data.total);

                })
                newCar.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-editone").data("area", ["70%", "70%"]);
                });

                //统计
                total(newCar,$('#total-new'));

                // 初始化表格
                newCar.bootstrapTable({
                    url: 'backoffice/Depositmanage/new_car',
                    extend: {
                        // index_url: 'plan/planacar/index',
                        // add_url: 'planmanagement/plantabs/firstadd',
                        edit_url: 'backoffice/Depositmanage/edit',
                        // del_url: 'planmanagement/plantabs/firstdel',
                        // multi_url: 'planmanagement/plantabs/firstmulti',
                        // table: 'plan_acar',
                    },

                    toolbar: '#toolbar1',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,

                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: Fast.lang('Id'),operate:false},
                            {field: 'financial_name', title: __('所属平台')},
                            {field: 'admin.nickname', title: __('所属销售'),formatter:Controller.api.formatter.sales},
                            {field: 'username', title: __('Username')},
                            {field: 'phone', title: __('Phone')},
                            {field: 'id_card', title: __('身份证号')},
                            {field: 'city', title: __('身份证地址'),operate:false},
                            {field: 'planacar.payment', title: __('首付(元)'),operate:false},
                            {field: 'planacar.monthly', title: __('月供(元)'),operate:false},
                            {field: 'planacar.nperlist', title: __('期数'),operate:false},
                            {field: 'planacar.tail_section', title: __('尾款(元)'),operate:false},
                            {field: 'planacar.gps', title: __('GPS(元)'),operate:false},
                            {field: 'bond', title: __('保证金(元)'),operate:false},
                            {field: 'customerdownpayment.openingbank', title: __('开户行')},
                            {field: 'customerdownpayment.bankcardnumber', title: __('银行卡卡号'),operate:false},
                            {field: 'customerdownpayment.totalmoney', title: __('车款总价(元)'),operate:false},
                            {field: 'customerdownpayment.downpayment', title: __('首期款(元)'),operate:false},
                            {field: 'customerdownpayment.moneyreceived', title: __('实收金额(元)'),operate:false},
                            {field: 'customerdownpayment.marginmoney', title: __('差额(元)'),operate:false},
                            {field: 'customerdownpayment.gatheringaccount', title: __('收款账户'),operate:false},
                            {field: 'customerdownpayment.decorate', title: __('装饰'),operate:false},
                            {field: 'customerdownpayment.note', title: __('票据号备注'),operate:false},
                            {field: 'customerdownpayment.makemoney_status', title: __('是否打款'),formatter:Controller.api.formatter.status,searchList:{'1':'已打款','2':'未打款'}},

                            {
                                field: 'operate', title: __('Operate'), table: newCar,

                                events: Controller.api.operate,
                                formatter: Table.api.formatter.operate
                            }
                        ]
                    ]
                });
                // 为表格1绑定事件
                Table.api.bindevent(newCar);



            },
            /**
             * 二手车定金
             */
            used_car: function () {
                // 表格1
                var usedCar = $("#usedCar");
                usedCar.on('load-success.bs.table', function (e, data) {
                    $('#new-customer').text(data.total);

                })
                usedCar.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-editone").data("area", ["70%", "70%"]);
                });
                //统计
                total(usedCar,$('#total-used'));

                // 初始化表格
                usedCar.bootstrapTable({
                    url: 'backoffice/Depositmanage/used_car',
                    extend: {
                        // index_url: 'plan/planacar/index',
                        // add_url: 'planmanagement/plantabs/firstadd',
                        edit_url: 'backoffice/Depositmanage/edit_used',
                        // del_url: 'planmanagement/plantabs/firstdel',
                        // multi_url: 'planmanagement/plantabs/firstmulti',
                        // table: 'plan_acar',
                    },

                    toolbar: '#toolbar2',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,

                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: Fast.lang('Id'), operate: false},
                            {field: 'financial_name', title: __('所属平台')},
                            {field: 'admin.nickname', title: __('所属销售'), formatter: Controller.api.formatter.sales},
                            {field: 'username', title: __('Username')},
                            {field: 'phone', title: __('Phone')},
                            {field: 'id_card', title: __('身份证号')},
                            {field: 'city', title: __('身份证地址'), operate: false},
                            {field: 'plansecond.newpayment', title: __('首付(元)'), operate: false},
                            {field: 'plansecond.monthlypaymen', title: __('月供(元)'), operate: false},
                            {field: 'plansecond.periods', title: __('期数'), operate: false},
                            {field: 'plansecond.totalprices', title: __('总价(元)'), operate: false},
                            {field: 'plansecond.tailmoney', title: __('尾款(元)'), operate: false},
                            {field: 'plansecond.kilometres', title: __('里程数(公里)'), operate: false},
                            {field: 'bond', title: __('保证金(元)'), operate: false},
                            {field: 'customerdownpayment.openingbank', title: __('开户行')},
                            {field: 'customerdownpayment.bankcardnumber', title: __('银行卡卡号'), operate: false},
                            {field: 'customerdownpayment.totalmoney', title: __('车款总价(元)'), operate: false},
                            {field: 'customerdownpayment.downpayment', title: __('首期款(元)'), operate: false},
                            {field: 'customerdownpayment.moneyreceived', title: __('实收金额(元)'), operate: false},
                            {field: 'customerdownpayment.marginmoney', title: __('差额(元)'), operate: false},
                            {field: 'customerdownpayment.gatheringaccount', title: __('收款账户'), operate: false},
                            {field: 'customerdownpayment.decorate', title: __('装饰'), operate: false},
                            {field: 'customerdownpayment.note', title: __('票据号备注'), operate: false},
                            {field: 'customerdownpayment.makemoney_status', title: __('是否打款'),formatter:Controller.api.formatter.status,searchList:{'1':'已打款','2':'未打款'}},


                            {
                                field: 'operate', title: __('Operate'), table: usedCar,

                                events: Controller.api.operate,
                                formatter: Table.api.formatter.operate
                            }
                        ]
                    ]
                });
                // 为表格1绑定事件
                Table.api.bindevent(usedCar);
            },
            /**
             * 租车定金
             */
            rent_car: function () {
                // 表格1
                var rentCar = $("#rentCar");
                rentCar.on('load-success.bs.table', function (e, data) {
                    $('#new-customer').text(data.total);

                })
                rentCar.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-editone").data("area", ["70%", "70%"]);
                });

                //统计
                total(rentCar,$('#total-rent'));

                // 初始化表格
                rentCar.bootstrapTable({
                    url: 'backoffice/Depositmanage/rent_car',
                    extend: {
                        // index_url: 'plan/planacar/index',
                        // add_url: 'planmanagement/plantabs/firstadd',
                        edit_url: 'backoffice/Depositmanage/edit_rent',
                        // del_url: 'planmanagement/plantabs/firstdel',
                        // multi_url: 'planmanagement/plantabs/firstmulti',
                        // table: 'plan_acar',
                    },

                    toolbar: '#toolbar3',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,

                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: Fast.lang('Id'), operate: false},
                            // {field: 'financial_name', title: __('所属平台')},
                            {field: 'admin.nickname', title: __('所属销售'), formatter: Controller.api.formatter.sales},
                            {field: 'username', title: __('Username')},
                            {field: 'phone', title: __('Phone')},
                            {field: 'id_card', title: __('身份证号')},
                            // {field: 'city', title: __('身份证地址'), operate: false},
                            {field: 'cash_pledge', title: __('押金(元)'), operate: false},
                            {field: 'rental_price', title: __('租金(元)'), operate: false},
                            {field: 'tenancy_term', title: __('租期(月)'), operate: false},
                            {field: 'bond', title: __('保证金(元)'), operate: false},
                            {field: 'customerdownpayment.openingbank', title: __('开户行')},
                            {field: 'customerdownpayment.bankcardnumber', title: __('银行卡卡号'), operate: false},
                            {field: 'customerdownpayment.totalmoney', title: __('车款总价(元)'), operate: false},
                            {field: 'customerdownpayment.downpayment', title: __('首期款(元)'), operate: false},
                            {field: 'customerdownpayment.moneyreceived', title: __('实收金额(元)'), operate: false},
                            {field: 'customerdownpayment.marginmoney', title: __('差额(元)'), operate: false},
                            {field: 'customerdownpayment.gatheringaccount', title: __('收款账户'), operate: false},
                            {field: 'customerdownpayment.decorate', title: __('装饰'), operate: false},
                            {field: 'customerdownpayment.note', title: __('票据号备注'), operate: false},
                            {field: 'customerdownpayment.makemoney_status', title: __('是否打款'),formatter:Controller.api.formatter.status,searchList:{'1':'已打款','2':'未打款'}},

                            {
                                field: 'operate', title: __('Operate'), table: rentCar,

                                events: Controller.api.operate,
                                formatter: Table.api.formatter.operate
                            }
                        ]
                    ]
                });
                // 为表格1绑定事件
                Table.api.bindevent(rentCar);
            },
            /**
             * 全款车定金
             */
            full_car: function () {
                // 表格1
                var fullCar = $("#fullCar");
                fullCar.on('load-success.bs.table', function (e, data) {
                    $('#new-customer').text(data.total);

                })
                fullCar.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-editone").data("area", ["70%", "70%"]);
                });

                //统计
                total(fullCar,$('#total-full'));

                // 初始化表格
                fullCar.bootstrapTable({
                    url: 'backoffice/Depositmanage/full_car',
                    extend: {
                        // index_url: 'plan/planacar/index',
                        // add_url: 'planmanagement/plantabs/firstadd',
                        edit_url: 'backoffice/Depositmanage/edit_full',
                        // del_url: 'planmanagement/plantabs/firstdel',
                        // multi_url: 'planmanagement/plantabs/firstmulti',
                        // table: 'plan_acar',
                    },

                    toolbar: '#toolbar4',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,

                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: Fast.lang('Id'), operate: false},
                            {field: 'financial_name', title: __('所属平台')},
                            {field: 'admin.nickname', title: __('所属销售'), formatter: Controller.api.formatter.sales},
                            {field: 'username', title: __('Username')},
                            {field: 'phone', title: __('Phone')},
                            {field: 'id_card', title: __('身份证号')},
                            {field: 'city', title: __('身份证地址'), operate: false},
                            {field: 'planfull.full_total_price', title: __('全款总价(元)'), operate: false},
                            {field: 'bond', title: __('保证金(元)'), operate: false},
                            {field: 'customerdownpayment.openingbank', title: __('开户行')},
                            {field: 'customerdownpayment.bankcardnumber', title: __('银行卡卡号'), operate: false},
                            {field: 'customerdownpayment.totalmoney', title: __('车款总价(元)'), operate: false},
                            {field: 'customerdownpayment.downpayment', title: __('首期款(元)'), operate: false},
                            {field: 'customerdownpayment.moneyreceived', title: __('实收金额(元)'), operate: false},
                            {field: 'customerdownpayment.marginmoney', title: __('差额(元)'), operate: false},
                            {field: 'customerdownpayment.gatheringaccount', title: __('收款账户'), operate: false},
                            {field: 'customerdownpayment.decorate', title: __('装饰'), operate: false},
                            {field: 'customerdownpayment.note', title: __('票据号备注'), operate: false},
                            {field: 'customerdownpayment.makemoney_status', title: __('是否打款'),formatter:Controller.api.formatter.status,searchList:{'1':'已打款','2':'未打款'}},

                            {
                                field: 'operate', title: __('Operate'), table: fullCar,

                                events: Controller.api.operate,
                                formatter: Table.api.formatter.operate
                            }
                        ]
                    ]
                });
                // 为表格1绑定事件
                Table.api.bindevent(fullCar);
            },
        },
        add: function () {
            Controller.api.bindevent();

        },
        edit: function () {
            Controller.api.bindevent();
        },
        edit_used:function () {
            Controller.api.bindevent();
        },
        edit_rent:function () {
            Controller.api.bindevent();
        },
        edit_full:function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                $(document).on('click', "input[name='row[ismenu]']", function () {
                    var name = $("input[name='row[name]']");
                    name.prop("placeholder", $(this).val() == 1 ? name.data("placeholder-menu") : name.data("placeholder-node"));
                });
                $("input[name='row[ismenu]']:checked").trigger("click");
                Form.api.bindevent($("form[role=form]"));
            },
            operate:{
                'click .btn-editone': function (e, value, row, index) {
                    e.stopPropagation();
                    e.preventDefault();
                    var table = $(this).closest('table');
                    var options = table.bootstrapTable('getOptions');
                    var ids = row[options.pk];
                    row = $.extend({}, row ? row : {}, {ids: ids});
                    var url = options.extend.edit_url;
                    Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
                },
            },
            formatter: {
                operate: function (value, row, index) {

                    var table = this.table;
                    // 操作配置
                    var options = table ? table.bootstrapTable('getOptions') : {};
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);


                    return Table.api.buttonlink(this, buttons, value, row, index, 'operate');
                },

                sales: function (value, row, index) {
                    if (value) {
                        row.admin.avatar = "https://static.aicheyide.com" + row.admin.avatar;
                    }
                    return value != null ? "<img src=" + row.admin.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' + value : value;

                },
                status: function (value, row, index) {

                    if(value){
                        value==1? value='已打款':value='未打款';

                    }else{
                        return value;
                    }
                    var custom = {'已打款': 'success',  '未打款': 'danger'};
                    if (typeof this.custom !== 'undefined') {
                        custom = $.extend(custom, this.custom);
                    }
                    this.custom = custom;
                    this.icon = 'fa fa-circle';
                    return Table.api.formatter.normal.call(this, value, row, index);
                },
            }
        }

    };

    /**
     * 表格加载完成统计条数
     * @param table
     * @param obj
     */
    function total(table,obj) {
        table.on('load-success.bs.table', function (e, data) {
            obj.text(data.total);

        })
    }
    return Controller;
});
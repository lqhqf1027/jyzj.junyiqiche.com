define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

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
             * 正在出租
             */
            being_rented: function () {
                var table = $("#beingRented");

                table.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-back_car").data("area", ["80%", "80%"]);
                    $(".btn-rentalDetails").data("area", ["95%", "95%"]);
                });
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "快速搜索：客户姓名，车牌号";};
                // 初始化表格
                table.bootstrapTable({
                    url: 'rentcar/Rentcarscustomer/being_rented',
                    pk: 'id',
                    sortName: 'id',
                    toolbar: '#toolbar1',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'), operate: false},
                            {field: 'order_no', title: __('订单编号')},
                            {field: 'username', title: __('客户姓名')},
                            {field: 'carrentalmodelsinfo.licenseplatenumber', title: __('车牌号')},
                            {field: 'carrentalmodelsinfo.vin', title: __('车架号')},
                            {field: 'carrentalmodelsinfo.engine_no', title: __('发动机号')},

                            {field: 'models.name', title: __('租车型号')},
                            {field: 'admin.nickname', title: __('销售员'), formatter: Controller.api.formatter.sales},


                            {field: 'phone', title: __('手机号')},
                            {field: 'id_card', title: __('身份证号')},

                            {field: 'cash_pledge', title: __('押金（元）'), operate: false},
                            {field: 'rental_price', title: __('租金（元）'), operate: false},
                            {field: 'tenancy_term', title: __('租期（月）'), operate: false,formatter:Controller.api.formatter.tenancy_term},
                            {
                                field: 'car_backtime',
                                title: __('应退租时间'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Controller.api.formatter.datetime,
                                datetimeFormat: 'YYYY-MM-DD'
                            },
                            {field: 'customer_information_note', title: __('备注信息'), operate: false},
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
                                field: 'operate', title: __('Operate'), table: table,

                                events: Controller.api.events.operate,

                                formatter: Controller.api.formatter.operate

                            }
                        ]
                    ]
                });


                // 为表格绑定事件
                Table.api.bindevent(table);

                table.on('load-success.bs.table', function (e, data) {

                    $('#using_total').text(data.total);
                })
            },
            /**
             * 已退租
             */
            retiring: function () {
                var retirings = $("#retirings");

                retirings.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-back_car").data("area", ["80%", "80%"]);
                    $(".btn-rentalDetails").data("area", ["95%", "95%"]);
                });
                // 初始化表格
                retirings.bootstrapTable({
                    url: 'rentcar/Rentcarscustomer/retiring',
                    pk: 'id',
                    sortName: 'id',
                    toolbar: '#toolbar2',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'), operate: false},
                            {field: 'carrentalmodelsinfo.licenseplatenumber', title: __('车牌号')},
                            {field: 'order_no', title: __('订单编号')},
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
                            {field: 'models.name', title: __('租车型号')},
                            {field: 'admin.nickname', title: __('销售员'), formatter: Controller.api.formatter.sales},

                            {field: 'username', title: __('客户姓名')},
                            {field: 'phone', title: __('手机号')},
                            {field: 'id_card', title: __('身份证号')},

                            {
                                field: 'car_backtime',
                                title: __('应退租时间'),
                                operate: false,
                                addclass: 'datetimerange',
                                formatter: Controller.api.formatter.datetime,
                                datetimeFormat: 'YYYY-MM-DD'
                            },
                            {
                                field: 'actual_backtime',
                                title: __('实际退租时间'),
                                operate: false,
                                addclass: 'datetimerange',
                                formatter: Controller.api.formatter.datetime,
                                datetimeFormat: 'YYYY-MM-DD'
                            },
                            {field: 'customer_information_note', title: __('备注信息'), operate: false},

                            {
                                field: 'operate', title: __('Operate'), table: retirings,

                                events: Controller.api.events.operate,

                                formatter: Controller.api.formatter.operate

                            }
                        ]
                    ]
                });


                // 为表格绑定事件
                Table.api.bindevent(retirings);

                retirings.on('load-success.bs.table', function (e, data) {
                    $('#back_total').text(data.total);

                })
            }
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        back_car: function () {
            Controller.api.bindevent();
        },
        delivery: function () {
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
            formatter: {
                tenancy_term:function (value, row, index) {
                  if(!value){
                      return '-';
                  }

                  if(!row.renewal_month){
                      return value;
                  }else{
                      row.renewal_month = JSON.parse(row.renewal_month);

                      for (var i in row.renewal_month){
                          value+='+<span class="text-warning">'+row.renewal_month[i]+'</span>';
                      }

                      return value;
                  }


                },
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

                    return value==null?value : "<img src=" + Config.cdn_url+row.admin.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' +row.admin.department+' - '+value;
                },
                operate: function (value, row, index) {
                    var table = this.table;
                    // 操作配置
                    var options = table ? table.bootstrapTable('getOptions') : {};
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);
                    if (row.review_the_data == 'for_the_car') {
                        buttons.push(
                            {
                                name: 'edits',
                                text: '续租',
                                icon: 'fa fa-pencil-square-o',
                                title: __('编辑'),
                                classname: 'btn btn-xs btn-success btn-editone',
                                url: 'rentcar/Rentcarscustomer/edit'
                            }
                        );
                    }


                    buttons.push(
                        {
                            name: 'rentalDetails',
                            text: '查看详细资料',
                            title: '查看订单详细资料',
                            icon: 'fa fa-eye',
                            classname: 'btn btn-xs btn-primary btn-rentalDetails',
                            callback: function (data) {
                                console.log(data)
                            }
                        }
                    );
                    if (row.review_the_data == 'for_the_car') {
                        buttons.push(
                            {
                                name: 'back',
                                text: '退车',
                                icon: 'fa fa-sign-out',
                                title: __('退车'),
                                classname: 'btn btn-xs btn-warning btn-back_car',
                            }
                        );
                    }


                    return Table.api.buttonlink(this, buttons, value, row, index, 'operate');
                }
            },
            events: {
                operate: {

                    'click .btn-editone': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'rentcar/Rentcarscustomer/edit';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
                    },
                    /**
                     * 退车
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-back_car': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        if(!row.car_backtime){
                            layer.msg('请先编辑退车时间，再点击退车');
                            return;
                        }
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'rentcar/Rentcarscustomer/back_car';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('退车'), $(this).data() || {});
                    },
                    /**
                     * 详情
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-rentalDetails': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'Sharedetailsdatas/rental_car_share_data';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('查看详情'), $(this).data() || {});
                    },
                }
            },

        }
    };
    return Controller;
});
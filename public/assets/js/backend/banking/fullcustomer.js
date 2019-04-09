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

        change_platform: function () {

            Table.api.init({});
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                Fast.api.close(data);
                Toastr.success("成功");
            }, function (data, ret) {
                Toastr.success("失败");

            });
        },
        batch_change_platform: function () {
            Table.api.init({});
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                Fast.api.close(data);
                Toastr.success("成功");
            }, function (data, ret) {
                Toastr.success("失败");

            });
        },

        table: {

            /**
             * 全款（新车）
             */
            new_car: function () {
                // 表格1
                var newCar = $("#newCar");
                newCar.on('load-success.bs.table', function (e, data) {
                    // var arr = data.rows;
                    //
                    // Controller.merge(arr, $("#newCar"), $('#new_car'));
                    //
                    //
                    $('#total-new').text(data.total);
                });

                newCar.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-changePlatform").data("area", ["30%", "30%"]);

                    $(".btn-editone").data("area", ["80%", "80%"]);
                    $(".btn-details").data("area", ["95%", "95%"]);
                    $(".btn-edit").data("area", ["80%", "80%"]);
                    $(".btn-loan").data("area", ["40%", "40%"]);
                    });
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "快速搜索:车牌号";};

                // 初始化表格
                newCar.bootstrapTable({
                    url: "banking/Fullcustomer/new_car",
                    extend: {
                        index_url: 'order/salesorder/index',
                        add_url: 'order/salesorder/add',
                        edit_url: 'banking/Fullcustomer/edit',
                        multi_url: 'order/salesorder/multi',
                        table: 'full_parment_order',
                    },
                    toolbar: '#toolbar1',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('ID'),operate: false},
                            {
                                field: 'mortgage.lending_date',
                                title: __('放款日期'),
                                formatter: Controller.api.formatter.Loan,
                                operate: false
                            },
                            {field: 'newinventory.household', title: __('开户公司名'),operate: false},
                            {field: 'mortgage.bank_card', title: __('扣款卡号'),operate: false},
                            {field: 'username', title: __('Username')},
                            {field: 'id_card', title: __('身份证号'),operate: false},
                            {field: 'detailed_address', title: __('身份证地址'),operate: false},
                            {field: 'phone', title: __('电话号码'),operate: false},
                            {field: 'models.name', title: __('车型'),operate: false},
                            {field: 'mortgage.invoice_monney', title: __('开票金额(元)'),operate: false},
                            {field: 'mortgage.registration_code', title: __('登记编码'),operate: false},
                            {field: 'mortgage.tax', title: __('购置税(元)'),operate: false},
                            {field: 'mortgage.business_risks', title: __('商业险(元)'),operate: false},
                            {field: 'mortgage.insurance', title: __('交强险(元)'),operate: false},
                            {field: 'newinventory.licensenumber', title: __('车牌号')},
                            {field: 'newinventory.frame_number', title: __('车架号')},
                            {
                                field: 'createtime',
                                title: __('订车时间'),
                                formatter: Controller.api.formatter.datetime,
                                operate: false
                            },
                            {
                                field: 'delivery_datetime',
                                title: __('提车时间'),
                                formatter: Controller.api.formatter.datetime,
                                operate: false
                            },
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: newCar,
                                events: Controller.api.events.operate,
                                formatter: Controller.api.formatter.operate,
                                buttons: [
                                    {
                                        name: 'edit',
                                        text: __('编辑'),
                                        icon: 'fa fa-pencil',
                                        title: '编辑',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-success btn-editone',
                                    },
                                    {
                                        name: 'change',
                                        text: __('更改平台'),
                                        icon: 'fa fa-arrows',
                                        title: __('更改平台'),
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-danger btn-changePlatform',
                                    },
                                    {
                                        
                                        name: 'details', 
                                        text: '查看详细资料', 
                                        title: '查看订单详细资料', 
                                        icon: 'fa fa-eye', 
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-primary btn-dialog btn-details',
                                        url: 'Sharedetailsdatas/new_car_share_data'
                                    },

                                ]
                            }
                        ]
                    ]
                });
                // 为表格1绑定事件
                Table.api.bindevent(newCar);

                $(document).on('click', '.btn-loan', function () {

                    var url = "banking/Fullcustomer/loan";
                    platform(newCar, url, '放款日期')

                });

                $(document).on('click', '.btn-platform', function () {
                    var url = "banking/Fullcustomer/batch_change_platform";



                    platform(newCar, url, '更换平台')

                })

            },
            /**
             * 南商行
             */
            yue_da_car: function () {
                // 表格2
                var yueDaCar = $("#yueDaCar");
                yueDaCar.on('load-success.bs.table', function (e, data) {

                    // var arr = data.rows;
                    //
                    //
                    // Controller.merge(arr, $("#yueDaCar"), $('#yue_da_car'))

                    $('#total-yueda').text(data.total);

                });

                yueDaCar.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-changePlatform").data("area", ["30%", "30%"]);
                    $(".btn-editone").data("area", ["80%", "80%"]);
                    $(".btn-edit").data("area", ["80%", "80%"]);
                    $(".btn-showOrderAndStock").data("area", ["90%", "90%"]);
                });
                // 初始化表格
                yueDaCar.bootstrapTable({
                    url: "banking/Fullcustomer/south_firm",
                    extend: {
                        index_url: 'order/salesorder/index',
                        add_url: 'order/salesorder/add',
                        edit_url: 'banking/exchangeplatformtabs/edit',
                        // del_url: 'order/salesorder/del',
                        multi_url: 'order/salesorder/multi',
                        table: 'sales_order',
                    },
                    toolbar: '#toolbar2',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('ID'),operate: false},
                            {
                                field: 'mortgage.lending_date',
                                title: __('放款日期'),
                                formatter: Controller.api.formatter.Loan,
                                operate: false
                            },
                            {field: 'newinventory.household', title: __('开户公司名'),operate: false},
                            {field: 'mortgage.bank_card', title: __('扣款卡号'),operate: false},
                            {field: 'username', title: __('Username')},
                            {field: 'id_card', title: __('身份证号'),operate: false},
                            {field: 'detailed_address', title: __('身份证地址'),operate: false},
                            {field: 'phone', title: __('电话号码'),operate: false},
                            {field: 'models.name', title: __('车型'),operate: false},
                            {field: 'mortgage.invoice_monney', title: __('开票金额(元)'),operate: false},
                            {field: 'mortgage.registration_code', title: __('登记编码'),operate: false},
                            {field: 'mortgage.tax', title: __('购置税(元)'),operate: false},
                            {field: 'mortgage.business_risks', title: __('商业险(元)'),operate: false},
                            {field: 'mortgage.insurance', title: __('交强险(元)'),operate: false},
                            {field: 'newinventory.licensenumber', title: __('车牌号')},
                            {field: 'newinventory.frame_number', title: __('车架号')},
                            {
                                field: 'createtime',
                                title: __('订车时间'),
                                formatter: Controller.api.formatter.datetime,
                                operate: false
                            },
                            {
                                field: 'delivery_datetime',
                                title: __('提车时间'),
                                formatter: Controller.api.formatter.datetime,
                                operate: false
                            },
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: yueDaCar,
                                events: Controller.api.events.operate,
                                formatter: Controller.api.formatter.operate,
                                buttons: [
                                    {
                                        name: 'edit',
                                        text: __('编辑'),
                                        icon: 'fa fa-pencil',
                                        title: '编辑',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-success btn-editone',
                                    },
                                    {
                                        name: 'change',
                                        text: __('更改平台'),
                                        icon: 'fa fa-arrows',
                                        title: __('更改平台'),
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-danger btn-changePlatform',
                                    },
                                    {

                                        name: 'details',
                                        text: '查看详细资料',
                                        title: '查看订单详细资料',
                                        icon: 'fa fa-eye',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-primary btn-dialog btn-showOrderAndStock',
                                        url: 'fullcar/vehicleinformation/show_order_and_stock',
                                    },

                                ]
                            }
                        ]
                    ]
                });
                // 为表格1绑定事件
                Table.api.bindevent(yueDaCar);

                $(document).on('click', '.btn-loan2', function () {

                    var url = "banking/Fullcustomer/loan";
                    platform(yueDaCar, url, '放款日期')

                });

                $(document).on('click', '.btn-platform2', function () {
                    var url = "banking/Fullcustomer/batch_change_platform";

                    platform(yueDaCar, url, '更换平台')

                })

            },
            /**
             * 其他
             */
            other_car: function () {
                // 表格3
                var otherCar = $("#otherCar");
                otherCar.on('load-success.bs.table', function (e, data) {
                    // var arr = data.rows;
                    // merge(arr, $("#otherCar"));
                    $('#total-other').text(data.total);

                    // Controller.merge(arr, $("#otherCar"), $('#other_car'))


                });

                otherCar.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-changePlatform").data("area", ["30%", "30%"]);
                    $(".btn-editone").data("area", ["80%", "80%"]);
                    $(".btn-edit").data("area", ["80%", "80%"]);
                });
                // 初始化表格
                otherCar.bootstrapTable({
                    url: "banking/Fullcustomer/other_car",
                    extend: {
                        index_url: 'order/salesorder/index',
                        add_url: 'order/salesorder/add',
                        edit_url: 'banking/exchangeplatformtabs/edit',
                        // del_url: 'order/salesorder/del',
                        multi_url: 'order/salesorder/multi',
                        table: 'sales_order',
                    },
                    toolbar: '#toolbar3',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('ID'),operate: false},
                            {
                                field: 'mortgage.lending_date',
                                title: __('放款日期'),
                                formatter: Controller.api.formatter.Loan,
                                operate: false
                            },
                            {field: 'newinventory.household', title: __('开户公司名'),operate: false},
                            {field: 'mortgage.bank_card', title: __('扣款卡号'),operate: false},
                            {field: 'username', title: __('Username')},
                            {field: 'id_card', title: __('身份证号'),operate: false},
                            {field: 'detailed_address', title: __('身份证地址'),operate: false},
                            {field: 'phone', title: __('电话号码'),operate: false},
                            {field: 'models.name', title: __('车型'),operate: false},
                            {field: 'mortgage.invoice_monney', title: __('开票金额(元)'),operate: false},
                            {field: 'mortgage.registration_code', title: __('登记编码'),operate: false},
                            {field: 'mortgage.tax', title: __('购置税(元)'),operate: false},
                            {field: 'mortgage.business_risks', title: __('商业险(元)'),operate: false},
                            {field: 'mortgage.insurance', title: __('交强险(元)'),operate: false},
                            {field: 'newinventory.licensenumber', title: __('车牌号')},
                            {field: 'newinventory.frame_number', title: __('车架号')},
                            {
                                field: 'createtime',
                                title: __('订车时间'),
                                formatter: Controller.api.formatter.datetime,
                                operate: false
                            },
                            {
                                field: 'delivery_datetime',
                                title: __('提车时间'),
                                formatter: Controller.api.formatter.datetime,
                                operate: false
                            },
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: otherCar,
                                events: Controller.api.events.operate,
                                formatter: Controller.api.formatter.operate,
                                buttons: [
                                    {
                                        name: 'edit',
                                        text: __('编辑'),
                                        icon: 'fa fa-pencil',
                                        title: '编辑',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-success btn-editone',
                                    },
                                    {
                                        name: 'change',
                                        text: __('更改平台'),
                                        icon: 'fa fa-arrows',
                                        title: __('更改平台'),
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-danger btn-changePlatform',
                                    },
                                    {

                                        name: 'details',
                                        text: '查看详细资料',
                                        title: '查看订单详细资料',
                                        icon: 'fa fa-eye',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-primary btn-dialog btn-showOrderAndStock',
                                        url: 'fullcar/vehicleinformation/show_order_and_stock',
                                    },

                                ]
                            }
                        ]
                    ]
                });
                // 为表格1绑定事件
                Table.api.bindevent(otherCar);

                $(document).on('click', '.btn-loan3', function () {

                    var url = "banking/Fullcustomer/loan";
                    platform(otherCar, url, '放款日期')

                });

                $(document).on('click', '.btn-platform3', function () {
                    var url = "banking/Fullcustomer/batch_change_platform";

                    platform(otherCar, url, '更换平台')

                })

            },

        },
        add: function () {
            Controller.api.bindevent();

        },
        edit: function () {
            Controller.api.bindevent();
        },
        loan: function () {
            Controller.api.bindevent();
        },
        /**
         *  合并
         * @param arr
         * @param obj
         * @param parentDom
         */
        merge: function (arr, obj, parentDom) {
            var hash = [];
            var data_arr = [];
            for (var i in arr) {



                if (hash.indexOf(arr[i]['mortgage']['lending_date']) == -1 ) {

                    hash.push(arr[i]['mortgage']['lending_date']);

                    data_arr.push([i, arr[i]['mortgage']['lending_date'], 0]);
                }


            }


            for (var i in arr) {
                for (var j in data_arr) {
                    if (arr[i]['mortgage']['lending_date'] == data_arr[j][1]) {
                        data_arr[j][2]++;
                    }
                }
            }


            for (var i in data_arr) {

                obj.bootstrapTable("mergeCells", {
                    index: data_arr[i][0],
                    field: 'mortgage.lending_date',
                    rowspan: data_arr[i][2]
                });

                var td = $(obj).find("tr[data-index=" + data_arr[i][0] + "]").find("td");

                if(data_arr[i][1]!=null){
                    i % 2 == 0 ? td.eq(2).css({"background-color": "#fff"}) : td.eq(2).css({"background-color": "#ddd"});
                }


            }
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
                operate: function (value, row, index) {

                    var table = this.table;
                    // 操作配置
                    var options = table ? table.bootstrapTable('getOptions') : {};
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);


                    return Table.api.buttonlink(this, buttons, value, row, index, 'operate');
                },
                Loan: function (value, row, index) {

                    if (row.mortgage.firm_stage && value) {
                        return value + " " + row.mortgage.firm_stage;
                    } else {

                       return value? value:" - ";
                    }


                },
                datetime:function (value, row, index) {

                    if(value){
                        return timestampToTime(value);
                    }

                    function timestampToTime(timestamp) {
                        var date = new Date(timestamp * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
                        var Y = date.getFullYear() + '-';
                        var M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
                        var D = date.getDate()<10? '0'+date.getDate():date.getDate();

                        return Y+M+D;
                    }

                }
            },
            events: {
                operate: {
                    'click .btn-editone': function (e, value, row, index) {  //编辑
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'banking/Fullcustomer/edit';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('编辑'), $(this).data() || {});

                    },
                    /**
                     * 更改平台
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-changePlatform': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'banking/Fullcustomer/change_platform';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('更改平台'), $(this).data() || {});
                    },

                }
            },


        }

    };

    /**
     *
     * @param arr
     * @param obj
     */

    function platform(tab, url, title) {
        var table = $(this).closest(table);

        var ids = Table.api.selectedids(tab);

        row = {ids: ids};

        var options = {
            shadeClose: false,
            shade: [0.3, '#393D49'],
            area: ['40%', '40%'],
            callback: function (value) {

            }
        };

        Fast.api.open(Table.api.replaceurl(url, row, table), __(title),options);
    }

    return Controller;
});
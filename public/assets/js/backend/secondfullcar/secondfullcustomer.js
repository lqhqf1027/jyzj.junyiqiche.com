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
            })
        },

        choose_stock: function () {

            // $(".btn-add").data("area", ["300px","200px"]);
            Table.api.init({});
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                // console.log(data);

                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                Fast.api.close(data);
                // console.log(data);
                Toastr.success("成功");


            }, function (data, ret) {
                Toastr.success("失败");

            });
            // Controller.api.bindevent();
            // console.log(Config.id);


        },

        table: {

            /**
             * 待提车
             */
            prepare_lift_car: function () {
                // 表格1
                var prepareLiftCar = $("#prepareLiftCar");
                prepareLiftCar.on('load-success.bs.table', function (e, data) {
                    $('#badge_prepare').text(data.total);

                });
                prepareLiftCar.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-chooseStock").data("area", ["70%", "70%"]);
                    $(".btn-showOrder").data("area", ["95%", "95%"]);
                });
                // 初始化表格
                prepareLiftCar.bootstrapTable({
                    url: "secondfullcar/secondfullcustomer/prepare_lift_car",
                    extend: {

                        table: 'second_full_order',
                    },
                    toolbar: '#toolbar1',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'),operate:false},
                            {field: 'order_no', title: __('订单编号')},
                            {field: 'models.name', title: __('销售车型')},
                            {field: 'plansecondfull.licenseplatenumber', title: __('车牌号')},
                            {field: 'admin.nickname', title: __('销售员'),formatter:Controller.api.formatter.sales},
                            {field: 'username', title: __('Username')},
                            {field: 'phone', title: __('电话号码')},
                            {field: 'id_card', title: __('身份证号')},
                            {field: 'plansecondfull.totalprices', title: __('车款总价（元）')},
                            
                            {
                                field: 'createtime',
                                title: __('订车时间'),
                                formatter: Table.api.formatter.datetime,
                                operate: false
                            },
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: prepareLiftCar,
                                events: Table.api.events.operate,
                                formatter: Table.api.formatter.operate,
                                buttons: [
                                    {
                                        name: 'detail',
                                        text: '选择库存车',
                                        title: '选择库存车',
                                        icon: 'fa fa-arrows',
                                        classname: 'btn btn-xs btn-danger btn-dialog btn-chooseStock',
                                        url: 'secondfullcar/secondfullcustomer/choose_stock',
                                    },
                                    {
                                        name: 'look',
                                        text: '查看客户详细资料',
                                        title: '查看客户详细资料',
                                        icon: 'fa fa-eye',
                                        classname: 'btn btn-xs btn-info btn-dialog btn-showOrder',
                                        url: 'Sharedetailsdatas/secondfull_car_share_data',
                                    }
                                ]
                            }
                        ]
                    ]
                });
                // 为表格1绑定事件
                Table.api.bindevent(prepareLiftCar);

                //销售推送
                // goeasy.subscribe({
                //     channel: 'demo-second_full_backoffice',
                //     onMessage: function (message) {
                //         Layer.alert('新消息：' + message.content, {icon: 0}, function (index) {
                //             Layer.close(index);
                //             $(".btn-refresh").trigger("click");
                //         });
                //
                //     }
                // });

                //内勤推送 --- 是否可以提车
                // goeasy.subscribe({
                //     channel: 'demo-secondfullcar_amount',
                //     onMessage: function (message) {
                //         Layer.alert('新消息：' + message.content, {icon: 0}, function (index) {
                //             Layer.close(index);
                //             $(".btn-refresh").trigger("click");
                //         });
                //
                //     }
                // });


            },
            /**
             * 已提车
             */
            already_lift_car: function () {

                // 表格2
                var alreadyLiftCar = $("#alreadyLiftCar");
                alreadyLiftCar.on('load-success.bs.table', function (e, data) {
                    $('#badge_already').text(data.total);

                });
                alreadyLiftCar.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-showOrderAndStock").data("area", ["95%", "95%"]);
                    $(".btn-editone").data("area", ["50%", "50%"]);
                });
                // 初始化表格
                alreadyLiftCar.bootstrapTable({
                    url: 'secondfullcar/secondfullcustomer/already_lift_car',
                    extend: {
                        // edit_url:'secondfullcar/secondfullcustomer/edit',
                        table: 'second_full_order',
                    },
                    toolbar: '#toolbar2',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'),operate:false},
                            {field: 'order_no', title: __('订单编号')},
                            {field: 'models.name', title: __('销售车型')},
                            {field: 'plansecondfull.licenseplatenumber', title: __('车牌号')},
                            {field: 'admin.nickname', title: __('销售员'),formatter:Controller.api.formatter.sales},
                            {field: 'username', title: __('Username')},
                            {field: 'phone', title: __('电话号码')},
                            {field: 'id_card', title: __('身份证号')},
                            {field: 'plansecondfull.totalprices', title: __('车款总价（元）'),operate:false},
                            
                            {
                                field: 'createtime',
                                title: __('订车时间'),
                                formatter: Table.api.formatter.datetime,
                                datetimeFormat:"YYYY-MM-DD",
                                operate: false
                            },
                            {
                                field: 'delivery_datetime',
                                title: __('提车时间'),
                                formatter: Table.api.formatter.datetime,
                                datetimeFormat:"YYYY-MM-DD",
                                operate: false
                            },
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: alreadyLiftCar,
                                events: Controller.api.events.operate,
                                formatter: Table.api.formatter.operate,
                                buttons: [
                                    {
                                        name: 'the_car', icon: 'fa fa-automobile', text: '已提车', extend: 'data-toggle="tooltip"', title: __('订单已完成，客户已提车'), classname: ' text-success ',

                                    },
                                    {
                                        name: 'edits',
                                        icon: 'fa fa-pencil',
                                        text:'编辑实际提车日期',
                                        title: __('编辑实际提车日期'),
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-success btn-editone',
                                        hidden:function (r) { 
                                            if(r.delivery_datetime!=null) return true;
                                        }

                                    },
                                    {
                                        name: 'look',
                                        text: '查看客户详细资料',
                                        title: '查看客户详细资料',
                                        icon: 'fa fa-eye',
                                        classname: 'btn btn-xs btn-info btn-dialog btn-showOrderAndStock',
                                        url: 'Sharedetailsdatas/secondfull_car_share_data',
                                    }
                                ]
                            }
                        ]
                    ]
                });
                // 为表格2绑定事件
                Table.api.bindevent(alreadyLiftCar);

                alreadyLiftCar.on('load-success.bs.table', function (e, data) {
                    $('#assigned-customer').text(data.total);

                })

            }

        },
        add: function () {
            Controller.api.bindevent();

        },
        edit: function () {
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
                operate: function (value, row, index) {

                    var table = this.table;
                    // 操作配置
                    var options = table ? table.bootstrapTable('getOptions') : {};
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);


                    return Table.api.buttonlink(this, buttons, value, row, index, 'operate');
                },
                sales:function (value, row, index) {

                    return value==null?value : "<img src=" + Config.cdn_url+row.admin.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' +row.admin.department+' - '+value;
                },
            },
            events: {
                operate: {
                    'click .btn-editone': function (e, value, row, index) {
                        // alert(1);return;
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'secondfullcar/secondfullcustomer/edit';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
                    },

                }
            },
        }

    };

    return Controller;
});
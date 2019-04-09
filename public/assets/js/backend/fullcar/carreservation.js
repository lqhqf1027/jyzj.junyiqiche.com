define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var goeasy = new GoEasy({
        appkey: 'BC-04084660ffb34fd692a9bd1a40d7b6c2'
    });

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

            // $('ul.nav-tabs li a[data-toggle="tab"]').each(function () {
            //     $(this).trigger("shown.bs.tab");
            // })
        },

        table: {

            /**
             * 全款待确认
             */
            fullcar_waitconfirm: function () {
                // 全款待确认
                var fullcarWaitconfirm = $("#fullcarWaitconfirm");
                
                // 初始化表格
                fullcarWaitconfirm.bootstrapTable({
                    url: "fullcar/carreservation/fullcarWaitconfirm",
                    extend: {
                        index_url: 'order/salesorder/index',
                        add_url: 'order/salesorder/add',
                        // edit_url: 'order/salesorder/edit',
                        // del_url: 'order/salesorder/del',
                        multi_url: 'order/salesorder/multi',
                        table: 'full_parment_order',
                    },
                    toolbar: '#toolbar1',
                    pk: 'id',
                    sortName: 'id',
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: '编号'},
                            {field: 'createtime', title: __('订车日期')},
                            {field: 'admin.nickname', title: __('销售员')},
                            {field: 'models.name', title: __('订车车型')},
                            {field: 'planfull.full_total_price', title: __('车款总价(元)')},
                            {field: 'username', title: __('客户姓名')},
                            {field: 'id_card', title: __('身份证号')},
                            {field: 'phone', title: __('联系电话')},
                            {field: 'city', title: __('居住地址')},
                            {field: 'detailed_address', title: __('详细地址')},
                            

                            {
                                field: 'operate', title: __('Operate'), table: fullcarWaitconfirm,
                                buttons: [
                                    {
                                        name: 'fullcarWaitconfirm',
                                        text: '是否可以提车',
                                        icon: 'fa fa-share',
                                        title: __('是否可以提车'),
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-success btn-fullcarWaitconfirm',

                                    }
                                ],

                                events: Controller.api.events.operate,
                                formatter: Controller.api.formatter.operate
                            }
                        ]
                    ]
                });
                // 为表格1绑定事件
                Table.api.bindevent(fullcarWaitconfirm);

                //数据实时统计
                fullcarWaitconfirm.on('load-success.bs.table',function(e,data){ 

                    var fullcarWaitconfirm =  $('#badge_fullcar_waitconfirm').text(data.total); 
                    fullcarWaitconfirm = parseInt($('#badge_fullcar_waitconfirm').text());
                    
                   
                })

                //通过
                // goeasy.subscribe({
                //     channel: 'demo-fullcar_amount',
                //     onMessage: function(message){
                //         Layer.alert('新消息：'+message.content,{ icon:0},function(index){
                //             Layer.close(index);
                //             $(".btn-refresh").trigger("click");
                //         });
                //
                //     }
                // });

            },
            /**
             * 全款已确认
             */
            fullcar_confirm: function () {

                // 全款确认
                var fullcarConfirm = $("#fullcarConfirm");
                
                // 初始化表格
                fullcarConfirm.bootstrapTable({
                    url: "fullcar/carreservation/fullcarConfirm",
                    extend: {
                        index_url: 'order/salesorder/index',
                        add_url: 'order/salesorder/add',
                        // edit_url: 'order/salesorder/edit',
                        // del_url: 'order/salesorder/del',
                        multi_url: 'order/salesorder/multi',
                        table: 'full_parment_order',
                    },
                    toolbar: '#toolbar2',
                    pk: 'id',
                    sortName: 'id',
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: '编号'},
                            {field: 'createtime', title: __('订车日期')},
                            {field: 'admin.nickname', title: __('销售员')},
                            {field: 'models.name', title: __('订车车型')},
                            {field: 'planfull.full_total_price', title: __('车款总价(元)')},
                            {field: 'username', title: __('客户姓名')},
                            {field: 'id_card', title: __('身份证号')},
                            {field: 'phone', title: __('联系电话')},
                            {field: 'city', title: __('居住地址')},
                            {field: 'detailed_address', title: __('详细地址')},
                            {field: 'operate', title: __('Operate'), table: fullcarConfirm, 
                                buttons: [
                                    {

                                        name: 'for_the_car', icon: 'fa fa-automobile', text: '已提车', extend: 'data-toggle="tooltip"', title: __('订单已完成，客户已提车'), classname: ' text-success ',
                                        hidden: function (row) {  /**已提车 */
                                            if (row.review_the_data == 'for_the_car') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                           
                                        }
                                    },
                                    {
                                        name: 'is_reviewing_pass', icon: 'fa fa-check-circle', text: '车管已通过，可以进行提车', classname: ' text-info ',
                                        hidden: function (row) {  /**车管已通过，可以进行提车 */
                                            if (row.review_the_data == 'is_reviewing_pass') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }

                                        }
                                    },

                                ],
                                    events: Controller.api.events.operate,
                                    
                                    formatter: Controller.api.formatter.operate
                                
                                }

                        ]
                    ]
                });
                // 为表格2绑定事件
                Table.api.bindevent(fullcarConfirm);

                //数据实时统计
                fullcarConfirm.on('load-success.bs.table',function(e,data){ 

                    var fullcarConfirm =  $('#badge_fullcar_confirm').text(data.total); 
                    fullcarConfirm = parseInt($('#badge_fullcar_confirm').text());
                    
                   
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
            },
            events: {
                operate: {
                    /**
                     * 确认提车
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-fullcarWaitconfirm': function (e, value, row, index) {
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
                            __('确定用户可以进行提车?'),
                            {icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true},
                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');

                                Fast.api.ajax({
                                        url: "secondhandcar/carreservation/setAudit",
                                        data: {id: row[options.pk]},

                                    }, function (data, ret) {
                                        // console.log(data);

                                        // Toastr.success("成功");
                                        Layer.close(index);
                                        table.bootstrapTable('refresh');

                                    }, function (data, ret) {

                                        console.log(ret);

                                    },
                                )

                            }
                        );
                    }
                }
            }
        }

    };



    return Controller;
});


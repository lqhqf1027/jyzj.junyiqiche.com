define(['jquery', 'bootstrap', 'backend', 'table', 'form','bootstrap-datetimepicker'], function ($, undefined, Backend, Table, Form) {

    // var goeasy = new GoEasy({
    //     appkey: 'BC-04084660ffb34fd692a9bd1a40d7b6c2'
    // });

    var liActive;
    var Controller = {

        index: function () {

            // 初始化表格参数配置
            Table.api.init({});

            //绑定事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {

                var panel = $($(this).attr("href"));
                // console.log(panel);
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
            // $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");

            $('ul.nav-tabs li a[data-toggle="tab"]').each(function () {
                $(this).trigger("shown.bs.tab");
            });

        },
        //批量反馈回调方法
        batchfeedback: function () {
            Table.api.init({});
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据

                Fast.api.close(data);//这里是重点

                // Toastr.success("成功");//这个可有可无
            }, function (data, ret) {
                Toastr.success("失败");
            });
        },

        table: {
            /**
             * 新客户
             */
            new_customer: function () {
                liActive = $('.nav-tabs').find('li.active .tabs-text').text();
                // 表格1
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                    return "快速搜索客户姓名";
                };

                var newCustomer = $("#newCustomer");
                newCustomer.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-newSalesList").data("area", ["90%", "90%"]);

                });
                // 初始化表格
                newCustomer.bootstrapTable({
                    url: 'salesmanagement/Customerlisttabs/newCustomer',
                    extend: {
                        index_url: 'customer/customerresource/index',
                        add_url: 'salesmanagement/customerlisttabs/add',
                        edit_url: 'salesmanagement/customerlisttabs/edit',
                        del_url: 'customer/customerresource/del',
                        multi_url: 'customer/customerresource/multi',
                        give_up_url: 'salesmanagement/customerlisttabs/give_up',
                        table: 'customer_resource',
                    },
                    toolbar: '#toolbar1',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: Fast.lang('Id'), operate: false},
                            {field: 'platform.name', title: __('客户来源')},
                            {
                                field: 'admin.nickname', title: __('Sales_id'), formatter: function (v, r, i) {
                                    return v != null ? "<img src=" + r.admin.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' + r.admin.department+' - '+v : v;
                                }
                            },
                            {field: 'username', title: __('客户姓名')},
                            {field: 'phone', title: __('Phone')},
                            {field: 'invalidtime', title: __('号码失效时间'),formatter:function (v,r,i) {
                                return Controller.numberFailure(v);
                            }},
                            {field: 'jobs', title: __('求职岗位')},
                            {
                                field: 'distributsaletime',
                                title: __('Distributsaletime'),
                                operate: false,
                                formatter: function (v, r, i) {
                                    if (v != null) {
                                        return Controller.getDateDiff(v) + '<br>' + '(' + Controller.getLocalTime(v) + ')';
                                    }

                                },
                                // datetimeFormat:'YYY-MM-DD'
                            },

                            {
                                field: 'operate', title: __('Operate'), table: newCustomer,
                                buttons: [
                                    {
                                        name: 'detail',
                                        text: '新增销售单',
                                        title: '新增销售单',
                                        icon: 'fa fa-share',
                                        classname: 'btn btn-xs btn-warning btn-newSalesList'
                                    },
                                    {

                                        name: 'edit',
                                        text: __('Feedback'),
                                        icon: 'fa fa-pencil',
                                        title: '反馈',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-success btn-editone',
                                        url: 'salesmanagement/customerlisttabs/edit'

                                    },
                                    {

                                        name: 'giveup',
                                        text: '放弃',
                                        icon: 'fa fa-trash',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('放弃客户'),
                                        classname: 'btn btn-xs btn-danger btn-give_up'

                                    }

                                ],
                                events: Controller.api.events.operate,
                                formatter: Controller.api.formatter.operate
                            }
                        ]
                    ]
                });
                Table.api.bindevent(newCustomer);

                batch_giveup(".btn-selected1", newCustomer, 'salesmanagement/Customerlisttabs/ajaxBatchGiveup');

                /**
                 * 批量反馈
                 */
                batch_feedback(".btn-batch-feedback-1", newCustomer);
                // $(document).on("click", ".btn-batch-feedback-1", function (e, value, row, index) {
                //     //获取ids对象
                //     var ids = Table.api.selectedids(newCustomer);
                //     var url = 'salesmanagement/customerlisttabs/batchfeedback?ids=' + ids;
                //     var options = {
                //         shadeClose: false,
                //         shade: [0.3, '#393D49'],
                //         area: ['50%', '50%'],
                //         callback: function (value) {
                //
                //         }
                //     }
                //     Fast.api.open(url, '批量反馈', options)
                // })
                newCustomer.on('load-success.bs.table', function (e, data) {
                    $('#badge_new_customer').text(data.total);


                })

                //实时消息
                //内勤分配给销售
                // goeasy.subscribe({
                //     channel: 'demo-internal',
                //     onMessage: function (message) {
                //         messageCont = split('|',message.content);
                //         if(Config.ADMIN_JS.id==messageCont[1]){
                //             Layer.alert('新消息：' + messageCont[0], {icon: 0}, function (index) {
                //                 Layer.close(index);
                //                 $(".btn-refresh").trigger("click");
                //             });
                //         }
                //     }
                // });

            },
            /**
             * 待联系
             */
            relation: function () {
                liActive = $('.nav-tabs').find('li.active .tabs-text').text();
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                    return "快速搜索客户姓名";
                };

                var relations = $("#relations");
                relations.on('post-body.bs.table', function (e, settings, json, xhr) {

                    $(".btn-showFeedback").data("area", ["80%", "80%"]);
                });

                // 初始化表格
                relations.bootstrapTable({
                    url: 'salesmanagement/Customerlisttabs/relation',
                    extend: {
                        index_url: 'customer/customerresource/index',
                        add_url: 'salesmanagement/customerlisttabs/add',
                        edit_url: 'salesmanagement/customerlisttabs/edit',
                        del_url: 'customer/customerresource/del',
                        multi_url: 'customer/customerresource/multi',
                        table: 'customer_resource',
                    },
                    toolbar: '#toolbar2',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: Fast.lang('Id'), operate: false},
                            {field: 'platform.name', title: __('客户来源')},
                            {
                                field: 'admin.nickname', title: __('Sales_id'), formatter: function (v, r, i) {
                                    return v != null ? "<img src=" + r.admin.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' + r.admin.department+' - '+v : v;
                                }
                            },
                            {field: 'username', title: __('客户姓名')},
                            {field: 'phone', title: __('Phone')},
                            {field: 'invalidtime', title: __('号码失效时间'),formatter:function (v,r,i) {
                                    return Controller.numberFailure(v);
                                }},
                            {field: 'jobs', title: __('求职岗位')},

                            {
                                field: 'customerlevel',
                                title: '客户等级',
                                operate: false,
                                formatter: Controller.api.formatter.status
                            },
                            {
                                field: 'feedbackContent',
                                title: __('历史反馈记录'),
                                operate: false,
                                formatter: function (v, r, i) {
                                    return Controller.feedFun(v);
                                }
                            },
                            {field: 'followupdate', title: '计划下次跟进时间', operate: false,formatter:function (v,r,i) {
                                    return Controller.followupdateFun(v);
                                }},
                            {
                                field: 'operate', title: __('Operate'), table: relations,
                                buttons: [
                                    {
                                        name: 'detail',
                                        text: '新增销售单',
                                        title: '新增销售单',
                                        icon: 'fa fa-share',
                                        classname: 'btn btn-xs btn-warning btn-newSalesList'
                                    },
                                    {

                                        name: 'edit',
                                        text: __('Feedback'),
                                        icon: 'fa fa-pencil',
                                        title: '反馈',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-success btn-editone',
                                        url: 'salesmanagement/customerlisttabs/edit'

                                    },
                                    {

                                        name: 'del',
                                        text: '放弃',
                                        icon: 'fa fa-trash',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('Del'),
                                        classname: 'btn btn-xs btn-danger btn-give_up'

                                    },
                                    {

                                        name: 'edit',
                                        text: __('查看跟进结果'),
                                        icon: 'fa fa-eye',
                                        title: '查看跟进结果',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-info btn-showFeedback',
                                        url: "salesmanagement/customerlisttabs/showFeedback"

                                    }
                                ],

                                events: Controller.api.events.operate,
                                formatter: Controller.api.formatter.operate
                            }
                        ]
                    ]
                });
                // 为表格1绑定事件
                Table.api.bindevent(relations);
                /**
                 * 批量反馈
                 */
                batch_feedback(".btn-batch-feedback-2", relations);
                /**
                 * 批量放弃
                 */
                batch_giveup(".btn-selected2", relations, 'salesmanagement/Customerlisttabs/ajaxBatchGiveup');


                relations.on('load-success.bs.table', function (e, data) {

                    $('#badge_relation').text(data.total);

                    // f(['new_customer','intention','nointention','overdue']);


                })


            },
            /**
             * 有意向
             */
            intention: function () {
                liActive = $('.nav-tabs').find('li.active .tabs-text').text();
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                    return "快速搜索客户姓名";
                };

                var intentions = $("#intentions");
                intentions.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-showFeedback").data("area", ["80%", "80%"]);
                });
                // 初始化表格
                intentions.bootstrapTable({
                    url: 'salesmanagement/Customerlisttabs/intention',
                    extend: {
                        index_url: 'customer/customerresource/index',
                        add_url: 'salesmanagement/customerlisttabs/add',
                        edit_url: 'salesmanagement/customerlisttabs/edit',
                        del_url: 'customer/customerresource/del',
                        multi_url: 'customer/customerresource/multi',
                        table: 'customer_resource',
                    },
                    toolbar: '#toolbar3',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: Fast.lang('Id'), operate: false},
                            {field: 'platform.name', title: __('客户来源')},
                            {
                                field: 'admin.nickname', title: __('Sales_id'), formatter: function (v, r, i) {
                                    return v != null ? "<img src=" + r.admin.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' + r.admin.department+' - '+v : v;                                }
                            },
                            {field: 'username', title: __('客户姓名')},
                            {field: 'phone', title: __('Phone')},
                            {field: 'invalidtime', title: __('号码失效时间'),formatter:function (v,r,i) {
                                    return Controller.numberFailure(v);
                                }},
                            {field: 'jobs', title: __('求职岗位')},
                            {
                                field: 'customerlevel',
                                title: '客户等级',
                                operate: false,
                                formatter: Controller.api.formatter.status
                            },
                            {
                                field: 'feedbackContent',
                                title: __('历史反馈记录'),
                                operate: false,
                                formatter: function (v, r, i) {
                                    // console.log(v);
                                    return Controller.feedFun(v);
                                }
                            },
                            {field: 'followupdate', title: '计划下次跟进时间', operate: false,formatter:function (v,r,i) {
                                    return Controller.followupdateFun(v);
                                }},

                            {
                                field: 'operate', title: __('Operate'), table: intentions,
                                buttons: [
                                    {
                                        name: 'detail',
                                        text: '新增销售单',
                                        title: '新增销售单',
                                        icon: 'fa fa-share',
                                        classname: 'btn btn-xs btn-warning btn-newSalesList'
                                    },
                                    {

                                        name: 'edit',
                                        text: __('Feedback'),
                                        icon: 'fa fa-pencil',
                                        title: '反馈',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-success btn-editone',
                                        url: 'salesmanagement/customerlisttabs/edit'

                                    },
                                    {

                                        name: 'del',
                                        text: '放弃',
                                        icon: 'fa fa-trash',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('Del'),
                                        classname: 'btn btn-xs btn-danger btn-give_up'

                                    },
                                    {

                                        name: 'edit',
                                        text: __('查看跟进结果'),
                                        icon: 'fa fa-eye',
                                        title: '查看跟进结果',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-info btn-showFeedback',
                                        url: "salesmanagement/customerlisttabs/showFeedback"

                                    }
                                ],
                                events: Controller.api.events.operate,
                                formatter: Controller.api.formatter.operate
                            }
                        ]
                    ]
                });
                // 为表格1绑定事件
                Table.api.bindevent(intentions);
                /**
                 * 批量放弃
                 */
                batch_giveup(".btn-selected3", intentions, 'salesmanagement/Customerlisttabs/ajaxBatchGiveup');


                /**
                 * 批量反馈
                 */
                batch_feedback(".btn-batch-feedback-3", intentions);

                intentions.on('load-success.bs.table', function (e, data) {
                    $('#badge_intention').text(data.total);

                    // f(['new_customer','relation','nointention','overdue']);
                })

            },
            /**
             *  暂无意向
             */
            nointention: function () {

                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                    return "快速搜索客户姓名";
                };
                var nointentions = $("#nointentions");
                nointentions.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-newSalesList").data("area", ["50%", "50%"]);
                });
                // 初始化表格
                nointentions.bootstrapTable({
                    url: 'salesmanagement/Customerlisttabs/nointention',
                    extend: {
                        index_url: 'customer/customerresource/index',
                        add_url: 'salesmanagement/customerlisttabs/add',
                        edit_url: 'salesmanagement/customerlisttabs/edit',
                        del_url: 'customer/customerresource/del',
                        multi_url: 'customer/customerresource/multi',
                        table: 'customer_resource',
                    },
                    toolbar: '#toolbar4',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: Fast.lang('Id'), operate: false},
                            {field: 'platform.name', title: __('客户来源')},
                            {
                                field: 'admin.nickname', title: __('Sales_id'), formatter: function (v, r, i) {
                                    return v != null ? "<img src=" + r.admin.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' + r.admin.department+' - '+v : v;                                }
                            },
                            {field: 'username', title: __('客户姓名')},
                            {field: 'phone', title: __('Phone')},
                            {field: 'invalidtime', title: __('号码失效时间'),formatter:function (v,r,i) {
                                    return Controller.numberFailure(v);
                                }},
                            {field: 'jobs', title: __('求职岗位')},
                            {
                                field: 'customerlevel',
                                title: '客户等级',
                                operate: false,
                                formatter: Controller.api.formatter.status
                            },
                            {
                                field: 'feedbackContent',
                                title: __('历史反馈记录'),
                                operate: false,
                                formatter: function (v, r, i) {

                                    return Controller.feedFun(v);
                                }
                            },
                            {
                                field: 'followupdate',
                                title: '计划下次跟进时间',
                                operate: false,
                                formatter: function (v, r, i) {
                                   return Controller.followupdateFun(v);
                                }
                            },
                            {
                                field: 'operate', title: __('Operate'), table: nointentions,
                                buttons: [
                                    {
                                        name: 'detail',
                                        text: '新增销售单',
                                        title: '新增销售单',
                                        icon: 'fa fa-share',
                                        classname: 'btn btn-xs btn-warning btn-newSalesList'
                                    },
                                    {

                                        name: 'edit',
                                        text: __('Feedback'),
                                        icon: 'fa fa-pencil',
                                        title: '反馈',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-success btn-editone',
                                        url: 'salesmanagement/customerlisttabs/edit'

                                    },
                                    {

                                        name: 'del',
                                        text: '放弃',
                                        icon: 'fa fa-trash',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('Del'),
                                        classname: 'btn btn-xs btn-danger btn-give_up'

                                    },
                                    {

                                        name: 'edit',
                                        text: __('查看跟进结果'),
                                        icon: 'fa fa-eye',
                                        title: '查看跟进结果',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-info btn-showFeedback',
                                        url: "salesmanagement/customerlisttabs/showFeedback"

                                    }
                                ],
                                events: Controller.api.events.operate,
                                formatter: Controller.api.formatter.operate
                            }
                        ]
                    ]
                });
                // 为表格1绑定事件
                Table.api.bindevent(nointentions);
                /**
                 * 批量放弃
                 *
                 */
                batch_giveup(".btn-selected4", nointentions, 'salesmanagement/Customerlisttabs/ajaxBatchGiveup');

                /**
                 * 批量反馈
                 */
                batch_feedback(".btn-batch-feedback-4", nointentions);


                nointentions.on('load-success.bs.table', function (e, data) {
                    $('#badge_no_intention').text(data.total);

                })


            },
            /**
             * 已放弃
             */
            giveup: function () {
                liActive = $('.nav-tabs').find('li.active .tabs-text').text();
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                    return "快速搜索客户姓名";
                };

                var giveups = $("#giveups");
                giveups.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-showFeedback").data("area", ["80%", "80%"]);
                });
                // 初始化表格
                giveups.bootstrapTable({
                    url: 'salesmanagement/Customerlisttabs/giveup',
                    extend: {
                        index_url: 'customer/customerresource/index',
                        add_url: 'salesmanagement/customerlisttabs/add',
                        edit_url: 'salesmanagement/customerlisttabs/edit',
                        del_url: 'customer/customerresource/del',
                        multi_url: 'customer/customerresource/multi',
                        table: 'customer_resource',
                    },
                    toolbar: '#toolbar5',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: Fast.lang('Id'), operate: false},
                            {field: 'platform.name', title: __('客户来源')},


                            {field: 'username', title: __('客户姓名')},
                            {field: 'phone', title: __('Phone')},
                            {
                                field: 'admin.nickname', title: __('Sales_id'), formatter: function (v, r, i) {
                                    return v != null ? "<img src=" + r.admin.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' + r.admin.department+' - '+v : v;                                }
                            },
                            {field: 'giveup_time', title: __('放弃时间'),operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime,datetimeFormat:'YYYY-MM-DD' },

                            {field: 'reason', title: __('放弃原因'), operate: false,formatter:function (value, row, index) {

                                var html = "";
                                html+="<span class='text-danger'>"+value+"</span>";
                                    return value? html:" - ";
                                }},


                        ]
                    ]
                });
                // 为表格1绑定事件
                Table.api.bindevent(giveups);

                giveups.on('load-success.bs.table', function (e, data) {

                    $('#badge_give_up').text(data.total);

                })


            },
            /**
             * 跟进过期用户
             */
            overdue: function () {
                liActive = $('.nav-tabs').find('li.active .tabs-text').text();
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                    return "快速搜索客户姓名";
                };
                var overdues = $("#overdues");
                overdues.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-showFeedback").data("area", ["80%", "80%"]);
                });
                // 初始化表格
                overdues.bootstrapTable({
                    url: 'salesmanagement/Customerlisttabs/overdue',
                    extend: {
                        index_url: 'customer/customerresource/index',
                        add_url: 'salesmanagement/customerlisttabs/add',
                        edit_url: 'salesmanagement/customerlisttabs/edit',
                        del_url: 'customer/customerresource/del',
                        multi_url: 'customer/customerresource/multi',
                        table: 'customer_resource',
                    },
                    toolbar: '#toolbar6',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: Fast.lang('Id'), operate: false},
                            {field: 'platform.name', title: __('客户来源')},
                            {
                                field: 'admin.nickname', title: __('Sales_id'), formatter: function (v, r, i) {
                                    return v != null ? "<img src=" + r.admin.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' + r.admin.department+' - '+v : v;                                }
                            },
                            // {field: 'sales_id', title: __('Sales_id')},
                            {field: 'username', title: __('客户姓名')},
                            {field: 'phone', title: __('Phone')},
                            {field: 'invalidtime', title: __('号码失效时间'),formatter:function (v,r,i) {
                                    return Controller.numberFailure(v);
                            }},
                            {field: 'jobs', title: __('求职岗位')},
                            // {field: 'followupdate', title: '下次跟进时间', operate: false},
                            {
                                field: 'customerlevel',
                                title: '客户等级',
                                operate: false,
                                formatter: Controller.api.formatter.status
                            },
                            {
                                field: 'feedbackContent',
                                title: __('历史反馈记录'),
                                operate: false,
                                formatter: function (v, r, i) {

                                    return Controller.feedFun(v);
                                }
                            },
                            {field: 'followupdate', title: '计划下次跟进时间', operate: false,formatter:function (v,r,i) {
                                    return Controller.followupdateFun(v);
                                }},
                            {
                                field: 'operate', title: __('Operate'), table: overdues,
                                buttons: [
                                    {
                                        name: 'overdue',
                                        text: '跟进时间已过期',
                                        title: '跟进过期',
                                        icon: 'fa fa-times',
                                        classname: 'text-danger'
                                    },
                                    {
                                        name: 'detail',
                                        text: '新增销售单',
                                        title: '新增销售单',
                                        icon: 'fa fa-share',
                                        classname: 'btn btn-xs btn-warning btn-newSalesList'
                                    },
                                    {

                                        name: 'edit',
                                        text: __('Feedback'),
                                        icon: 'fa fa-pencil',
                                        title: '反馈',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-success btn-editone',
                                        // url: 'salesmanagement/customerlisttabs/edit'

                                    },
                                    {

                                        name: 'del',
                                        text: '放弃',
                                        icon: 'fa fa-trash',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('Del'),
                                        classname: 'btn btn-xs btn-danger btn-give_up'

                                    },
                                    {

                                        name: 'edit',
                                        text: __('查看跟进结果'),
                                        icon: 'fa fa-eye',
                                        title: '查看跟进结果',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-info btn-showFeedback',
                                        url: "salesmanagement/customerlisttabs/showFeedback"

                                    }
                                ],
                                events: Controller.api.events.operate,
                                formatter: Controller.api.formatter.operate
                            }
                        ]
                    ]
                });
                // 为表格1绑定事件
                Table.api.bindevent(overdues);
                /**
                 * 批量放弃
                 */
                batch_giveup(".btn-selected5", overdues, 'salesmanagement/Customerlisttabs/ajaxBatchGiveup');

                /**
                 * 批量反馈
                 */
                batch_feedback(".btn-batch-feedback-5", overdues);

                overdues.on('load-success.bs.table', function (e, data) {
                    $('#badge_overdue').text(data.total);

                })

            },
        },
        add: function () {

            Controller.api.bindevent();

        },
        edit: function () {


            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                //切换跳转tabs
                var tablsToger = $(window.parent.document).find('.nav-tabs li');


                tablsToger.each(function () {
                    var liHtml = $(this).find('.tabs-text');

                    if (liHtml.text() === ret.data) {

                    }

                })

                Controller.api.bindevent();

            }, function (data, ret) {
                Toastr.error("失败");

            });
        },
        /**
         * 判断时间是否过期
         * @param v 格式时间
         * @returns {string}
         */
        numberFailure:function(v){

            return v == null ? '-' : new Date(v.replace(/-/g, '/')).getTime() < new Date().getTime() ? v + ' <span class="label label-danger">已过期</span>' : v;

        },
        /**
         * 格式化时间 几天前 时 分 秒
         * @param dateTimeStamp
         * @returns {*|string}
         */
        getDateDiff: function (timestamp) {
            var mistiming = Math.round(new Date() / 1000) - timestamp;
            var postfix = mistiming > 0 ? '前' : '后'
            mistiming = Math.abs(mistiming)
            var arrr = ['年', '个月', '星期', '天', '小时', '分钟', '秒'];
            var arrn = [31536000, 2592000, 604800, 86400, 3600, 60, 1];

            for (var i = 0; i < 7; i++) {
                var inm = Math.floor(mistiming / arrn[i])
                if (inm != 0) {
                    return inm + arrr[i] + postfix
                }
            }
        },
        /**
         * 时间戳格式化日期
         * @param Ns
         * @returns {string}
         */
        getLocalTime: function (nS) {
            return new Date(parseInt(nS) * 1000).toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ");

        },
        /**
         * 计划下次跟进时间
         * @param v 格式化的时间
         * @returns {string}
         */
        followupdateFun:function(v){
            return v != null ? '（' + Controller.getDateDiff(Date.parse(new Date(v)) / 1000) + '）' + v : '-';
        },
        /**
         * 记录反馈内容
         * @param v 时间戳
         * @returns {string}
         */
        feedFun:function(v){
           v =  v.sort(compare('feedbacktime'));
            var feedHtml = '';
            if (v != null) {

                if(v.length>4){
                    var arr = [];

                    for (var i in v){
                        if(i>3){
                            break;
                        }

                        arr.push(v[i]);
                    }

                    for (var i in arr) {
                        if(arr[i]['feedbackcontent'].length>=30){
                            arr[i]['feedbackcontent'] = arr[i]['feedbackcontent'].replace(arr[i]['feedbackcontent'].substr(30),'...');
                        }
                        var level = "";
                        switch (arr[i]['customerlevel']){
                            case '有意向':
                                level+="<span class='text-success'>"+arr[i]['customerlevel']+"</span>";
                                break;
                            case '暂无意向':
                                level+="<span class='text-warning'>"+arr[i]['customerlevel']+"</span>";
                                break;
                            case '待联系':
                                level+="<span class='text-info'>"+arr[i]['customerlevel']+"</span>";
                                break;
                            case '已放弃':
                                level+="<span class='text-danger'>"+arr[i]['customerlevel']+"</span>";
                                break;
                        }
                        feedHtml += "<span class='text-gray'>" + Controller.getDateDiff(arr[i]["feedbacktime"]) + '（' + Controller.getLocalTime(arr[i]['feedbacktime']) + '）' + '&nbsp;' + "</span>" + arr[i]['feedbackcontent'] + "（等级：" + level + "）" + '<br>';
                    }

                }else{
                    for (var i in v) {
                        if(v[i]['feedbackcontent'].length>=30){
                            v[i]['feedbackcontent'] = v[i]['feedbackcontent'].replace(v[i]['feedbackcontent'].substr(30),'...');
                        }
                        var level = "";
                        switch (v[i]['customerlevel']){
                            case '有意向':
                                level+="<span class='text-success'>"+v[i]['customerlevel']+"</span>";
                                break;
                            case '暂无意向':
                                level+="<span class='text-warning'>"+v[i]['customerlevel']+"</span>";
                                break;
                            case '待联系':
                                level+="<span class='text-info'>"+v[i]['customerlevel']+"</span>";
                                break;
                            case '已放弃':

                                level+="<span class='text-danger'>"+v[i]['customerlevel']+"</span>";
                                break;
                        }
                        feedHtml += "<span class='text-gray'>" + Controller.getDateDiff(v[i]["feedbacktime"]) + '（' + Controller.getLocalTime(v[i]['feedbacktime']) + '）' + '&nbsp;' + "</span>" + v[i]['feedbackcontent'] + "（等级：" + level + "）" + '<br>';
                    }
                }

            }
            return feedHtml ? feedHtml : '-';
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
            events: {
                operate: {

                    'click .btn-editone': function (e, value, row, index) {  //编辑
                        $('.btn-editone').data("area", ["60%", "60%"]);
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'salesmanagement/customerlisttabs/edit';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('反馈'), $(this).data() || {
                            callback: function (value) {


                                //    在这里可以接收弹出层中使用`Fast.api.close(data)`进行回传的数据
                            },
                            success: function (data) {

                            }

                        });

                    },

                    /**
                     * 查看跟进结果
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-showFeedback': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = "salesmanagement/customerlisttabs/showFeedback";
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('查看跟进信息'), $(this).data() || {});
                    },
                    /***
                     *加入放弃名单
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-give_up': function (e, value, row, index) {
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
                            __('确定加入放弃客户名单吗?'),
                            {icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true},
                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');
                                Layer.close(index);
                                Layer.prompt(

                                    { title: __('请填写放弃原因'), shadeClose: true },
                                    function (text, index) {
                                        Fast.api.ajax({
                                            url:"salesmanagement/Customerlisttabs/ajaxGiveup",
                                            data:{
                                                text:text,
                                                id:row[options.pk]
                                            }
                                        },function (data,ret) {
                                            // console.log(data);
                                            var pre = $('#badge_give_up').text();
                                                pre = parseInt(pre);
                                                $('#badge_give_up').text(pre + 1);
                                                Layer.close(index);
                                                table.bootstrapTable('refresh');
                                        },function (data,ret) {


                                        })
                                    })



                            }
                        );

                    },

                    /**
                     * 新增销售单
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-newSalesList': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        // alert(row[options.pk]);
                        //第三方扩展皮肤

                        layer.alert('请前往订单列表,选择对应的方案进行新增销售单', {
                            // icon: 1,
                            skin: 'layer-ext-moon', //该皮肤由layer.seaning.com友情扩展。关于皮肤的扩展规则，去这里查阅
                            // content: "<h4>请前往订单列表,选择对应的方案进行新增销售单</h4>",
                            btn: ['前往'],
                            btn1: function () {

                                window.top.location.href = "orderlisttabs?ref=addtabs";

                            }

                        });
                        $(".layui-layer-btn0").css({"margin-right": "132px"});
                        $(".la").css({"margin-left": "10px"});


                    }
                }
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
                status: function (value, row, index) {

                    var colorArr = {relation: 'info', intention: 'success', nointention: 'danger'};
                    //如果字段列有定义custom
                    if (typeof this.custom !== 'undefined') {
                        colorArr = $.extend(colorArr, this.custom);
                    }
                    value = value === null ? '' : value.toString();

                    var color = value && typeof colorArr[value] !== 'undefined' ? colorArr[value] : 'primary';

                    var newValue = value.charAt(0).toUpperCase() + value.slice(1);
                    //渲染状态
                    var html = '<span class="text-' + color + '"><i class="fa fa-circle"></i> ' + __(newValue) + '</span>';
                    // if (this.operate != false) {
                    //     html = '<a href="javascript:;" class="searchit" data-toggle="tooltip" title="' + __('Click to search %s', __(newValue)) + '" data-field="' + this.field + '" data-value="' + value + '">' + html + '</a>';
                    // }
                    return html;
                },
            }
        }

    };


    /**
     * 批量放弃
     * @param clickobj
     * @param table
     * @param url
     */
    function batch_giveup(clickobj, table, url) {
        $(document).on("click", clickobj, function (e, value, row, index) {
            var ids = Table.api.selectedids(table);
            e.stopPropagation();
            e.preventDefault();
            var that = this;

            var top = $(that).offset().top - $(window).scrollTop() + 100;
            var left = $(that).offset().left - $(window).scrollLeft() + 500;
            if (top + 154 > $(window).height()) {
                top = top - 154;
            }
            if ($(window).width() < 480) {
                top = left = undefined;
            }

            Layer.confirm(
                __('确定加入放弃客户名单吗?'),
                {icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true},

                function (index) {

                    Layer.close(index);
                    Layer.prompt(

                        { title: __('请填写放弃原因'), shadeClose: true },
                        function (text, index) {
                            Fast.api.ajax({
                                url:url,
                                data:{
                                    text:text,
                                    id:JSON.stringify(ids)
                                }
                            },function (data,ret) {


                                var pre = $('#badge_give_up').text();
                                pre = parseInt(pre);
                                $('#badge_give_up').text(pre + 1);
                                Layer.close(index);
                                table.bootstrapTable('refresh');
                            },function (data,ret) {


                            })
                        })


                }
            );
        });
    }


    /**
     * 批量反馈
     * @param clickobj
     * @param table
     */
    function batch_feedback(clickobj, table) {
        $(document).on("click", clickobj, function (e, value, row, index) {
            //获取ids对象
            var ids = Table.api.selectedids(table);
            var url = 'salesmanagement/customerlisttabs/batchfeedback?ids=' + ids;
            var options = {
                shadeClose: false,
                shade: [0.3, '#393D49'],
                area: ['60%', '60%'],
                callback: function (value) {

                }
            }
            Fast.api.open(url, '批量反馈', options)
        })
    }


    /**
     * 跟进跟进时间排序
     * @param property
     * @returns {function(*, *): number}
     */
    function compare(property){
        return function(a,b){
            var value1 = a[property];
            var value2 = b[property];
            return  value2-value1;
        }
    }

    return Controller;
});

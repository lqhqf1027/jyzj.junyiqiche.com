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

            $('ul.nav-tabs li a[data-toggle="tab"]').each(function () {
                $(this).trigger("shown.bs.tab");
            });

            // goeasy.subscribe({
            //     channel: 'send_peccancy',
            //     onMessage: function(message){
            //
            //             Layer.alert(message.content,{ icon:0},function(index){
            //                 Layer.close(index);
            //                 $(".btn-refresh").trigger("click");
            //             });
            //
            //     }
            // });

        },
        table:{
            /**
             * 待反馈
             */
            prepare_feedback: function () {
                var table = $("#prepareFeedback");

                table.on('load-success.bs.table', function (e, data) {

                    $('#prepare-total').text(data.total);

                })
                table.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-newCustomer").data("area", ["60%", "60%"]);
                });
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                    return "快速搜索：客户姓名,车牌号";
                };
                // 初始化表格
                table.bootstrapTable({
                    url: 'customerservice/Breakrules/prepare_feedback',
                    extend: {
                        index_url: 'customerservice/breakrules/index',
                        add_url: 'customerservice/breakrules/add',
                        // edit_url: 'customerservice/breakrules/edit',
                        // del_url: 'customerservice/breakrules/del',
                        multi_url: 'customerservice/breakrules/multi',
                        table: 'violation_inquiry',
                    },
                    pk: 'id',
                    toolbar: '#toolbar1',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'),operate:false},
                            {field: 'username', title: __('Username')},
                            {field: 'phone', title: __('Phone')},
                            {field: 'models', title: __('Models')},
                            {field: 'license_plate_number', title: __('License_plate_number')},
                            {field: 'frame_number', title: __('Frame_number')},
                            {field: 'engine_number', title: __('Engine_number')},
                            {field: 'total_deduction', title: __('Total_deduction'), operate: 'BETWEEN'},
                            {field: 'total_fine', title: __('Total_fine'), operate: 'BETWEEN'},
                            {field: 'query_times', title: __('Query_times'),operate:false},
                            {
                                field: 'car_type',
                                title: __('Car_type'),
                                searchList: {
                                    "1": __('Car_type 1'),
                                    "2": __('Car_type 2'),
                                    "3": __('Car_type 3'),
                                    "4": __('Car_type 4')
                                },
                                formatter: Controller.api.formatter.normal
                            },
                            {field: 'peccancy_status', title: __('Peccancy_status'),formatter:Controller.api.formatter.status,searchList: {
                                    "1": __('已处理'),
                                    "2": __('未处理'),

                                },},
                            // {
                            //     field: 'final_time',
                            //     title: __('Final_time'),
                            //     operate: false,
                            //     addclass: 'datetimerange',
                            //     formatter: Table.api.formatter.datetime
                            // },
                            {
                                field: 'customer_time',
                                title: __('Customer_time'),
                                operate: false,
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },
                            {
                                field: 'start_renttime',
                                title: __('Start_renttime'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },
                            {
                                field: 'end_renttime',
                                title: __('End_renttime'),
                                operate: false,
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },
                            {field: 'inquiry_note', title: __('备注'),operate:false},
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: table,
                                events: Controller.api.events.operate,
                                formatter: Controller.api.formatter.operate,

                            }
                        ]
                    ]
                });

                // 批量删除按钮事件
                $(document).on('click', '.btn-feedback-all', function () {
                    var ids = Table.api.selectedids(table);

                    layer.prompt({
                        formType: 2,
                        title: '请输入反馈内容',
                        area: ['800px', '350px'] //自定义文本域宽高
                    }, function(value, index, elem){
                        Fast.api.ajax({
                            url: 'customerservice/Breakrules/handle_feedback_lots',
                            data: {
                                content:value,
                                ids:JSON.stringify(ids)
                            }

                        }, function (data, ret) {
                            // console.log(data);
                            table.bootstrapTable('refresh');
                            layer.close(index);

                            var pre = $('#already-total').text();

                            pre = parseInt(pre);

                            $('#already-total').text(pre+parseInt(data));
                        })


                    });
                });

                // 为表格绑定事件
                Table.api.bindevent(table);
            },
            /**
             * 已反馈
             */
            already_feedback: function () {
                var alreadyFeedback = $("#alreadyFeedback");

                alreadyFeedback.on('load-success.bs.table', function (e, data) {

                    $('#already-total').text(data.total);

                })
                alreadyFeedback.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-newCustomer").data("area", ["60%", "60%"]);
                });
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                    return "快速搜索：客户姓名,车牌号";
                };
                // 初始化表格
                alreadyFeedback.bootstrapTable({
                    url: 'customerservice/Breakrules/already_feedback',
                    extend: {
                        index_url: 'customerservice/breakrules/index',
                        add_url: 'customerservice/breakrules/add',
                        // edit_url: 'customerservice/breakrules/edit',
                        // del_url: 'customerservice/breakrules/del',
                        multi_url: 'customerservice/breakrules/multi',
                        table: 'violation_inquiry',
                    },
                    pk: 'id',
                    toolbar: '#toolbar2',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'),operate:false},
                            {field: 'username', title: __('Username')},
                            {field: 'phone', title: __('Phone')},
                            {field: 'models', title: __('Models')},
                            {field: 'license_plate_number', title: __('License_plate_number')},
                            {field: 'frame_number', title: __('Frame_number')},
                            {field: 'engine_number', title: __('Engine_number')},
                            {field: 'total_deduction', title: __('Total_deduction'), operate: 'BETWEEN'},
                            {field: 'total_fine', title: __('Total_fine'), operate: 'BETWEEN'},
                            {field: 'query_times', title: __('Query_times'),operate:false},
                            {
                                field: 'car_type',
                                title: __('Car_type'),
                                searchList: {
                                    "1": __('Car_type 1'),
                                    "2": __('Car_type 2'),
                                    "3": __('Car_type 3'),
                                    "4": __('Car_type 4')
                                },
                                formatter: Controller.api.formatter.normal
                            },
                            {field: 'peccancy_status', title: __('Peccancy_status'),formatter:Controller.api.formatter.status,searchList: {
                                    "1": __('已处理'),
                                    "2": __('未处理'),

                                },},

                            {
                                field: 'customer_time',
                                title: __('Customer_time'),
                                operate: false,
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },
                            {
                                field: 'start_renttime',
                                title: __('Start_renttime'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },
                            {
                                field: 'end_renttime',
                                title: __('End_renttime'),
                                operate: false,
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },
                            {field: 'inquiry_note', title: __('备注'),operate:false},
                            {field: 'feedback', title: __('反馈内容'),operate:false},
                            {
                                field: 'feedbacktime',
                                title: __('反馈时间'),
                                operate: false,
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },

                        ]
                    ]
                });


                // 为表格绑定事件
                Table.api.bindevent(alreadyFeedback);
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
            formatter:{
                normal: function (value, row, index) {
                    switch (value) {
                        case 1:
                            return "(以租代购)新车";
                        case 2:
                            return "二手车";
                        case 3:
                            return "全款车";
                        case 4:
                            return "租车";
                    }
                },
                status: function (value, row, index) {
                    if (!value) {
                        return '-';
                    }

                    value == 1 ? value = '已处理' : value = '未处理';
                    var custom = {'已处理': 'success', '未处理': 'danger'};
                    if (typeof this.custom !== 'undefined') {
                        custom = $.extend(custom, this.custom);
                    }
                    this.custom = custom;

                    this.icon = 'fa fa-circle';
                    return Table.api.formatter.normal.call(this, value, row, index);
                },
                operate: function (value, row, index) {
                    var table = this.table;
                    // 操作配置
                    var options = table ? table.bootstrapTable('getOptions') : {};
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);


                    if(row && row.feedback==null){
                       buttons.push({
                           name: 'feedback',
                           text: '反馈',
                           icon: 'fa fa-pencil',
                           title: __('反馈'),
                           classname: 'btn btn-xs btn-info btn-feedback',
                       })
                    }
                    return Table.api.buttonlink(this, buttons, value, row, index, 'operate');
                }
            },
            events: {
                operate:{
                    /**
                     * 编辑反馈
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-feedback': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        layer.prompt({
                            formType: 2,
                            title: '请输入反馈内容',
                            area: ['800px', '350px'] //自定义文本域宽高
                        }, function(value, index, elem){


                            Fast.api.ajax({
                                url: 'customerservice/Breakrules/handle_feedback',
                                data: {
                                    content:value,
                                    ids:row.id
                                }

                            }, function (data, ret) {
                                table.bootstrapTable('refresh');
                                layer.close(index);

                                var pre = $('#already-total').text();

                                pre = parseInt(pre);

                                $('#already-total').text(pre+1);
                            })


                        });
                    }
                }
            }
        }
    };
    return Controller;
});
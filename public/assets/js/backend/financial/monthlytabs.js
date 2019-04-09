
define(['jquery', 'bootstrap', 'backend', 'table', 'form','echarts', 'echarts-theme','addtabs'], function ($, undefined, Backend, Table, Form,Echarts, undefined, Template) {

    var goeasy = new GoEasy({
        appkey: 'BC-04084660ffb34fd692a9bd1a40d7b6c2'
    });
    var Controller = {
        index: function () {

            // // 初始化表格参数配置
            Table.api.init({
            });
            // //绑定事件
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
        },
        //批量导入
        import: function () {
            // console.log(123);
            // return;
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                Fast.api.close(data);//这里是重点
                console.log(data);
                // Toastr.success("成功");//这个可有可无
            }, function (data, ret) {
                // console.log(data);

                Toastr.success("失败");

            });
            Controller.api.bindevent();
            // console.log(Config.id);

        },

        table: {
            /**
             * 新车月供（扣款失败）
             */
            newcar_monthly: function () {
                var newcarMonthly = $("#newcarMonthly");
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                    return "快速搜索客户姓名";
                };
                newcarMonthly.bootstrapTable({
                    url: 'financial/monthlytabs/newcarMonthly',
                    extend: {
                        add_url: 'financial/monthlytabs/add',
                        edit_url: 'financial/monthlytabs/edit',
                        del_url: 'financial/monthlytabs/del',
                        import_url: 'financial/monthlytabs/import',
                        multi_url: 'financial/monthlytabs/multi',
                        table: 'newcar_monthly',
                    },
                    toolbar: '#toolbar1',
                    pk: 'id',
                    sortName: 'id',
                    // search:false,
                    pageSize: 50,
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'),operate:false},
                            {field: 'monthly_card_number', title: __('Monthly_card_number')},
                            {field: 'monthly_name', title: __('Monthly_name')},
                            {field: 'monthly_phone_number', title: __('Monthly_phone_number')},
                            {field: 'monthly_models', title: __('Monthly_models')},
                            {field: 'monthly_monney', title: __('Monthly_monney'), operate:false},
                            {field: 'monthly_data', title: __('Monthly_data'), searchList: {"failure":__('Monthly_data failure'),"success":__('Monthly_data success')}, formatter: function (v,r,i) {
                                    return v=='failure'?'<span class="text-danger">失败</span>':'<span class="text-success">成功</span>';
                                }},
                            {field: 'monthly_failure_why', title: __('Monthly_failure_why'),operate:false},
                            {field: 'monthly_in_arrears_time', title: __('Monthly_in_arrears_time'), operate:'RANGE', addclass:'datetimerange'},
                            {field: 'monthly_company', title: __('Monthly_company')},
                            {field: 'monthly_car_number', title: __('Monthly_car_number')},

                            {field: 'monthly_arrears_months', title: __('Monthly_arrears_months')},
                            {field: 'monthly_family_unit', title: __('上户单位')},

                            // {field: 'monthly_arrears_months', title: __('Monthly_arrears_months')},
                            {field: 'monthly_note', title: __('Monthly_note'),operate:false},

                            {field: 'monthly_status', title: __('发送给风控状态'), operate: false,formatter: function (value,row,index) {


                                    return row.monthly_data=='failure'?"<span class='text-danger'><i class='fa fa-circle'></i> 待发送</span>":'未知状态'

                                }},
                            {
                                field: 'operate', title: __('Operate'), table: newcarMonthly,
                                buttons: [
                                    {
                                        icon: 'fa fa-trash', name: 'del', icon: 'fa fa-trash', extend: 'data-toggle="tooltip"', title: __('Del'), classname: 'btn btn-xs btn-danger btn-delone',
                                        url: 'monthly/newcarmonthly/del',/**删除 */


                                    },
                                    {
                                        name: 'edit', text: '', icon: 'fa fa-pencil', extend: 'data-toggle="tooltip"', title: __('Edit'), classname: 'btn btn-xs btn-success btn-editone',
                                        url: 'monthly/newcarmonthly/edit',/**编辑 */

                                    },


                                ],
                                events: Controller.api.events.operate,

                                formatter: Controller.api.formatter.operate

                            }
                        ]
                    ]
                });
                Table.api.bindevent(newcarMonthly);
                newcarMonthly.on('load-success.bs.table', function (e, data) {
                    $('#newcar_monthly_badge').text(data.total);
                    // console.log(data);
                })
                /**
                 * 批量发送给风控
                 */
                $(document).on("click", ".btn-sed-risk", function () {
                    var ids = Table.api.selectedids(newcarMonthly);
                    var page = newcarMonthly.bootstrapTable('getData');
                   var closeLay =  Layer.confirm("请选择要发送到风控的客户数据", {
                        title: '发送数据',
                        btn: ["选中项(" + ids.length + "条)", "本页(" + page.length + "条)"],
                        success: function (layero, index) {
                            $(".layui-layer-btn a", layero).addClass("layui-layer-btn0");
                        }
                        ,
                       //选中项
                        yes: function (index, layero) {
                            if(ids.length<1){
                                Layer.alert('数据不能为空!',{icon:5})
                                return false
                            }
                            Fast.api.ajax({
                                url:'financial/monthlytabs/sedRisk',
                                data:{ids:ids}
                            },function (data,ret) {
                                Layer.close(closeLay);

                                newcarMonthly.bootstrapTable('refresh');
                            })
                        }
                        ,
                       //本页
                        btn2: function (index, layero) {
                            var ids = [];
                            for (var i in page){
                                ids.push(page[i]['id'])
                            }

                            Fast.api.ajax({
                                url:'financial/monthlytabs/sedRisk',
                                data:{ids:ids}
                            },function (data,ret) {
                                Layer.close(closeLay);

                                newcarMonthly.bootstrapTable('refresh');
                            })
                        }
                        ,
                       // //全部
                       //  btn3: function (index, layero) {
                       //      var ids = [];
                       //      for (var i in all){
                       //          ids.push(all[i]['id'])
                       //      }
                       //      var options = newcarMonthly.bootstrapTable('getOptions');
                       //      // var columns = [];
                       //      // $.each(options.columns[0], function (i, j) {
                       //      //     if (j.field && !j.checkbox && j.visible && j.field != 'operate') {
                       //      //         columns.push(j.field);
                       //      //     }
                       //      // });
                       //      console.log(options);
                       //
                       //      return;
                       //      Fast.api.ajax({
                       //          url:'financial/monthlytabs/sedRisk',
                       //          data:{ids:ids}
                       //      },function (data,ret) {
                       //          Layer.close(closeLay);
                       //
                       //          newcarMonthly.bootstrapTable('refresh');
                       //      })
                       //  }
                    })
                });

            },
            /**
             * 新车月供已发送
             */
            hasbeen_sent: function () {
                var hasBeenSent = $("#hasBeenSent");
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                    return "快速搜索客户姓名";
                };
                hasBeenSent.bootstrapTable({
                    url: 'financial/monthlytabs/hasBeenSent',
                    extend: {
                        edit_url: 'financial/monthlytabs/edit',
                        del_url: 'financial/monthlytabs/del',
                        import_url: 'financial/monthlytabs/import',
                        multi_url: 'financial/monthlytabs/multi',
                        table: 'newcar_monthly',

                    },
                    toolbar: '#toolbar2',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'),operate:false},
                            {field: 'monthly_card_number', title: __('Monthly_card_number')},
                            {field: 'monthly_name', title: __('Monthly_name')},
                            {field: 'monthly_phone_number', title: __('Monthly_phone_number')},
                            {field: 'monthly_models', title: __('Monthly_models')},
                            {field: 'monthly_monney', title: __('Monthly_monney'), operate:false},
                            {field: 'monthly_data', title: __('Monthly_data'), searchList: {"failure":__('Monthly_data failure'),"success":__('Monthly_data success')}, formatter: function (v,r,i) {
                                    if(v=='failure'&& r.monthly_supplementary !=null){
                                        v = '<span class="text-danger">失败</span>';
                                        return v+' <span class="label label-success">'+r.monthly_supplementary+'</span>'

                                    }else {
                                        return '<span class="text-danger">失败</span>';
                                    }
                                }},
                            {field: 'monthly_failure_why', title: __('Monthly_failure_why'),operate:false},
                            {field: 'monthly_in_arrears_time', title: __('Monthly_in_arrears_time'), operate:'RANGE', addclass:'datetimerange'},
                            {field: 'monthly_company', title: __('Monthly_company')},
                            {field: 'monthly_car_number', title: __('Monthly_car_number')},
                            {field: 'monthly_family_unit', title: __('上户单位')},

                            // {field: 'monthly_arrears_months', title: __('Monthly_arrears_months')},
                            {field: 'monthly_note', title: __('Monthly_note'),operate:false},
                            {field: 'monthly_status', title: __('发送给风控状态'), operate: false,formatter: function (value,row,index) {


                                    return value=='has_been_sent'?"<span class='text-success'><i class='fa fa-circle'></i> 已发送</span>":'未知状态'

                                }},
                            {
                                field: 'operate', title: __('Operate'), table: hasBeenSent,
                                buttons: [
                                    {
                                        icon: 'fa fa-trash', name: 'del', icon: 'fa fa-trash', extend: 'data-toggle="tooltip"', title: __('Del'), classname: 'btn btn-xs btn-danger btn-delone',
                                        url: 'monthly/newcarmonthly/del',/**删除 */


                                    },
                                    {
                                        name: 'edit', text: '', icon: 'fa fa-pencil', extend: 'data-toggle="tooltip"', title: __('Edit'), classname: 'btn btn-xs btn-success btn-editone',
                                        url: 'monthly/newcarmonthly/edit',/**编辑 */

                                    },


                                ],
                                events: Controller.api.events.operate,

                                formatter: Controller.api.formatter.operate

                            }

                        ]
                    ]
                });
                Table.api.bindevent(hasBeenSent);
                hasBeenSent.on('load-success.bs.table', function (e, data) {
                    $('#hasbeen_sent_badge').text(data.total);
                })

            },
            /**
             * 扣款成功客户
             */
            deductions_succ:function () {
                var deductionsSucc = $("#deductionsSucc");
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                    return "快速搜索客户姓名";
                };
                deductionsSucc.bootstrapTable({
                    url: 'financial/monthlytabs/deductionsSucc',
                    extend: {
                        add_url: '',
                        edit_url: 'financial/monthlytabs/edit',
                        del_url: 'financial/monthlytabs/del',
                        import_url: 'financial/monthlytabs/import',
                        multi_url: 'financial/monthlytabs/multi',
                        table: 'newcar_monthly',
                    },
                    toolbar: '#toolbar3',
                    pk: 'id',
                    sortName: 'id',
                    // search:false,
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'),operate:false},
                            {field: 'monthly_card_number', title: __('Monthly_card_number')},
                            {field: 'monthly_name', title: __('Monthly_name')},
                            {field: 'monthly_phone_number', title: __('Monthly_phone_number')},
                            {field: 'monthly_models', title: __('Monthly_models')},
                            {field: 'monthly_monney', title: __('Monthly_monney'), operate:false},
                            {field: 'monthly_data', title: __('Monthly_data'), searchList: {"failure":__('Monthly_data failure'),"success":__('Monthly_data success')}, formatter: Table.api.formatter.normal},
                            {field: 'monthly_failure_why', title: __('Monthly_failure_why')},
                            {field: 'monthly_in_arrears_time', title: __('Monthly_in_arrears_time'), operate:'RANGE', addclass:'datetimerange'},
                            {field: 'monthly_company', title: __('Monthly_company')},
                            {field: 'monthly_car_number', title: __('Monthly_car_number')},
                            {field: 'monthly_family_unit', title: __('上户单位')},
                            // {field: 'monthly_arrears_months', title: __('Monthly_arrears_months')},
                            {field: 'monthly_note', title: __('Monthly_note'),operate:false},
                            {
                                field: 'operate', title: __('Operate'), table: deductionsSucc,
                                buttons: [
                                    {
                                        icon: 'fa fa-trash', name: 'del', icon: 'fa fa-trash', extend: 'data-toggle="tooltip"', title: __('Del'), classname: 'btn btn-xs btn-danger btn-delone',
                                        url: 'monthly/newcarmonthly/del',/**删除 */


                                    },
                                    {
                                        name: 'edit', text: '', icon: 'fa fa-pencil', extend: 'data-toggle="tooltip"', title: __('Edit'), classname: 'btn btn-xs btn-success btn-editone',
                                        url: 'monthly/newcarmonthly/edit',/**编辑 */

                                    },


                                ],
                                events: Controller.api.events.operate,

                                formatter: Controller.api.formatter.operate

                            }
                        ]
                    ]
                });
                Table.api.bindevent(deductionsSucc);
                deductionsSucc.on('load-success.bs.table', function (e, data) {
                    $('#deductions_succ_badge').text(data.total);
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
            events: {
                operate: {
                    //编辑按钮
                    'click .btn-editone': function (e, value, row, index) { /**编辑按钮 */
                    $(".btn-editone").data("area", ["95%", "95%"]);

                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, { ids: ids });
                        var url = options.extend.edit_url;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
                    },
                    //删除按钮
                    'click .btn-delone': function (e, value, row, index) {  /**删除按钮 */

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
                            __('Are you sure you want to delete this item?'),
                            { icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true },
                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');
                                Table.api.multi("del", row[options.pk], table, that);
                                Layer.close(index);
                            }
                        );
                    },
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

                    var colorArr = { has_been_sent: 'success', did_not_send: 'danger'};
                    //如果字段列有定义custom
                    if (typeof this.custom !== 'undefined') {
                        colorArr = $.extend(colorArr, this.custom);
                    }
                    value = value === null ? '' : value.toString();

                    var color = value && typeof colorArr[value] !== 'undefined' ? colorArr[value] : 'primary';
                    console.log(value);
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
    return Controller;
});
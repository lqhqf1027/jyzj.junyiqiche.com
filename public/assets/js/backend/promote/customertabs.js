define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'promote/customertabs/index' + location.search,
                    add_url: 'promote/customertabs/add',
                    edit_url: 'promote/customertabs/edit',
                    del_url: 'promote/customertabs/del',
                    multi_url: 'promote/customertabs/multi',
                    import_url: 'promote/customertabs/import',
                    table: 'customer_resource',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate: false,sortable:true},

                        {
                            field: 'backoffice.nickname',
                            title: __('所属内勤'),
                            formatter: Controller.api.formatter.backoffice
                        },
                        {field: 'username', title: __('Username')},
                        {field: 'phone', title: __('Phone')},

                        {
                            field: 'distributinternaltime',
                            title: __('Distributinternaltime'),
                            operate: false,
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime,
                            datetimeFormat: "YYYY-MM-DD",
                            sortable:true
                        },

                        {field: 'invalidtime', title: __('失效时间'), formatter: function (v, r, i) {
                            return Controller.api.formatter.datetime(v);
                        }},

                        {field: 'admin.nickname', title: __('销售员'),formatter: Controller.api.formatter.saleAvatar},

                        {
                            field: 'feedback_content',
                            title: __('反馈结果'),
                            operate: false,
                            formatter: function (v, r, i) {
                                return Controller.feedFun(v);
                            }
                        },

                        {field: 'status', title: __('Status'), searchList: {"今日头条":__('今日头条'),"百度":__('百度'),"58同城":__('58同城'),"抖音":__('抖音'),"转介绍":__('转介绍'),"自己邀约":__('自己邀约'),"其他":__('其他')}, formatter: Table.api.formatter.status},

                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,

                            events: Controller.api.events.operate,
                            formatter: Controller.api.operate
                        },
                        
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            batch_share('.btn-selected', table);


        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        //单个分配
        dstribution: function () {

            // $(".btn-add").data("area", ["300px","200px"]);
            Table.api.init({});
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据

                Fast.api.close(data);//这里是重点
                // console.log(data);
                // Toastr.success("成功");//这个可有可无
            }, function (data, ret) {
                // console.log(data);
                Toastr.success("失败");
            });
            Controller.api.bindevent();
            // console.log(Config.id);

        },
        //批量分配
        distribution: function () {


            // $(".btn-add").data("area", ["300px","200px"]);
            Table.api.init({});
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据

                // console.log(data);
                // newAllocationNum = parseInt($('#badge_new_allocation').text());
                // num = parseInt(data);
                // $('#badge_new_allocation').text(num+newAllocationNum);
                Fast.api.close(data);//这里是重点

                // Toastr.success("成功");//这个可有可无
            }, function (data, ret) {
                // console.log(data);

                Toastr.success("失败");

            });
            // Controller.api.bindevent();
            // console.log(Config.id);


        },
        //批量导入
        import: function () {
            // console.log(123);
            // return;
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                Fast.api.close(data);//这里是重点

                // Toastr.success("成功");//这个可有可无
            }, function (data, ret) {
                // console.log(data);

                Toastr.success("失败");

            });
            Controller.api.bindevent();
            // console.log(Config.id);

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
         * 排序
         * @param property
         * @returns {function(*, *): number}
         */
        compare: function (property) {
            return function (a, b) {
                var value1 = a[property];
                var value2 = b[property];
                return value2 - value1;
            }
        },
        /**
         * 记录反馈内容
         * @param v 时间戳
         * @returns {string}
         */
        feedFun: function (v) {
            v = v.sort(Controller.compare('feedbacktime'));
            var feedHtml = '';
            if (v != null) {
                if (v.length > 4) {
                    var arr = [];

                    for (var i in v) {
                        if (i > 3) {
                            break;
                        }

                        arr.push(v[i]);
                    }


                    for (var i in arr) {
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
                        if(arr[i]['feedbackcontent'].length>=30){
                            arr[i]['feedbackcontent'] = arr[i]['feedbackcontent'].replace(arr[i]['feedbackcontent'].substr(30),'...');
                        }
                        feedHtml += "<span class='text-gray'>" + Controller.getDateDiff(arr[i]["feedbacktime"]) + '（' + Controller.getLocalTime(arr[i]['feedbacktime']) + '）' + '&nbsp;' + "</span>" + arr[i]['feedbackcontent'] + "（等级：" + level + "）" + '<br>';
                    }

                } else {
                        for (var i in v) {

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
                            if(v[i]['feedbackcontent'].length>=30){
                                v[i]['feedbackcontent'] = v[i]['feedbackcontent'].replace(v[i]['feedbackcontent'].substr(30),'...');
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
                    /**
                     * 分配给内勤
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-newCustomer': function (e, value, row, index) {
                        $(".btn-newCustomer").data("area", ["30%", "30%"]);
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'promote/customertabs/dstribution';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
                    },
                    //删除按钮
                    'click .btn-delone': function (e, value, row, index) {
                        /**删除按钮 */

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
                            {icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true},
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
            operate: function (value, row, index) {
                var table = this.table;
                // 操作配置
                var options = table ? table.bootstrapTable('getOptions') : {};
                // 默认按钮组
                var buttons = $.extend([], this.buttons || []);



                if (row.backoffice_id == null) {
                    buttons.push(
                        {
                            name: 'detail',
                            text: '分配',
                            title: '分配',
                            icon: 'fa fa-share',
                            classname: 'btn btn-xs btn-info btn-newCustomer',
                        },
                        {
                            name: 'del',
                            icon: 'fa fa-trash',
                            title: __('Del'),
                            extend: 'data-toggle="tooltip"',
                            classname: 'btn btn-xs btn-danger btn-delone'
                        }
                    )
                } else {
                    buttons.push(
                        {
                            name: 'allocated',
                            text: '已分配给内勤',
                            title: '已分配',
                            icon: 'fa fa-check',
                            classname: 'text-info',
                        }
                    );
                }


                return Table.api.buttonlink(this, buttons, value, row, index, 'operate');
            },
            formatter: {
                //内勤头像
                backoffice: function (value, row, index) {
                    if (value) {
                        row.backoffice.avatar = row.avatar_url + row.backoffice.avatar;
                    }
                    return value != null ? "<img src=" + row.backoffice.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' + value : value;

                },
                //销售头像
                saleAvatar:function (value,row,index) {

                    switch (row.admin.rule_message){
                        case 'message6':

                            return  "<img src=" + row.avatar_url + row.admin.avatar+" style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' + '销售顾问 - '+value;
                            break;
                        default:
                            return '未知销售';
                            break;
                    }
                },
                /**
                 * 失效时间
                 */
                datetime: function (v) {
                    var datetimeFormat = typeof this.datetimeFormat === 'undefined' ? 'YYYY-MM-DD HH:mm:ss' : this.datetimeFormat;
                    if (isNaN(v)) {
                        return v ? Moment(v).format(datetimeFormat) : __('None');
                    } else {
                        return v ? Moment(parseInt(v) * 1000).format(datetimeFormat) : __('None');
                    }
                },
            },
            operate: function (value, row, index) {
                var table = this.table;
                // 操作配置
                var options = table ? table.bootstrapTable('getOptions') : {};
                // 默认按钮组
                var buttons = $.extend([], this.buttons || []);



                if (row.backoffice_id == null) {
                    buttons.push(
                        {
                            name: 'detail',
                            text: '分配',
                            title: '分配',
                            icon: 'fa fa-share',
                            classname: 'btn btn-xs btn-info btn-newCustomer',
                        },
                        {
                            name: 'del',
                            icon: 'fa fa-trash',
                            title: __('Del'),
                            extend: 'data-toggle="tooltip"',
                            classname: 'btn btn-xs btn-danger btn-delone'
                        }
                    )
                } else {
                    buttons.push(
                        {
                            name: 'allocated',
                            text: '已分配给内勤',
                            title: '已分配',
                            icon: 'fa fa-check',
                            classname: 'text-info',
                        }
                    );
                }


                return Table.api.buttonlink(this, buttons, value, row, index, 'operate');
            }
        }
    };

    /**
     * 批量分配
     * @param clickname
     * @param table
     */
    function batch_share(clickname, table) {
        var num = 0;
        $(document).on("click", clickname, function () {
            var ids = Table.api.selectedids(table);
            num = parseInt(ids.length);
            var url = 'promote/customertabs/distribution?ids=' + ids;
            var options = {
                shadeClose: false,
                shade: [0.3, '#393D49'],
                area: ['30%', '30%'],
                callback: function (value) {

                }
            }
            Fast.api.open(url, '批量分配', options)
        })
    }


    return Controller;
});
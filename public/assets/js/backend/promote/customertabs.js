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
            // $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");

            $('ul.nav-tabs li a[data-toggle="tab"]').each(function () {
                $(this).trigger("shown.bs.tab")
            })
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
        table: {
            /**
             * 今日头条
             */
            headline: function () {

                var headlines = $("#headlines");
                headlines.bootstrapTable({
                    url: 'promote/Customertabs/headline',
                    extend: {
                        index_url: 'customer/customerresource/index',
                        add_url: 'customer/customerresource/add',
                        // edit_url: 'customer/customerresource/edit',
                        del_url: 'promote/customertabs/del',
                        multi_url: 'promote/customertabs/multi',
                        distribution_url: 'promote/customertabs/distribution',
                        import_url: 'promote/customertabs/import',
                        table: 'customer_resource',
                    },
                    toolbar: '#toolbar1',
                    pk: 'id',
                    // sortName: 'id',
                    searchFormVisible: true,
                    // fixedColumns:true,
                    // fixedNumber:1,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'), operate: false,sortable:true},

                            {field: 'platform.name', title: __('所属平台')},

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
                                datetimeFormat: "YYYY-MM-DD"
                                ,sortable:true
                            },
                            // {
                            //     field: 'createtime',
                            //     title: __('导入时间'),
                            //     operate: false,
                            //     addclass: 'datetimerange',
                            //     formatter: Table.api.formatter.datetime,
                            //     datetimeFormat: "YYYY-MM-DD"
                            //     ,sortable:true
                            // },

                            // {
                            //     field: 'updatetime',
                            //     title: __('Updatetime'),
                            //     operate: false,
                            //     addclass: 'datetimerange',
                            //     formatter: Table.api.formatter.datetime,
                            //     datetimeFormat: "YYYY-MM-DD"
                            //     ,sortable:true
                            // },
                            {field: 'admin.nickname', title: __('销售员'),formatter: Controller.api.formatter.saleAvatar},
                            {
                                field: 'feedback_content',
                                title: __('反馈结果'),
                                operate: false,
                                formatter: function (v, r, i) {
                                    return Controller.feedFun(v);
                                }
                            },
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: headlines,

                                events: Controller.api.events.operate,
                                formatter: Controller.api.operate
                            }

                        ]
                    ]

                });

                // 为已分配的客户表格绑定事件
                Table.api.bindevent(headlines);

                //数据实时统计
                headlines.on('load-success.bs.table', function (e, data) {

                        // $('#badge_new_toutiao').text(data.total);
                })
                add_data('.add-headline', headlines, 'promote/Customertabs/add_headline');
                batch_share('.btn-selected-headline', headlines);


            },

            /**
             * 百度
             */
            baidu: function () {
                // 已分配的客户
                var baidus = $("#baidus");
                baidus.bootstrapTable({
                    url: 'promote/Customertabs/baidu',
                    extend: {
                        index_url: 'customer/customerresource/index',
                        add_url: 'customer/customerresource/add',
                        // edit_url: 'customer/customerresource/edit',
                        del_url: 'promote/customertabs/del',
                        multi_url: 'promote/customertabs/multi',
                        distribution_url: 'promote/customertabs/distribution',
                        import_url: 'promote/customertabs/import',
                        table: 'customer_resource',
                    },
                    toolbar: '#toolbar5',
                    pk: 'id',
                    // sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'), operate: false,sortable:true},

                            {field: 'platform.name', title: __('所属平台')},

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
                                datetimeFormat: "YYYY-MM-DD"
                                ,sortable:true

                            },
                            {field: 'admin.nickname', title: __('销售员'),formatter: Controller.api.formatter.saleAvatar},
                            {
                                field: 'feedback_content',
                                title: __('反馈结果'),
                                operate: false,
                                formatter: function (v, r, i) {
                                    return Controller.feedFun(v);
                                }
                            },
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: baidus,

                                events: Controller.api.events.operate,
                                formatter: Controller.api.operate
                            }

                        ]
                    ]

                });

                // 为已分配的客户表格绑定事件
                Table.api.bindevent(baidus);

                //数据实时统计
                baidus.on('load-success.bs.table', function (e, data) {
                    // $('#badge_new_baidu').text(data.total);
                })



                add_data('.add-baidu', baidus, 'promote/Customertabs/add_baidu');

                batch_share('.btn-selected-baidu', baidus);


            },

            /**
             * 58同城
             */
            same_city: function () {
                // 已分配的客户
                var sameCity = $("#sameCity");
                sameCity.bootstrapTable({
                    url: 'promote/Customertabs/same_city',
                    extend: {
                        index_url: 'customer/customerresource/index',
                        add_url: 'customer/customerresource/add',
                        // edit_url: 'customer/customerresource/edit',
                        del_url: 'promote/customertabs/del',
                        multi_url: 'promote/customertabs/multi',
                        distribution_url: 'promote/customertabs/distribution',
                        import_url: 'promote/customertabs/import',
                        table: 'customer_resource',
                    },
                    toolbar: '#toolbar6',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'), operate: false,sortable:true},

                            {field: 'platform.name', title: __('所属平台')},

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
                                datetimeFormat: "YYYY-MM-DD"
                                ,sortable:true
                            },

                            {field: 'invalidtime', title: __('失效时间')},
                            {field: 'admin.nickname', title: __('销售员'),formatter: Controller.api.formatter.saleAvatar},
                            {
                                field: 'feedback_content',
                                title: __('反馈结果'),
                                operate: false,
                                formatter: function (v, r, i) {
                                    return Controller.feedFun(v);
                                }
                            },
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: sameCity,

                                events: Controller.api.events.operate,
                                formatter: Controller.api.operate
                            }

                        ]
                    ]

                });

                // 为已分配的客户表格绑定事件
                Table.api.bindevent(sameCity);

                //数据实时统计
                sameCity.on('load-success.bs.table', function (e, data) {

                //   $('#badge_new_58').text(data.total);
                })
                add_data('.add-same_city', sameCity, 'promote/Customertabs/add_same_city');

                batch_share('.btn-selected-same_city', sameCity);


            },

            /**
             * 抖音
             */
            music: function () {
                // 已分配的客户
                var musics = $("#musics");
                musics.bootstrapTable({
                    url: 'promote/Customertabs/music',
                    extend: {
                        index_url: 'customer/customerresource/index',
                        add_url: 'customer/customerresource/add',
                        // edit_url: 'customer/customerresource/edit',
                        del_url: 'promote/customertabs/del',
                        multi_url: 'promote/customertabs/multi',
                        distribution_url: 'promote/customertabs/distribution',
                        import_url: 'promote/customertabs/import',
                        table: 'customer_resource',
                    },
                    toolbar: '#toolbar7',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'), operate: false,sortable:true},

                            {field: 'platform.name', title: __('所属平台')},

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
                                datetimeFormat: "YYYY-MM-DD"
                                ,sortable:true
                            },
                            {field: 'admin.nickname', title: __('销售员'),formatter: Controller.api.formatter.saleAvatar},
                            {
                                field: 'feedback_content',
                                title: __('反馈结果'),
                                operate: false,
                                formatter: function (v, r, i) {
                                    return Controller.feedFun(v);
                                }
                            },
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: musics,

                                events: Controller.api.events.operate,
                                formatter: Controller.api.operate
                            }

                        ]
                    ]

                });

                // 为已分配的客户表格绑定事件
                Table.api.bindevent(musics);

                //数据实时统计
                musics.on('load-success.bs.table', function (e, data) {
                    // $('#badge_new_douyin').text(data.total);

                })

                add_data('.add-music', musics, 'promote/Customertabs/add_music');

                batch_share('.btn-selected-music', musics);


            },
        },

        add: function () {
            Controller.api.bindevent();

        },
        add_headline: function () {
            Controller.api.bindevent();
        },
        add_baidu: function () {
            Controller.api.bindevent();
        },
        add_same_city: function () {
            Controller.api.bindevent();
        },
        add_music: function () {
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
            formatter: {
                //内勤头像
                backoffice: function (value, row, index) {
                    if (value) {
                        row.backoffice.avatar = "https://static.aicheyide.com" + row.backoffice.avatar;
                    }
                    return value != null ? "<img src=" + row.backoffice.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' + value : value;

                },
                //销售头像
                saleAvatar:function (value,row,index) {

                    switch (row.admin.rule_message){
                        case 'message8':

                            return  "<img src=" + row.admin.avatar_url + row.admin.avatar+" style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' + '销售一部 - '+value;
                            break;
                        case 'message9':
                            return  "<img src=" + row.admin.avatar_url + row.admin.avatar+" style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' + '销售二部 - '+value;

                            break;
                        case 'message23':
                            return  "<img src=" + row.admin.avatar_url + row.admin.avatar+" style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' + '销售三部 - '+value;

                            break;
                        default:
                            return '未知销售';
                            break;
                    }
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
            }
        }

    };


    /**
     * 添加按钮
     * @param clickname
     * @param table
     * @param urls
     */
    function add_data(clickname, table, urls) {
        $(document).on('click', clickname, function () {
            var ids = Table.api.selectedids(table);
            var url = urls;
            if (url.indexOf("{ids}") !== -1) {
                url = Table.api.replaceurl(url, {ids: ids.length > 0 ? ids.join(",") : 0}, table);
            }
            Fast.api.open(url, __('Add'), $(this).data() || {});
        });
    }


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
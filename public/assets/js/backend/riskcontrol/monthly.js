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
                    url: 'riskcontrol/monthly/newcarMonthly',
                    extend: {
                        add_url: 'monthly/newcarmonthly/add',
                        edit_url: 'monthly/newcarmonthly/edit',
                        del_url: 'monthly/newcarmonthly/del',
                        import_url: 'monthly/newcarmonthly/import',
                        multi_url: 'monthly/newcarmonthly/multi',
                        table: 'newcar_monthly',
                    },
                    toolbar: '#toolbar1',
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
                            {field: 'monthly_monney', title: __('Monthly_monney'), operate: false},
                            {
                                field: 'monthly_data',
                                title: __('Monthly_data'),
                                searchList: {
                                    "failure": __('Monthly_data failure'),
                                    "success": __('Monthly_data success')
                                },
                                formatter: function (v, r, i) {
                                    if(v==='failure'&& r.monthly_supplementary !=null){
                                        v = '<span class="text-danger">失败</span>';
                                        return v+' <span class="label label-success">'+r.monthly_supplementary+'</span>'

                                    }else {
                                        return '<span class="text-danger">失败</span>';
                                    }
                                }
                            },
                            {field: 'monthly_failure_why', title: __('Monthly_failure_why'),operate:false},
                            {
                                field: 'monthly_in_arrears_time',
                                title: __('Monthly_in_arrears_time'),
                                operate: 'RANGE',
                                addclass: 'datetimerange'
                            },
                            {field: 'monthly_company', title: __('Monthly_company')},
                            {field: 'monthly_car_number', title: __('Monthly_car_number')},
                            {field: 'monthly_family_unit', title: __('上户单位')},

                            // {field: 'monthly_arrears_months', title: __('Monthly_arrears_months')},
                            {field: 'monthly_note', title: __('Monthly_note'),operate:false},
                            {
                                field: 'operate', title: __('Operate'), table: newcarMonthly,
                                buttons: [
                                    {
                                        icon: 'fa fa-trash',
                                        name: 'del',
                                        icon: 'fa fa-trash',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('Del'),
                                        classname: 'btn btn-xs btn-danger btn-delone',
                                        url: 'monthly/newcarmonthly/del', /**删除 */


                                    },
                                    {
                                        name: 'edit',
                                        text: '',
                                        icon: 'fa fa-pencil',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('Edit'),
                                        classname: 'btn btn-xs btn-success btn-editone',
                                        url: 'monthly/newcarmonthly/edit', /**编辑 */

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
                })
                //导出新客户的信息
                var submitForm = function (ids, layero) {
                    var options = newcarMonthly.bootstrapTable('getOptions');
                    var columns = [];
                    $.each(options.columns[0], function (i, j) {
                        if (j.field && !j.checkbox && j.visible && j.field != 'operate') {
                            columns.push(j.field);
                        }
                    });
                    var search = options.queryParams({});
                    $("input[name=search]", layero).val(options.searchText);
                    $("input[name=ids]", layero).val(ids);
                    $("input[name=filter]", layero).val(search.filter);
                    $("input[name=op]", layero).val(search.op);
                    $("input[name=columns]", layero).val(columns.join(','));
                    $("form", layero).submit();
                };
                /**
                 * 批量导出客户信息
                 * @param ids
                 * @param layero
                 */
                $(document).on("click", ".btn-export", function () {
                    var ids = Table.api.selectedids(newcarMonthly);
                    var page = newcarMonthly.bootstrapTable('getData');
                    var all = newcarMonthly.bootstrapTable('getOptions').totalRows;
                    Layer.confirm("请选择导出的选项<form action='" + Fast.api.fixurl("riskcontrol/monthly/export") + "' method='post' target='_blank'><input type='hidden' name='ids' value='' /><input type='hidden' name='filter' ><input type='hidden' name='op'><input type='hidden' name='search'><input type='hidden' name='columns'></form>", {
                        title: '导出数据',
                        btn: ["选中项(" + ids.length + "条)", "本页(" + page.length + "条)",  "<span class='text-danger'>全部(" + all + "条)</span>"],
                        success: function (layero, index) {
                            $(".layui-layer-btn a", layero).addClass("layui-layer-btn0");
                        }
                        ,
                        yes: function (index, layero) {
                            if (ids.length < 1) {
                                Layer.alert('请先选择要导出的数据!', {icon: 5})
                                return false;
                            }
                            submitForm(ids.join(","), layero);
                            // return false;
                        }
                        ,
                        btn2: function (index, layero) {
                            var ids = [];
                            $.each(page, function (i, j) {
                                ids.push(j.id);
                            });
                            submitForm(ids.join(","), layero);
                            // return false;
                        }
                        ,
                        btn3: function (index, layero) {
                            submitForm("all", layero);
                            // return false;
                        }
                    })
                });
                /**
                 * 批量发送短信
                 */
                $(document).on("click", ".btn-selected", function () {
                    var ids = [];
                    var tableRow = Controller.api.selectIdsRow(newcarMonthly);//获取选中的行数据

                    var page = newcarMonthly.bootstrapTable('getData');

                    //选中项
                    if(tableRow!=''){
                        for (var i in tableRow) {
                            ids.push({
                                'id':tableRow[i]['id'],
                                'monthly_name':tableRow[i]['monthly_name'],
                                'monthly_phone_number':tableRow[i]['monthly_phone_number'].slice('1,10'),
                                'monthly_card_number':tableRow[i]['monthly_card_number'].match(/.*(.{4})/)[1],
                                'monthly_monney':tableRow[i]['monthly_monney']
                            });
                        }
                    }

                    var closeLay = Layer.confirm("请选择要发送的客户数据", {
                        title: '发送数据',
                        btn: ["选中项(" + ids.length + "条)", "本页(" + page.length + "条)"],
                        success: function (layero, index) {
                            $(".layui-layer-btn a", layero).addClass("layui-layer-btn0");
                        }
                        ,
                        //选中项
                        yes: function (index, layero) {
                            // var sendTemplte = Layer.confirm('请选择发送类型',{
                            //     title:'选择要发送的模板类型',
                            //     btn:['①提醒']
                            // })
                            if (ids.length < 1) {
                                Layer.alert('数据不能为空!', {icon: 5})
                                return false;
                            }

                            Fast.api.ajax({
                                url: 'riskcontrol/monthly/sedMessage',
                                data: {ids}

                            }, function (data, ret) {
                                Layer.close(closeLay);
                                newcarMonthly.bootstrapTable('refresh');
                            })
                        }
                        ,
                        //本页
                        btn2: function (index, layero) {
                             ids = [];
                            for (var i in page) {
                                ids.push({
                                    'id':page[i]['id'],
                                    'monthly_name':page[i]['monthly_name'],
                                    'monthly_phone_number':page[i]['monthly_phone_number'].slice(0,11),
                                    'monthly_card_number':page[i]['monthly_card_number'].match(/.*(.{4})/)[1],
                                    'monthly_monney':page[i]['monthly_monney']
                                });
                            }

                            // return;false;
                            Fast.api.ajax({
                                url: 'riskcontrol/monthly/sedMessage',
                                data: {ids}
                            }, function (data, ret) {
                                Layer.close(closeLay);

                                newcarMonthly.bootstrapTable('refresh');
                            })
                        }
                        ,
                    })
                });
            },
            /**
             * 扣款成功客户
             */
            deductions_succ: function () {
                var deductionsSucc = $("#deductionsSucc");
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                    return "快速搜索客户姓名";
                };
                deductionsSucc.bootstrapTable({
                    url: 'riskcontrol/monthly/deductionsSucc',
                    extend: {
                        add_url: 'monthly/newcarmonthly/add',
                        edit_url: 'monthly/newcarmonthly/edit',
                        del_url: 'monthly/newcarmonthly/del',
                        import_url: 'monthly/newcarmonthly/import',
                        multi_url: 'monthly/newcarmonthly/multi',
                        table: 'newcar_monthly',
                    },
                    toolbar: '#toolbar2',
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
                            {field: 'monthly_monney', title: __('Monthly_monney'), operate: false},
                            {
                                field: 'monthly_data',
                                title: __('Monthly_data'),
                                searchList: {
                                    "failure": __('Monthly_data failure'),
                                    "success": __('Monthly_data success')
                                },
                                formatter: Table.api.formatter.normal
                            },
                            {field: 'monthly_failure_why', title: __('Monthly_failure_why')},
                            {
                                field: 'monthly_in_arrears_time',
                                title: __('Monthly_in_arrears_time'),
                                operate: 'RANGE',
                                addclass: 'datetimerange'
                            },
                            {field: 'monthly_company', title: __('Monthly_company')},
                            {field: 'monthly_car_number', title: __('Monthly_car_number')},
                            {field: 'monthly_family_unit', title: __('上户单位')},

                            // {field: 'monthly_arrears_months', title: __('Monthly_arrears_months')},
                            {field: 'monthly_note', title: __('Monthly_note'),operate:false},
                            {
                                field: 'operate', title: __('Operate'), table: deductionsSucc,
                                buttons: [
                                    {
                                        icon: 'fa fa-trash',
                                        name: 'del',
                                        icon: 'fa fa-trash',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('Del'),
                                        classname: 'btn btn-xs btn-danger btn-delone',
                                        url: 'monthly/newcarmonthly/del', /**删除 */


                                    },
                                    {
                                        name: 'edit',
                                        text: '',
                                        icon: 'fa fa-pencil',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('Edit'),
                                        classname: 'btn btn-xs btn-success btn-editone',
                                        url: 'monthly/newcarmonthly/edit', /**编辑 */

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
        /**
         * 数组去重
         * @param arr
         * @returns {Array}
         */
        deleteRepetion:function(arr){
            var arrTable = {},arrData = [];
            for (var i = 0; i < arr.length; i++) {
                if( !arrTable[ arr[i] ]){
                    arrTable[ arr[i] ] = true;
                    arrData.push(arr[i])
                }
            }
            return arrData;
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
                    'click .btn-editone': function (e, value, row, index) {
                        /**编辑按钮 */
                        $(".btn-editone").data("area", ["95%", "95%"]);

                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = options.extend.edit_url;
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
                operate: function (value, row, index) {

                    var table = this.table;
                    // 操作配置
                    var options = table ? table.bootstrapTable('getOptions') : {};
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);

                    return Table.api.buttonlink(this, buttons, value, row, index, 'operate');
                },
                status: function (value, row, index) {

                    var colorArr = {has_been_sent: 'success', did_not_send: 'danger'};
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

            },
            /**
             * 得到选中行信息
             * @param table
             * @returns {*}
             */
            selectIdsRow: function (table) {
                var options = table.bootstrapTable('getOptions');
                if (options.templateView) {
                    return $.map($("input[data-id][name='checkbox']:checked"), function (dom) {
                        return $(dom)
                    });
                } else {
                    return $.map(table.bootstrapTable('getSelections'), function (row) {
                        return row;
                    });
                }
            }

        }

    };


    return Controller;
});


define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({

                extend: {
                    index_url: 'plan/planacar/index',
                    add_url: 'planmanagement/plantabs/firstadd',
                    edit_url: 'planmanagement/plantabs/firstedit',
                    del_url: 'planmanagement/plantabs/firstdel',
                    multi_url: 'planmanagement/plantabs/firstmulti',
                    import_url: 'planmanagement/plantabs/import',
                    table: 'plan_acar',
                },

            });

            // //绑定事件
            // $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            //     var panel = $($(this).attr("href"));
            //     if (panel.size() > 0) {
            //         Controller.table[panel.attr("id")].call(this);
            //         $(this).on('click', function (e) {
            //             $($(this).attr("href")).find(".btn-refresh").trigger("click");
            //         });
            //     }
            //     //移除绑定的事件
            //     $(this).unbind('shown.bs.tab');
            // });

            // //必须默认触发shown.bs.tab事件
            // $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");

            var table1 = $("#table1");
                table1.on('load-success.bs.table', function (e, data) {
                    var arr = data.rows;
                    // console.log(arr);
                    Controller.merge(arr, table1);
                    //靠左对齐
                    var td = $("#table1 td:nth-child(5)");

                    for (var i = 0; i<td.length;i++) {
            
                        td[i].style.textAlign = "left";

                    }
                });

                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                    return "快速搜索车型";
                };
                $(".btn-add").data("area", ["80%", "80%"]);
                // 初始化表格
                table1.bootstrapTable({
                    url: 'planmanagement/plantabs/table1',
                    toolbar: '#toolbar1',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {
                                checkbox: true, formatter: function (v, r, i) {
                                    return r.match_plan === 'match_success' ? {disabled: true} : {disabled: false};
                                }
                            },
                            {field: 'id', title: __('Id'), operate: false},
                            {field: 'city_store', title: __('城市门店'), operate: false},
                            {
                                field: 'schemecategory.name', title: __('方案类型'), formatter: function (v, r, i) {
                                    // console.log( r.schemecategory.category_note.length);
                                    return r.schemecategory.category_note != null ? v + "<br />" + '<u>' + Controller.substrPlanTyleNode(r.schemecategory.category_note, 16) + '</u>' : v;
                                }
                            },
                            // {field: 'schemecategory.category_note', title: __('方案类型备注'),operate:false},
                            {
                                field: 'models.name', title: '销售车型', operate: false, formatter: function (v, r, i) {
                                    return v != null ? "<img src=" + r.brand_log + " alt='品牌logo' width='30' height='30'>"+ v : v;
                                }
                            },
                            {field: 'financialplatform.name', title: '所属金融平台'},
                            {field: 'payment', title: __('Payment'), operate: 'BETWEEN', operate: false},
                            {field: 'monthly', title: __('NewcarMonthly'), operate: 'BETWEEN', operate: false},
                            {
                                field: 'nperlist',
                                title: __('Nperlist'),
                                visible: false,
                                searchList: {
                                    "12": __('Nperlist 12'),
                                    "24": __('Nperlist 24'),
                                    "36": __('Nperlist 36'),
                                    "48": __('Nperlist 48'),
                                    "60": __('Nperlist 60')
                                }
                            },
                            {field: 'nperlist_text', title: __('Nperlist'), operate: false},
                            {field: 'margin', title: __('Margin'), operate: 'BETWEEN', operate: false},
                            {field: 'tail_section', title: __('Tail_section'), operate: 'BETWEEN', operate: false},
                            {field: 'gps', title: __('Gps'), operate: false},
                            {field: 'admin.nickname', title: __('销售定制方案')},
                            {
                                field: 'working_insurance',
                                title: __('是否营运险'),
                                searchList: {"yes": '是', "no": "否"},
                                formatter: function (v, r, i) {
                                    return r.working_insurance == 'yes' ? '是' : '否'
                                }
                            },

                            {
                                field: 'note', title: __('销售方案备注'), operate: false, formatter: function (v, r, i) {

                                    return v != null ? '<u>' + Controller.substrPlanTyleNode(v, 16) + '</u>' : v;

                                }
                            },

                            {
                                field: 'createtime',
                                title: __('Createtime'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime,
                                datetimeFormat: 'YYYY-MM-DD'
                            },
                            {
                                field: 'updatetime',
                                title: __('Updatetime'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime,
                                datetimeFormat: 'YYYY-MM-DD'
                            },
                            {
                                field: 'ismenu',
                                title: __('Ismenu'),
                                formatter: Controller.api.formatter.toggle,
                                operate: false
                            },

                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: table1,
                                events: Controller.api.events.operate,
                                formatter: Controller.api.operate
                            }
                        ]
                    ]
                });
                // 为表格1绑定事件
                Table.api.bindevent(table1);
                $(document).on('click', '.btn_import_dialog', function () {

                    var url = 'planmanagement/plantabs/import_first_plan';
                    var options = {
                        shadeClose: false,
                        shade: [0.3, '#393D49'],
                        area: ['90%', '90%'],
                        callback: function (value) {

                        }
                    }
                    Fast.api.open(url, '批量分配', options)

                });

                $(document).on("click", "a.btn-channel", function () {
                    $("#archivespanel").toggleClass("col-md-9", $("#channelbar").hasClass("hidden"));
                    $("#channelbar").toggleClass("hidden");
                });

                require(['jstree'], function () {
                    //全选和展开
                    $(document).on("click", "#checkall", function () {
                        $("#channeltree").jstree($(this).prop("checked") ? "check_all" : "uncheck_all");
                    });
                    $(document).on("click", "#expandall", function () {
                        $("#channeltree").jstree($(this).prop("checked") ? "open_all" : "close_all");
                    });
                    $('#channeltree').on("changed.jstree", function (e, data) {
                        console.log(data);
                        console.log(data.selected);
                        var options = table1.bootstrapTable('getOptions');
                        options.pageNumber = 1;
                        options.queryParams = function (params) {
                            params.filter = JSON.stringify(data.selected.length > 0 ? {store_id: data.selected.join(",")} : {});
                            params.op = JSON.stringify(data.selected.length > 0 ? {store_id: 'in'} : {});
                            return params;
                        };
                        table1.bootstrapTable('refresh', {});
                        return false;
                    });
                    $('#channeltree').jstree({
                        "themes": {
                            "stripes": true
                        },
                        "checkbox": {
                            "keep_selected_style": false,
                        },
                        "types": {
                            "channel": {
                                "icon": "fa fa-th",
                            },
                            "list": {
                                "icon": "fa fa-list",
                            },
                            "link": {
                                "icon": "fa fa-link",
                            },
                            "disabled": {
                                "check_node": false,
                                "uncheck_node": false
                            }
                        },
                        'plugins': ["types", "checkbox"],
                        "core": {
                            "multiple": true,
                            'check_callback': true,
                            "data": Config.storeList
                        }
                    });
                });

        },
        add: function () {

            Controller.api.bindevent();
        },
        edit: function () {

            Controller.api.bindevent();
        },
        /**
         * 合并表格
         * @param arr
         * @param obj
         */
        merge: function (arr, obj) {
            var hash = [];
            var data_arr = [];
            for (var i in arr) {


                if (hash.indexOf(arr[i]['schemecategory']['name']) == -1) {

                    hash.push(arr[i]['schemecategory']['name']);

                    data_arr.push([i, arr[i]['schemecategory']['name'], 0]);
                }


            }


            for (var i in arr) {
                for (var j in data_arr) {
                    if (arr[i]['schemecategory']['name'] == data_arr[j][1]) {
                        data_arr[j][2]++;
                    }
                }
            }

            // console.log(data_arr);

            for (var i in data_arr) {

                obj.bootstrapTable("mergeCells", {
                    index: data_arr[i][0],
                    field: 'schemecategory.name',
                    rowspan: data_arr[i][2]
                });

                var td = $(obj).find("tr[data-index=" + data_arr[i][0] + "]").find("td");


                if (data_arr[i][1] != null) {
                    i % 2 == 0 ? td.eq(2).css({"background-color": "#fff"}) : td.eq(2).css({"background-color": "#ddd"});
                }


            }
        },

        firstedit: function () {
            
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

            //门店下的车型
            $(document).on("change", "#c-store_id", function () {

                $('#c-models_id_text').val('');
            });
            $("#c-models_id").data("params", function (obj) {

                return {custom: {store_ids: $('#c-store_id').val()}};

            });

            //门店下的类别
            $(document).on("change", "#c-store_id", function () {

                $('#c-category_id_text').val('');
            });
            $("#c-category_id").data("params", function (obj) {

                return {custom: {store_ids: $('#c-store_id').val()}};

            });

            Controller.api.bindevent();
        },
       
        //导入以租代购（新车）方案
        import_first_plan: function () {
            require(['upload'], function (Upload) {
                Upload.api.plupload($('.btn-import-dialog'), function (data, ret) {

                    Fast.api.ajax({
                        url: 'product/stock_import',
                        data: {file: data.url},
                    }, function (data, ret) {
                        //table.bootstrapTable('refresh');
                    });
                });
            });
            Controller.api.bindevent(function () {

            });

        },
        firstadd: function () {

            //门店下的车型
            $(document).on("change", "#c-store_id", function () {

                $('#c-models_id_text').val('');
            });
            $("#c-models_id").data("params", function (obj) {

                return {custom: {store_ids: $('#c-store_id').val()}};

            });

            //门店下的类别
            $(document).on("change", "#c-store_id", function () {

                $('#c-category_id_text').val('');
            });

            $("#c-category_id").data("params", function (obj) {

                return {custom: {store_ids: $('#c-store_id').val()}};

            });

            Controller.api.bindevent();
        },
       
        /**
         *   字符串按照指定长度换行
         * @param s   字符串
         * @param $length   长度
         * @returns { string}  返回新的数组
         */
        substrPlanTyleNode: function (s, $length) {
            ++$length;
            var re = '';
            var length = s.length;
            for (var i = 0, j = 1; i < length; i++, j++) {
                if (j && j % $length == 0) {
                    re += '<br />';
                } else {
                    re += s[i];
                }
            }
            return re;
        },

        api: {
            bindevent: function () {

                Form.api.bindevent($("form[role=form]"));
            },
            events: {
                operate: {
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
                    'click .btn-editone': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = options.extend.edit_url;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
                    },
                }
            },
            formatter: {

                /**
                 * 是否销售
                 * @param value
                 * @param row
                 * @param index
                 * @returns {string}
                 */
                toggle: function (value, row, index) {
                    var color = typeof this.color !== 'undefined' ? this.color : 'success';
                    var yes = typeof this.yes !== 'undefined' ? this.yes : 1;
                    var no = typeof this.no !== 'undefined' ? this.no : 0;
                    // return row.match_plan == 'match_success' ? '正在销售或已出售' : "<a href='javascript:;' data-toggle='tooltip' title='" + __('Click to toggle') + "' class='btn-change' data-id='"
                    //     + row.id + "' data-params='" + this.field + "=" + (value ? no : yes) + "'><i class='fa fa-toggle-on " + (value == yes ? 'text-' + color : 'fa-flip-horizontal text-gray') + " fa-2x'></i></a>";

                    return row.sales_id?'正在销售或已出售':"<a href='javascript:;' data-toggle='tooltip' title='" + __('Click to toggle') + "' class='btn-change' data-id='"
                        + row.id + "' data-params='" + this.field + "=" + (value ? no : yes) + "'><i class='fa fa-toggle-on " + (value == yes ? 'text-' + color : 'fa-flip-horizontal text-gray') + " fa-2x'></i></a>";

                },

            },
            operate: function (value, row, index) {

                var table = this.table;
                // 操作配置
                var options = table ? table.bootstrapTable('getOptions') : {};
                // 默认按钮组
                var buttons = $.extend([], this.buttons || []);
                if (row.match_plan == 'match_success') {
                    return '<span class="text-danger">禁止编辑或删除</span>'
                    // buttons.push(
                    //     {
                    //         name: 'edit',
                    //         icon: 'fa fa-pencil',
                    //         title: __('Edit'),
                    //         extend: 'data-toggle="tooltip"',
                    //         classname: 'btn btn-xs btn-success btn-editone',
                    //         url: options.extend.edit_url
                    //     }
                    // )
                }
                else {
                    buttons.push(
                        {
                            name: 'del',
                            icon: 'fa fa-trash',
                            title: __('Del'),
                            extend: 'data-toggle="tooltip"',
                            classname: 'btn btn-xs btn-danger btn-delone',
                            url: options.extend.del_url
                        },
                        {
                            name: 'edit',
                            icon: 'fa fa-pencil',
                            title: __('Edit'),
                            extend: 'data-toggle="tooltip"',
                            classname: 'btn btn-xs btn-success btn-editone',
                            url: options.extend.edit_url
                        }
                    )
                }
                return Table.api.buttonlink(this, buttons, value, row, index, 'operate');
            }
        }

    };
    return Controller;
});
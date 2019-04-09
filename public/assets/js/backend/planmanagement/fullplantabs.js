define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                        index_url: 'plan/planfull/index',
                        add_url: 'planmanagement/fullplantabs/fulladd',
                        edit_url: 'planmanagement/fullplantabs/fulledit',
                        del_url: 'planmanagement/fullplantabs/fulldel',
                        multi_url: 'planmanagement/fullplantabs/fullmulti',
                        table: 'plan_full',
                    },
            });

            var table3 = $("#table3");
                table3.bootstrapTable({
                    url: 'planmanagement/fullplantabs/table3',
                    
                    toolbar: '#toolbar3',
                    pk: 'id',
                    sortName: 'id',
                    columns: [
                        [
                            {
                                checkbox: true, formatter: function (v, r, i) {
                                    return r.match_plan === 'match_success' ? {disabled: true} : {disabled: false};
                                }
                            },
                            {field: 'id', title: __('Id')},
                            {
                                field: 'models.name', title: '销售车型', formatter: function (v, r, i) {

                                    return v != null ? r.brand_name + '-' + v : v;
                                }
                            },

                            {field: 'full_total_price', title: __('Full_total_price'), operate: 'BETWEEN'},
                            {field: 'margin', title: __('保证金（元）'), operate: 'BETWEEN'},

                            {
                                field: 'createtime',
                                title: __('Createtime'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },
                            {
                                field: 'updatetime',
                                title: __('Updatetime'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },
                            {field: 'ismenu', title: __('Ismenu'), formatter: Controller.api.formatter.toggle},

                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: table3,
                                events: Table.api.events.operate,
                                formatter: Controller.api.operate
                            }
                        ]
                    ]
                });
                // 为表格3绑定事件
                Table.api.bindevent(table3);

            
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

        fulledit: function () {
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
        },
        
        fulladd: function () {
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
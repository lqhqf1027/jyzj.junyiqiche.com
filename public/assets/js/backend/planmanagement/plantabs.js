define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'planmanagement/plantabs/index',
                    add_url: 'planmanagement/plantabs/add',
                    edit_url: 'planmanagement/plantabs/edit',
                    del_url: 'planmanagement/plantabs/del',
                    multi_url: 'planmanagement/plantabs/multi',
                    import_url: 'planmanagement/plantabs/import',
                    table: 'plan_acar',
                }
            });

            var table = $("#table");

            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                return "快速搜索车型";
            };
            $(".btn-add").data("area", ["80%", "80%"]);

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'category.name', title: __('Category.name')},

                        {field: 'models.name', title: __('Models.name')},
                        
                        {field: 'payment', title: __('Payment'), operate:'BETWEEN'},
                        {field: 'monthly', title: __('Monthly'), operate:'BETWEEN'},
                        {field: 'nperlist', title: __('Nperlist'), searchList: {"12":__('Nperlist 12'),"24":__('Nperlist 24'),"36":__('Nperlist 36'),"48":__('Nperlist 48'),"60":__('Nperlist 60')}, formatter: Table.api.formatter.normal},
                        {field: 'margin', title: __('Margin'), operate:'BETWEEN'},
                        {field: 'tail_section', title: __('Tail_section'), operate:'BETWEEN'},
                        {field: 'gps', title: __('Gps'), operate:'BETWEEN'},
                        {field: 'admin.nickname', title: __('销售定制方案')},

                        {field: 'working_insurance', title: __('Working_insurance'), searchList: {"yes":__('Working_insurance yes'),"no":__('Working_insurance no')}, formatter: Table.api.formatter.normal},

                        {field: 'ismenu', title: __('Ismenu'),formatter: Controller.api.formatter.toggle, operate: false},

                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,datetimeFormat: 'YYYY-MM-DD'},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,datetimeFormat: 'YYYY-MM-DD'},
                        {field: 'operate', title: __('Operate'), table: table, events: Controller.api.events.operate, formatter: Controller.api.operate}
                    ]
                ]
            });
            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            operate: function (value, row, index) {

                var table = this.table;
                // 操作配置
                var options = table ? table.bootstrapTable('getOptions') : {};
                // 默认按钮组
                var buttons = $.extend([], this.buttons || []);
                if (row.match_plan == 'match_success') {
                    return '<span class="text-danger">禁止编辑或删除</span>'
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
            },
            events: {
                operate: {
                    'click .btn-delone': function (e, value, row, index) {
                        /**
                         * 删除按钮 
                         */
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
                    /**
                     * 编辑按钮 
                     */
                    'click .btn-editone': function (e, value, row, index) {
                        $(".btn-editone").data("area", ["80%", "80%"]);
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
                    
                    return row.sales_id?'正在销售或已出售':"<a href='javascript:;' data-toggle='tooltip' title='" + __('Click to toggle') + "' class='btn-change' data-id='"
                        + row.id + "' data-params='" + this.field + "=" + (value ? no : yes) + "'><i class='fa fa-toggle-on " + (value == yes ? 'text-' + color : 'fa-flip-horizontal text-gray') + " fa-2x'></i></a>";

                },

            },
        }
    };
    return Controller;
});
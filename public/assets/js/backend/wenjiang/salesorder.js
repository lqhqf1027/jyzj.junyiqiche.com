define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'wenjiang/salesorder/index',
                    add_url: 'wenjiang/salesorder/add',
                    edit_url: 'wenjiang/salesorder/edit',
                    del_url: 'wenjiang/salesorder/del',
                    multi_url: 'wenjiang/salesorder/multi',
                    table: 'trench',
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
                        {field: 'id', title: __('ID')},
                        {field: 'data1', title: __('Data1')},
                        {field: 'data2', title: __('Data2')},
                        {field: 'data3', title: __('Data3')},
                        {field: 'data4', title: __('Data4')},
                        {field: 'data5', title: __('Data5'), operate:'BETWEEN'},
                        {field: 'data6', title: __('Data6'), operate:'BETWEEN'},
                        {field: 'data7', title: __('Data7'), searchList: {"12":__('Data7 12'),"24":__('Data7 24'),"36":__('Data7 36'),"48":__('Data7 48'),"60":__('Data7 60')}, formatter: Table.api.formatter.normal},
                        {field: 'data8', title: __('Data8'), operate:'BETWEEN'},
                        {field: 'data9', title: __('Data9'), operate:'BETWEEN'},
                        {field: 'data10', title: __('Data10'), operate:'BETWEEN'},
                        {field: 'data11', title: __('Data11'), operate:'BETWEEN'},
                        {field: 'data12', title: __('Data12'), operate:'BETWEEN'},
                        {field: 'data13', title: __('Data13'), operate:'BETWEEN'},
                        {field: 'data28', title: __('Data28')},
                        {field: 'data29', title: __('Data29')},
                        {field: 'salestype', title: __('Salestype'), searchList: {"new_car":__('Salestype new_car'),"rental_car":__('Salestype rental_car'),"second_car":__('Salestype second_car'),"full_car":__('Salestype full_car'),"second_full_car":__('Salestype second_full_car')}, formatter: Table.api.formatter.normal},
                        {field: 'operate', title: __('Operate'), table: table,
                        buttons: [
                            /**编辑 */
                            {
                                name: 'edit', 
                                text: '', 
                                icon: 'fa fa-pencil', 
                                extend: 'data-toggle="tooltip"', 
                                title: __('编辑'), 
                                classname: 'btn btn-xs btn-success btn-editone',
                                
                            },
                            /**删除 */
                            {
                                name: 'del',
                                icon: 'fa fa-trash',  
                                icon: 'fa fa-trash', 
                                extend: 'data-toggle="tooltip"', 
                                title: __('Del'), 
                                classname: 'btn btn-xs btn-danger btn-delone',
                                hidden: function (row) { 
                                    if (!row.the_car_username) {
                                        return false;
                                    }
                                    else if (row.the_car_username) {
                                        return true;
                                    }
                                }
                            },  
                        ],
                        events: Controller.api.events.operate, formatter: Controller.api.formatter.operate}
                    ]
                ]
            });

            
            // 绑定TAB事件
            $('.panel-heading a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                $(".btn-add").data("area", ["95%", "95%"]);
                $(".btn-edit").data("area", ["95%", "95%"]);
                var field = $(this).closest("ul").data("field");
                var value = $(this).data("value");
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                options.queryParams = function (params) {
                    var filter = {};
                    if (value !== '') {
                        filter[field] = value;
                    }
                    params.filter = JSON.stringify(filter);
                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;
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
                }

            }
        }
    };
    return Controller;
});
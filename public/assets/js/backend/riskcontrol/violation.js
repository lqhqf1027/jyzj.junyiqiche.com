define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'riskcontrol/violation/index',
                    add_url: 'riskcontrol/violation/add',
                    edit_url: 'riskcontrol/violation/edit',
                    del_url: 'riskcontrol/violation/del',
                    multi_url: 'riskcontrol/violation/multi',
                    import_url: 'riskcontrol/violation/import',
                    table: 'violation_inquiry_old',
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
                        {field: 'id', title: __('Id')},
                        {field: 'username', title: __('Username')},
                        {field: 'phone', title: __('Phone')},
                        {field: 'models', title: __('Models')},
                        {field: 'license_plate_number', title: __('License_plate_number')},
                        {field: 'frame_number', title: __('Frame_number')},
                        {field: 'engine_number', title: __('Engine_number')},
                        {field: 'total_deduction', title: __('Total_deduction')},
                        {field: 'total_fine', title: __('Total_fine'), operate:'BETWEEN'},
                        {field: 'peccancy_status', title: __('Peccancy_status'), searchList: {"1":__('Peccancy_status 1'),"2":__('Peccancy_status 2')}, formatter: Controller.api.formatter.peccancy_state},
                        {field: 'final_time', title: __('Final_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'start_renttime', title: __('Start_renttime'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'end_renttime', title: __('End_renttime'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'companyaccount', title: __('Companyaccount')},
                        {field: 'branch_office', title: __('Branch_office')},
                        {field: 'import_time', title: __('Import_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'total_violation', title: __('Total_violation')},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3')}, formatter: Controller.api.formatter.normal},
                        {field: 'operate', title: __('Operate'), table: table, events: Controller.api.events.operate, formatter: Controller.api.formatter.operate}
                    ]
                ]
            });

            
            // 绑定TAB事件
            $('.panel-heading a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
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

            //批量查询违章
            Controller.api.inquire_violation('.btn-peccancy', table);

            //导出违章客户的信息
            var submitForm = function (ids, layero) {
                var options = table.bootstrapTable('getOptions');
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
             * 批量导出违章信息
             * @param ids
             * @param layero
             */
            $(document).on("click", ".btn-export", function () {
                var ids = Table.api.selectedids(table);
                var page = table.bootstrapTable('getData');
                var all = table.bootstrapTable('getOptions').totalRows;
                Layer.confirm("请选择导出的选项<form action='" + Fast.api.fixurl("riskcontrol/violation/export") + "' method='post' target='_blank'><input type='hidden' name='ids' value='' /><input type='hidden' name='filter' ><input type='hidden' name='op'><input type='hidden' name='search'><input type='hidden' name='columns'></form>", {
                    title: '导出数据',
                    btn: ["选中项(" + ids.length + "条)", "本页(" + page.length + "条)"],
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

                })
            });


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
            formatter: {

                operate: function (value, row, index) {
                    var table = this.table;
                    // 操作配置
                    var options = table ? table.bootstrapTable('getOptions') : {};
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);

                    buttons.push(
                        {
                            name: '查询违章',
                            text: '查询违章',
                            icon: 'fa fa-search',
                            title: __('查看违章详情'),
                            // extend: 'data-toggle="tooltip"',
                            classname: 'btn btn-xs btn-info btn-search',
                        },
                        {
                            name: '编辑',
                            text: '编辑',
                            icon: 'fa fa-pencil',
                            title: __('编辑'),
                            // extend: 'data-toggle="tooltip"',
                            classname: 'btn btn-xs btn-success btn-editone',
                        },
                        {
                            name: '删除',
                            text: '删除',
                            icon: 'fa fa-pencil',
                            title: __('删除'),
                            // extend: 'data-toggle="tooltip"',
                            classname: 'btn btn-xs btn-danger btn-delone',
                        }
                    );



                    if (row && row.total_fine == 0 && row.total_deduction == 0) {

                        return Table.api.buttonlink(this, buttons, value, row, index, 'operate');
                    }

                    if (row && !row.total_fine && !row.total_deduction) {
                        return Table.api.buttonlink(this, buttons, value, row, index, 'operate');
                    }

                    buttons.push({
                        name: 'detail',
                        text: '查看违章详情',
                        icon: 'fa fa-eye',
                        title: __('查看违章详情'),
                        // extend: 'data-toggle="tooltip"',
                        classname: 'btn btn-xs btn-primary btn-detail',
                    });


                    return Table.api.buttonlink(this, buttons, value, row, index, 'operate');
                },
                
                /**
                 * 处理状态
                 * @param value
                 * @param row
                 * @param index
                 * @returns {string}
                 */
                peccancy_state: function (value, row, index) {

                    if (!value) {
                        return '-';
                    }

                    return row.peccancy_status == 1 ? "<span class='label label-success' style='cursor: pointer'>正常</span>" : "<span class='label label-danger' style='cursor: pointer'>有违章</span>"
                },

                /**
                 * 购车类型
                 * @param value
                 * @param row
                 * @param index
                 * @returns {string}
                 */
                normal: function (value, row, index) {

                    if (row.status == 1) {
                        return "<span class='label label-danger'>按揭</span>";
                    }
                    if (row.status == 2) {
                        return "<span class='label label-danger'>租车</span>";
                    }
                    if (row.status == 3) {
                        return "<span class='label label-danger'>全款车</span>";
                    }
               
                }
                
            },
            events: {
                operate: {
                    /**
                     * 查看违章详情
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-detail': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'riskcontrol/violation/details';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('查看违章详情'), $(this).data() || {});
                    },

                    /**
                     * 查询违章
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-search': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var that = this;
                        Layer.confirm('是否查询违章?', {icon: 3, title: '提示'}, function (index) {


                            if (!row['license_plate_number'] || row['license_plate_number'] == '') {
                                Layer.msg('请补全车牌号');
                                return
                            }

                            if (!row['engine_number'] || row['engine_number'] == '') {
                                Layer.msg('请补全发动机号');
                                return
                            }

                            if (!row['frame_number'] || row['frame_number'] == '') {
                                Layer.msg('请补全车架号');
                                return
                            }


                            var table = $(that).closest('table');
                            var ids = [{
                                hphm: row['license_plate_number'].substr(0, 2),
                                hphms: row['license_plate_number'],
                                engineno: row['engine_number'],
                                classno: row['frame_number']
                            }];

                            Fast.api.ajax({
                                url: 'riskcontrol/violation/sendMessagePerson',
                                data: {ids}

                            }, function (data, ret) {

                                Layer.close(index);
                                table.bootstrapTable('refresh');


                            })


                        });


                    },

                    /**
                     * 编辑按钮
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
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

                    /**
                     * 删除按钮
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
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
            },

            /**
             * 批量查询违章
             */
            inquire_violation: function (clickobj, table) {
                $(document).on("click", clickobj, function () {
                    var ids = [];
                    var tableRow = Controller.api.selectIdsRow(table);//获取选中的行数据
                    var flag = -1;
                    var page = table.bootstrapTable('getData');

                    // console.log(tableRow);return;
                    var closeLay = Layer.confirm("请选择要查询的客户数据", {
                        title: '查询数据',
                        btn: ["选中项(" + tableRow.length + "条)", "本页(" + page.length + "条)"],
                        success: function (layero, index) {
                            $(".layui-layer-btn a", layero).addClass("layui-layer-btn0");
                        }
                        ,
                        //选中项
                        yes: function (index, layero) {


                            if (tableRow.length < 1) {
                                Layer.alert('数据不能为空!', {icon: 5});
                                return false;
                            }
                            ids = [];
                            for (var i in tableRow) {

                                if (!tableRow[i]['license_plate_number'] || tableRow[i]['license_plate_number'] == '') {
                                    flag = -2;
                                    break;
                                }

                                if (!tableRow[i]['engine_number'] || tableRow[i]['engine_number'] == '') {
                                    flag = -3;
                                    break;
                                }

                                if (!tableRow[i]['frame_number'] || tableRow[i]['frame_number'] == '') {
                                    flag = -4;
                                    break;
                                }

                                ids.push({
                                    hphm: tableRow[i]['license_plate_number'].substr(0, 2),
                                    hphms: tableRow[i]['license_plate_number'],
                                    engineno: tableRow[i]['engine_number'],
                                    classno: tableRow[i]['frame_number']
                                })
                            }

                            if (flag == -2) {
                                layer.msg('选中行中有数据没有车牌号，请添加后查询');
                                return;
                            }

                            if (flag == -3) {
                                layer.msg('选中行中有数据没有发动机号，请添加后查询');
                                return;
                            }

                            if (flag == -4) {
                                layer.msg('选中行中有数据没有车架号，请添加后查询');
                                return;
                            }


                            Fast.api.ajax({
                                url: 'riskcontrol/violation/sendMessage',
                                data: {ids}

                            }, function (data, ret) {
                                console.log(data);
                                Layer.close(closeLay);
                                table.bootstrapTable('refresh');
                            })
                        }
                        ,
                        //本页
                        btn2: function (index, layero) {
                            ids = [];
                            for (var i in page) {

                                if (!page[i]['license_plate_number'] || page[i]['license_plate_number'] == '') {
                                    flag = -2;
                                    break;
                                }

                                if (!page[i]['engine_number'] || page[i]['engine_number'] == '') {
                                    flag = -3;
                                    break;
                                }

                                if (!page[i]['frame_number'] || page[i]['frame_number'] == '') {
                                    flag = -4;
                                    break;
                                }


                                ids.push({
                                    hphm: page[i]['license_plate_number'].substr(0, 2),
                                    hphms: page[i]['license_plate_number'],
                                    engineno: page[i]['engine_number'],
                                    classno: page[i]['frame_number']
                                });
                            }

                            if (flag == -2) {
                                layer.msg('本页中有数据没有车牌号，请添加后查询');
                                return;
                            }

                            if (flag == -3) {
                                layer.msg('本页中有数据没有发动机号，请添加后查询');
                                return;
                            }

                            if (flag == -4) {
                                layer.msg('本页中有数据没有车架号，请添加后查询');
                                return;
                            }

                            Fast.api.ajax({
                                url: 'riskcontrol/violation/sendMessage',
                                data: {ids}
                            }, function (data, ret) {
                                console.log(data);
                                Layer.close(closeLay);

                                table.bootstrapTable('refresh');
                            })
                        }


                    })
                });
            },
        }
    };
    return Controller;
});
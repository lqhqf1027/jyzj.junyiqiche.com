define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cms/subscribe/index',
                    add_url: 'cms/subscribe/add',
                    // edit_url: 'cms/subscribe/edit',
                    del_url: 'cms/subscribe/del',
                    multi_url: 'cms/subscribe/multi',
                    table: 'subscribe',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                searchFormVisible: true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate: false},
                        {field: 'user.nickname', title: __('客户昵称'),operate: false},
                        {field: 'user.avatar', title: __('头像'), formatter: Controller.api.formatter.sales,operate: false},
                        {field: 'user.mobile', title: __('客户电话')},
                        {field: 'plan.models_name', title: __('预约车型'),operate: false},
                        {
                            field: 'cartype', title: __('车辆类型'), formatter: function (value, row, index) {
                                 switch (value) {
                                     case 'new':
                                         return '新车';
                                     case 'used':
                                         return '二手车';
                                     case 'logistics':
                                         return '新能源汽车';
                                 }
                            },searchList: {'new':'新车','used':'二手车','logistics':'新能源汽车'}
                        },
                        {field: 'plan.payment', title: __('首付'),operate: false},
                        {field: 'plan.monthly', title: __('月供'),operate: false},
                        {field: 'plan.nperlist', title: __('期数'),operate: false},
                        {field: 'plan.company_name', title: __('所属门店'),operate: false},
                        {
                            field: 'state',
                            title: __('State'),
                            searchList: {"newcustomer": __('新客户'), "send": __('已发送')},
                            formatter: Controller.api.formatter.status
                        },
                        {
                            field: 'createtime',
                            title: __('预定时间'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
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
                    var options = table.bootstrapTable('getOptions');
                    console.log(options);
                    options.pageNumber = 1;
                    options.queryParams = function (params) {
                        params.filter = JSON.stringify(data.selected.length > 0 ? {store_ids: data.selected.join(",")} : {});
                        params.op = JSON.stringify(data.selected.length > 0 ? {store_ids: 'in'} : {});
                        console.log(params);
                        return params;
                    };
                    table.bootstrapTable('refresh', {});
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
                status: function (value, row, index) {
                    value = value == 'newcustomer' ? '新客户' : '已发送';
                    var custom = {'新客户': 'warning', '已发送': 'success'};
                    if (typeof this.custom !== 'undefined') {
                        custom = $.extend(custom, this.custom);
                    }
                    this.custom = custom;
                    this.icon = 'fa fa-circle';
                    return Table.api.formatter.normal.call(this, value, row, index);
                },
                sales: function (value, row, index) {

                    return value != null ? "<img src=" + value + " style='height:30px;width:30px;border-radius:50%'></img>" : '-';

                }
            }

        }
    };
    return Controller;
});
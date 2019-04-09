define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cms/newplan/index',
                    add_url: 'cms/newplan/add',
                    edit_url: 'cms/newplan/edit',
                    del_url: 'cms/newplan/del',
                    multi_url: 'cms/newplan/multi',
                    dragsort_url: 'cms/newplan/dragsort',
                    table: 'plan_acar',
                }
            });

            var table = $("#table");

            table.on('load-success.bs.table', function (e, data) {
                $(".btn-editone").data("area", ["70%", "70%"]);

                var td = $("#table td:nth-child(3)");

                for (var i = 0; i < td.length; i++) {

                    td[i].style.textAlign = "left";

                }

            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                sortName: 'weigh',
                escape: false,
                columns: [
                    [
                        {
                            checkbox: true,
                        },
                        {field: 'id', title: __('Id'), operate: false},
                        {
                            field: 'models.name', title: '销售车型', operate: false, formatter: function (v, r, i) {
                                return v != null ? "<img src=" + r.brand_log + " alt='品牌logo' width='30' height='30'>" + r.brand_name + '-' + v : v;
                            }
                        },
                        {field: 'weigh', title: __('权重（排序）')},
                        {field: 'models_main_images', title: __('封面图片'), formatter: Table.api.formatter.images},
                        {field: 'modelsimages', title: __('车型亮点'), formatter: Table.api.formatter.images},
                        // {
                        //     field: 'flashviewismenu',
                        //     title: __('是否为首页轮播'),
                        //     events: Controller.api.events.operate,
                        //     formatter: Controller.api.formatter.toggle1, searchList: {"1": "是", "0": "否"}
                        // },
                        {
                            field: 'recommendismenu',
                            title: __('是否为推荐'),
                            events: Controller.api.events.operate,
                            formatter: Controller.api.formatter.toggle, searchList: {"1": "是", "0": "否"},
                        },
                        {
                            field: 'subjectismenu',
                            title: __('是否为专题'),
                            events: Controller.api.events.operate,
                            formatter: Controller.api.formatter.toggle3, searchList: {"1": "是", "0": "否"},
                        },
                        {
                            field: 'subject.title', title: __('专题标题'),
                        },
                        {field: 'subject.coverimages', title: __('专题代表图片'), formatter: Table.api.formatter.images},
                        {
                            field: 'specialismenu',
                            title: __('是否为专场车型'),
                            buttons: [
                                {
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-info btn-specialismenu',
                                },
                            ],
                            events: Controller.api.events.operate,
                            formatter: Controller.api.formatter.toggle2, searchList: {"1": "是", "0": "否"}
                        },
                        {field: 'specialimages', title: __('专场车型代表图片'), formatter: Table.api.formatter.images},

                        {
                            field: 'label.name',
                            title: __('标签名称'),
                            searchList: {"1": __('新能源'), "2": __('低首付')},
                            operate: 'FIND_IN_SET',
                            formatter: Table.api.formatter.label
                        },
                        {field: 'label.lableimages', title: __('标签图片'), formatter: Table.api.formatter.images},
                        {
                            field: 'store_name', title: __('门店名称'),
                        },

                        
                        {field: 'payment', title: __('首付（元）'), operate: 'BETWEEN', operate: false},
                        {field: 'monthly', title: __('月供（元）'), operate: 'BETWEEN', operate: false},
                        {
                            field: 'nperlist',
                            title: __('期数'),
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
                        {field: 'margin', title: __('保证金（元）'), operate: 'BETWEEN', operate: false},
                        {field: 'tail_section', title: __('尾款（元）'), operate: 'BETWEEN', operate: false},
                        {field: 'gps', title: __('GPS（元）'), operate: false},
                        {field: 'note', title: __('销售方案备注'), operate: false},
                        {
                            field: 'createtime',
                            title: __('创建时间'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime,
                            datetimeFormat: 'YYYY-MM-DD'
                        },
                        {
                            field: 'updatetime',
                            title: __('更新时间'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime,
                            datetimeFormat: 'YYYY-MM-DD'
                        },
                        {
                            field: 'operate', title: __('Operate'), table: table,
                            buttons: [
                                {
                                    name: 'firstedit',
                                    icon: 'fa fa-pencil',
                                    text: '编辑方案',
                                    title: '编辑方案',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-info btn-editone',
                                },
                            ],
                            events: Controller.api.events.operate,
                            formatter: Controller.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格1绑定事件
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
                    options.pageNumber = 1;
                    options.queryParams = function (params) {
                        params.filter = JSON.stringify(data.selected.length > 0 ? {store_id: data.selected.join(",")} : {});
                        params.op = JSON.stringify(data.selected.length > 0 ? {store_id: 'in'} : {});
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
            $(document).on("change", "#c-store_id", function () {
                Controller.getSubject($(this).val())

            });

            Controller.api.bindevent();
        },
        dragsort: function () {

            Controller.api.bindevent();
        },

        getSubject: function (store_id) {
            console.log(store_id);
            Fast.api.ajax({
                url: 'cms/Newplan/getSubject',
                data: {
                    store_id: store_id
                }
            }, function (data, ret) {
                console.log(data);
                $('#c-subject_id option').remove();
                var options = '';

                if (data) {
                    for (var i in data) {
                        if(!data[i]){
                            options += '<option value="' + i + '" style="display: none">该门店暂无专题</option>';
                            break;
                        }
                        options += '<option value="' + i + '">' + data[i] + '</option>';
                    }
                }

                $('#c-subject_id').append(options);
                return false;
            })
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

            Controller.api.bindevent();
        },
        api: {
            bindevent: function (value, row, index) {
                //专题
                $(document).on('click', "input[name='row[subjectismenu]']", function () {
                    var name = $("input[name='row[name]']");
                    name.prop("placeholder", $(this).val() == 1 ? name.data("placeholder-menu") : name.data("placeholder-node"));
                });
                $("input[name='row[subjectismenu]']:checked").trigger("click");
                //推荐
                $(document).on('click', "input[name='row[recommendismenu]']", function () {
                    var name = $("input[name='row[name]']");
                    name.prop("placeholder", $(this).val() == 1 ? name.data("placeholder-menu") : name.data("placeholder-node"));
                });
                $("input[name='row[recommendismenu]']:checked").trigger("click");
                //轮播
                $(document).on('click', "input[name='row[flashviewismenu]']", function () {
                    var name = $("input[name='row[name]']");
                    name.prop("placeholder", $(this).val() == 1 ? name.data("placeholder-menu") : name.data("placeholder-node"));
                });
                $("input[name='row[flashviewismenu]']:checked").trigger("click");
                //专场
                $(document).on('click', "input[name='row[specialismenu]']", function () {
                    var name = $("input[name='row[name]']");
                    name.prop("placeholder", $(this).val() == 1 ? name.data("placeholder-menu") : name.data("placeholder-node"));
                });
                $("input[name='row[specialismenu]']:checked").trigger("click");

                Form.api.bindevent($("form[role=form]"));
            },
            events: {
                operate: {
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

                },
            },
            formatter: {
                operate: function (value, row, index) {
                    var table = this.table;
                    // 操作配置
                    var options = table ? table.bootstrapTable('getOptions') : {};
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);

                    buttons.push({
                        name: 'dragsort',
                        icon: 'fa fa-arrows',
                        title: __('Drag to sort'),
                        extend: 'data-toggle="tooltip"',
                        classname: 'btn btn-xs btn-primary btn-dragsort'
                    });

                    return Table.api.buttonlink(this, buttons, value, row, index, 'operate');
                },
                /**
                 * 是否
                 * @param value
                 * @param row
                 * @param index
                 * @returns {string}
                 */
                //专题
                toggle3: function (value, row, index) {

                    if (row.subject.coverimages) {

                        var color = typeof this.color !== 'undefined' ? this.color : 'success';
                        var yes = typeof this.yes !== 'undefined' ? this.yes : 1;
                        var no = typeof this.no !== 'undefined' ? this.no : 0;
                        return "<a href='javascript:;' data-toggle='tooltip' title='" + __('Click to toggle') + "' class='btn-change' data-id='"
                            + row.id + "' data-params='" + this.field + "=" + (value ? no : yes) + "'><i class='fa fa-toggle-on " + (value == yes ? 'text-' + color : 'fa-flip-horizontal text-gray') + " fa-2x'></i></a>";

                    }
                    else {
                        return "<span style='color:red'>上传专题图片,就可以点击</span>"
                    }

                },
                //推荐
                toggle: function (value, row, index) {

                    if (row.models_main_images) {

                        var color = typeof this.color !== 'undefined' ? this.color : 'success';
                        var yes = typeof this.yes !== 'undefined' ? this.yes : 1;
                        var no = typeof this.no !== 'undefined' ? this.no : 0;
                        return "<a href='javascript:;' data-toggle='tooltip' title='" + __('Click to toggle') + "' class='btn-change' data-id='"
                            + row.id + "' data-params='" + this.field + "=" + (value ? no : yes) + "'><i class='fa fa-toggle-on " + (value == yes ? 'text-' + color : 'fa-flip-horizontal text-gray') + " fa-2x'></i></a>";

                    }
                    else {
                        return "<span style='color:red'>上传封面图片,就可以点击</span>"
                    }
                },
                //轮播
                toggle1: function (value, row, index) {

                    var color = typeof this.color !== 'undefined' ? this.color : 'success';
                    var yes = typeof this.yes !== 'undefined' ? this.yes : 1;
                    var no = typeof this.no !== 'undefined' ? this.no : 0;
                    return "<a href='javascript:;' data-toggle='tooltip' title='" + __('Click to toggle') + "' class='btn-change' data-id='"
                        + row.id + "' data-params='" + this.field + "=" + (value ? no : yes) + "'><i class='fa fa-toggle-on " + (value == yes ? 'text-' + color : 'fa-flip-horizontal text-gray') + " fa-2x'></i></a>";

                },
                //专场
                toggle2: function (value, row, index) {

                    if (row.specialimages) {

                        var color = typeof this.color !== 'undefined' ? this.color : 'success';
                        var yes = typeof this.yes !== 'undefined' ? this.yes : 1;
                        var no = typeof this.no !== 'undefined' ? this.no : 0;
                        return "<a href='javascript:;' data-toggle='tooltip' title='" + __('Click to toggle') + "' class='btn-change' data-id='"
                            + row.id + "' data-params='" + this.field + "=" + (value ? no : yes) + "'><i class='fa fa-toggle-on " + (value == yes ? 'text-' + color : 'fa-flip-horizontal text-gray') + " fa-2x'></i></a>";

                    }
                    else {
                        return "<span style='color:red'>上传专场图片,就可以点击</span>"
                    }

                }

            },

        }

    };
    return Controller;
});


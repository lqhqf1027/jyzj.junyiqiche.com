define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cms/logistics/index',
                    add_url: 'cms/logistics/add',
                    edit_url: 'cms/logistics/edit',
                    del_url: 'cms/logistics/del',
                    multi_url: 'cms/logistics/multi',
                    table: 'cms_logistics_project',
                }
            });

            var table = $("#table");

            table.on('load-success.bs.table', function (e, data) {
                $(".btn-add").data("area", ["75%", "90%"]);
                $(".btn-editone").data("area", ["75%", "90%"]);

                var td = $("#table td:nth-child(16)");

                for (var i = 0; i<td.length;i++) {
            
                    td[i].style.textAlign = "left";

                }

            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'models.name', title: __('车辆车型')},
                        {field: 'models_main_images', title: __('Models_main_images'), formatter: Table.api.formatter.images},
                        {field: 'modelsimages', title: __('Modelsimages'), formatter: Table.api.formatter.images},
                        
                        // {field: 'flashviewismenu', title: __('Flashviewismenu'),formatter: Controller.api.formatter.toggle,searchList:{"1":"是","0":"否"}},
                        {field: 'recommendismenu', title: __('Recommendismenu'),formatter: Controller.api.formatter.toggle1,searchList:{"1":"是","0":"否"}},
                        
                        {field: 'subjectismenu', title: __('Subjectismenu'),formatter: Controller.api.formatter.toggle2,searchList:{"1":"是","0":"否"}},
                        {field: 'subject.title', title: __('专题名称')},
                        {field: 'subject.coverimages', title: __('专题封面图片'), formatter: Table.api.formatter.images},
                        
                        {field: 'specialismenu', title: __('Specialismenu'),formatter: Controller.api.formatter.toggle3,searchList:{"1":"是","0":"否"}},
                        {field: 'specialimages', title: __('Specialimages'), formatter: Table.api.formatter.images},
                     
                        {field: 'label.name', title: __('标签名称')},
                        {field: 'label.lableimages', title: __('标签图片'), formatter: Table.api.formatter.images},

                        {field: 'store.store_name', title: __('门店名称')},
                        {field: 'popularity', title: __('Popularity')},

                        {field: 'payment', title: __('Payment'), operate:'BETWEEN'},
                        {field: 'monthly', title: __('Monthly'), operate:'BETWEEN'},
                        {field: 'nperlist', title: __('Nperlist'), searchList: {"12":__('Nperlist 12'),"24":__('Nperlist 24'),"36":__('Nperlist 36'),"48":__('Nperlist 48'),"60":__('Nperlist 60')}, formatter: Table.api.formatter.normal},
                        {field: 'margin', title: __('Margin'), operate:'BETWEEN'},
                        {field: 'total_price', title: __('Total_price'), operate:'BETWEEN'},
                        {field: 'note', title: __('Note')},
                        {field: 'ismenu', title: __('Ismenu'),formatter: Controller.api.formatter.toggle4,searchList:{"1":"是","0":"否"}},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
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
            //门店下的车型
            $(document).on("change", "#c-store_id", function () {

                $('#c-models_id_text').val('');
            });
            $("#c-models_id").data("params", function (obj) {

                return {custom: {store_ids: $('#c-store_id').val()}};

            });


            Controller.api.bindevent();
        },
        edit: function () {
           //门店下的车型
            $(document).on("change", "#c-store_id", function () {

                $('#c-models_id_text').val('');
            });
            $("#c-models_id").data("params", function (obj) {

                return {custom: {store_ids: $('#c-store_id').val()}};

            });

            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {

                //轮播
                $(document).on('click', "input[name='row[flashviewismenu]']", function () {
                    var name = $("input[name='row[name]']");
                    name.prop("placeholder", $(this).val() == 1 ? name.data("placeholder-menu") : name.data("placeholder-node"));
                });
                $("input[name='row[flashviewismenu]']:checked").trigger("click");

                //推荐
                $(document).on('click', "input[name='row[recommendismenu]']", function () {
                    var name = $("input[name='row[name]']");
                    name.prop("placeholder", $(this).val() == 1 ? name.data("placeholder-menu") : name.data("placeholder-node"));
                });
                $("input[name='row[recommendismenu]']:checked").trigger("click");

                //专题
                $(document).on('click', "input[name='row[subjectismenu]']", function () {
                    var name = $("input[name='row[name]']");
                    name.prop("placeholder", $(this).val() == 1 ? name.data("placeholder-menu") : name.data("placeholder-node"));
                });
                $("input[name='row[subjectismenu]']:checked").trigger("click");

                //专场
                $(document).on('click', "input[name='row[specialismenu]']", function () {
                    var name = $("input[name='row[name]']");
                    name.prop("placeholder", $(this).val() == 1 ? name.data("placeholder-menu") : name.data("placeholder-node"));
                });
                $("input[name='row[specialismenu]']:checked").trigger("click");

                //上线销售
                $(document).on('click', "input[name='row[ismenu]']", function () {
                    var name = $("input[name='row[name]']");
                    name.prop("placeholder", $(this).val() == 1 ? name.data("placeholder-menu") : name.data("placeholder-node"));
                });
                $("input[name='row[ismenu]']:checked").trigger("click");

                Form.api.bindevent($("form[role=form]"));
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
                //轮播
                toggle: function (value, row, index) {

                    var color = typeof this.color !== 'undefined' ? this.color : 'success';
                    var yes = typeof this.yes !== 'undefined' ? this.yes : 1;
                    var no = typeof this.no !== 'undefined' ? this.no : 0;
                    return "<a href='javascript:;' data-toggle='tooltip' title='" + __('Click to toggle') + "' class='btn-change' data-id='"
                                + row.id + "' data-params='" + this.field + "=" + (value ? no : yes) + "'><i class='fa fa-toggle-on " + (value == yes ? 'text-' + color : 'fa-flip-horizontal text-gray') + " fa-2x'></i></a>";
                    
                   
                },
                //推荐
                toggle1: function (value, row, index) {

                    if(row.models_main_images){

                        var color = typeof this.color !== 'undefined' ? this.color : 'success';
                        var yes = typeof this.yes !== 'undefined' ? this.yes : 1;
                        var no = typeof this.no !== 'undefined' ? this.no : 0;
                        return "<a href='javascript:;' data-toggle='tooltip' title='" + __('Click to toggle') + "' class='btn-change' data-id='"
                                + row.id + "' data-params='" + this.field + "=" + (value ? no : yes) + "'><i class='fa fa-toggle-on " + (value == yes ? 'text-' + color : 'fa-flip-horizontal text-gray') + " fa-2x'></i></a>";
                    
                    }
                    else{
                       return "<span style='color:red'>上传封面图片,就可以点击</span>"
                    }
                },
                //专题
                toggle2: function (value, row, index) {

                    if(row.subject.coverimages){

                        var color = typeof this.color !== 'undefined' ? this.color : 'success';
                        var yes = typeof this.yes !== 'undefined' ? this.yes : 1;
                        var no = typeof this.no !== 'undefined' ? this.no : 0;
                        return "<a href='javascript:;' data-toggle='tooltip' title='" + __('Click to toggle') + "' class='btn-change' data-id='"
                                + row.id + "' data-params='" + this.field + "=" + (value ? no : yes) + "'><i class='fa fa-toggle-on " + (value == yes ? 'text-' + color : 'fa-flip-horizontal text-gray') + " fa-2x'></i></a>";
                    
                    }
                    else{
                       return "<span style='color:red'>上传专题图片,就可以点击</span>"
                    }

                },
                //专场
                toggle3: function (value, row, index) {

                    if(row.specialimages){

                        var color = typeof this.color !== 'undefined' ? this.color : 'success';
                        var yes = typeof this.yes !== 'undefined' ? this.yes : 1;
                        var no = typeof this.no !== 'undefined' ? this.no : 0;
                        return "<a href='javascript:;' data-toggle='tooltip' title='" + __('Click to toggle') + "' class='btn-change' data-id='"
                                + row.id + "' data-params='" + this.field + "=" + (value ? no : yes) + "'><i class='fa fa-toggle-on " + (value == yes ? 'text-' + color : 'fa-flip-horizontal text-gray') + " fa-2x'></i></a>";
                    
                    }
                    else{
                       return "<span style='color:red'>上传专场图片,就可以点击</span>"
                    }

                },
                //上线销售
                toggle4: function (value, row, index) {

                    var color = typeof this.color !== 'undefined' ? this.color : 'success';
                    var yes = typeof this.yes !== 'undefined' ? this.yes : 1;
                    var no = typeof this.no !== 'undefined' ? this.no : 0;
                    return "<a href='javascript:;' data-toggle='tooltip' title='" + __('Click to toggle') + "' class='btn-change' data-id='"
                                + row.id + "' data-params='" + this.field + "=" + (value ? no : yes) + "'><i class='fa fa-toggle-on " + (value == yes ? 'text-' + color : 'fa-flip-horizontal text-gray') + " fa-2x'></i></a>";
                    
                }

            },
        }
    };
    return Controller;
});
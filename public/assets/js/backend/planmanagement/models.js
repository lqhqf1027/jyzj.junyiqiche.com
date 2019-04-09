define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    /**
     * 车型
     * @type {{index: index, add: add, edit: edit, api: {bindevent: bindevent}}}
     */
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'planmanagement/models/index',
                    add_url: 'planmanagement/models/add',
                    edit_url: 'planmanagement/models/edit',
                    del_url: 'planmanagement/models/del',
                    multi_url: 'planmanagement/models/multi',
                    table: 'models',
                }
            });

            var table = $("#table");
            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                return "快速搜索车型";
            };
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'brand.name',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'city_store', title: __('城市门店'), operate: false},
                        {field: 'brand.name', title: '所属品牌'},
                        
                        {field: 'name', title: __('Name')},
                        // {field: 'standard_price', title: __('Standard_price'), operate:'BETWEEN'},
                        {field: 'price', title: __('厂商指导价')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status},
                        
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
                        params.filter = JSON.stringify(data.selected.length > 0 ? {store_ids: data.selected.join(",")} : {});
                        params.op = JSON.stringify(data.selected.length > 0 ? {store_ids: 'in'} : {});
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

            //车系
            $(document).on("change", "#c-brand_id", function () {

                $.post("planmanagement/models/getSeries",{

                    id: $('#c-brand_id').val(),

                    },function(result){
                    // console.log(result);
                    $('#c-series_name').selectPageData(result.list);
                });
            });
            //车型
            $(document).on("change", "#c-series_name", function () {

                $.post("planmanagement/models/getModel",{

                    id: $('#c-series_name').val(),

                    },function(result){
                    // console.log(result);
                    $('#c-model_name').selectPageData(result.list);
                });
            });


            Controller.api.bindevent();
        },
        edit: function () {

            //车系
            $(document).on("change", "#c-brand_id", function () {

                $.post("planmanagement/models/getSeries",{

                    id: $('#c-brand_id').val(),

                    },function(result){
                    // console.log(result);
                    $('#c-series_name').selectPageData(result.list);
                });
            });
            //车型
            $(document).on("change", "#c-series_name", function () {

                $.post("planmanagement/models/getModel",{

                    id: $('#c-series_name').val(),

                    },function(result){
                    // console.log(result);
                    $('#c-model_name').selectPageData(result.list);
                });
            });

            
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
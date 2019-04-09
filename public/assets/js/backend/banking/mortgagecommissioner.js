define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'banking/mortgagecommissioner/index',
                    add_url: 'banking/mortgagecommissioner/add',
                    edit_url: 'banking/mortgagecommissioner/edit',
                    del_url: 'banking/mortgagecommissioner/del',
                    multi_url: 'banking/mortgagecommissioner/multi',
                    import_url: 'banking/mortgagecommissioner/import',
                    table: 'mortgage_commissioner',
                }
            });

            var table = $("#table");

            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "快速搜索：司机姓名，车牌号";};

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                searchFormVisible: true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'data1', title: __('Data1'),operate:false},
                        {field: 'data2', title: __('Data2'),operate:false},
                        {field: 'data3', title: __('Data3'),operate:false},
                        {field: 'data4', title: __('Data4'),operate:false},
                        {field: 'data5', title: __('Data5')},
                        {field: 'data6', title: __('Data6')},
                        {field: 'data7', title: __('Data7'),operate:false},
                        {field: 'data8', title: __('Data8')},
                        {field: 'data9', title: __('Data9')},
                        {field: 'data10', title: __('Data10'), operate:false},
                        {field: 'data11', title: __('Data11'), operate:false},
                        {field: 'data12', title: __('Data12'), operate:false},
                        {field: 'data13', title: __('Data13'), operate:false},
                        {field: 'data14', title: __('Data14'), operate:false},
                        {field: 'data15', title: __('Data15')},
                        {field: 'data16', title: __('Data16'), operate:false},
                        {field: 'data17', title: __('Data17'), operate:false},
                        {field: 'data18', title: __('Data18'), operate:false},
                        {field: 'data19', title: __('Data19'), operate:false},
                        {field: 'platformtype', title: __('Platformtype'), searchList: {"new":__('Platformtype new'),"other":__('Platformtype other')}, formatter: Table.api.formatter.normal, operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            table.on('load-success.bs.table', function (e, data) {
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
            }
        }
    };
    return Controller;
});
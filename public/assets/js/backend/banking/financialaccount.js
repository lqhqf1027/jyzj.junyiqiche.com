define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        /**
         * 金融办抵押台账
         */
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'banking/financialaccount/index',
                    add_url: 'banking/financialaccount/add',
                    edit_url: 'banking/financialaccount/edit',
                    del_url: 'banking/financialaccount/del',
                    multi_url: 'banking/financialaccount/multi',
                    table: 'financial_account',
                }
            });

            var table = $("#table");
            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                return "快速搜索客户姓名、车架号";
            };
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                searchFormVisible: true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'company', title: __('Company')},
                        {field: 'full_name', title: __('Full_name')},
                        {field: 'registration_certificate', title: __('Registration_certificate')},
                        {field: 'licenseplatenumber', title: __('Licenseplatenumber')},
                        {field: 'framenumber', title: __('Framenumber')},
                        {field: 'company_contacts', title: __('Company_contacts')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
            }
        }
    };
    return Controller;
});
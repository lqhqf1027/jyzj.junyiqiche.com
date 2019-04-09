define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'material/mortgageregistration/index',
                    add_url: 'material/mortgageregistration/add',
                    edit_url: 'material/mortgageregistration/edit',
                    del_url: 'material/mortgageregistration/del',
                    multi_url: 'material/mortgageregistration/multi',
                    table: 'mortgage_registration',
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
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'archival_coding', title: __('Archival_coding')},
                        {field: 'signtime', title: __('Signtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'total_contract', title: __('Total_contract')},
                        {field: 'end_money', title: __('End_money')},
                        {field: 'hostdate', title: __('Hostdate'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'mortgage', title: __('Mortgage')},
                        {field: 'mortgage_people', title: __('Mortgage_people')},
                        {field: 'ticketdate', title: __('Ticketdate'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'supplier', title: __('Supplier')},
                        {field: 'tax_amount', title: __('Tax_amount'), operate:'BETWEEN'},
                        {field: 'no_tax_amount', title: __('No_tax_amount'), operate:'BETWEEN'},
                        {field: 'pay_taxesdate', title: __('Pay_taxesdate'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'house_fee', title: __('House_fee'), operate:'BETWEEN'},
                        {field: 'luqiao_fee', title: __('Luqiao_fee'), operate:'BETWEEN'},
                        {field: 'insurance_buydate', title: __('Insurance_buydate'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'insurance_policy', title: __('Insurance_policy')},
                        {field: 'commercial_insurance_policy', title: __('Commercial_insurance_policy')},
                        {field: 'transferdate', title: __('Transferdate'), operate:'RANGE', addclass:'datetimerange'},
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
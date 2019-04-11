define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'riskcontrol/previouscustomer/index',
                    add_url: 'riskcontrol/previouscustomer/add',
                    edit_url: 'riskcontrol/previouscustomer/edit',
                    del_url: 'riskcontrol/previouscustomer/del',
                    multi_url: 'riskcontrol/previouscustomer/multi',
                    import_url: 'riskcontrol/previouscustomer/import',
                    table: 'previous_customer',
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
                        {field: 'archival_coding', title: __('Archival_coding')},
                        {field: 'signdate', title: __('Signdate')},
                        {field: 'username', title: __('Username')},
                        {field: 'id_card', title: __('Id_card')},
                        {field: 'phone', title: __('Phone')},
                        {field: 'contract_total', title: __('Contract_total'), operate:'BETWEEN'},
                        {field: 'payment', title: __('Payment'), operate:'BETWEEN'},
                        {field: 'monthly', title: __('Monthly'), operate:'BETWEEN'},
                        {field: 'nperlist', title: __('Nperlist'), searchList: {"12":__('Nperlist 12'),"24":__('Nperlist 24'),"36":__('Nperlist 36'),"48":__('Nperlist 48'),"60":__('Nperlist 60')}, formatter: Table.api.formatter.normal},
                        {field: 'end_money', title: __('End_money')},
                        {field: 'margin', title: __('Margin'), operate:'BETWEEN'},
                        {field: 'hostdate', title: __('Hostdate')},
                        {field: 'models_name', title: __('Models_name')},
                        {field: 'licensenumber', title: __('Licensenumber')},
                        {field: 'frame_number', title: __('Frame_number')},
                        {field: 'engine_number', title: __('Engine_number')},
                        {field: 'mortgage_people', title: __('Mortgage_people')},
                        {field: 'ticketdate', title: __('Ticketdate')},
                        {field: 'supplier', title: __('Supplier')},
                        {field: 'tax_amount', title: __('Tax_amount'), operate:'BETWEEN'},
                        {field: 'no_tax_amount', title: __('No_tax_amount'), operate:'BETWEEN'},
                        {field: 'pay_taxesdate', title: __('Pay_taxesdate'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'purchase_of_taxes', title: __('Purchase_of_taxes'), operate:'BETWEEN'},
                        {field: 'house_fee', title: __('House_fee'), operate:'BETWEEN'},
                        {field: 'luqiao_fee', title: __('Luqiao_fee'), operate:'BETWEEN'},
                        {field: 'insurance_buydate', title: __('Insurance_buydate')},
                        {field: 'insurance_policy', title: __('Insurance_policy')},
                        {field: 'insurance', title: __('Insurance')},
                        {field: 'car_boat_tax', title: __('Car_boat_tax'), operate:'BETWEEN'},
                        {field: 'commercial_insurance_policy', title: __('Commercial_insurance_policy')},
                        {field: 'business_risks', title: __('Business_risks')},
                        {field: 'subordinate_branch', title: __('Subordinate_branch')},
                        {field: 'saler', title: __('Saler')},
                        {field: 'transfer_time', title: __('Transfer_time')},
                        {field: 'note', title: __('Note')},
                        {field: 'classification', title: __('Classification'), searchList: {"new":__('New'),"used":__('Used'),"full":__('Full')}, formatter: Table.api.formatter.normal},
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
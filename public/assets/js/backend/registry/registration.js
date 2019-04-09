define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'registry/registration/index',
                    add_url: 'registry/registration/add',
                    edit_url: 'registry/registration/edit',
                    del_url: 'registry/registration/del',
                    multi_url: 'registry/registration/multi',
                    table: 'registry_registration',
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
                        {field: 'marry_and_divorceimages', title: __('Marry_and_divorceimages'), formatter: Table.api.formatter.images},
                        {field: 'halfyear_bank_flowimages', title: __('Halfyear_bank_flowimages'), formatter: Table.api.formatter.images},
                        {field: 'residence_permitimages', title: __('Residence_permitimages'), formatter: Table.api.formatter.images},
                        {field: 'company_contractimages', title: __('Company_contractimages'), formatter: Table.api.formatter.images},
                        {field: 'rent_house_contactimages', title: __('Rent_house_contactimages'), formatter: Table.api.formatter.images},
                        {field: 'keys', title: __('Keys')},
                        {field: 'lift_listimages', title: __('Lift_listimages'), formatter: Table.api.formatter.images},
                        {field: 'explain_situation', title: __('Explain_situation')},
                        {field: 'truth_management_protocolimages', title: __('Truth_management_protocolimages'), formatter: Table.api.formatter.images},
                        {field: 'confidentiality_agreementimages', title: __('Confidentiality_agreementimages'), formatter: Table.api.formatter.images},
                        {field: 'supplementary_contract_agreementimages', title: __('Supplementary_contract_agreementimages'), formatter: Table.api.formatter.images},
                        {field: 'tianfu_bank_cardimages', title: __('Tianfu_bank_cardimages'), formatter: Table.api.formatter.images},
                        {field: 'other_documentsimages', title: __('Other_documentsimages'), formatter: Table.api.formatter.images},
                        {field: 'tax_proofimages', title: __('Tax_proofimages'), formatter: Table.api.formatter.images},
                        {field: 'invoice_or_deduction_coupletimages', title: __('Invoice_or_deduction_coupletimages'), formatter: Table.api.formatter.images},
                        {field: 'registration_certificateimages', title: __('Registration_certificateimages'), formatter: Table.api.formatter.images},
                        {field: 'mortgage_registration_fee', title: __('Mortgage_registration_fee'), operate:'BETWEEN'},
                        {field: 'maximum_guarantee_contractimages', title: __('Maximum_guarantee_contractimages'), formatter: Table.api.formatter.images},
                        {field: 'credit_reportimages', title: __('Credit_reportimages'), formatter: Table.api.formatter.images},
                        {field: 'information_remark', title: __('Information_remark')},
                        {field: 'driving_licenseimages', title: __('Driving_licenseimages'), formatter: Table.api.formatter.images},
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
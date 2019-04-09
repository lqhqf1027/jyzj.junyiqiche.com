define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        /**
         * 客户池
         */
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'customer/customerresource/index',
                    add_url: 'customer/customerresource/add',
                    edit_url: 'customer/customerresource/edit',
                    del_url: 'customer/customerresource/del',
                    multi_url: 'customer/customerresource/multi',
                    import_url: 'customer/customerresource/import',
                    table: 'customer_resource',
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

                        {field: 'platform.name', title: __('所属平台')},
                        
                        {field: 'username', title: __('Username')},
                        {field: 'phone', title: __('Phone')},
                        {field: 'age', title: __('Age')},
                        {field: 'genderdata', title: __('Genderdata'), visible:false, searchList: {"male":__('genderdata male'),"female":__('genderdata female')}},
                        {field: 'genderdata_text', title: __('Genderdata'), operate:false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},

                        {field: 'operate', title: __('Operate'), table: newCustomer, events: Table.api.events.operate,
                        buttons: [
                                    {name: 'detail', text: '分配', title: '分配', icon: 'fa fa-share', classname: 'btn btn-xs btn-info btn-dialog btn-newCustomer', url: 'promote/customertabs/dstribution',
                                        success:function(data, ret){
                                        }, 
                                        error:function(data,ret){

                                        }
                                    }
                                ],
                                
                                 events: Table.api.events.operate,formatter: Table.api.formatter.operate}
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
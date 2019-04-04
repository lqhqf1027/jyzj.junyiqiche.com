define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'pastcustomers/pastinformation/index',
                    add_url: 'pastcustomers/pastinformation/add',
                    edit_url: 'pastcustomers/pastinformation/edit',
                    del_url: 'pastcustomers/pastinformation/del',
                    multi_url: 'pastcustomers/pastinformation/multi',
                    import_url: 'pastcustomers/pastinformation/import',
                    table: 'past_information',
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
                        {field: 'signtime', title: __('Signtime')},
                        {field: 'username', title: __('Username')},
                        {field: 'id_card', title: __('Id_card')},
                        {field: 'phone', title: __('Phone')},
                        {field: 'constract_total', title: __('Constract_total')},
                        {field: 'payment', title: __('Payment')},
                        {field: 'monthly', title: __('Monthly'), operate:'BETWEEN'},
                        {field: 'term', title: __('Term')},
                        {field: 'last_rent', title: __('Last_rent'), operate:'BETWEEN'},
                        {field: 'bond', title: __('Bond'), operate:'BETWEEN'},
                        {field: 'wealthytime', title: __('Wealthytime')},
                        {field: 'models', title: __('Models')},
                        {field: 'platenumber', title: __('Platenumber')},
                        {field: 'framenumber', title: __('Framenumber')},
                        {field: 'mortgage', title: __('Mortgage')},
                        {field: 'mortgage_man', title: __('Mortgage_man')},
                        {field: 'tickettime', title: __('Tickettime')},
                        {field: 'supplier', title: __('Supplier')},
                        {field: 'tax_amount', title: __('Tax_amount'), operate:'BETWEEN'},
                        {field: 'no_tax', title: __('No_tax')},
                        {field: 'paymenttime', title: __('Paymenttime')},
                        {field: 'purchase_tax', title: __('Purchase_tax')},
                        {field: 'house_fee', title: __('House_fee'), operate:'BETWEEN'},
                        {field: 'road_fee', title: __('Road_fee')},
                        {field: 'buytime', title: __('Buytime')},
                        {field: 'strong_insurance_list', title: __('Strong_insurance_list')},
                        {field: 'strong_insurance_money', title: __('Strong_insurance_money'), operate:'BETWEEN'},
                        {field: 'car_money', title: __('Car_money'), operate:'BETWEEN'},
                        {field: 'commercial_insurance_list', title: __('Commercial_insurance_list')},
                        {field: 'commercial_insurance_money', title: __('Commercial_insurance_money')},
                        {field: 'transfertime', title: __('Transfertime')},
                        {field: 'branch_office', title: __('Branch_office')},
                        {field: 'sales', title: __('Sales')},
                        {field: 'rent_month', title: __('Rent_month')},
                        {field: 'car_line', title: __('Car_line')},
                        {field: 'deposit', title: __('Deposit')},
                        {field: 'rent_money_month', title: __('Rent_money_month')},
                        {field: 'renttime', title: __('Renttime')},
                        {field: 'backtime', title: __('Backtime')},
                        {field: 'note', title: __('Note')},
                        {field: 'types', title: __('Types'), searchList: {"full":__('Full'),"rent":__('Rent'),"mortgage":__('Mortgage')}, formatter: Table.api.formatter.normal},
                        {field: 'admin_id', title: __('Admin_id')},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'operate', title: __('Operate'),
                            table: table,
                            events: Controller.api.events.operate,
                            formatter: Controller.api.formatter.operate,
                            buttons: [
                                {
                                    name:'unauthorized',
                                    text:'未授权',
                                    title:'未授权',
                                    icon: 'fa fa-address-card',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-info btn-unauthorized',
                                    visible:function (row) {
                                        if(!row.user_id){
                                           return true;
                                        }
                                        return false;
                                    }
                                },
                                {
                                    name:'authorized',
                                    text:'已授权',
                                    title:'已授权',
                                    icon: 'fa fa-address-card',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-danger btn-unauthorized',
                                    visible:function (row) {
                                        if(row.user_id){
                                            return true;
                                        }
                                        return false;
                                    }
                                }
                            ]
                        }
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
            },
            // 单元格元素事件
            events: {
                operate: {
                    'click .btn-unauthorized': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'pastcustomers/pastinformation/grant_authorization';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
                    },
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
                    'click .btn-delone': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var that = this;
                        var top = $(that).offset().top - $(window).scrollTop();
                        var left = $(that).offset().left - $(window).scrollLeft() - 260;
                        if (top + 154 > $(window).height()) {
                            top = top - 154;
                        }
                        if ($(window).width() < 480) {
                            top = left = undefined;
                        }
                        Layer.confirm(
                            __('Are you sure you want to delete this item?'),
                            {icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true},
                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');
                                Table.api.multi("del", row[options.pk], table, that);
                                Layer.close(index);
                            }
                        );
                    }
                }
            },
            formatter:{
                operate: function (value, row, index) {
                    var table = this.table;
                    // 操作配置
                    var options = table ? table.bootstrapTable('getOptions') : {};
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);
                    // 所有按钮名称
                    var names = [];
                    buttons.forEach(function (item) {
                        names.push(item.name);
                    });

                    if (options.extend.dragsort_url !== '' && names.indexOf('dragsort') === -1) {
                        buttons.push({
                            name: 'dragsort',
                            icon: 'fa fa-arrows',
                            title: __('Drag to sort'),
                            extend: 'data-toggle="tooltip"',
                            classname: 'btn btn-xs btn-primary btn-dragsort'
                        });
                    }
                    if (options.extend.edit_url !== '' && names.indexOf('edit') === -1) {
                        buttons.push({
                            name: 'edit',
                            icon: 'fa fa-pencil',
                            title: __('Edit'),
                            extend: 'data-toggle="tooltip"',
                            classname: 'btn btn-xs btn-success btn-editone',
                            url: options.extend.edit_url
                        });
                    }
                    if (options.extend.del_url !== '' && names.indexOf('del') === -1) {
                        buttons.push({
                            name: 'del',
                            icon: 'fa fa-trash',
                            title: __('Del'),
                            extend: 'data-toggle="tooltip"',
                            classname: 'btn btn-xs btn-danger btn-delone'
                        });
                    }
                    return Table.api.buttonlink(this, buttons, value, row, index, 'operate');
                }
            }
        }
    };
    return Controller;
});
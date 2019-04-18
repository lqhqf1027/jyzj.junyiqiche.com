define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'editable'], function ($, undefined, Backend, Table, Form, undefined) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'product/plantabs/index' + location.search,
                    add_url: 'product/plantabs/add',
                    edit_url: 'product/plantabs/edit',
                    newedit_url: 'product/plantabs/newedit',
                    usedcaredit_url: 'product/plantabs/usedcaredit',
                    rentaledit_url: 'product/plantabs/rentaledit',
                    del_url: 'product/plantabs/del',
                    multi_url: 'product/plantabs/multi',
                    table: 'plan',
                }
            });

            var table = $("#table");

            table.on('load-success.bs.table', function (e, data) {
                $(".btn-add").data("area", ["65%", "80%"]);
            })

            // 绑定TAB事件
            $('.panel-heading ul[data-field] li a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                switch (e.currentTarget.innerHTML) {
                    case '按揭（新车）':
                        Controller.api.show_and_hide_table(table, 'show', ['financial_platform_name', 'payment', 'monthly', 'nperlist', 'margin', 'tail_section',
                                                                        'gps','full_total_price','working_insurance']);
                        Controller.api.show_and_hide_table(table, 'hide', ['companyaccount', 'licenseplatenumber', 'vin', 'engine_no', 'kilometres', 'cashpledge',
                                                                        'rent_price','car_licensetime','emission_standard','emission_load','speed_changing_box']);
                        break;
                    case '按揭（二手车）':
                        Controller.api.show_and_hide_table(table, 'show', ['financial_platform_name', 'payment', 'monthly', 'nperlist', 'margin', 'tail_section', 'gps',
                                                                'full_total_price','working_insurance','companyaccount','licenseplatenumber','vin','engine_no', 'kilometres']);
                        Controller.api.show_and_hide_table(table, 'hide', ['cashpledge','rent_price','car_licensetime','emission_standard','emission_load','speed_changing_box']);
                        
                        break;
                    case '租车':
                        Controller.api.show_and_hide_table(table, 'show', ['companyaccount', 'licenseplatenumber', 'vin', 'engine_no', 'kilometres', 'cashpledge',
                                                                'rent_price','car_licensetime','emission_standard','emission_load','speed_changing_box']);
                        Controller.api.show_and_hide_table(table, 'hide', ['financial_platform_name', 'payment', 'monthly', 'nperlist', 'margin', 'tail_section',
                                                                        'gps','full_total_price','working_insurance']);
                        break;
                   
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

                        {field: 'schemecategory.name', title: __('Schemecategory.name')},
                        {field: 'brand_name', title: __('所属品牌')},
                        {field: 'models.name', title: __('Models.name')},

                        {field: 'financial_platform_name', title: __('Financial_platform_name'), editable: true},
                        {field: 'payment', title: __('Payment'), operate:'BETWEEN'},
                        {field: 'monthly', title: __('Monthly'), operate:'BETWEEN'},
                        {field: 'nperlist', title: __('Nperlist'), searchList: {"12":__('Nperlist 12'),"24":__('Nperlist 24'),"36":__('Nperlist 36'),"48":__('Nperlist 48'),"60":__('Nperlist 60')}, formatter: Table.api.formatter.normal},
                        {field: 'margin', title: __('Margin'), operate:'BETWEEN'},
                        {field: 'tail_section', title: __('Tail_section'), operate:'BETWEEN'},
                        {field: 'gps', title: __('Gps'), operate:'BETWEEN'},
                        // {field: 'total_payment', title: __('Total_payment'), operate:'BETWEEN'},
                        {field: 'full_total_price', title: __('Full_total_price'), operate:'BETWEEN'},
                        {field: 'working_insurance', title: __('Working_insurance'), searchList: {"yes":__('Working_insurance yes'),"no":__('Working_insurance no')}, formatter: Table.api.formatter.normal},
                        {field: 'companyaccount', title: __('Companyaccount')},
                        {field: 'licenseplatenumber', title: __('Licenseplatenumber')},
                        {field: 'vin', title: __('Vin')},
                        {field: 'engine_no', title: __('Engine_no')},
                        {field: 'kilometres', title: __('Kilometres'), operate:'BETWEEN'},
                        {field: 'cashpledge', title: __('Cashpledge'), operate:'BETWEEN'},
                        {field: 'rent_price', title: __('Rent_price'), operate:'BETWEEN'},
                        {field: 'car_licensetime', title: __('Car_licensetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'emission_standard', title: __('Emission_standard')},
                        {field: 'emission_load', title: __('Emission_load')},
                        {field: 'speed_changing_box', title: __('Speed_changing_box')},
                        // {field: 'drivinglicenseimages', title: __('Drivinglicenseimages'), events: Table.api.events.image, formatter: Table.api.formatter.images},
                        {field: 'ismenu', title: __('Ismenu'), formatter: Controller.api.formatter.toggle},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'type', title: __('Type'), searchList: {"mortgage":__('Type mortgage'),"used_car_mortgage":__('Type used_car_mortgage'),"car_rental":__('Type car_rental'),"full_new_car":__('Type full_new_car'),"full_used_car":__('Type full_used_car')}, formatter: Table.api.formatter.normal},
          
                        {field: 'operate', title: __('Operate'), table: table, events: Controller.api.events.operate, formatter: Controller.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        newedit: function () {
            Table.api.init({
               
            });
            Form.api.bindevent($("form[role=form]"), function(data, ret){
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                
                Fast.api.close(data);//这里是重点
                
                Toastr.success("成功");//这个可有可无
            }, function(data, ret){
                
                Toastr.success("失败");
                
            });
            // Controller.api.bindevent();
        },
        usedcaredit: function () {
            Table.api.init({
               
            });
            Form.api.bindevent($("form[role=form]"), function(data, ret){
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                
                Fast.api.close(data);//这里是重点
                
                Toastr.success("成功");//这个可有可无
            }, function(data, ret){
                
                Toastr.success("失败");
                
            });
            // Controller.api.bindevent();
        },
        rentaledit: function () {
            Table.api.init({
               
            });
            Form.api.bindevent($("form[role=form]"), function(data, ret){
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                
                Fast.api.close(data);//这里是重点
                
                Toastr.success("成功");//这个可有可无
            }, function(data, ret){
                
                Toastr.success("失败");
                
            });
            // Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {

                operate: function (value, row, index) {
                    var table = this.table;
                    // 操作配置
                    var options = table ? table.bootstrapTable('getOptions') : {};
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);

                    if(row.type == 'mortgage'){
                        buttons.push({
                            name: 'mortgage',
                            text: '新车方案编辑',
                            icon: 'fa fa-pencil',
                            title: __('新车方案编辑'),
                            extend: 'data-toggle="tooltip"',
                            classname: 'btn btn-xs btn-success btn-mortgage'
                        });
                    }
                    if(row.type == 'used_car_mortgage'){
                        buttons.push({
                            name: 'used_car_mortgage',
                            text: '二手车方案编辑',
                            icon: 'fa fa-pencil',
                            title: __('二手车方案编辑'),
                            extend: 'data-toggle="tooltip"',
                            classname: 'btn btn-xs btn-success btn-used_car_mortgage'
                        });
                    }
                    if(row.type == 'car_rental'){
                        buttons.push({
                            name: 'car_rental',
                            text: '租车方案编辑',
                            icon: 'fa fa-pencil',
                            title: __('租车方案编辑'),
                            extend: 'data-toggle="tooltip"',
                            classname: 'btn btn-xs btn-success btn-car_rental'
                        });
                    }

                    buttons.push({
                        name: 'del',
                        icon: 'fa fa-trash',
                        title: __('Del'),
                        extend: 'data-toggle="tooltip"',
                        classname: 'btn btn-xs btn-danger btn-delone'
                    });

                    return Table.api.buttonlink(this, buttons, value, row, index, 'operate');
                },
                /**
                 * 是否上线销售
                 * @param value
                 * @param row
                 * @param index
                 * @returns {string}
                 */
                toggle: function (value, row, index) {
                    var color = typeof this.color !== 'undefined' ? this.color : 'success';
                    var yes = typeof this.yes !== 'undefined' ? this.yes : 1;
                    var no = typeof this.no !== 'undefined' ? this.no : 0;
                    // return row.match_plan == 'match_success' ? '正在销售或已出售' : "<a href='javascript:;' data-toggle='tooltip' title='" + __('Click to toggle') + "' class='btn-change' data-id='"
                    //     + row.id + "' data-params='" + this.field + "=" + (value ? no : yes) + "'><i class='fa fa-toggle-on " + (value == yes ? 'text-' + color : 'fa-flip-horizontal text-gray') + " fa-2x'></i></a>";

                    return "<a href='javascript:;' data-toggle='tooltip' title='" + __('Click to toggle') + "' class='btn-change' data-id='"
                        + row.id + "' data-params='" + this.field + "=" + (value ? no : yes) + "'><i class='fa fa-toggle-on " + (value == yes ? 'text-' + color : 'fa-flip-horizontal text-gray') + " fa-2x'></i></a>";

                },

            },
            events: {
                operate: {
                    /**
                     * 按揭（新车）方案编辑
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-mortgage': function (e, value, row, index) {
                        $(".btn-mortgage").data("area", ["65%", "80%"]);
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, { ids: ids });
                        var url = options.extend.newedit_url;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
                    },
                    /**
                     * 按揭（二手车）方案编辑
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-used_car_mortgage': function (e, value, row, index) {
                        $(".btn-used_car_mortgage").data("area", ["65%", "80%"]);
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, { ids: ids });
                        var url = options.extend.usedcaredit_url;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
                    },
                    /**
                     * 租车方案编辑
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-car_rental': function (e, value, row, index) {
                        $(".btn-car_rental").data("area", ["65%", "80%"]);
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, { ids: ids });
                        var url = options.extend.rentaledit_url;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
                    },
                    /**
                     * 删除
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
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
            show_and_hide_table: function (table, type, data) {

                var types = type == 'show' ? 'showColumn' : 'hideColumn';
                for (var i in data) {
                    table.bootstrapTable(types, data[i]);
                }
            },
        }
    };
    return Controller;
});
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    // var goeasy = new GoEasy({
    //     appkey: 'BC-04084660ffb34fd692a9bd1a40d7b6c2'
    // });

    var Controller = {
        index: function () {

            // 初始化表格参数配置
            Table.api.init({});

            //绑定事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var panel = $($(this).attr("href"));
                if (panel.size() > 0) {
                    Controller.table[panel.attr("id")].call(this);
                    $(this).on('click', function (e) {
                        $($(this).attr("href")).find(".btn-refresh").trigger("click");
                    });
                }
                //移除绑定的事件
                $(this).unbind('shown.bs.tab');
            });

            //必须默认触发shown.bs.tab事件
            $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");

            $('ul.nav-tabs li a[data-toggle="tab"]').each(function () {
                $(this).trigger("shown.bs.tab");
            });

        },


        admeasure: function () {

            Form.api.bindevent($("form[role=form]"), function (data, ret) {

                var pre = parseInt($('#assigned-customer').text());

                $('#assigned-customer').text(pre+1);

                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                Fast.api.close(data);//这里是重点
                // console.log(data);
                Toastr.success("成功");//这个可有可无
            }, function (data, ret) {




                Toastr.success("失败");

            });
            // Controller.api.bindevent();



        },
        batch: function () {


            // $(".btn-add").data("area", ["300px","200px"]);
            Table.api.init({});
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                var datas = parseInt(data);

                var pre = parseInt($('#assigned-customer').text());

                $('#assigned-customer').text(pre+datas);
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                Fast.api.close(data);//这里是重点
                // console.log(data);
                 Toastr.success("成功");//这个可有可无
            }, function (data, ret) {


                Toastr.success("失败");

            });
            // Controller.api.bindevent();



        },

        table: {
            /**
             * 新客户
             */
            new_customer: function () {
                // 表格1
                var newCustomer = $("#newCustomer");
                newCustomer.on('load-success.bs.table', function (e, data) {

                    $('#new-customer').text(data.total);

                })
                newCustomer.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-newCustomer").data("area", ["60%", "60%"]);
                });
                // 初始化表格
                newCustomer.bootstrapTable({
                    url: 'backoffice/Custominfotabs/newCustomer',

                    toolbar: '#toolbar1',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,

                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: Fast.lang('Id'),operate:false},
                            // {field: 'platform_id', title: __('Platform_id')},
                            // {field: 'backoffice_id', title: __('Backoffice_id')},
                            {field: 'platform.name', title: __('所属平台')},
                            {field: 'backoffice.nickname', title: __('所属内勤'),operate:false,formatter:Controller.api.formatter.backoffice},

                            {field: 'username', title: __('Username')},
                            {field: 'phone', title: __('Phone')},
                            {
                                field: 'genderdata',
                                title: __('Genderdata'),
                                visible: false,
                                searchList: {"male": __('genderdata male'), "female": __('genderdata female')}
                            },
                            {
                                field: 'distributinternaltime',
                                title: __('Distributinternaltime'),
                                operate: false,
                                formatter: Table.api.formatter.datetime,
                                datetimeFormat:"YYYY-MM-DD"
                            },

                            {
                                field: 'operate', title: __('Operate'), table: newCustomer,
                                buttons: [
                                    {
                                        name: 'detail',
                                        text: '分配',
                                        title: __('Allocation'),
                                        icon: 'fa fa-share',
                                        classname: 'btn btn-xs btn-info btn-newCustomer',

                                    },

                                ],
                                events: Controller.api.operate,
                                formatter: Controller.api.formatter.operate
                            }
                        ]
                    ]
                });
                // 为表格1绑定事件
                Table.api.bindevent(newCustomer);

                //实时消息
                //推广平台分配给内勤
                // goeasy.subscribe({
                //     channel: 'demo-platform',
                //     onMessage: function(message){
                //
                //         var contents = message.content;
                //
                //         contents = contents.split('|');
                //
                //         if(Config.ADMIN_JS.id == contents[1]){
                //             Layer.alert('新消息：'+contents[0],{ icon:0},function(index){
                //                 Layer.close(index);
                //                 $(".btn-refresh").trigger("click");
                //             });
                //         }
                //
                //
                //
                //     }
                // });


                /**
                 * 批量分配
                 */
                $(document).on("click", ".btn-selected", function () {
                    var ids = Table.api.selectedids(newCustomer);
                    var url = 'backoffice/custominfotabs/batch?ids=' + ids;

                    var options = {
                        shadeClose: false,
                        shade: [0.3, '#393D49'],
                        area: ['60%', '60%'],
                        callback: function (value) {

                        }
                    };
                    Fast.api.open(url, '批量分配', options)
                });




            },
            /**
             * 已分配客户
             */
            assigned_customers: function () {
                // 表格2
                var assignedCustomers = $("#assignedCustomers");
                assignedCustomers.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-newCustomer").data("area", ["30%", "30%"]);
                });
                // 初始化表格
                assignedCustomers.bootstrapTable({
                    url: 'backoffice/Custominfotabs/assignedCustomers',
                    extend: {
                        index_url: 'customer/customerresource/index',
                        add_url: 'customer/customerresource/add',
                        edit_url: 'customer/customerresource/edit',
                        del_url: 'customer/customerresource/del',
                        multi_url: 'customer/customerresource/multi',
                        table: 'customer_resource',
                    },
                    toolbar: '#toolbar2',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'),operate:false},

                            {field: 'platform.name', title: __('所属平台')},
                            {field: 'backoffice.nickname', title: __('所属内勤'),operate:false,formatter:Controller.api.formatter.backoffice},
                            {field: 'admin.nickname', title: __('所属销售'),formatter:Controller.api.formatter.sales},

                            {field: 'username', title: __('Username')},
                            {field: 'phone', title: __('Phone')},
                            {
                                field: 'genderdata',
                                title: __('Genderdata'),
                                visible: false,
                                searchList: {"male": __('genderdata male'), "female": __('genderdata female')}
                            },
                            {
                                field: 'distributinternaltime',
                                title: __('Distributinternaltime'),
                                operate: false,
                                formatter: Table.api.formatter.datetime,
                                datetimeFormat:"YYYY-MM-DD"
                            },
                            {
                                field: 'distributsaletime',
                                title: __('Distributsaletime'),
                                operate: false,
                                formatter: Table.api.formatter.datetime,
                                datetimeFormat:"YYYY-MM-DD"
                            },

                        ]
                    ]
                });
                // 为表格2绑定事件
                Table.api.bindevent(assignedCustomers);

                assignedCustomers.on('load-success.bs.table', function (e, data) {
                    $('#assigned-customer').text(data.total);

                })

            }


        },
        add: function () {
            Controller.api.bindevent();

        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                $(document).on('click', "input[name='row[ismenu]']", function () {
                    var name = $("input[name='row[name]']");
                    name.prop("placeholder", $(this).val() == 1 ? name.data("placeholder-menu") : name.data("placeholder-node"));
                });
                $("input[name='row[ismenu]']:checked").trigger("click");
                Form.api.bindevent($("form[role=form]"));
            },
            operate:{
                /**
                 * 单个分配给销售
                 * @param e
                 * @param value
                 * @param row
                 * @param index
                 */
                'click .btn-newCustomer': function (e, value, row, index) {
                    e.stopPropagation();
                    e.preventDefault();
                    var table = $(this).closest('table');
                    var options = table.bootstrapTable('getOptions');
                    var ids = row[options.pk];
                    row = $.extend({}, row ? row : {}, {ids: ids});
                    var url = 'backoffice/custominfotabs/admeasure';
                    Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
                },
            },
            formatter: {
                operate: function (value, row, index) {

                    var table = this.table;
                    // 操作配置
                    var options = table ? table.bootstrapTable('getOptions') : {};
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);


                    return Table.api.buttonlink(this, buttons, value, row, index, 'operate');
                },
                backoffice: function (value, row, index) {
                    if (value) {
                        row.backoffice.avatar = "https://static.aicheyide.com" + row.backoffice.avatar;
                    }

                    return value != null ? "<img src=" + row.backoffice.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' + value : value;

                },
                sales: function (value, row, index) {

                    if (value) {
                        row.admin.avatar = "https://static.aicheyide.com" + row.admin.avatar;
                    }
                    return value != null ? "<img src=" + row.admin.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' +row.admin.department+' - '+ value : value;

                }
            }
        }

    };
    return Controller;
});
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
            })
        },

        table: {

            /**
             * 二手车待车管确认
             */
            secondcar_waitconfirm: function () {
                // 表格1
                var secondcarWaitconfirm = $("#secondcarWaitconfirm");
                
                // 初始化表格
                secondcarWaitconfirm.bootstrapTable({
                    url: "secondhandcar/carreservation/secondcarWaitconfirm",
                    extend: {
                        index_url: 'order/salesorder/index',
                        add_url: 'order/salesorder/add',
                        // edit_url: 'order/salesorder/edit',
                        // del_url: 'order/salesorder/del',
                        multi_url: 'order/salesorder/multi',
                        table: 'second_sales_order',
                    },
                    toolbar: '#toolbar1',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: '编号',operate:false},
                            {field: 'createtime', title: __('订车日期'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                            {field: 'plansecond.licenseplatenumber', title: __('车牌号')},
                            {field: 'plansecond.companyaccount', title: __('所属公司户')},
                            {field: 'admin.nickname', title: __('销售员'),formatter:Controller.api.formatter.sales},
                            {field: 'username', title: __('客户姓名')},
                            {field: 'id_card', title: __('身份证号')},
                            // {field: 'city', title: __('居住地址'),operate:false},
                            // {field: 'detailed_address', title: __('详细地址'),operate:false},
                            {field: 'phone', title: __('联系电话')},
                            {field: 'models.name', title: __('订车车型')},
                            {field: 'plansecond.newpayment', title: __('首付(元)'),operate:false},
                            {field: 'plansecond.monthlypaymen', title: __('月供(元)'),operate:false},
                            {field: 'plansecond.periods', title: __('期数'),operate:false},
                            {field: 'plansecond.bond', title: __('保证金(元)'),operate:false},
                            {field: 'plansecond.tailmoney', title: __('尾款(元)'),operate:false},
                            {field: 'plansecond.totalprices', title: __('车款总价(元)'),operate:false},
                            {field: 'downpayment', title: __('首期款(元)'),operate:false},
                            {
                                field: 'operate', title: __('Operate'), table: secondcarWaitconfirm,
                                buttons: [
                                    {
                                        name: 'secondcarWaitconfirm',
                                        text: '提交金融匹配',
                                        icon: 'fa fa-pencil',
                                        title: __('提交金融匹配'),
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-danger btn-secondcarWaitconfirm',

                                    }
                                ],

                                events: Controller.api.events.operate,
                                formatter: Controller.api.formatter.operate
                            }
                        ]
                    ]
                });
                // 为表格1绑定事件
                Table.api.bindevent(secondcarWaitconfirm);

                //数据实时统计
                secondcarWaitconfirm.on('load-success.bs.table',function(e,data){ 

                    var secondcarWaitconfirm =  $('#badge_secondcar_waitconfirm').text(data.total); 
                    secondcarWaitconfirm = parseInt($('#badge_secondcar_waitconfirm').text());
                    
                   
                })

            },
            /**
             * 二手车车管已确认
             */
            secondcar_confirm: function () {

                // 表格2
                var secondcarConfirm = $("#secondcarConfirm");
                
                // 初始化表格
                secondcarConfirm.bootstrapTable({
                    url: "secondhandcar/carreservation/secondcarConfirm",
                    extend: {
                        index_url: 'order/salesorder/index',
                        add_url: 'order/salesorder/add',
                        // edit_url: 'order/salesorder/edit',
                        // del_url: 'order/salesorder/del',
                        multi_url: 'order/salesorder/multi',
                        table: 'second_sales_order',
                    },
                    toolbar: '#toolbar2',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: '编号',operate:false},
                            {field: 'createtime', title: __('订车日期')},
                            {field: 'plansecond.licenseplatenumber', title: __('车牌号')},
                            {field: 'plansecond.companyaccount', title: __('所属公司户')},
                            {field: 'admin.nickname', title: __('销售员'),formatter:Controller.api.formatter.sales},
                            {field: 'username', title: __('客户姓名')},
                            {field: 'id_card', title: __('身份证号')},
                            // {field: 'city', title: __('居住地址'),operate:false},
                            // {field: 'detailed_address', title: __('详细地址'),operate:false},
                            {field: 'phone', title: __('联系电话')},
                            {field: 'models.name', title: __('订车车型')},
                            {field: 'plansecond.newpayment', title: __('首付(元)'),operate:false},
                            {field: 'plansecond.monthlypaymen', title: __('月供(元)'),operate:false},
                            {field: 'plansecond.periods', title: __('期数'),operate:false},
                            {field: 'plansecond.bond', title: __('保证金(元)'),operate:false},
                            {field: 'plansecond.tailmoney', title: __('尾款(元)'),operate:false},
                            {field: 'plansecond.totalprices', title: __('车款总价(元)'),operate:false},
                            {field: 'downpayment', title: __('首期款(元)'),operate:false},

                            {
                                field: 'operate', title: __('Operate'), table: secondcarConfirm,
                                buttons: [
                                    {
                                        name: 'is_reviewing_control', text: '风控正在审核中',
                                        hidden: function (row) {  /**风控正在审核中 */
                                            if (row.review_the_data == 'is_reviewing_control') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    {
                                        name: 'is_reviewing_finance', text: '正在匹配金融',
                                        hidden: function (row) {  /**正在匹配金融 */
                                            if (row.review_the_data == 'is_reviewing_finance') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    {
                                        name: 'is_reviewing_pass', icon: 'fa fa-check-circle', text: '风控正在匹配车辆', classname: ' text-info ',
                                        hidden: function (row) {  /**风控正在匹配车辆 */
                                            if (row.review_the_data == 'is_reviewing_pass') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    {
                                        name: 'for_the_car', icon: 'fa fa-check-circle', text: '车管备车中，通知客户可以进行提车', classname: ' text-info ',
                                        hidden: function (row) {  /**通知客户可以进行提车 */
                                            if (row.review_the_data == 'for_the_car') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    {
                                        name: 'not_through', icon: 'fa fa-times', text: '征信未通过，订单已关闭', classname: ' text-danger ',
                                        hidden: function (row) {  /**征信不通过 */

                                            if (row.review_the_data == 'not_through') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    {
                                        name: 'not_through', icon: 'fa fa-times', text: '征信不通过，待补录资料', classname: ' text-danger ',
                                        hidden: function (row) {  /**征信不通过，待补录资料 */

                                            if (row.review_the_data == 'collection_data') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                        }
                                    },
                                    {
                                        name: 'the_guarantor', text: '需交保证金', title: __('点击上传保证金收据'), classname: 'text-info',
                                        hidden: function (row) {  /**需交保证金 */

                                            if (row.review_the_data == 'the_guarantor') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    {

                                        name: 'the_car', icon: 'fa fa-automobile', text: '已提车', extend: 'data-toggle="tooltip"', title: __('订单已完成，客户已提车'), classname: ' text-success ',
                                        hidden: function (row) {  /**已提车 */
                                            if (row.review_the_data == 'the_car') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    }

                                ],
                                events: Controller.api.events.operate,

                                formatter: Controller.api.formatter.operate

                            }

                        ]
                    ]
                });
                // 为表格2绑定事件
                Table.api.bindevent(secondcarConfirm);

                //数据实时统计
                secondcarConfirm.on('load-success.bs.table',function(e,data){ 

                    var secondcarConfirm =  $('#badge_secondcar_confirm').text(data.total); 
                    secondcarConfirm = parseInt($('#badge_secondcar_confirm').text());
                    
                   
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
            formatter: {
                operate: function (value, row, index) {

                    var table = this.table;
                    // 操作配置
                    var options = table ? table.bootstrapTable('getOptions') : {};
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);


                    return Table.api.buttonlink(this, buttons, value, row, index, 'operate');
                },
                sales:function (value, row, index) {
                    // console.log(row);

                    return value==null?value : "<img src=" + Config.cdn_url+row.admin.avatar + " style='height:40px;width:40px;border-radius:50%'></img>" + '&nbsp;' +row.admin.department+' - '+value;
                },
            },
            events: {
                operate: {
                    /**
                     * 确认提交金融匹配
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-secondcarWaitconfirm': function (e, value, row, index) {
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
                            __('确定提交金融进行匹配吗?'),
                            {icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true},
                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');

                                Fast.api.ajax({
                                        url: "secondhandcar/carreservation/setAudit",
                                        data: {id: row[options.pk]},

                                    }, function (data, ret) {
                                        // console.log(data);

                                        // Toastr.success("成功");
                                        Layer.close(index);
                                        table.bootstrapTable('refresh');

                                    }, function (data, ret) {

                                        console.log(ret);

                                    },
                                )

                                // Table.api.multi("del", row[options.pk], table, that);
                                // Layer.close(index);
                            }
                        );
                    }
                }
            }
        }

    };



    return Controller;
});


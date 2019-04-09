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


        },

        table: {

            /**
             * 进件列表---待车管确认
             */
            secondcar_waitconfirm: function () {
                // 表格1
                var secondcarWaitconfirm = $("#secondcarWaitconfirm");
                
                // 初始化表格
                secondcarWaitconfirm.bootstrapTable({
                    url: "secondhandcar/takesecondcar/secondtakecar",
                    extend: {
                        // index_url: 'order/salesorder/index',
                        // add_url: 'order/salesorder/add',
                        // edit_url: 'order/salesorder/edit',
                        // del_url: 'order/salesorder/del',
                        // multi_url: 'order/salesorder/multi',
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
                                        name: 'data_dock', icon: 'fa pencil', text: '资料对接', extend: 'data-toggle="tooltip"', title: __('资料对接'), classname: ' btn btn-xs btn-info btn-editone ',
                                        hidden: function (row) {  /**已提车 */
                                            if(row.review_the_data == 'the_car' && !row.mortgage_registration_id){
                                                return false;
                                            }else if(row.review_the_data == 'the_car' && row.mortgage_registration_id){
                                                return true;
                                            }
                                            else if(row.review_the_data == 'for_the_car'){

                                                return true;
                                            }
                                        }
                                    },
                                    {
                                        name: 'data_dockk', icon: 'fa fa-check', text: '已对接资料', title: __('资料对接'), classname: 'text-info ',
                                        hidden: function (row) {  /**已提车 */
                                            if(row.review_the_data == 'the_car' && row.mortgage_registration_id){
                                                return false;
                                            }else if(row.review_the_data == 'the_car' && !row.mortgage_registration_id){
                                                return true;
                                            }
                                            else if(row.review_the_data == 'for_the_car'){

                                                return true;
                                            }
                                        }
                                    },
                                    {name: 'secondDetails', text: '查看详细资料', title: '查看订单详细资料' ,icon: 'fa fa-eye',classname: 'btn btn-xs btn-primary btn-dialog btn-secondDetails', 
                                        url: 'Sharedetailsdatas/second_car_share_data', callback:function(data){
                                            // console.log(data)
                                        }
                                    },

                                    {
                                        name: 'for_the_car', text: '确认提车', icon: 'fa fa-share', title: __('确认提车'), extend: 'data-toggle="tooltip"', classname: 'btn btn-xs btn-success btn-secondtakecar',
                                        hidden: function (row) {  /**确认提车 */
                                            if(row.review_the_data == 'for_the_car'){
                                                return false; 
                                            }
                                            else if(row.review_the_data == 'the_car'){
                                              
                                                return true;
                                            } 
                                        }
                                    },

                                    {
                                        name: 'the_car', icon: 'fa fa-automobile', text: '已提车', extend: 'data-toggle="tooltip"', title: __('订单已完成，客户已提车'), classname: ' text-success ',
                                        hidden: function (row) {  /**已提车 */
                                            if(row.review_the_data == 'the_car'){
                                                return false; 
                                            }
                                            else if(row.review_the_data == 'for_the_car'){
                                              
                                                return true;
                                            } 
                                        }
                                    },

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
                    $(".btn-secondDetails").data("area", ["95%", "95%"]);
                    $(".btn-editone").data("area", ["90%", "90%"]);

                    var secondcarWaitconfirm =  $('#badge_secondcar_waitconfirm').text(data.total); 
                    secondcarWaitconfirm = parseInt($('#badge_secondcar_waitconfirm').text());
                    
                   
                })

                //实时消息
                //内勤发送---车管
                // goeasy.subscribe({
                //     channel: 'demo-second_amount',
                //     onMessage: function(message){
                //         Layer.alert('新消息：'+message.content,{ icon:0},function(index){
                //             Layer.close(index);
                //             $(".btn-refresh").trigger("click");
                //         });
                //
                //     }
                // });

            },
            

        },
        add: function () {
            Controller.api.bindevent();

        },
        edit: function () {
            Controller.api.bindevent();
        },
        delivery: function () {
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

                    return value==null?value : "<img src=" + Config.cdn_url+row.admin.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' +row.admin.department+' - '+value;
                },
            },
            events: {
                operate: {

                    'click .btn-editone': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = "secondhandcar/takesecondcar/edit";
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
                    },
                    'click .btn-secondtakecar': function (e, value, row, index) {
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
                            __('确定进行提车吗?'),
                            {icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true},
                            function (index) {

                                Layer.close(index);
                                layer.prompt({
                                    formType:0,
                                    title:'请输入实际提车日期(<span class="text-danger">格式为：2018-05-08</span>)',
                                }, function(value, indexs, elem){

                                    var table = $(that).closest('table');
                                    var options = table.bootstrapTable('getOptions');

                                    Fast.api.ajax({
                                            url: "secondhandcar/takesecondcar/takecar",
                                            data: {
                                                id: row[options.pk],
                                                delivery:value
                                            },

                                        }, function (data, ret) {
                                            // console.log(data);

                                            // Toastr.success("成功");
                                            // Layer.close(index);
                                            table.bootstrapTable('refresh');

                                        }, function (data, ret) {

                                            console.log(ret);

                                        },
                                    )
                                    layer.close(indexs);
                                })


                            }
                        );
                    }
                }
            }
        }

    };



    return Controller;
});


define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template'], function ($, undefined, Backend, Table, Form, Template) {

    var Controller = {

        index: function () {

            Table.api.init({

            });

            /**
             * 绑定事件
             */
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var panel = $($(this).attr("href"));
                if (panel.size() > 0) {
                    Controller.table[panel.attr("id")].call(this);
                    $(this).on('click', function (e) {
                        $($(this).attr("href")).find(".btn-refresh").trigger("click");
                    });
                }
                /**
                 * 移除绑定的事件
                 */
                $(this).unbind('shown.bs.tab');
            });

            /**
             * 必须默认触发shown.bs.tab事件
             */
            $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");

            $('ul.nav-tabs li a[data-toggle="tab"]').each(function () {
                $(this).trigger("shown.bs.tab");
            })

        },
        /**
         * 多表格渲染
         */
        table: {
            /**
             * 按揭（新车）单
             */
            new_mortgage: function () {
                var newMortgage = $("#newMortgage");
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "快速搜索:客户姓名";};
                /**
                 * 初始化表格
                 */
                newMortgage.bootstrapTable({
                    url: 'sales/order/newMortgage',
                    extend: {
                        newadd_url: 'sales/order/newadd',
                        newedit_url: 'sales/order/newedit',
                        import_url: 'sales/order/import',
                        del_url: 'sales/order/del',
                        multi_url: 'sales/order/multi',
                        table: 'order',
                    },
                    toolbar: '#toolbar1',
                    pk: 'id',
                    sortName: 'id',
                    // searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id')},
                            {
                                field: 'admin_id', title: __('销售员'), formatter: function (v, r, i) {
                                    return v ? '  <img src='+Config.cdn + r.admin.avatar+' alt="" width="25" height="25" >  '  + r.admin.nickname : '';
                                }
                            },
                            {
                                field: 'customer_source',
                                title: __('Customer_source'),
                                searchList: {
                                    "direct_the_guest": __('Customer_source direct_the_guest'),
                                    "turn_to_introduce": __('Customer_source turn_to_introduce')
                                },
                                formatter: Table.api.formatter.normal
                            },
                            {field: 'financial_name', title: __('Financial_name')},
                            {field: 'username', title: __('Username'), formatter: Controller.api.formatter.judge},
                            {field: 'phone', title: __('Phone')},
                            {field: 'id_card', title: __('Id_card')},
                            {
                                field: 'type',
                                title: __('Type'),
                                searchList: {
                                    "mortgage": __('Type mortgage'),
                                    "used_car_mortgage": __('Type used_car_mortgage'),
                                    "car_rental": __('Type car_rental'),
                                    "full_new_car": __('Type full_new_car'),
                                    "full_used_car": __('Type full_used_car'),
                                    "sublet": __('Type sublet'),
                                    "affiliated": __('Type affiliated')
                                },
                                formatter: Table.api.formatter.normal
                            },
                            {field: 'models_name', title: __('Models_name')},
                            {field: 'payment', title: __('Payment'), operate: 'BETWEEN'},
                            {field: 'monthly', title: __('Monthly'), operate: 'BETWEEN'},
                            {
                                field: 'nperlist',
                                title: __('Nperlist'),
                                searchList: {
                                    "12": __('Nperlist 12'),
                                    "24": __('Nperlist 24'),
                                    "36": __('Nperlist 36'),
                                    "48": __('Nperlist 48'),
                                    "60": __('Nperlist 60')
                                },
                                formatter: Table.api.formatter.normal
                            },
                            {field: 'gps', title: __('Gps'), operate: 'BETWEEN'},
                            {
                                field: 'createtime',
                                title: __('Createtime'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },
                            {
                                field: 'delivery_datetime',
                                title: __('Delivery_datetime'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },
                            {field: 'note_sales', title: __('Note_sales')},
                            {
                                field: 'operate', title: __('Operate'), table: newMortgage,
                                buttons: [

                                    /**
                                     * 按揭（新车）单编辑
                                     */
                                    {
                                        name: 'newedit',
                                        text: '按揭（新车）单编辑',
                                        icon: 'fa fa-pencil',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('按揭（新车）单编辑'),
                                        classname: 'btn btn-xs btn-success btn-newedit',
                                        visible: function (row) {
                                            return row.lift_car_status == 'no' ? true : false;
                                        }

                                    },
                                    /**
                                     * 等待车管审批
                                     */
                                    {
                                        name: '',
                                        text: '等待车管审批',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('等待车管审批'),
                                        icon: 'fa fa-spinner',
                                        classname: 'btn btn-xs btn-danger',
                                        visible: function (row) {
                                            return row.lift_car_status == 'no' ? true : false;
                                        }

                                    },
                                    /**
                                     * 已提车
                                     */
                                    {
                                        name: 'success',
                                        icon: 'fa fa-check',
                                        title: __('已提车'),
                                        text: '已提车',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'text-info',
                                        visible: function (row) {
                                            return row.lift_car_status == 'yes' ? true : false;
                                        }
                                    },


                                ],
                                events: Controller.api.events.operate,
                                formatter: Controller.api.formatter.operate
                            }
                        ]
                    ],
                    // fixedColumns: true,   //开启冻结列
                    // fixedNumber: 3,       //冻结左侧前3行

                });
                // newMortgage.bootstrapTable('fixedEvents'); //注册冻结列事件
                /**
                 * 刷新表格渲染
                 */
                newMortgage.on('load-success.bs.table', function (e, data) {
                    // $('#badge_order_acar').text(data.total);
                    $(".btn-newadd").data("area", ["65%", "80%"]);

                })
                // 为新增按揭（新车）单绑定事件
                Table.api.bindevent(newMortgage);

                /**
                 * 新增按揭（新车）单
                 */
                $(document).on("click", ".btn-newadd", function () {   
                    var url = 'sales/order/newadd';
                    var options = {
                        shadeClose: false,
                        shade: [0.3, '#393D49'],
                        area:['65%','80%'],
                        callback:function(value){

                        }
                    }
                    Fast.api.open(url,'新增按揭（新车）单',options)
                })
            },
            /**
             * 按揭（二手车）单
             */
            used_car_mortgage: function () {
                var usedCarMortgage = $("#usedCarMortgage");
                // 初始化表格
                usedCarMortgage.bootstrapTable({
                    url: 'sales/order/usedCarMortgage',
                    extend: {
                        usedcaradd_url: 'sales/order/usedcaradd',
                        usedcaredit_url: 'sales/order/usedcaredit',
                        import_url: 'sales/order/import',
                        del_url: 'sales/order/seconddel',
                        multi_url: 'sales/order/multi',
                        table: 'order',
                    },
                    toolbar: '#toolbar2',
                    pk: 'id',
                    // searchFormVisible: true,
                    sortName: 'id',
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id')},
                            {
                                field: 'admin_id', title: __('销售员'), formatter: function (v, r, i) {
                                    return v ? '  <img src='+Config.cdn + r.admin.avatar+' alt="" width="25" height="25" >  '  + r.admin.nickname : '';
                                }
                            },
                            {
                                field: 'customer_source',
                                title: __('Customer_source'),
                                searchList: {
                                    "direct_the_guest": __('Customer_source direct_the_guest'),
                                    "turn_to_introduce": __('Customer_source turn_to_introduce')
                                },
                                formatter: Table.api.formatter.normal
                            },
                            {field: 'financial_name', title: __('Financial_name')},
                            {field: 'username', title: __('Username'), formatter: Controller.api.formatter.judge},
                            {field: 'phone', title: __('Phone')},
                            {field: 'id_card', title: __('Id_card')},
                            {
                                field: 'type',
                                title: __('Type'),
                                searchList: {
                                    "mortgage": __('Type mortgage'),
                                    "used_car_mortgage": __('Type used_car_mortgage'),
                                    "car_rental": __('Type car_rental'),
                                    "full_new_car": __('Type full_new_car'),
                                    "full_used_car": __('Type full_used_car'),
                                    "sublet": __('Type sublet'),
                                    "affiliated": __('Type affiliated')
                                },
                                formatter: Table.api.formatter.normal
                            },
                            {field: 'models_name', title: __('Models_name')},
                            {field: 'payment', title: __('Payment'), operate: 'BETWEEN'},
                            {field: 'monthly', title: __('Monthly'), operate: 'BETWEEN'},
                            {
                                field: 'nperlist',
                                title: __('Nperlist'),
                                searchList: {
                                    "12": __('Nperlist 12'),
                                    "24": __('Nperlist 24'),
                                    "36": __('Nperlist 36'),
                                    "48": __('Nperlist 48'),
                                    "60": __('Nperlist 60')
                                },
                                formatter: Table.api.formatter.normal
                            },
                            {field: 'gps', title: __('Gps'), operate: 'BETWEEN'},
                            {
                                field: 'createtime',
                                title: __('Createtime'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },
                            {
                                field: 'delivery_datetime',
                                title: __('Delivery_datetime'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },
                            {field: 'note_sales', title: __('Note_sales')},
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: usedCarMortgage,
                                buttons: [

                                    /**
                                     * 按揭（二手车）单编辑 
                                     */
                                    {
                                        name: 'newedit', 
                                        text: '按揭（二手车）单编辑', 
                                        icon: 'fa fa-pencil', 
                                        extend: 'data-toggle="tooltip"', 
                                        title: __('按揭（二手车）单编辑'), 
                                        classname: 'btn btn-xs btn-success btn-usedcaredit',
                                        visible: function (row) {
                                            return row.lift_car_status == 'no' ? true : false;
                                        }
                                       
                                    },
                                    /**
                                     * 等待车管审批
                                     */
                                    {
                                        name: '',
                                        text: '等待车管审批',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('等待车管审批'),
                                        icon: 'fa fa-spinner', 
                                        classname: 'btn btn-xs btn-danger',
                                        visible: function (row) {
                                            return row.lift_car_status == 'no' ? true : false;
                                        }

                                    },
                                    /**
                                     * 已提车
                                     */
                                    {
                                        name: 'success',
                                        icon: 'fa fa-check',
                                        title: __('已提车'),
                                        text: '已提车',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'text-info',
                                        visible: function (row) {
                                            return row.lift_car_status == 'yes' ? true : false;
                                        }
                                    },

                           
                                ],
                                events: Controller.api.events.operate,
                                formatter: Controller.api.formatter.operate
                            }
                        ]
                    ]

                });

                /**
                 * 刷新表格渲染
                 */
                usedCarMortgage.on('load-success.bs.table', function (e, data) {
                    // $('#badge_order_second').text(data.total);
                    $(".btn-usedcaradd").data("area", ["65%", "80%"]);

                })
                /**
                 * 为按揭（二手车）单表格绑定事件
                 */
                Table.api.bindevent(usedCarMortgage);
                /**
                 * 新增按揭（二手车）单
                 */
                $(document).on("click", ".btn-usedcaradd", function () {   

                    var url = 'sales/order/usedcaradd';
                    var options = {
                        shadeClose: false,
                        shade: [0.3, '#393D49'],
                        area:['65%','80%'],
                        callback:function(value){  

                        }
                    }
                    Fast.api.open(url,'新增按揭（二手车）单',options)
                })
            },
            // /**
            //  * 租车单
            //  */
            // car_rental: function () {
            //     var carRental = $("#carRental"); 
            //     $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "快速搜索:客户姓名";};
            //     // 初始化表格
            //     carRental.bootstrapTable({
            //         url: 'sales/order/carRental',
            //         extend: {
            //             rentaladd_url: 'sales/order/rentaladd',
            //             rentaledit_url: 'sales/order/rentaledit',
            //             import_url: 'sales/order/import',
            //             del_url: 'sales/order/rentaldel',
            //             multi_url: 'sales/order/multi',
            //             table: 'order',
            //         },
            //         toolbar: '#toolbar3',
            //         pk: 'id',
            //         sortName: 'id',
            //         // searchFormVisible: true,
            //         columns: [
            //             [
            //                 {checkbox: true},
            //                 {field: 'id', title: __('Id')},
            //                 {
            //                     field: 'admin_id', title: __('销售员'), formatter: function (v, r, i) {
            //                         return v ? '  <img src='+Config.cdn + r.admin.avatar+' alt="" width="25" height="25" >  '  + r.admin.nickname : '';
            //                     }
            //                 },
            //                 {
            //                     field: 'customer_source',
            //                     title: __('Customer_source'),
            //                     searchList: {
            //                         "direct_the_guest": __('Customer_source direct_the_guest'),
            //                         "turn_to_introduce": __('Customer_source turn_to_introduce')
            //                     },
            //                     formatter: Table.api.formatter.normal
            //                 },
            //                 {field: 'username', title: __('Username'), formatter: Controller.api.formatter.judge},
            //                 {field: 'phone', title: __('Phone')},
            //                 {field: 'id_card', title: __('Id_card')},
            //                 {
            //                     field: 'type',
            //                     title: __('Type'),
            //                     searchList: {
            //                         "mortgage": __('Type mortgage'),
            //                         "used_car_mortgage": __('Type used_car_mortgage'),
            //                         "car_rental": __('Type car_rental'),
            //                         "full_new_car": __('Type full_new_car'),
            //                         "full_used_car": __('Type full_used_car'),
            //                         "sublet": __('Type sublet'),
            //                         "affiliated": __('Type affiliated')
            //                     },
            //                     formatter: Table.api.formatter.normal
            //                 },
            //                 {field: 'models_name', title: __('Models_name')},
            //                 {field: 'rent', title: __('Rent'), operate: 'BETWEEN'},
            //                 {field: 'deposit', title: __('Deposit'), operate: 'BETWEEN'},
            //                 {
            //                     field: 'createtime',
            //                     title: __('Createtime'),
            //                     operate: 'RANGE',
            //                     addclass: 'datetimerange',
            //                     formatter: Table.api.formatter.datetime
            //                 },
            //                 {
            //                     field: 'delivery_datetime',
            //                     title: __('Delivery_datetime'),
            //                     operate: 'RANGE',
            //                     addclass: 'datetimerange',
            //                     formatter: Table.api.formatter.datetime
            //                 },
            //                 {field: 'note_sales', title: __('Note_sales')},
            //                 {
            //                     field: 'operate',
            //                     title: __('Operate'),
            //                     table: carRental,
            //                     buttons: [

            //                         /**
            //                          * 租车单编辑 
            //                          */
            //                         {
            //                             name: 'newedit', 
            //                             text: '租车单编辑', 
            //                             icon: 'fa fa-pencil', 
            //                             extend: 'data-toggle="tooltip"', 
            //                             title: __('租车单编辑'), 
            //                             classname: 'btn btn-xs btn-success btn-rentaledit',
            //                             visible: function (row) {
            //                                 return row.lift_car_status == 'no' ? true : false;
            //                             }
                                       
            //                         },
            //                         /**
            //                          * 已提车
            //                          */
            //                         {
            //                             name: 'success',
            //                             icon: 'fa fa-check',
            //                             title: __('已提车'),
            //                             text: '已提车',
            //                             extend: 'data-toggle="tooltip"',
            //                             classname: 'text-info',
            //                             visible: function (row) {
            //                                 return row.lift_car_status == 'yes' ? true : false;
            //                             }
            //                         },

                           
            //                     ],
            //                     events: Controller.api.events.operate,
            //                     formatter: Controller.api.formatter.operate
            //                 }
            //             ]
            //         ]
                    
            //     });

            //     /**
            //      * 表格刷新渲染
            //      */
            //     carRental.on('load-success.bs.table', function (e, data) {
            //         // $('#badge_order_rental').text(data.total);
            //         $(".btn-rentaladd").data("area", ["60%", "85%"]);

            //     })
            //     /**
            //      * 为租车单表格绑定事件
            //      */
            //     Table.api.bindevent(carRental);
            //     /**
            //      * 新增租车单
            //      */
            //     $(document).on("click", ".btn-rentaladd", function () {   
                        
            //         var url = 'sales/order/rentaladd';
            //         var options = {
            //             shadeClose: false,
            //             shade: [0.3, '#393D49'],
            //             area:['65%','80%'],
            //             callback:function(value){

            //             }
            //         }
            //         Fast.api.open(url,'新增租车单',options)
            //     })

            // },
            /**
             * 全款单（新车）
             */
            full_new_car: function () {
                var fullNewCar = $("#fullNewCar");
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "快速搜索:客户姓名";};
                // 初始化表格
                fullNewCar.bootstrapTable({
                    url: 'sales/order/fullNewCar',
                    extend: {
                        fulladd_url: 'sales/order/fulladd',
                        fulledit_url: 'sales/order/fulledit',
                        import_url: 'sales/order/import',
                        del_url: 'sales/order/fulldel',
                        multi_url: 'ssales/order/multi',
                        table: 'order',
                    },
                    toolbar: '#toolbar4',
                    pk: 'id',
                    sortName: 'id',
                    // searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id')},
                            {
                                field: 'admin_id', title: __('销售员'), formatter: function (v, r, i) {
                                    return v ? '  <img src='+Config.cdn + r.admin.avatar+' alt="" width="25" height="25" >  '  + r.admin.nickname : '';
                                }
                            },
                            {
                                field: 'customer_source',
                                title: __('Customer_source'),
                                searchList: {
                                    "direct_the_guest": __('Customer_source direct_the_guest'),
                                    "turn_to_introduce": __('Customer_source turn_to_introduce')
                                },
                                formatter: Table.api.formatter.normal
                            },
                            {field: 'financial_name', title: __('Financial_name')},
                            {field: 'username', title: __('Username'), formatter: Controller.api.formatter.judge},
                            {field: 'phone', title: __('Phone')},
                            {field: 'id_card', title: __('Id_card')},
                            {
                                field: 'type',
                                title: __('Type'),
                                searchList: {
                                    "mortgage": __('Type mortgage'),
                                    "used_car_mortgage": __('Type used_car_mortgage'),
                                    "car_rental": __('Type car_rental'),
                                    "full_new_car": __('Type full_new_car'),
                                    "full_used_car": __('Type full_used_car'),
                                    "sublet": __('Type sublet'),
                                    "affiliated": __('Type affiliated')
                                },
                                formatter: Table.api.formatter.normal
                            },
                            {field: 'models_name', title: __('Models_name')},
                            {field: 'payment', title: __('Payment'), operate: 'BETWEEN'},
                            {field: 'monthly', title: __('Monthly'), operate: 'BETWEEN'},
                            {
                                field: 'nperlist',
                                title: __('Nperlist'),
                                searchList: {
                                    "12": __('Nperlist 12'),
                                    "24": __('Nperlist 24'),
                                    "36": __('Nperlist 36'),
                                    "48": __('Nperlist 48'),
                                    "60": __('Nperlist 60')
                                },
                                formatter: Table.api.formatter.normal
                            },
                            {field: 'gps', title: __('Gps'), operate: 'BETWEEN'},
                            {
                                field: 'createtime',
                                title: __('Createtime'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },
                            {
                                field: 'delivery_datetime',
                                title: __('Delivery_datetime'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },
                            {field: 'note_sales', title: __('Note_sales')},
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: fullNewCar,
                                buttons: [

                                    /**
                                     * 全款（新车）单编辑 
                                     */
                                    {
                                        name: 'newedit', 
                                        text: '全款（新车）单编辑', 
                                        icon: 'fa fa-pencil', 
                                        extend: 'data-toggle="tooltip"', 
                                        title: __('全款（新车）单编辑'), 
                                        classname: 'btn btn-xs btn-success btn-fulledit',
                                        visible: function (row) {
                                            return row.lift_car_status == 'no' ? true : false;
                                        }
                                       
                                    },
                                    /**
                                     * 等待车管审批
                                     */
                                    {
                                        name: '',
                                        text: '等待车管审批',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('等待车管审批'),
                                        icon: 'fa fa-spinner', 
                                        classname: 'btn btn-xs btn-danger',
                                        visible: function (row) {
                                            return row.lift_car_status == 'no' ? true : false;
                                        }

                                    },
                                    /**
                                     * 已提车
                                     */
                                    {
                                        name: 'success',
                                        icon: 'fa fa-check',
                                        title: __('已提车'),
                                        text: '已提车',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'text-info',
                                        visible: function (row) {
                                            return row.lift_car_status == 'yes' ? true : false;
                                        }
                                    },

                           
                                ],
                                events: Controller.api.events.operate,
                                formatter: Controller.api.formatter.operate
                            }
                        ]
                    ]
                });

                /**
                 * 刷新表格渲染
                 */
                fullNewCar.on('load-success.bs.table', function (e, data) {
                    
                    $(".btn-fulladd").data("area", ["65%", "80%"]);

                })
          
                /**
                 * 为新增全款（新车）单表格绑定事件
                 */
                Table.api.bindevent(fullNewCar);

                /**
                 * 新增全款（新车）单
                 */
                $(document).on("click", ".btn-fulladd", function () {   
                        
                    var url = 'sales/order/fulladd';
                    var options = {
                        shadeClose: false,
                        shade: [0.3, '#393D49'],
                        area:['65%','80%'],
                        callback:function(value){ 

                        }
                    }
                    Fast.api.open(url,'新增全款（新车）单',options)
                })
            },
            /**
             * 全款单（二手车）
             */
            full_used_car: function () {
                var fullUsedCar = $("#fullUsedCar");
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "快速搜索:客户姓名";};
                // 初始化表格
                fullUsedCar.bootstrapTable({
                    url: 'sales/order/fullUsedCar',
                    extend: {
                        fullusedadd_url: 'sales/order/fullusedadd',
                        fullusededit_url: 'sales/order/fullusededit',
                        import_url: 'sales/order/import',
                        del_url: 'sales/order/del',
                        multi_url: 'sales/order/multi',
                        table: 'order',
                    },
                    toolbar: '#toolbar5',
                    pk: 'id',
                    sortName: 'id',
                    // searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id')},
                            {
                                field: 'admin_id', title: __('销售员'), formatter: function (v, r, i) {
                                    return v ? '  <img src='+Config.cdn + r.admin.avatar+' alt="" width="25" height="25" >  '  + r.admin.nickname : '';
                                }
                            },
                            {
                                field: 'customer_source',
                                title: __('Customer_source'),
                                searchList: {
                                    "direct_the_guest": __('Customer_source direct_the_guest'),
                                    "turn_to_introduce": __('Customer_source turn_to_introduce')
                                },
                                formatter: Table.api.formatter.normal
                            },
                            {field: 'financial_name', title: __('Financial_name')},
                            {field: 'username', title: __('Username'), formatter: Controller.api.formatter.judge},
                            {field: 'phone', title: __('Phone')},
                            {field: 'id_card', title: __('Id_card')},
                            {
                                field: 'type',
                                title: __('Type'),
                                searchList: {
                                    "mortgage": __('Type mortgage'),
                                    "used_car_mortgage": __('Type used_car_mortgage'),
                                    "car_rental": __('Type car_rental'),
                                    "full_new_car": __('Type full_new_car'),
                                    "full_used_car": __('Type full_used_car'),
                                    "sublet": __('Type sublet'),
                                    "affiliated": __('Type affiliated')
                                },
                                formatter: Table.api.formatter.normal
                            },
                            {field: 'models_name', title: __('Models_name')},
                            {field: 'payment', title: __('Payment'), operate: 'BETWEEN'},
                            {field: 'monthly', title: __('Monthly'), operate: 'BETWEEN'},
                            {
                                field: 'nperlist',
                                title: __('Nperlist'),
                                searchList: {
                                    "12": __('Nperlist 12'),
                                    "24": __('Nperlist 24'),
                                    "36": __('Nperlist 36'),
                                    "48": __('Nperlist 48'),
                                    "60": __('Nperlist 60')
                                },
                                formatter: Table.api.formatter.normal
                            },
                            {field: 'gps', title: __('Gps'), operate: 'BETWEEN'},
                            {
                                field: 'createtime',
                                title: __('Createtime'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },
                            {
                                field: 'delivery_datetime',
                                title: __('Delivery_datetime'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },
                            {field: 'note_sales', title: __('Note_sales')},
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: fullNewCar,
                                buttons: [

                                    /**
                                     * 全款（二手车）单编辑 
                                     */
                                    {
                                        name: 'newedit', 
                                        text: '全款（二手车）单编辑', 
                                        icon: 'fa fa-pencil', 
                                        extend: 'data-toggle="tooltip"', 
                                        title: __('全款（二手车）单编辑'), 
                                        classname: 'btn btn-xs btn-success btn-fullusededit',
                                        visible: function (row) {
                                            return row.lift_car_status == 'no' ? true : false;
                                        }
                                       
                                    },
                                    /**
                                     * 等待车管审批
                                     */
                                    {
                                        name: '',
                                        text: '等待车管审批',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('等待车管审批'),
                                        icon: 'fa fa-spinner', 
                                        classname: 'btn btn-xs btn-danger',
                                        visible: function (row) {
                                            return row.lift_car_status == 'no' ? true : false;
                                        }

                                    },
                                    /**
                                     * 已提车
                                     */
                                    {
                                        name: 'success',
                                        icon: 'fa fa-check',
                                        title: __('已提车'),
                                        text: '已提车',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'text-info',
                                        visible: function (row) {
                                            return row.lift_car_status == 'yes' ? true : false;
                                        }
                                    },

                           
                                ],
                            }
                        ]
                    ]
                });

                /**
                 * 刷新表格渲染
                 */
                fullUsedCar.on('load-success.bs.table', function (e, data) {
                    // $('#badge_second_order_full').text(data.total);
                    $(".btn-fullusedadd").data("area", ["65%", "80%"]);

                })
                /**
                 * 为全款单表格绑定事件
                 */
                Table.api.bindevent(fullUsedCar);
                /**
                 * 新增全款（二手车）单
                 */
                $(document).on("click", ".btn-fullusedadd", function () {

                    var url = 'sales/order/fullusedadd';
                    var options = {
                        shadeClose: false,
                        shade: [0.3, '#393D49'],
                        area:['65%','80%'],
                        callback:function(value){

                        }
                    }
                    Fast.api.open(url,'新增全款（二手车）单',options)
                })
            },
        },
        /**
         * 新车资料添加
         */
        newadd: function () {

            $('#c-customer_source').on('change', function (e) {  //客户资源
                $('.turn').toggleClass('hidden');
                // var v =  $(this).children('option:selected').val();
            })
            $('.marriage').on('change',function () {   //配偶身份证反面
                $('.marriage_block').toggleClass('hidden');
            })
            
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
        /**
         * 新车资料修改
         */
        newedit: function () {

            var turn = $('#c-customer_source').val();

            turn == 'turn_to_introduce' ? $('.turn').toggleClass('hidden') : $('.turn').toggleClass('show');

            var marriage = $('.marriage').val();
           
            marriage == 'no' ? $('.marriage_block').toggleClass('hidden') : $('.marriage_block').toggleClass('show');

            $('#c-customer_source').on('change', function (e) {  //客户资源
                $('.turn').toggleClass('hidden');
            })
            $('.marriage').on('change',function () {   //配偶身份证反面
                $('.marriage_block').toggleClass('hidden');
            })

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
        /**
         * 二手车资料添加
         */
        usedcaradd:function(){

            $('#c-customer_source').on('change', function (e) {  //客户资源
                $('.turn').toggleClass('hidden');
                // var v =  $(this).children('option:selected').val();
            })
            $('.marriage').on('change',function () {   //配偶身份证反面
                $('.marriage_block').toggleClass('hidden');
            })
            
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
        /**
         * 二手车资料修改
         */
        usedcaredit:function(){

            var turn = $('#c-customer_source').val();

            turn == 'turn_to_introduce' ? $('.turn').toggleClass('hidden') : $('.turn').toggleClass('show');

            var marriage = $('.marriage').val();
           
            marriage == 'no' ? $('.marriage_block').toggleClass('hidden') : $('.marriage_block').toggleClass('show');

            $('#c-customer_source').on('change', function (e) {  //客户资源
                $('.turn').toggleClass('hidden');
                // var v =  $(this).children('option:selected').val();
            })
            $('.marriage').on('change',function () {   //配偶身份证反面
                $('.marriage_block').toggleClass('hidden');
            })
            
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
        /**
         * 租车资料添加
         */
        rentaladd:function(){

            $('#c-customer_source').on('change', function (e) {  //客户资源
                $('.turn').toggleClass('hidden');
                // var v =  $(this).children('option:selected').val();
            })
            $('.marriage').on('change',function () {   //配偶身份证反面
                $('.marriage_block').toggleClass('hidden');
            })
            
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
        /**
         * 租车资料修改
         */
        rentaledit:function(){

            var turn = $('#c-customer_source').val();

            turn == 'turn_to_introduce' ? $('.turn').toggleClass('hidden') : $('.turn').toggleClass('show');

            var marriage = $('.marriage').val();
           
            marriage == 'no' ? $('.marriage_block').toggleClass('hidden') : $('.marriage_block').toggleClass('show');

            $('#c-customer_source').on('change', function (e) {  //客户资源
                $('.turn').toggleClass('hidden');
                // var v =  $(this).children('option:selected').val();
            })
            $('.marriage').on('change',function () {   //配偶身份证反面
                $('.marriage_block').toggleClass('hidden');
            })
            
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
        /**
         * 全款车资料添加
         */
        fulladd:function(){

            $('#c-customer_source').on('change', function (e) {  //客户资源
                $('.turn').toggleClass('hidden');
                // var v =  $(this).children('option:selected').val();
            })
            $('.marriage').on('change',function () {   //配偶身份证反面
                $('.marriage_block').toggleClass('hidden');
            })
            
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
        /**
         * 全款车资料修改
         */
        fulledit:function(){

            var turn = $('#c-customer_source').val();

            turn == 'turn_to_introduce' ? $('.turn').toggleClass('hidden') : $('.turn').toggleClass('show');

            var marriage = $('.marriage').val();
           
            marriage == 'no' ? $('.marriage_block').toggleClass('hidden') : $('.marriage_block').toggleClass('show');

            $('#c-customer_source').on('change', function (e) {  //客户资源
                $('.turn').toggleClass('hidden');
                // var v =  $(this).children('option:selected').val();
            })
            $('.marriage').on('change',function () {   //配偶身份证反面
                $('.marriage_block').toggleClass('hidden');
            })
            
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
        /**
         * 全款二手车资料添加
         */
        fullusedadd:function(){

            $('#c-customer_source').on('change', function (e) {  //客户资源
                $('.turn').toggleClass('hidden');
                // var v =  $(this).children('option:selected').val();
            })
            $('.marriage').on('change',function () {   //配偶身份证反面
                $('.marriage_block').toggleClass('hidden');
            })
            
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
        /**
         * 全款二手车资料编辑
         */
        fullusededit:function(){

            var turn = $('#c-customer_source').val();

            turn == 'turn_to_introduce' ? $('.turn').toggleClass('hidden') : $('.turn').toggleClass('show');

            var marriage = $('.marriage').val();
           
            marriage == 'no' ? $('.marriage_block').toggleClass('hidden') : $('.marriage_block').toggleClass('show');

            $('#c-customer_source').on('change', function (e) {  //客户资源
                $('.turn').toggleClass('hidden');
                // var v =  $(this).children('option:selected').val();
            })
            $('.marriage').on('change',function () {   //配偶身份证反面
                $('.marriage_block').toggleClass('hidden');
            })
            
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
            events: {
                operate: {
                    /**
                     * 按揭（新车）编辑
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-newedit': function (e, value, row, index) {
                        $(".btn-newedit").data("area", ["65%", "80%"]);
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
                     * 按揭（二手车）编辑
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-usedcaredit': function (e, value, row, index) {
                        $(".btn-usedcaredit").data("area", ["65%", "80%"]);
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
                     * 租车编辑
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-rentaledit': function (e, value, row, index) {
                        $(".btn-rentaledit").data("area", ["65%", "80%"]);
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
                     * 全款（新车）编辑
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-fulledit': function (e, value, row, index) {
                        $(".btn-fulledit").data("area", ["65%", "80%"]);
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, { ids: ids });
                        var url = options.extend.fulledit_url;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
                    },
                    /**
                     * 全款（二手车）编辑
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-fullusededit': function (e, value, row, index) {
                        $(".btn-fullusededit").data("area", ["65%", "80%"]);
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, { ids: ids });
                        var url = options.extend.fullusededit_url;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
                    },

                    /**
                     * 删除按钮
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-delone': function (e, value, row, index) {  /**删除按钮 */
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
                            { icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true },
                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');
                                Table.api.multi("del", row[options.pk], table, that);
                                Layer.close(index);
                            }
                        );
                    },
                }
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
                /**
                 * 提车返回√
                 * @param value
                 * @returns {string}
                 */
                judge: function (value, row, index) {

                    var res = "";
                    var color = "";
                   
                   if(row.lift_car_status == 'yes'){
                        res = "<i class='fa fa-check'></i>"
                        color = "success";
                    
                    }

                    //渲染状态
                    var html = '<span class="text-' + color + '"> ' + row.username +  __(res) + '</span>';

                    return html;

                },
            }
        }
    };
    return Controller;
});
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    /**
     * goeasy推送的key
     */
    // var goeasy = new GoEasy({
    //     appkey: 'BC-c02d73e1952048ecb954436f3bf79b4a'
    // });


    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'vehicle/vehiclemanagement/index' + location.search,
                    add_url: 'vehicle/vehiclemanagement/add',
                    // edit_url: 'vehicle/vehiclemanagement/edit',
                    // del_url: 'vehicle/vehiclemanagement/del',
                    multi_url: 'vehicle/vehiclemanagement/multi',
                    table: 'order',
                }
            });

            var table = $("#table");
            // 绑定TAB事件
            $('.panel-heading ul[data-field] li a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                switch (e.currentTarget.innerHTML) {
                    case '按揭（新车）':
                        Controller.api.show_and_hide_table(table, 'show', ['payment', 'monthly', 'nperlist', 'end_money', 'tail_money', 'margin']);
                        Controller.api.show_and_hide_table(table, 'hide', ['orderdetails.subordinate_branch']);
                        break;
                    case '按揭（二手车）':
                        break;
                    case '纯租':
                        break;
                    case '全款（新车）':
                        table.bootstrapTable('showColumn', 'orderdetails.subordinate_branch');
                        Controller.api.show_and_hide_table(table, 'show', ['orderdetails.subordinate_branch']);
                        Controller.api.show_and_hide_table(table, 'hide', ['payment', 'monthly', 'nperlist', 'end_money', 'tail_money', 'margin']);
                        break;
                    case '全款（二手车）':
                        break;
                    case '转租':
                        break;
                    case '挂靠':
                        break;
                    case '全部':
                        Controller.api.show_and_hide_table(table, 'show', ['orderdetails.subordinate_branch', 'payment', 'monthly', 'nperlist', 'end_money', 'tail_money', 'margin']);
                        break;
                }

            });

            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
                return "快速搜索：客户姓名,车牌号";
            };

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                searchFormVisible: true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),},
                        {field: 'orderdetails.file_coding', title: __('Orderdetails.file_coding')},
                        {field: 'username', title: __('Username')},
                        {field: 'id_card', title: __('Id_card')},
                        {field: 'phone', title: __('Phone')},
                        {field: 'orderdetails.licensenumber', title: __('Orderdetails.licensenumber')},
                        {field: 'orderdetails.frame_number', title: __('Orderdetails.frame_number')},
                        {field: 'orderdetails.engine_number', title: __('Orderdetails.engine_number')},
                        {
                            field: 'admin.nickname', title: __('所属销售'), formatter: function (value, row, index) {

                                return "<img src=" + Config.cdn + row.admin.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' + value;
                            }
                        },
                        {field: 'models_name', title: __('Models_name')},
                        // {
                        //     field: 'type',
                        //     title: __('Type'),
                        //     searchList: {
                        //         "mortgage": __('Type mortgage'),
                        //         "used_car_mortgage": __('Type used_car_mortgage'),
                        //         "car_rental": __('Type car_rental'),
                        //         "full_new_car": __('Type full_new_car'),
                        //         "full_used_car": __('Type full_used_car'),
                        //         "sublet": __('Type sublet'),
                        //         "affiliated": __('Type affiliated')
                        //     },
                        //     formatter: function (value, row, index) {

                        //         switch (value) {
                        //             case 'mortgage':
                        //                 return this.searchList.mortgage;
                        //             case 'used_car_mortgage':
                        //                 return this.searchList.used_car_mortgage;
                        //             case 'full_new_car':
                        //                 return this.searchList.full_new_car;
                        //             case 'full_used_car':
                        //                 return this.searchList.full_used_car;
                        //             case 'sublet':
                        //                 return this.searchList.sublet;
                        //             case 'affiliated':
                        //                 return this.searchList.affiliated;
                        //             case 'car_rental':
                        //                 return this.searchList.car_rental;
                        //         }
                        //     }
                        // },
                        {
                            field: 'orderdetails.is_it_illegal', title: __('违章状态'), formatter: function (value, row, index) {
                                console.log(row);
                                if(value == 'no_queries'){
                                    return '-';
                                }

                                let color = '';
                                let content = '';

                                switch (value) {
                                    case 'no_violation':
                                        color = 'success';
                                        content = '无违章';
                                        break;
                                    case 'violation_of_regulations':
                                        color = 'danger';
                                        content = '有违章';
                                        break;
                                    case 'query_failed':
                                        color = 'primary';
                                        content = '查询违章失败';
                                        break;
                                }

                                if(value!='query_failed'){
                                    return  '<span class=\'label label-'+color+'\' style=\'cursor: pointer\'>'+content+'</span>' ;
                                }

                                return  '<span class=\'label label-'+color+'\' style=\'cursor: pointer\'>'+content+'</span><span class="text-danger" style="font-size: smaller;display: block;margin-top: 5px">'+row.orderdetails.reson_query_fail+'</span>' ;


                            },
                            searchList: {
                                "no_violation": __('没有违章'),
                                "violation_of_regulations": __('有违章'),
                                "no_queries": __('未查询违章'),
                                "query_failed": __('违章查询失败')
                            },
                        },
                        {
                            field: 'orderdetails.total_deduction',
                            title: __('总扣分'),
                            operate: 'BETWEEN',
                            formatter: Controller.api.formatter.fen
                        },
                        {
                            field: 'orderdetails.update_violation_time',
                            title: __('最后查询违章时间'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime,
                            datetimeFormat: "YYYY-MM-DD"
                        },
                        {
                            field: 'orderdetails.annual_inspection_time',
                            title: __('年检截至日期'),
                            operate: false,
                            addclass: 'datetimerange',
                            formatter: Controller.api.formatter.datetime,
                            datetimeFormat: "YYYY-MM-DD"
                        },
                        {
                            field: 'orderdetails.traffic_force_insurance_time',
                            title: __('保险截至日期'),
                            operate: false,
                            addclass: 'datetimerange',
                            formatter: Controller.api.formatter.datetime,
                            datetimeFormat: "YYYY-MM-DD"
                        },
                        // {
                        //     field: 'orderdetails.business_insurance_time',
                        //     title: __('商业险截至日期'),
                        //     operate: false,
                        //     addclass: 'datetimerange',
                        //     formatter: Controller.api.formatter.datetime,
                        //     datetimeFormat: "YYYY-MM-DD"
                        // },
                        {
                            field: 'orderdetails.annual_inspection_status',
                            title: '年检状态',
                            searchList: {
                                "normal": __('正常'),
                                "soon": __('即将'),
                                "overdue": __('过期'),
                                "no_queries": __('暂未查询')
                            },
                            visible: false
                        },
                        {
                            field: 'orderdetails.traffic_force_insurance_status',
                            title: '保险状态',
                            searchList: {
                                "normal": __('正常'),
                                "soon": __('即将'),
                                "overdue": __('过期'),
                                "no_queries": __('暂未查询')
                            },
                            visible: false
                        },
                        // {
                        //     field: 'orderdetails.business_insurance_status',
                        //     title: '商业险状态',
                        //     searchList: {
                        //         "normal": __('正常'),
                        //         "soon": __('即将'),
                        //         "overdue": __('过期'),
                        //         "no_queries": __('暂未查询')
                        //     },
                        //     visible: false
                        // },
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operates',
                            title: __('详情'),
                            table: table,
                            // events: Controller.api.events.operate,
                            operate: false,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'customer_information',
                                    icon: 'fa fa-eye',
                                    title: __('查看客户详细资料'),
                                    text: '查看客户详细资料',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-primary btn-dialog btn-customer_information',
                                    url: 'vehicle/vehiclemanagement/customer_information',
                                    // visible: function (row) {
                                    // }
                                },

                            ]
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Controller.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                // {
                                //     name: 'view_information',
                                //     icon: 'fa fa-eye',
                                //     title: __('查看提车资料'),
                                //     text: '查看提车资料',
                                //     extend: 'data-toggle="tooltip"',
                                //     classname: 'btn btn-xs btn-primary btn-view_information',
                                //     visible: function (row) {
                                //         return row.lift_car_status == 'no' ? true : false;
                                //     }
                                // },
                                {
                                    name: 'edits',
                                    icon: 'fa fa-pencil',
                                    title: __('提车'),
                                    text: '确认提车',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-info btn-lift-car',
                                    visible: function (row) {
                                        return row.lift_car_status == 'no' ? true : false;
                                    }
                                },
                                // {
                                //     name: 'edits',
                                //     icon: 'fa fa-check',
                                //     title: __('已提车'),
                                //     text: '已提车',
                                //     extend: 'data-toggle="tooltip"',
                                //     classname: 'text-info',
                                //     visible: function (row) {
                                //         return row.lift_car_status == 'yes' ? true : false;
                                //     }
                                // },
                                {
                                    name: 'modifying_data',
                                    icon: 'fa fa-pencil',
                                    title: __('修改资料'),
                                    text: '修改资料',
                                    extend: 'data-toggle="tooltip"',
                                    dropdown: '更多',
                                    classname: 'btn btn-xs btn-modifying_data',
                                    visible: function (row) {
                                        return row.lift_car_status == 'yes' ? true : false;
                                    }
                                },

                                {
                                    name: 'search',
                                    icon: 'fa fa-search',
                                    title: __('查询违章'),
                                    text: '查询违章',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-danger btn-search',
                                    visible: function (row) {
                                        return row.lift_car_status == 'yes' && row.orderdetails.licensenumber && row.orderdetails.frame_number && row.orderdetails.engine_number ? true : false;
                                    }
                                },
                                {
                                    name: 'violation_details',
                                    icon: 'fa fa-eye',
                                    title: __('查看未处理违章详情'),
                                    text: '查看违章详情',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-primary btn-violation_details',
                                    visible: function (row) {
                                        return row.orderdetails && row.orderdetails.is_it_illegal == 'violation_of_regulations' ? true : false;
                                    }
                                },
                                {
                                    name: 'accredit',
                                    icon: 'fa fa-eye',
                                    title: __('小程序授权'),
                                    text: '小程序授权',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-success btn-accredit',
                                    visible: function (row) {
                                        return false;
                                        return !row.user_id ? true : false;
                                    }

                                },
                                {
                                    name: '',
                                    icon: 'fa fa-check',
                                    title: __('已授权'),
                                    text: '已授权',
                                    extend: 'data-toggle="tooltip"',
                                    dropdown: '更多',
                                    classname: 'text-info',
                                    visible: function (row) {
                                        return row.user_id ? true : false;
                                    }

                                },
                                {
                                    name: 'wechat',
                                    icon: 'fa fa-eye',
                                    title: __('微信公众号授权'),
                                    text: '微信公众号授权',
                                    extend: 'data-toggle="tooltip"',
                                    dropdown: '更多',
                                    classname: 'btn btn-xs btn-wechat',
                                    visible:function (row) {
                                        return !row.wx_public_user_id?true:false;
                                    }

                                },
                                {
                                    name: '',
                                    icon: 'fa fa-send',
                                    title: __('公众号推送违章信息'),
                                    text: '公众号推送违章信息',
                                    extend: 'data-toggle="tooltip"',
                                    dropdown: '更多',
                                    classname: 'btn btn-xs btn-push_violation',
                                    visible: function (row) {
                                        return row.orderdetails && row.orderdetails.is_it_illegal == 'violation_of_regulations' ? true : false;
                                    }
                                },

                            ]
                        }
                    ]
                ]
            });

            table.on('load-success.bs.table', function (e, data) {
                $(".btn-customer_information").data("area", ["95%", "95%"]);
                if (data.else) {
                      $('#peccancy').text(data.else.statistics_total_violation);
                      $('#year_inspect').text(data.else.soon_year);
                      $('#year_overdue').text(data.else.year_overdue);
                      $('#strong').text(data.else.soon_traffic);
                      $('#strong_overdue').text(data.else.traffic_overdue);
                      $('#business').text(data.else.soon_business);
                      $('#business_overdue').text(data.else.business_overdue);
                }
            });

            /**
             * 批量查询违章
             */
            $('.btn-peccancy').on("click", function () {
                var ids = [];
                var tableRow = Controller.api.selectIdsRow(table);//获取选中的行数据
                var flag = -1;
                var page = table.bootstrapTable('getData');

                var closeLay = Layer.confirm("请选择要查询的客户数据", {
                    title: '查询数据',
                    btn: ["选中项(" + tableRow.length + "条)", "本页(" + page.length + "条)"],
                    success: function (layero, index) {
                        $(".layui-layer-btn a", layero).addClass("layui-layer-btn0");
                    }
                    ,
                    //选中项
                    yes: function (index, layero) {

                        if (tableRow.length < 1) {
                            Layer.alert('数据不能为空!', {icon: 5});
                            return false;
                        }
                        ids = [];
                        for (var i in tableRow) {
                            if (!tableRow[i]['orderdetails']['licensenumber'] || tableRow[i]['orderdetails']['licensenumber'] == '') {
                                layer.open({
                                    type: 0,
                                    content: '选中行中，客户姓名为<span class="text-danger">' + tableRow[i]['username'] + '</span>的用户没有填写车牌号，请添加后查询' //这里content是一个普通的String
                                });
                                return;
                            }

                            if (!tableRow[i]['orderdetails']['engine_number'] || tableRow[i]['orderdetails']['engine_number'] == '') {
                                layer.open({
                                    type: 0,
                                    content: '选中行中，客户姓名为<span class="text-danger">' + tableRow[i]['username'] + '</span>的用户没有填写发动机号，请添加后查询' //这里content是一个普通的String
                                });
                                return;
                            }

                            if (!tableRow[i]['orderdetails']['frame_number'] || tableRow[i]['orderdetails']['frame_number'] == '') {
                                layer.open({
                                    type: 0,
                                    content: '选中行中，客户姓名为<span class="text-danger">' + tableRow[i]['username'] + '</span>的用户没有填写车架号，请添加后查询' //这里content是一个普通的String
                                });
                                return;
                            }
                            ids.push({
                                hphm: Controller.api.trim(tableRow[i]['orderdetails']['licensenumber']).substr(0, 2),
                                hphms: Controller.api.trim(tableRow[i]['orderdetails']['licensenumber']),
                                engineno: Controller.api.trim(tableRow[i]['orderdetails']['engine_number']),
                                classno: Controller.api.trim(tableRow[i]['orderdetails']['frame_number']),
                                order_id: tableRow[i]['id'],
                                username: tableRow[i]['username'],
                            });
                        }


                        Fast.api.ajax({
                            url: 'vehicle/vehiclemanagement/sendMessagePerson',
                            data: {ids}

                        }, function (data, ret) {
                            // var arrs = [
                            //     {
                            //         username: '企鹅啊',
                            //         license_plate_number: '川A56554',
                            //         status: 'error',
                            //         msg: '车辆信息错误',
                            //         is_it_illegal: '-',
                            //         total_deduction: '-',
                            //         total_fine: '-'
                            //     },
                            //     {
                            //         username: '的方式',
                            //         license_plate_number: '川A56554',
                            //         status: 'error',
                            //         msg: '车辆信息错误',
                            //         is_it_illegal: '-',
                            //         total_deduction: '-',
                            //         total_fine: '-'
                            //     },
                            //     {
                            //         username: '的法国队',
                            //         license_plate_number: '川A56554',
                            //         status: 'success',
                            //         msg: '-',
                            //         is_it_illegal: '有',
                            //         total_deduction: '3',
                            //         total_fine: '300'
                            //     },
                            // ];
                            Controller.api.layer_violation(data);

                            Layer.close(closeLay);
                            table.bootstrapTable('refresh');
                        });
                    }
                    ,
                    //本页
                    btn2: function (index, layero) {
                        ids = [];
                        for (var i in page) {

                            if (!page[i]['orderdetails']['licensenumber'] || page[i]['orderdetails']['licensenumber'] == '') {
                                layer.open({
                                    type: 0,
                                    content: '本页中，客户姓名为<span class="text-danger">' + page[i]['username'] + '</span>的用户没有填写车牌号，请添加后查询' //这里content是一个普通的String
                                });
                                return;
                            }

                            if (!page[i]['orderdetails']['engine_number'] || page[i]['orderdetails']['engine_number'] == '') {
                                // layer.msg('本页中，客户姓名为<span class="text-danger">'+page[i]['username']+'</span>的用户没有填写发动机号，请添加后查询');
                                layer.open({
                                    type: 0,
                                    content: '本页中，客户姓名为<span class="text-danger">' + page[i]['username'] + '</span>的用户没有填写发动机号，请添加后查询' //这里content是一个普通的String
                                });
                                return;
                            }

                            if (!page[i]['orderdetails']['frame_number'] || page[i]['orderdetails']['frame_number'] == '') {
                                layer.open({
                                    type: 0,
                                    content: '本页中，客户姓名为<span class="text-danger">' + page[i]['username'] + '</span>的用户没有填写车架号，请添加后查询' //这里content是一个普通的String
                                });
                                return;
                            }


                            ids.push({
                                hphm: Controller.api.trim(page[i]['orderdetails']['licensenumber']).substr(0, 2),
                                hphms: Controller.api.trim(page[i]['orderdetails']['licensenumber']),
                                engineno: Controller.api.trim(page[i]['orderdetails']['engine_number']),
                                classno: Controller.api.trim(page[i]['orderdetails']['frame_number']),
                                order_id: page[i]['id'],
                                username: page[i]['username']
                            });
                        }

                        Fast.api.ajax({
                            url: 'vehicle/vehiclemanagement/sendMessagePerson',
                            data: {ids}
                        }, function (data, ret) {

                            Controller.api.layer_violation(data);

                            Layer.close(closeLay);

                            table.bootstrapTable('refresh');
                        });
                    }


                });
            });



            // /**
            //  * 公众号推送违章信息
            //  */
            // $('.btn-sendoneviolation').on("click", function () {

            //     Fast.api.ajax({
            //         url: 'vehicle/vehiclemanagement/sendoneviolation',
            //     }, function (data, ret) {

            //         table.bootstrapTable('refresh');
                    
            //     });
           
            // });

            /**
             * 公众号推送违章信息
             */
            $('.btn-violation').on("click", function () {
                $(".btn-violation").data("area", ["80%", "80%"]);
                var url = 'vehicle/vehiclemanagement/canviolation';
                Fast.api.open(
                    Table.api.replaceurl(url, table), __('可以推送违章信息的客户信息展示'), $(this).data() || {});
           
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        //可以推送违章信息的客户展示
        canviolation: function () {

            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");
            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function () {
            };
            // 初始化表格
            table.bootstrapTable({
                url: 'vehicle/vehiclemanagement/canviolation',
                pk: 'id',
                sortName: 'id',
                toolbar: '#toolbar',
                // searchFormVisible: true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),},
                        {field: 'orderdetails.file_coding', title: __('Orderdetails.file_coding')},
                        {field: 'username', title: __('Username')},
                        {field: 'id_card', title: __('Id_card')},
                        {field: 'phone', title: __('Phone')},
                        {field: 'orderdetails.licensenumber', title: __('Orderdetails.licensenumber')},
                        {field: 'orderdetails.frame_number', title: __('Orderdetails.frame_number')},
                        {field: 'orderdetails.engine_number', title: __('Orderdetails.engine_number')},
                        {
                            field: 'admin.nickname', title: __('所属销售'), formatter: function (value, row, index) {

                                return "<img src=" + Config.cdn + row.admin.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' + value;
                            }
                        },
                        {field: 'models_name', title: __('Models_name')},
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
                            formatter: function (value, row, index) {

                                switch (value) {
                                    case 'mortgage':
                                        return this.searchList.mortgage;
                                    case 'used_car_mortgage':
                                        return this.searchList.used_car_mortgage;
                                    case 'full_new_car':
                                        return this.searchList.full_new_car;
                                    case 'full_used_car':
                                        return this.searchList.full_used_car;
                                    case 'sublet':
                                        return this.searchList.sublet;
                                    case 'affiliated':
                                        return this.searchList.affiliated;
                                    case 'car_rental':
                                        return this.searchList.car_rental;
                                }
                            }
                        },
                        {
                            field: 'orderdetails.is_it_illegal', title: __('违章状态'), formatter: function (value, row, index) {
                                console.log(row);
                                if(value == 'no_queries'){
                                    return '-';
                                }

                                let color = '';
                                let content = '';

                                switch (value) {
                                    case 'no_violation':
                                        color = 'success';
                                        content = '无违章';
                                        break;
                                    case 'violation_of_regulations':
                                        color = 'danger';
                                        content = '有违章';
                                        break;
                                    case 'query_failed':
                                        color = 'primary';
                                        content = '查询违章失败';
                                        break;
                                }

                                if(value!='query_failed'){
                                    return  '<span class=\'label label-'+color+'\' style=\'cursor: pointer\'>'+content+'</span>' ;
                                }

                                return  '<span class=\'label label-'+color+'\' style=\'cursor: pointer\'>'+content+'</span><span class="text-danger" style="font-size: smaller;display: block;margin-top: 5px">'+row.orderdetails.reson_query_fail+'</span>' ;


                            }
                        },
                        {
                            field: 'orderdetails.update_violation_time',
                            title: __('最后查询违章时间'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime,
                            datetimeFormat: "YYYY-MM-DD"
                        },
                        // {
                        //     field: 'orderdetails.annual_inspection_time',
                        //     title: __('年检截至日期'),
                        //     operate: 'RANGE',
                        //     addclass: 'datetimerange',
                        //     formatter: Controller.api.formatter.datetime,
                        //     datetimeFormat: "YYYY-MM-DD"
                        // },
                        // {
                        //     field: 'orderdetails.traffic_force_insurance_time',
                        //     title: __('交强险截至日期'),
                        //     operate: 'RANGE',
                        //     addclass: 'datetimerange',
                        //     formatter: Controller.api.formatter.datetime,
                        //     datetimeFormat: "YYYY-MM-DD"
                        // },
                        // {
                        //     field: 'orderdetails.business_insurance_time',
                        //     title: __('商业险截至日期'),
                        //     operate: 'RANGE',
                        //     addclass: 'datetimerange',
                        //     formatter: Controller.api.formatter.datetime,
                        //     datetimeFormat: "YYYY-MM-DD"
                        // },
                       
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);


            /**
             * 公众号推送违章信息
             */
            $('.btn-pushviolation').on("click", function () {
                var ids = Table.api.selectedids(table);

                Fast.api.ajax({
                    url: 'vehicle/vehiclemanagement/sendviolation?ids=' + ids,
                }, function (data, ret) {

                    table.bootstrapTable('refresh');
                    
                });
           
            });


        },
        add: function () {

            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.show_and_hide();

            Controller.api.bindevent();
        },
        modifying_data: function () {
            Controller.api.show_and_hide();
            Controller.api.bindevent();
        },
        view_information: function () {
            Controller.api.bindevent();
        },

        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
                datetime: function (value, row, index) {
                    var datetimeFormat = typeof this.datetimeFormat === 'undefined' ? 'YYYY-MM-DD HH:mm:ss' : this.datetimeFormat;
                    if (isNaN(value)) {
                        return value ? Moment(value).format(datetimeFormat) : __('None');
                    } else {
                        if (!value) {
                            return value;
                        }
                        var status = '';
                        var text = '';
                        switch (this.field) {
                            case 'orderdetails.annual_inspection_time':
                                status = row.orderdetails.annual_inspection_status;
                                text = '年检';
                                break;
                            case 'orderdetails.traffic_force_insurance_time':
                                status = row.orderdetails.traffic_force_insurance_status;
                                text = '保险';
                                break;
                            // case 'orderdetails.business_insurance_time':
                            //     status = row.orderdetails.business_insurance_status;
                            //     text = '商业险';
                            //     break;
                        }
                        let sign = '';
                        let content = '';
                        if (status == 'normal') {
                            sign = 'success';
                            content = '正常';
                        } else if (status == 'soon') {
                            sign = 'warning';
                            content = text + '即将过期';
                        } else if (status == 'overdue') {
                            sign = 'danger';
                            content = text + '已过期';
                        }

                        return status != 'no_queries' ? Moment(parseInt(value) * 1000).format(datetimeFormat) + ' ' + "<span class='label label-" + sign + "' style='cursor: pointer'>" + content + "</span>" : value;
                    }
                },
                /**
                 * 0分标记
                 * @param value
                 * @param row
                 * @param index
                 * @returns {string}
                 */
                fen: function (value, row, index) {

                    if (value) {
                        return "<span class='text-danger'>" + value + "</span>";
                    }
                    return value == null ? '-' : "<span class='text-success'><strong>" + value + "</strong></span>";

                },
            },
            // 单元格元素事件
            events: {
                operate: {
                    'click .btn-lift-car': function (e, value, row, index) {
                        $(".btn-lift-car").data("area", ["80%", "80%"]);
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'vehicle/vehiclemanagement/edit';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('提车'), $(this).data() || {});
                    },
                    'click .btn-modifying_data': function (e, value, row, index) {
                        $(".btn-modifying_data").data("area", ["95%", "95%"]);
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'vehicle/vehiclemanagement/modifying_data';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('修改资料'), $(this).data() || {});
                    },
                    'click .btn-view_information': function (e, value, row, index) {
                        $(".btn-view_information").data("area", ["80%", "80%"]);
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'vehicle/vehiclemanagement/view_information';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('查看提车资料'), $(this).data() || {});
                    },
                    'click .btn-violation_details': function (e, value, row, index) {
                        $(".btn-violation_details").data("area", ["80%", "80%"]);
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'vehicle/vehiclemanagement/violation_details';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('查看未处理违章详情'), $(this).data() || {});
                    },
                    /**
                     * 查询违章
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-search': function (e, value, row, index) {
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

                        Layer.confirm('是否查询违章?', {icon: 3,offset: [top, left], shadeClose: true ,title: '提示'}, function (index) {

                            if (!row.orderdetails.licensenumber || row.orderdetails.licensenumber == '') {
                                Layer.msg('请补全车牌号');
                                return;
                            }

                            if (!row.orderdetails.engine_number || row.orderdetails.engine_number == '') {
                                Layer.msg('请补全发动机号');
                                return;
                            }

                            if (!row.orderdetails.frame_number || row.orderdetails.frame_number == '') {
                                Layer.msg('请补全车架号');
                                return;
                            }


                            var table = $(that).closest('table');
                            var ids = [{
                                hphm: Controller.api.trim(row.orderdetails.licensenumber).substr(0, 2),
                                hphms: Controller.api.trim(row.orderdetails.licensenumber),
                                engineno: Controller.api.trim(row.orderdetails.engine_number),
                                classno: Controller.api.trim(row.orderdetails.frame_number),
                                order_id: row.id,
                                username: row.username
                            }];
                            var id = row.id;

                            Fast.api.ajax({
                                url: 'vehicle/vehiclemanagement/sendMessagePerson',
                                data: {ids}

                            }, function (data, ret) {

                                Controller.api.layer_violation(data,id);
                                Layer.close(index);
                                table.bootstrapTable('refresh');


                            });


                        });


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
                    },
                    /**
                     * 小程序授权
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-accredit': function (e, value, row, index) {

                        Fast.api.ajax({
                            url: 'vehicle/vehiclemanagement/setqrcode',
                            data: {order_id: JSON.stringify(row.id), username: JSON.stringify(row.username)},
                        }, function (data, ret) {

                            // console.log('https://jyzj.junyiqiche.com' + data);
                            layer.open({
                                title: '小程序授权', //页面标题
                                type: 2, 
                                area: ['180px', '250px'],  //弹出层页面比例
                                content: ['https://jyzj.junyiqiche.com' + data, 'no'] //这里content是一个URL，如果你不想让iframe出现滚动条，你还可以content: ['http://sentsin.com', 'no']
                            });    

                            // if(goeasy){
                                //goeasy关闭弹框
                                // goeasy.subscribe({
                                //     channel: 'accredit',
                                //     onMessage: function(message){
                                //
                                //         $(".btn-refresh").trigger("click");
                                //     }
                                // });
                            // }


                        }, function (data, ret) {

                        });


                    },
                    /**
                     * 微信公众号授权
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-wechat': function (e, value, row, index) {

                        Fast.api.ajax({
                            url: 'vehicle/vehiclemanagement/public_qr_code',
                            data: {order_id: JSON.stringify(row.id), username: JSON.stringify(row.username)},
                        }, function (data, ret) {

                            // console.log('https://jyzj.junyiqiche.com' + data); 
                            layer.open({
                                title: '公众号授权', //页面标题
                                type: 2, 
                                area: ['280px', '330px'],  //弹出层页面比例
                                content: ['https://jyzj.junyiqiche.com' + data, 'no'] //这里content是一个URL，如果你不想让iframe出现滚动条，你还可以content: ['http://sentsin.com', 'no']
                            });    
                            
                            //goeasy关闭弹框
                            // goeasy.subscribe({
                            //     channel: 'accredit',
                            //     onMessage: function(message){
                            //
                            //         $(".btn-refresh").trigger("click");
                            //     }
                            // });

                        }, function (data, ret) {

                        });


                    },
                    /**
                     * 微信公众号推送违章信息
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-push_violation': function (e, value, row, index) {
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
                            __('是否确认进行违章推送?'),
                            { icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true },

                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');

                                Fast.api.ajax({

                                    url: 'vehicle/vehiclemanagement/sendoneviolation',
                                    data: {id: row[options.pk]}
 
                                }, function (data, ret) {

                                    Toastr.success('操作成功');
                                    Layer.close(index);
                                    table.bootstrapTable('refresh');
                                    return false;
                                }, function (data, ret) {
                                    //失败的回调
                                    Toastr.success(ret.msg);

                                    return false;
                                });
                            }
                        );
                    },



                }
            },
            layer_violation: function (data,id = '') {
                var html = '';
                html += '<h4 style="text-align: center;color: #FF0000">如需查看违章详情，请关闭当前页面点击右侧的【查看违章详情】按钮</h4>';
                html += '<h3 style="text-align: center">总成功数：' + data['success_num'] + '，总失败数：' + data['error_num'] + '</h3>';

                html += '<table class="table table-bordered table-striped table-hover">\n' +
                    '    <thead>\n' +
                    '\n' +
                    '    <tr>\n' +
                    '\n' +
                    '        <th style="text-align: center;vertical-align: middle !important;">客户姓名</th>\n' +
                    '        <th style="text-align: center;vertical-align: middle !important;">车牌号</th>\n' +
                    '        <th style="text-align: center;vertical-align: middle !important;">查询是否成功</th>\n' +
                    '        <th style="text-align: center;vertical-align: middle !important;">原因</th>\n' +
                    '        <th style="text-align: center;vertical-align: middle !important;">是否有违章</th>\n' +
                    '        <th style="text-align: center;vertical-align: middle !important;">总扣分</th>\n' +
                    '        <th style="text-align: center;vertical-align: middle !important;">总罚款</th>\n' +
                    '\n' +
                    '\n' +
                    '        <!--<th>邮编</th>-->\n' +
                    '    </tr>\n' +
                    '    </thead><tbody>';

                for (let i of data['query_record']) {
                    html += '<tr>' +
                        '<td style="text-align: center;vertical-align: middle !important;">' + i.username + '</td>' +
                        '<td style="text-align: center;vertical-align: middle !important;">' + i.license_plate_number + '</td>';
                    if (i.status == 'success') {
                        html += '<td style="text-align: center;vertical-align: middle !important;color: green">成功</td>';
                    } else {
                        html += '<td style="text-align: center;vertical-align: middle !important;color: #FF0000">失败</td>';
                    }
                    let color = i.is_it_illegal == '有' ? 'red' : 'green';
                    html += '<td style="text-align: center;vertical-align: middle !important;color: #FF0000">' + i.msg + '</td>';
                    html += '<td style="text-align: center;vertical-align: middle !important;color: ' + color + '">' + i.is_it_illegal + '</td>';
                    html += '<td style="text-align: center;vertical-align: middle !important">' + i.total_deduction + '</td>';
                    html += '<td style="text-align: center;vertical-align: middle !important">' + i.total_fine + '</td></tr>';

                }


                html += '</tbody></table>';
                html += '<div class="form-group layer-footer">';
                html += '<div style="text-align: center;vertical-align: middle !important;">'
                html += '<button  type="submit" class="btn btn-success btn-embossed btn-sendoneviolation">推送违章信息</button>';
                html += '<script>';
                html += '$(".btn-sendoneviolation").on("click", function () {\n' + 
                    'var confirm = layer.confirm(\n' + 
                    '    __("确定进行违章模板推送吗?"),\n' + 
                    '    {icon: 3, title: __("Warning"), shadeClose: true},\n' + 
                    '    function (index) {\n' + 
            
                    '        Fast.api.ajax({\n' + 
                    '            url: "vehicle/vehiclemanagement/sendoneviolation",\n' + 
                    '            data: {id: JSON.stringify(' + id + ')}\n' + 
                    '        }, function (data, ret) {\n' + 
                    '           parent.$("#toolbar .btn-refresh", parent.document).trigger("click")\n' + 
                    '           Layer.close(confirm);\n' + 
                    '           var index = parent.layer.getFrameIndex(window.name); \n' + 
                    '           parent.layer.close(index);\n' + 
                    '           Toastr.success(ret.msg);\n' + 
                    '           return false;\n' + 
                    '        }, function (data, ret) {\n' + 
                                //失败的回调
                    '           return false;\n' + 
                    '        });\n' + 
            
                    '   }\n' + 
                    ');\n' + 
               
                '});';
                html += '</script>';
    
                layer.open({
                    type: 1,
                    skin: 'layui-layer-demo', 
                    closeBtn: 1,
                    area: ['1000px', '750px'],
                    title: ['查询违章结果', 'font-size:18px;text-align:center'],
                    maxmin: true,
                    content: html
                });
            },
            show_and_hide: function () {
                let type = $('input[type=hidden]').val();
                let mortgage = $('#c-is_mortgage').val();

                type == 'full_new_car' || type == 'full_used_car' ? $('.full').show() : $('.full').hide();
                mortgage == '是' ? $('#mortgage-people').show() : $('#mortgage-people').hide();

                $('#c-is_mortgage').on('change', function () {
                    $(this).val() == '是' ? $('#mortgage-people').show() : $('#mortgage-people').hide();
                });
            },
            /**
             * 得到选中行信息
             * @param table
             * @returns {*}
             */
            selectIdsRow: function (table) {
                var options = table.bootstrapTable('getOptions');
                if (options.templateView) {
                    return $.map($("input[data-id][name='checkbox']:checked"), function (dom) {
                        return $(dom)
                    });
                } else {
                    return $.map(table.bootstrapTable('getSelections'), function (row) {
                        return row;
                    });
                }
            },
            //去左右空格;
            trim: function (s) {
                return s.replace(/(^\s*)|(\s*$)/g, "");
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
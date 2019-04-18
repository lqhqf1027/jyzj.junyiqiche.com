define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

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
                return "快速搜索：客户姓名";
            };

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),},
                        {
                            field: 'username', title: __('Username'), formatter: function (value, row, index) {
                                // if(!row.orderdetails.is_it_illegal){
                                //     return value;
                                // }
                                return row.orderdetails.is_it_illegal == 'no_queries' ? value : row.orderdetails.is_it_illegal == 'violation_of_regulations' ? value + ' <span class=\'label label-danger\' style=\'cursor: pointer\'>有违章</span>' : value + ' <span class=\'label label-success\' style=\'cursor: pointer\'>无违章</span>';
                            }
                        },
                        {
                            field: 'admin.nickname', title: __('所属销售'), formatter: function (value, row, index) {

                                return "<img src=" +Config.cdn+row.admin.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' + value;
                            }
                        },
                        {field: 'phone', title: __('Phone')},
                        {field: 'id_card', title: __('Id_card')},
                        {field: 'models_name', title: __('Models_name')},
                        {field: 'payment', title: __('Payment')},
                        {field: 'monthly', title: __('月供'), operate: 'BETWEEN'},
                        {field: 'nperlist', title: __('期数')},
                        {field: 'end_money', title: __('End_money')},
                        {field: 'tail_money', title: __('Tail_money')},
                        {field: 'margin', title: __('Margin')},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
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
                        {field: 'orderdetails.file_coding', title: __('Orderdetails.file_coding')},
                        {field: 'orderdetails.signdate', title: __('Orderdetails.signdate')},
                        {field: 'orderdetails.total_contract', title: __('Orderdetails.total_contract')},
                        {field: 'orderdetails.hostdate', title: __('Orderdetails.hostdate')},
                        {field: 'orderdetails.licensenumber', title: __('Orderdetails.licensenumber')},
                        {field: 'orderdetails.frame_number', title: __('Orderdetails.frame_number')},
                        {field: 'orderdetails.engine_number', title: __('Orderdetails.engine_number')},
                        {field: 'orderdetails.is_mortgage', title: __('Orderdetails.is_mortgage')},
                        {field: 'orderdetails.mortgage_people', title: __('Orderdetails.mortgage_people')},
                        {field: 'orderdetails.ticketdate', title: __('Orderdetails.ticketdate')},
                        {field: 'orderdetails.supplier', title: __('Orderdetails.supplier')},
                        {field: 'orderdetails.tax_amount', title: __('Orderdetails.tax_amount')},
                        {field: 'orderdetails.no_tax_amount', title: __('Orderdetails.no_tax_amount')},
                        {field: 'orderdetails.pay_taxesdate', title: __('Orderdetails.pay_taxesdate')},
                        {field: 'orderdetails.purchase_of_taxes', title: __('Orderdetails.purchase_of_taxes')},
                        {field: 'orderdetails.house_fee', title: __('Orderdetails.house_fee')},
                        {field: 'orderdetails.luqiao_fee', title: __('Orderdetails.luqiao_fee')},
                        {field: 'orderdetails.insurance_buydate', title: __('Orderdetails.insurance_buydate')},
                        {field: 'orderdetails.insurance_policy', title: __('Orderdetails.insurance_policy')},
                        {field: 'orderdetails.insurance', title: __('Orderdetails.insurance')},
                        {field: 'orderdetails.car_boat_tax', title: __('Orderdetails.car_boat_tax')},
                        {
                            field: 'orderdetails.commercial_insurance_policy',
                            title: __('Orderdetails.commercial_insurance_policy')
                        },
                        {field: 'orderdetails.business_risks', title: __('Orderdetails.business_risks')},
                        {field: 'orderdetails.subordinate_branch', title: __('Orderdetails.subordinate_branch')},
                        {field: 'orderdetails.transfer_time', title: __('Orderdetails.transfer_time')},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Controller.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'view_information',
                                    icon: 'fa fa-eye',
                                    title: __('查看提车资料'),
                                    text: '查看提车资料',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-primary btn-view_information',
                                    visible: function (row) {
                                        return row.lift_car_status == 'no' ? true : false;
                                    }
                                },
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
                                {
                                    name: 'edits',
                                    icon: 'fa fa-check',
                                    title: __('已提车'),
                                    text: '已提车',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'text-info',
                                    visible: function (row) {
                                        return row.lift_car_status == 'yes' ? true : false;
                                    }
                                },
                                {
                                    name: 'modifying_data',
                                    icon: 'fa fa-pencil',
                                    title: __('修改资料'),
                                    text: '修改资料',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-success btn-modifying_data',
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
                                }

                            ]
                        }
                    ]
                ]
            });

            table.on('load-success.bs.table', function (e, data) {

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
                            //         username:'企鹅啊',
                            //         license_plate_number:'川A56554',
                            //         status:'error',
                            //         msg:'违法禁令指示的'
                            //     },
                            //     {
                            //         username:'的方式',
                            //         license_plate_number:'川A56554',
                            //         status:'error',
                            //         msg:'违法禁令指示的'
                            //     },
                            //     {
                            //         username:'的法国队',
                            //         license_plate_number:'川A56554',
                            //         status:'success',
                            //         msg:''
                            //     },
                            // ]

                            var html = '';
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

                                html += '<td style="text-align: center;vertical-align: middle !important;color: #FF0000">' + i.msg + '</td>' +
                                    '</tr>';
                            }


                            html += '</tbody></table>';
                            layer.open({
                                type: 1,
                                area: ['800px', '600px'],
                                title: ['查询违章结果', 'font-size:18px;text-align:center'],
                                maxmin: true,
                                content: html
                            });
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
                            console.log(data);
                            Layer.close(closeLay);

                            table.bootstrapTable('refresh');
                        });
                    }


                });
            });


            // 为表格绑定事件
            Table.api.bindevent(table);
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
            // alert($('#p-mate_id_cardimages').find('.btn-trash').css('display', 'block'));
            Controller.api.bindevent();
        },


        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
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
                        // console.log(row);return;
                        Layer.confirm('是否查询违章?', {icon: 3, title: '提示'}, function (index) {


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

                            Fast.api.ajax({
                                url: 'vehicle/vehiclemanagement/sendMessagePerson',
                                data: {ids}

                            }, function (data, ret) {

                                Layer.close(index);
                                table.bootstrapTable('refresh');


                            });


                        });


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
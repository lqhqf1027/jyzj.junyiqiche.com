define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

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


        table: {
            /**
             * 新车信息登记
             */
            new_customer: function () {
                // 表格1
                var newCustomer = $("#newCustomer");

                newCustomer.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-editone").data("area", ["80%", "80%"]);
                    $(".btn-detail").data("area", ["95%", "95%"]);
                    $(".btn-edit").data("area", ["80%", "80%"]);
                });
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "快速搜索:车架号";};

                total(newCustomer,$('#total-login'));

                // 初始化表格
                newCustomer.bootstrapTable({
                    url: 'material/Newcarinfo/new_customer',
                    extend: {
                        edit_url: 'material/newcarinfo/edit',
                        del_url: 'material/mortgageregistration/del',
                        multi_url: 'material/mortgageregistration/multi',
                        table: 'mortgage_registration',
                    },
                    toolbar: '#toolbar1',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('ID'), operate: false},
                            {field: 'mortgageregistration.archival_coding', title: __('档案编码')},
                            {field: 'admin.nickname', title: __('部门-销售员'), operate: false,formatter:Controller.api.formatter.sales},
                            {
                                field: 'createtime',
                                title: __('签订日期'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime,
                                datetimeFormat:"YYYY-MM-DD",



                            },
                            {field: 'username', title: __('Username'), formatter: Controller.api.formatter.inspection},

                            // {
                            //     field: 'mortgageregistration.next_inspection',
                            //     title: __('年检截止日期'),
                            //     operate: 'RANGE',
                            //     addclass: 'datetimerange',
                            //     formatter: Table.api.formatter.datetime,
                            //     datetimeFormat:"YYYY-MM-DD",
                            // },

                            {field: 'id_card', title: __('身份证号')},
                            {field: 'phone', title: __('联系方式')},
                            {field: 'planacar.payment', title: __('首付'), operate: false},
                            {field: 'planacar.monthly', title: __('月供'), operate: false},
                            {field: 'planacar.nperlist', title: __('期数'), operate: false},
                            {field: 'mortgageregistration.end_money', title: __('末期租金'), operate: false},
                            {field: 'planacar.tail_section', title: __('尾款'), operate: false},
                            {field: 'planacar.margin', title: __('保证金'), operate: false},
                            {field: 'mortgageregistration.hostdate', title: __('上户日期'),},
                            {field: 'models.name', title: __('规格型号')},
                            {field: 'newinventory.licensenumber', title: __('车牌号')},
                            {field: 'newinventory.frame_number', title: __('车架号')},
                            {field: 'mortgageregistration.mortgage_people', title: __('抵押人')},
                            {
                                field: 'mortgageregistration.transfer',
                                title: __('是否过户'),
                                searchList: {"1": __('是'), "0": __('否')},
                                formatter: Controller.api.formatter.transfer
                            },
                            {
                                field: 'mortgageregistration.transferdate', title: __('过户日期'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',

                            },
                            {field: 'mortgageregistration.registry_remark', title: __('备注信息'), operate: false},
                            {
                                field: 'mortgageregistration.year_status',
                                title: __('年检是否过期'),
                                searchList: {"1": __('即将过期'), "2": __('已过期')},
                                visible: false,

                            },
                            {
                                field: 'operate', title: __('Operate'), table: newCustomer,
                                buttons: [

                                    {
                                        name: 'edit',
                                        icon: 'fa fa-pencil',
                                        text: __('Edit'),
                                        title: __('Edit'),
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-success btn-editone',
                                        // hidden:function (row) {
                                        //     // return false;
                                        // }
                                    },


                                ],
                                events: Controller.api.events.operate,
                                formatter: Controller.api.formatter.operate
                            }
                        ]
                    ]
                });
                // 为表格1绑定事件
                Table.api.bindevent(newCustomer);

                // 批量分配
                $(document).on("click", ".btn-selected", function () {
                    var ids = Table.api.selectedids(newCustomer);
                    var url = 'backoffice/custominfotabs/batch?ids=' + ids;

                    var options = {
                        shadeClose: false,
                        shade: [0.3, '#393D49'],
                        area: ['50%', '50%'],
                        callback: function (value) {

                        }
                    };
                    Fast.api.open(url, '批量分配', options)
                });


            },
            /**
             * 新车资料入库
             */
            registry_registration: function () {
                // 表格2
                var registryRegistration = $("#registryRegistration");
                registryRegistration.on('post-body.bs.table', function (e, settings, json, xhr) {
                    $(".btn-edittwo").data("area", ["50%", "80%"]);
                    $(".btn-edit").data("area", ["50%", "80%"]);
                });

                total(registryRegistration,$('#total-warehousing'));

                // 初始化表格
                registryRegistration.bootstrapTable({
                    url: 'material/Newcarinfo/data_warehousing',
                    extend: {
                        index_url: 'registry/Newcarinfo/index',
                        add_url: 'registry/registration/add',
                        edit_url: 'material/newcarinfo/warehousing',
                        del_url: 'registry/registration/del',
                        multi_url: 'registry/registration/multi',
                        table: 'registry_registration',
                    },
                    toolbar: '#toolbar2',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('ID')},
                            {field: 'mortgageregistration.archival_coding', title: __('档案编码')},
                            {field: 'username', title: __('Username')},
                            {field: 'financial_name', title: __('金融公司')},
                            {field: 'phone', title: __('电话')},
                            {field: 'newinventory.licensenumber', title: __('车牌号')},
                            {field: 'newinventory.frame_number', title: __('车架号')},
                            {field: 'newinventory.household', title: __('所属分公司')},
                            {field: 'admin.nickname', title: __('销售员'),formatter:Controller.api.formatter.sales},
                            {
                                field: 'registryregistration.id_card',
                                title: __('身份证复印件'),
                                formatter: Controller.api.formatter.judge,
                                operate: false
                            },
                            {
                                field: 'registryregistration.registered_residence',
                                title: __('户口复印件'),
                                formatter: Controller.api.formatter.judge,
                                operate: false
                            },
                            {
                                field: 'registryregistration.marry_and_divorceimages',
                                title: __('结婚证或者离婚证'),
                                formatter: Controller.api.formatter.judge,
                                operate: false
                            },
                            {
                                field: 'registryregistration.credit_reportimages',
                                title: __('征信报告'),
                                formatter: Controller.api.formatter.judge,
                                operate: false
                            },
                            {
                                field: 'registryregistration.halfyear_bank_flowimages',
                                title: __('半年银行流水'),
                                formatter: Controller.api.formatter.judge,
                                operate: false
                            },
                            {
                                field: 'registryregistration.detailed_list',
                                title: __('通话清单'),
                                formatter: Controller.api.formatter.judge,
                                operate: false
                            },
                            {
                                field: 'registryregistration.guarantee',
                                title: __('担保人'),
                                formatter: Controller.api.formatter.judge,
                                operate: false
                            },
                            {
                                field: 'registryregistration.residence_permitimages',
                                title: __('居住证/租房合同/房产证'),
                                formatter: Controller.api.formatter.judge,
                                operate: false
                            },
                            {
                                field: 'registryregistration.driving_license',
                                title: __('驾照'),
                                formatter: Controller.api.formatter.judge,
                                operate: false
                            },
                            {
                                field: 'registryregistration.company_contractimages',
                                title: __('公司合同'),
                                formatter: Controller.api.formatter.judge,
                                operate: false
                            },
                            {
                                field: 'registryregistration.car_keys',
                                title: __('钥匙'),
                                formatter: Controller.api.formatter.judge,
                                operate: false
                            },
                            {
                                field: 'registryregistration.lift_listimages',
                                title: __('提车单'),
                                formatter: Controller.api.formatter.judge,
                                operate: false
                            },
                            {
                                field: 'registryregistration.deposit',
                                title: __('定金协议'),
                                formatter: Controller.api.formatter.judge, operate: false
                            },
                            {
                                field: 'registryregistration.truth_management_protocolimages',
                                title: __('道路管理条例告知书'),
                                formatter: Controller.api.formatter.judge, operate: false
                            },
                            {
                                field: 'registryregistration.confidentiality_agreementimages',
                                title: __('保密协议'),
                                formatter: Controller.api.formatter.judge, operate: false
                            },
                            {
                                field: 'registryregistration.supplementary_contract_agreementimages',
                                title: __('合同补充协议/客户告知书'),
                                formatter: Controller.api.formatter.judge, operate: false
                            },
                            {
                                field: 'registryregistration.explain_situation',
                                title: __('情况说明'),
                                formatter: Controller.api.formatter.judge,
                                operate: false
                            },
                            {
                                field: 'registryregistration.tianfu_bank_cardimages',
                                title: __('天府银行卡附件'),
                                formatter: Controller.api.formatter.judge, operate: false
                            },
                            {
                                field: 'registryregistration.driving_licenseimages',
                                title: __('行驶证'),
                                formatter: Controller.api.formatter.judge,
                                operate: false
                            },
                            {
                                field: 'registryregistration.strong_insurance',
                                title: __('交强险'),
                                formatter: Controller.api.formatter.judge,
                                operate: false
                            },
                            {
                                field: 'registryregistration.tax_proofimages',
                                title: __('完税证明'),
                                formatter: Controller.api.formatter.judge,
                                operate: false
                            },
                            {
                                field: 'registryregistration.invoice_or_deduction_coupletimages',
                                title: __('发票或抵扣联'),
                                formatter: Controller.api.formatter.judge, operate: false
                            },
                            {
                                field: 'registryregistration.registration_certificateimages',
                                title: __('登记证书'),
                                formatter: Controller.api.formatter.judge, operate: false
                            },
                            {
                                field: 'registryregistration.commercial_insurance',
                                title: __('商业险'),
                                formatter: Controller.api.formatter.judge,
                                operate: false
                            },
                            //
                            {
                                field: 'registryregistration.tax',
                                title: __('购置税'),
                                formatter: Controller.api.formatter.judge,
                                operate: false
                            },
                            {
                                field: 'registryregistration.maximum_guarantee_contractimages',
                                title: __('最高保障合同'),
                                formatter: Controller.api.formatter.judge, operate: false
                            },
                            {field: 'registryregistration.information_remark', title: __('备注'), operate: false},
                            {
                                field: 'operate', title: __('Operate'), table: registryRegistration,
                                buttons: [
                                    {
                                        name: 'edit2',
                                        icon: 'fa fa-pencil',
                                        title: __('Edit'),

                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-success btn-edittwo',
                                    },

                                ],
                                events: Controller.api.events.operate,
                                formatter: Controller.api.formatter.operate
                            },
                        ]
                    ]
                });
                // 为表格2绑定事件
                Table.api.bindevent(registryRegistration);

                registryRegistration.on('load-success.bs.table', function (e, data) {
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

        warehousing: function () {
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
            events: {
                operate: {

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

                    'click .btn-edittwo': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = options.extend.edit_url;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
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
                sales:function (value, row, index) {
                    // console.log(row);

                    return value==null?value : "<img src=" + Config.cdn_url+row.admin.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' +row.admin.department+' - '+value;
                },
                /**
                 * 返回√和x
                 * @param value
                 * @returns {string}
                 */
                judge: function (value) {



                        var res = "";
                        var color = "";
                        if (value == "no") {
                            res = "<i class='fa fa-times'></i>";
                            color = "danger";
                        } else if(value == 'yes'){
                            res = "<i class='fa fa-check'></i>"
                            color = "success";
                        }else{
                            return '-' ;
                        }

                        //渲染状态
                        var html = '<span class="text-' + color + '"> ' + __(res) + '</span>';

                        return html;


                },

                /**
                 * 判断年检
                 * @param value
                 * @param row
                 * @param index
                 * @returns {*}
                 */
                inspection: function (value, row, index) {

                    var license_status = -1;
                    var status = -1;

                    var new_license = row.newinventory.licensenumber;

                    var license_arr = row.used_car;


                    for (var i in license_arr){
                        if(new_license == license_arr[i]){
                            license_status = -2;
                        }
                    }

                    if (row.mortgageregistration.year_range) {
                        var range = row.mortgageregistration.year_range;

                        var arr = range.split("|");

                        var soon_arr = arr[0];
                        var now_arr = arr[1];

                        soon_arr = soon_arr.split(';');
                        now_arr = now_arr.split(';');



                        var soon_first = soon_arr[0];
                        var soon_last = soon_arr[1];

                        var now = new Date(getNowFormatDate()).getTime();

 
                        soon_first = new Date(soon_first).getTime();
                        soon_last = new Date(soon_last).getTime();



                        var now_first = now_arr[0];
                        var now_last = now_arr[1];

                        now_first = new Date(now_first).getTime();
                        now_last = new Date(now_last).getTime();


                        if (now >= soon_first && now <= soon_last) {
 
                            status = 1;
                        } else if (now >= now_first && now <= now_last) {
                            status = 3;
                        } else if(now>now_last){
                            status = 2;
                        }else{
                            status = 0;
                        }
                        $.ajax({
                                url:'material/Newcarinfo/check_year',
                                dataType:"json",
                                type:"post",
                                data:{
                                    status: status,
                                    id:row.mortgage_registration_id
                                }, success:function (data) {

                                },error:function (type) {
                                }
                            });




                    }

                    switch (status){
                        case 0:
                            if(license_status == -1){
                                return value
                            }else{
                                return value +  "  " + "<span class='label label-info' style='cursor: pointer'>新车转卖</span>";
                            }

                        case 1:
                            if(license_status == -1){
                                return value +  "  " + "<span class='label label-warning' style='cursor: pointer'>即将年检</span>";
                            }else{
                                return value +  "  " + "<span class='label label-warning' style='cursor: pointer'>即将年检</span>" + "  " + "<span class='label label-info' style='cursor: pointer'>新车转卖</span>";
                            }
                        case 2:

                            if(license_status == -1){
                                return value +  "  " + "<span class='label label-danger' style='cursor: pointer'>年检已过期</span>";
                            }else{
                                return value +  "  " + "<span class='label label-danger' style='cursor: pointer'>年检已过期</span>" +"  "+ "<span class='label label-info' style='cursor: pointer'>新车转卖</span>";
                            }

                        default:
                            if(license_status == -1){
                                return value
                            }else{
                                return value +  "  " + "<span class='label label-info' style='cursor: pointer'>新车转卖</span>";
                            }
                    }



                    if (status==1 &&license_status == -2) {
                        return value + "<span class='label label-warning' style='cursor: pointer'>即将年检</span>";
                    } else if (status==2) {
                        return value + "<span class='label label-danger' style='cursor: pointer'>年检已过期</span>";
                    } else {
                        return value;
                    }




                },
                transfer: function (value, row, index) {
                    if (value == 1) {
                        return "已过户"
                    } else if (value == 0) {
                        return "未过户"
                    }
                }
            }
        }

    };

    /**
     * 表格加载完成统计条数
     * @param table
     * @param obj
     */
    function total(table,obj) {
        table.on('load-success.bs.table', function (e, data) {
            obj.text(data.total);

        })
    }

    function getNowFormatDate() {
        var date = new Date();
        var seperator1 = "-";
        var year = date.getFullYear();
        var month = date.getMonth() + 1;
        var strDate = date.getDate();
        if (month >= 1 && month <= 9) {
            month = "0" + month;
        }
        if (strDate >= 0 && strDate <= 9) {
            strDate = "0" + strDate;
        }
        var currentdate = year + seperator1 + month + seperator1 + strDate;
        return currentdate;
    }
    return Controller;


});
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    // var goeasy = new GoEasy({
    //     appkey: 'BC-04084660ffb34fd692a9bd1a40d7b6c2'
    // });

    /**
     * 租车车辆信息
     * @type {{index: index, carsingle: carsingle, add: add, edit: edit, api: {bindevent: bindevent, events: {operate: {"click .btn-editone": click .btn-editone, "click .btn-delone": click .btn-delone, "click .btn-rentalrequest": click .btn-rentalrequest, "click .btn-carsingle": click .btn-carsingle, "click .btn-takecar": click .btn-takecar}}, formatter: {operate: (function(*=, *=, *=): *), toggle: toggle}}}}
     */
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'rentcar/vehicleinformation/index',
                    add_url: 'rentcar/vehicleinformation/add',
                    edit_url: 'rentcar/vehicleinformation/edit',
                    del_url: 'rentcar/vehicleinformation/del',
                    multi_url: 'rentcar/vehicleinformation/multi',
                    table: 'car_rental_models_info',
                }
            });
            //实时消息
           
            // goeasy.subscribe({
            //     channel: 'demo',
            //     onMessage: function(message){
            //         Layer.alert('新消息：'+message.content,{ icon:0},function(index){
            //             Layer.close(index);
            //             $(".btn-refresh").trigger("click");
            //         });
            //
            //     }
            // });

            //预定
            // goeasy.subscribe({
            //     channel: 'demo-reserve',
            //     onMessage: function(message){
            //         Layer.alert('新消息：'+message.content,{ icon:0},function(index){
            //             Layer.close(index);
            //             $(".btn-refresh").trigger("click");
            //         });
            //
            //     }
            // });
            
            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                searchFormVisible: true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        // {field: 'sales_id', title: __('Sales_id')},
                        {field: 'city_store', title: __('城市门店'), operate: false},
                        {field: 'licenseplatenumber', title: __('Licenseplatenumber'), formatter:function(value,row,index){
                            return row.status_data==''?row.licenseplatenumber:row.licenseplatenumber+' <span class="label label-danger">该车在签单流程中, 销售员：' + row.department + '--' + row.admin_name + '</span>';
                        }},
                        {field: 'models.name', title: __('Models.name')},
                        {field: 'kilometres', title: __('Kilometres'), operate:false},
                        {field: 'companyaccount', title: __('Companyaccount')},
                        {field: 'cashpledge', title: __('Cashpledge'),operate:false},
                        {field: 'threemonths', title: __('Threemonths'),operate:false},
                        {field: 'sixmonths', title: __('Sixmonths'),operate:false},
                        {field: 'manysixmonths', title: __('Manysixmonths'),operate:false},
                        {field: 'drivinglicenseimages', title: __('Drivinglicenseimages'), formatter: Table.api.formatter.images},
                        {field: 'vin', title: __('Vin')},
                        {field: 'engine_no', title: __('发动机号')},
                        {field: 'expirydate', title: __('Expirydate'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,dateteimeFormat: 'YYYY-MM-DD'},
                        {field: 'annualverificationdate', title: __('Annualverificationdate'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,dateteimeFormat: 'YYYY-MM-DD'},
                        {field: 'carcolor', title: __('Carcolor'),operate:false},
                        {field: 'aeratedcard', title: __('Aeratedcard'),operate:false},
                        {field: 'volumekeys', title: __('Volumekeys'),operate:false},
                        {field: 'Parkingposition', title: __('Parkingposition'),operate:false},
                        {field: 'shelfismenu', title: __('Shelfismenu'), formatter: Controller.api.formatter.toggle,searchList:{"1":"是","0":"否"}},
                        {field: 'vehiclestate', title: __('Vehiclestate'),operate:false},


                        {field: 'sales.nickname', title: __('预定销售人员')},
                        {field: 'note', title: __('Note'),operate:false},
                        {field: 'operate', title: __('Operate'), table: table, 
                        buttons: [
                            { 
                                name: 'rentalrequest',text:'销售员租车请求', title:'销售员租车请求',icon: 'fa fa-automobile', extend: 'data-toggle="tooltip"',classname: 'btn btn-xs btn-success btn-dialog btn-rentalrequest',
                                // url:'rentcar/vehicleinformation/rentalrequest',/**销售员租车请求 */
                                hidden:function(row){
                                    if(row.status == 'is_reviewing'){
                                        return false; 
                                    }
                                    else if(row.status_data == 'is_reviewing_true'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'is_reviewing_pass'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'for_the_car'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'the_car'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == ''){
                                      
                                        return true;
                                    } 
                                },
                                
                            },
                            { 
                                name: 'is_reviewing_pass',text:'打印提车单', title:'打印提车单',icon: 'fa fa-automobile', extend: 'data-toggle="tooltip"',classname: 'btn btn-xs btn-success btn-dialog btn-carsingle',
                                // url:'rentcar/vehicleinformation/rentalrequest',/**打印提车单 */
                                hidden:function(row){
                                    if(row.status == 'is_reviewing_pass'){
                                        return false; 
                                    }
                                    else if(row.status_data == 'is_reviewing_true'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'is_reviewing'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'for_the_car'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'the_car'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == ''){
                                      
                                        return true;
                                    } 
                                },
                                
                            },
                            { 
                                name: 'for_the_car',text:'确认提车', title:'确认提车',icon: 'fa fa-automobile', extend: 'data-toggle="tooltip"',classname: 'btn btn-xs btn-success btn-dialog btn-takecar',
                                // url:'rentcar/vehicleinformation/rentalrequest',/**打印提车单 */
                                hidden:function(row){
                                    if(row.status_data == 'for_the_car'){
                                        return false; 
                                    }
                                    else if(row.status_data == 'is_reviewing_pass'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'is_reviewing_true'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'is_reviewing'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'the_car'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == ''){
                                      
                                        return true;
                                    } 
                                },
                                
                            },
                            {
                                name: 'is_reviewing_true', icon: 'fa fa-check-circle', text: '已有销售预定', classname: ' text-info ',
                                hidden: function (row) {  /**已有销售预定 */
                                    if(row.status_data == 'is_reviewing_true'){
                                        return false; 
                                    }
                                    else if(row.status_data == 'is_reviewing_pass'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'for_the_car'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'is_reviewing'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'the_car'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == ''){
                                      
                                        return true;
                                    } 
                                }
                            },
                            {
                                name: '', icon: 'fa fa-check-circle', text: '等待出租', classname: ' text-info ',
                                hidden: function (row) {  /**等待出租 */
                                    if(row.status_data == '' && row.shelfismenu != 0){
                                        return false; 
                                    }

                                    else if(row.shelfismenu == 0 && row.status_data == ''){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'is_reviewing_pass'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'for_the_car'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'is_reviewing'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'the_car'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'is_reviewing_true'){
                                      
                                        return true;
                                    } 
                                }
                            },
                            {
                                name: '', icon: 'fa fa-check-circle', text: '已下架', classname: ' text-warning ',
                                hidden: function (row) {  /**已下架 */
                                    if(row.shelfismenu == 0){
                                        return false; 
                                    }
                                    else if(row.status_data == '' && row.shelfismenu != 0){

                                        return true;
                                    }
                                    else if(row.status_data == 'is_reviewing_pass'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'for_the_car'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'is_reviewing'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'the_car'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'is_reviewing_true'){
                                      
                                        return true;
                                    }
                                    else if(row.shelfismenu == 0 ){
                                        return true;
                                    }
                                }
                            },
                            /**
                             * 删除
                             */
                            { 
                                icon: 'fa fa-trash', name: 'del', icon: 'fa fa-trash', extend: 'data-toggle="tooltip"',text:'删除', title: __('删除'),classname: 'btn btn-xs btn-danger btn-delone',
                                url:'rentcar/vehicleinformation/del',/** */
                                hidden:function(row){
                                    if(row.status_data == ''){
                                        return false; 
                                    }
                                    else if(row.status_data == 'is_reviewing_pass'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'for_the_car'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'is_reviewing'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'the_car'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'is_reviewing_true'){
                                      
                                        return true;
                                    }
                                    else if(row.shelfismenu == 0){
                                        return true;
                                    }
                                   
                                },
                                
                            },
                            /**
                             * 编辑
                             */
                            { 
                                name: 'edit',text: '',icon: 'fa fa-pencil',extend: 'data-toggle="tooltip"',text:'编辑', title: __('编辑'),classname: 'btn btn-xs btn-success btn-editone',
                                url:'rencar/vehicleinformation/edit',/**编辑信息 */
                                hidden:function(row,value,index){ 
                                    if(row.status_data == ''){
                                        return false; 
                                    }
                                    else if(row.status_data == 'is_reviewing_pass'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'for_the_car'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'is_reviewing'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'the_car'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'is_reviewing_true'){
                                      
                                        return true;
                                    } 
                                }, 
                            },
                            {

                                name: 'the_car', icon: 'fa fa-automobile', text: '已提车', extend: 'data-toggle="tooltip"', title: __('订单已完成，客户已提车'), classname: ' text-success ',
                                hidden: function (row) {  /**已提车 */
                                    if(row.status_data == 'the_car'){
                                        return false; 
                                    }
                                    else if(row.status_data == ''){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'is_reviewing_pass'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'for_the_car'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'is_reviewing'){
                                      
                                        return true;
                                    } 
                                    else if(row.status_data == 'is_reviewing_true'){
                                      
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

            // 为表格绑定事件
            Table.api.bindevent(table);

            //实时消息
            //风控通过---可以提车
            // goeasy.subscribe({
            //     channel: 'demo-rental_pass',
            //     onMessage: function(message){
            //         Layer.alert('新消息：'+message.content,{ icon:0},function(index){
            //             Layer.close(index);
            //             $(".btn-refresh").trigger("click");
            //         });
            //
            //     }
            // });

            //销售预定---发送车管
            // goeasy.subscribe({
            //     channel: 'demo-reserve',
            //     onMessage: function(message){
            //         Layer.alert('新消息：'+message.content,{ icon:0},function(index){
            //             Layer.close(index);
            //             $(".btn-refresh").trigger("click");
            //         });
            //
            //     }
            // });

            //数据实时统计
            table.on('load-success.bs.table', function (e, data) {
               
                $(".btn-carsingle").data("area", ["80%", "80%"]);
                $(".btn-add").data("area", ["90%", "90%"]);
                $(".btn-edit").data("area", ["90%", "90%"]);
                
            });

            $(document).on("click", "a.btn-channel", function () {
                $("#archivespanel").toggleClass("col-md-9", $("#channelbar").hasClass("hidden"));
                $("#channelbar").toggleClass("hidden");
            });

            require(['jstree'], function () {
                //全选和展开
                $(document).on("click", "#checkall", function () {
                    $("#channeltree").jstree($(this).prop("checked") ? "check_all" : "uncheck_all");
                });
                $(document).on("click", "#expandall", function () {
                    $("#channeltree").jstree($(this).prop("checked") ? "open_all" : "close_all");
                });
                $('#channeltree').on("changed.jstree", function (e, data) {
                    console.log(data);
                    console.log(data.selected);
                    var options = table.bootstrapTable('getOptions');
                    options.pageNumber = 1;
                    options.queryParams = function (params) {
                        params.filter = JSON.stringify(data.selected.length > 0 ? {store_id: data.selected.join(",")} : {});
                        params.op = JSON.stringify(data.selected.length > 0 ? {store_id: 'in'} : {});
                        return params;
                    };
                    table.bootstrapTable('refresh', {});
                    return false;
                });
                $('#channeltree').jstree({
                    "themes": {
                        "stripes": true
                    },
                    "checkbox": {
                        "keep_selected_style": false,
                    },
                    "types": {
                        "channel": {
                            "icon": "fa fa-th",
                        },
                        "list": {
                            "icon": "fa fa-list",
                        },
                        "link": {
                            "icon": "fa fa-link",
                        },
                        "disabled": {
                            "check_node": false,
                            "uncheck_node": false
                        }
                    },
                    'plugins': ["types", "checkbox"],
                    "core": {
                        "multiple": true,
                        'check_callback': true,
                        "data": Config.storeList
                    }
                });
            });

        },
        carsingle: function () {
            // console.log(123);
            // return;
           

            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                Fast.api.close(data);//这里是重点
                console.log(data);
                 
                // Toastr.success("成功");//这个可有可无
            }, function (data, ret) {
                // console.log(data);

                Toastr.success("失败");

            });
            Controller.api.bindevent();
            // console.log(Config.id); 

        },
        
        add: function () {

            //门店下的车型
            $(document).on("change", "#c-store_id", function () {

                $('#c-models_id_text').val('');
            });
            $("#c-models_id").data("params", function (obj) {

                return {custom: {store_ids: $('#c-store_id').val()}};

            });

            Controller.api.bindevent();
        },
        edit: function () {

            //门店下的车型
            $(document).on("change", "#c-store_id", function () {

                $('#c-models_id_text').val('');
            });
            $("#c-models_id").data("params", function (obj) {

                return {custom: {store_ids: $('#c-store_id').val()}};

            });
            
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                $(document).on('click', "input[name='row[shelfismenu]']", function () {
                    var name = $("input[name='row[name]']");
                    name.prop("placeholder", $(this).val() == 1 ? name.data("placeholder-menu") : name.data("placeholder-node"));
                });
                $("input[name='row[shelfismenu]']:checked").trigger("click");
                Form.api.bindevent($("form[role=form]"));
            },
            events:{
                operate: {
                    /**编辑按钮 */
                    'click .btn-editone': function (e, value, row, index) {
                    $(".btn-editone").data("area", ["95%","95%"]); 

                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = options.extend.edit_url;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
                    },
                    /**删除按钮 */
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
                    //
                    /**
                     * 车管同意租车预定
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-rentalrequest': function (e, value, row, index) {

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
                            __('确定可以进行租车?'),
                            {icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true},

                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');


                                Fast.api.ajax({
                                    url: 'rentcar/vehicleinformation/rentalrequest',
                                    data: {id: row[options.pk]}
                                }, function (data, ret) {

                                    Toastr.success("成功");
                                    Layer.close(index);
                                    table.bootstrapTable('refresh');
                                    return false;
                                }, function (data, ret) {
                                    //失败的回调

                                    return false;
                                });


                            }
                        );

                    },
                    /**打印提车单 */
                    'click .btn-carsingle': function (e, value, row, index) {
    
                            e.stopPropagation();
                            e.preventDefault();
                            var table = $(this).closest('table');
                            var options = table.bootstrapTable('getOptions');
                            var ids = row[options.pk];
                            row = $.extend({}, row ? row : {}, {ids: ids});
                            var url = 'rentcar/vehicleinformation/carsingle';
                            Fast.api.open(Table.api.replaceurl(url, row, table), __('打印提车单'), $(this).data() || {});
                    },
                    /**确认提车按钮 */
                    'click .btn-takecar': function (e, value, row, index) {  

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
                            __('确定进行车辆提取吗?'),
                            { icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true },

                            function (index) {
                            layer.close(index);
                                layer.prompt({
                                    formType:0,
                                    title:'请输入实际提车日期(<span class="text-danger">格式为：2018-05-08</span>)',
                                }, function(value, indexs, elem){

                                    var table = $(that).closest('table');
                                    var options = table.bootstrapTable('getOptions');


                                    Fast.api.ajax({

                                        url: 'rentcar/vehicleinformation/takecar',
                                        data: {
                                            id: row[options.pk],
                                            delivery:value
                                        }

                                    }, function (data, ret) {

                                        Toastr.success('操作成功');
                                        Layer.close(indexs);
                                        table.bootstrapTable('refresh');

                                        Layer.alert('提车成功后，可到租车客户信息查看客户信息',{ icon:0},function(indexss){
                                            Layer.close(indexss);
                                            $(".btn-refresh").trigger("click");
                                        });

                                        return false;
                                    }, function (data, ret) {
                                        //失败的回调
                                        Toastr.success(ret.msg);

                                        return false;
                                    });
                                });



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
                toggle: function (value, row, index) {
                    if(row.status_data == ''){
                        var color = typeof this.color !== 'undefined' ? this.color : 'success';
                        var yes = typeof this.yes !== 'undefined' ? this.yes : 1;
                        var no = typeof this.no !== 'undefined' ? this.no : 0;
                        return "<a href='javascript:;' data-toggle='tooltip' title='" + __('Click to toggle') + "' class='btn-change' data-id='"
                            + row.id + "' data-params='" + this.field + "=" + (value ? no : yes) + "'><i class='fa fa-toggle-on " + (value == yes ? 'text-' + color : 'fa-flip-horizontal text-gray') + " fa-2x'></i></a>";
                    }
                    else{
                        return "该车在签单流程中";
                    }
                    
                }

            }
        }
    };
    return Controller;
});
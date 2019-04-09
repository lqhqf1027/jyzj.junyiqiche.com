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
                    index_url: 'cms/rentcar/index',
                    add_url: 'cms/rentcar/add',
                    edit_url: 'cms/rentcar/edit',
                    del_url: 'cms/rentcar/del',
                    multi_url: 'cms/rentcar/multi',
                    dragsort_url: 'cms/rentcar/dragsort',
                    table: 'car_rental_models_info',
                }
            });
            
            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                // searchFormVisible: true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        // {field: 'sales_id', title: __('Sales_id')},
                        {field: 'weigh', title: __('权重（排序）')},
                        {field: 'city_store', title: __('城市门店'), operate: false},
                        {field: 'licenseplatenumber', title: __('Licenseplatenumber'), formatter:function(value,row,index){
                            return row.status_data==''?row.licenseplatenumber:row.licenseplatenumber+' <span class="label label-danger">该车在签单流程中, 销售员：' + row.department + '--' + row.admin_name + '</span>';
                        }},
                        {field: 'models.name', title: __('Models.name')},
                        {
                            field: 'label.name', title: __('标签名称'), searchList: {"1":__('新能源'),"2":__('低首付')}, operate:'FIND_IN_SET', formatter: Table.api.formatter.label
                        },
                        {field: 'label.lableimages', title: __('标签图片'), formatter: Table.api.formatter.images},
                        {field: 'models_main_images', title: __('封面图片'), formatter: Table.api.formatter.images},
                        {field: 'modelsimages', title: __('车型亮点'), formatter: Table.api.formatter.images},
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
                        {
                            field: 'operate', title: __('Operate'), table: table,
                            buttons: [
                                {
                                    name: 'edit',
                                    icon: 'fa fa-pencil',
                                    text: '编辑方案',
                                    title: '编辑方案',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-info btn-editone',
                                },
                            ],
                            events: Controller.api.events.operate,
                            formatter: Controller.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);


            //数据实时统计
            table.on('load-success.bs.table', function (e, data) {
               
                $(".btn-carsingle").data("area", ["80%", "80%"]);
                $(".btn-add").data("area", ["90%", "90%"]);
                $(".btn-edit").data("area", ["60%", "60%"]);
                
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
                    $(".btn-editone").data("area", ["60%","60%"]); 

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

                    buttons.push({
                        name: 'dragsort',
                        icon: 'fa fa-arrows',
                        title: __('Drag to sort'),
                        extend: 'data-toggle="tooltip"',
                        classname: 'btn btn-xs btn-primary btn-dragsort'
                    });

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
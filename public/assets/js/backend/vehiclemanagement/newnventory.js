define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    // var goeasy = new GoEasy({
    //     appkey: 'BC-04084660ffb34fd692a9bd1a40d7b6c2'
    // });

    /**
     * 车辆管理库存
     * @type {{index: index, add: add, edit: edit, api: {bindevent: bindevent, events: {operate: {"click .btn-editone": click .btn-editone, "click .btn-delone": click .btn-delone}}, formatter: {operate: (function(*=, *=, *=): *)}}}}
     */
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'vehiclemanagement/newnventory/index',
                    add_url: 'vehiclemanagement/newnventory/add',
                    edit_url: 'vehiclemanagement/newnventory/edit',
                    del_url: 'vehiclemanagement/newnventory/del',
                    multi_url: 'vehiclemanagement/newnventory/multi',
                    table: 'car_new_inventory',
                }
            });

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
                        {field: 'models.name', title: __('车型名称'),formatter:function(v,r,i){
                            return r.the_car_username ? v+ ' <span class="label label-info"><i class="fa fa-user"> '+r.the_car_username+'</i></span>':v;
                        }},
                        // {field: 'carnumber', title: __('Carnumber')},
                        // {field: 'reservecar', title: __('Reservecar')},
                        {field: 'licensenumber', title: __('车牌号')},
                        {field: 'frame_number', title: __('车架号')},
                        {field: 'engine_number', title: __('发动机号')},
                        {field: 'household', title: __('所属户')},
                        {field: '4s_shop', title: __('4S店')},
                        {field: 'open_fare', title: __('开票价(元)')},
                        {field: 'note', title: __('备注'),operate:false},
                        {field: 'createtime', title: __('创建时间'), operate:false, addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('更新时间'), operate:false, addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, 
                        buttons: [
                            /**删除 */
                            {
                                name: 'del',
                                icon: 'fa fa-trash',  
                                icon: 'fa fa-trash', 
                                extend: 'data-toggle="tooltip"', 
                                title: __('Del'), 
                                classname: 'btn btn-xs btn-danger btn-delone',
                                hidden: function (row) { 
                                    if (!row.the_car_username) {
                                        return false;
                                    }
                                    else if (row.the_car_username) {
                                        return true;
                                    }
                                }
                            },
                            /**编辑 */
                            {
                                name: 'edit', 
                                text: '', 
                                icon: 'fa fa-pencil', 
                                extend: 'data-toggle="tooltip"', 
                                title: __('编辑'), 
                                classname: 'btn btn-xs btn-success btn-editone',
                                
                            },
                            /**车辆已出售 */
                            {
                                name: '车辆已出售', 
                                text: '车辆已出售',  
                                icon: 'fa fa-automobile', 
                                classname: 'text text-success',
                                hidden: function (row) { 
                                    if (row.the_car_username) {
                                        return false;
                                    }
                                    else if (!row.the_car_username) {
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
            //通过---录入库存通知
            // goeasy.subscribe({
            //     channel: 'demo-newcontrol_tube',
            //     onMessage: function(message){
            //         Layer.alert('新消息：'+message.content,{ icon:0},function(index){
            //             Layer.close(index);
            //             $(".btn-refresh").trigger("click");
            //         });
            //
            //     }
            // });

            //实时消息----其他金融
            //通过---录入库存通知
            // goeasy.subscribe({
            //     channel: 'demo-newcontrol_tube_finance',
            //     onMessage: function(message){
            //         Layer.alert('新消息：'+message.content,{ icon:0},function(index){
            //             Layer.close(index);
            //             $(".btn-refresh").trigger("click");
            //         });
            //
            //     }
            // });


        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            events: {
                operate: {
                    //编辑按钮
                    'click .btn-editone': function (e, value, row, index) { /**编辑按钮 */
                        $(".btn-editone").data("area", ["55%", "60%"]);

                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, { ids: ids });
                        var url = options.extend.edit_url;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
                    },
                    //删除按钮
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
                    }
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
                }

            }
        }
    };
    return Controller;
});
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cms/prizerecord/index',
                    add_url: 'cms/prizerecord/add',
                    edit_url: 'cms/prizerecord/edit',
                    del_url: 'cms/prizerecord/del',
                    multi_url: 'cms/prizerecord/multi',
                    table: 'cms_prize_record',
                }
            });

            var table = $("#table");

            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "快速搜索:中奖人的手机号";};

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user.nickname', title: __('User.nickname')},
                        {field: 'user.mobile', title: __('User.mobile')},
                        {field: 'user.avatar', title: __('User.avatar'), formatter: Table.api.formatter.image},

                        {field: 'prize.prize_name', title: __('Prize.prize_name')},
                        {field: 'prize.prize_image', title: __('Prize.prize_image'), formatter: Table.api.formatter.image},
                        // {field: 'conversion_code', title: __('Conversion_code')},

                        {field: 'is_use', title: __('是否已领取'),formatter:function(value,row,index){
                            return row.is_use =='1'?'<span class="text-success">已领取</span>' : '<span class="text-danger">未领取</span>' ;

                        }},
                        {field: 'awardtime', title: __('中奖时间'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'accepttime', title: __('兑奖时间'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},

                        {field: 'operate', title: __('Operate'), table: table, 
                        buttons: [
                            /**
                             * 编辑
                             */
                            { 
                                name: 'edit',
                                icon: 'fa fa-pencil',
                                extend: 'data-toggle="tooltip"',
                                text:'领取奖品', 
                                title: __('领取奖品'),
                                classname: 'btn btn-xs btn-danger btn-editone',
                                hidden: function (row) {  
                                    if(row.is_use == '0'){
                                        return false; 
                                    } 
                                    else if(row.is_use == '1'){
                                      
                                        return true;
                                    } 
                                }
                            },
                            /**
                             * 已领取奖品
                             */
                            { 
                                name: 'receive',
                                icon: 'fa fa-check-circle',
                                extend: 'data-toggle="tooltip"',
                                text:'奖品已领取', 
                                classname: 'btn btn-xs btn-success btn-receive',
                                hidden: function (row) {  
                                    if(row.is_use == '1'){
                                        return false; 
                                    } 
                                    else if(row.is_use == '0'){
                                      
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

            

            // 为表格绑定事件
            Table.api.bindevent(table);
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
            events:{
                operate: {
                    /*
                     *编辑按钮 
                     */
                    'click .btn-editone': function (e, value, row, index) {
                        $(".btn-editone").data("area", ["50%","50%"]); 

                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = options.extend.edit_url;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
                    },
                    /*
                     *删除按钮 
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
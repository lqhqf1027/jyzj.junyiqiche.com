define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
        
            
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    import_url: 'promote/customertabs/import',
                    // add_url: 'promote/platform/add',
                    // edit_url: 'promote/platform/edit',
                    // del_url: 'promote/platform/del',
                    // multi_url: 'promote/platform/multi',
                    // table: 'platform',
                }
            });

            // var table = $("#table");

            // // 初始化表格
            // table.bootstrapTable({
            //     url: $.fn.bootstrapTable.defaults.extend.index_url,
            //     pk: 'id',
            //     sortName: 'id',
            //     columns: [
            //         [
            //             {checkbox: true},
            //             {field: 'id', title: __('Id')},
            //             {field: 'name', title: __('Name')},
            //             {field: 'status', title: __('Status'), visible:false, searchList: {"normal":__('normal'),"hidden":__('hidden')}},
            //             {field: 'status_text', title: __('Status'), operate:false},
            //             {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
            //             {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
            //             {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
            //         ]
            //     ]
            // });

            // // 为表格绑定事件
            // Table.api.bindevent(table);


            
        },
        // import:function(){
        //     // console.log(123);
        //     // return;
        //     Form.api.bindevent($("form[role=form]"), function(data, ret){
        //         //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
        //         Fast.api.close(data);//这里是重点
        //         console.log(data);
        //         // Toastr.success("成功");//这个可有可无
        //     }, function(data, ret){
        //         // console.log(data);
                
        //         Toastr.success("失败");
                
        //     });
        //     // Controller.api.bindevent();
        //     // console.log(Config.id); 
 
        // },
        add: function () {
            Form.api.bindevent($("form[role=form]"), function(data, ret){
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                Fast.api.close(data);//这里是重点
                Toastr.success("成功");//这个可有可无
            }, function(data, ret){
                Toastr.success("失败");
            });
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
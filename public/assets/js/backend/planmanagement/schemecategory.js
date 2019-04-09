define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    /**
     * 方案类型
     * @type {{index: index, add: add, edit: edit, api: {bindevent: bindevent}}}
     */
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'planmanagement/schemecategory/index',
                    add_url: 'planmanagement/schemecategory/add',
                    edit_url: 'planmanagement/schemecategory/edit',
                    del_url: 'planmanagement/schemecategory/del',
                    multi_url: 'planmanagement/schemecategory/multi',
                    table: 'scheme_category',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'cities.cities_name', title: __('所属城市')},
                        {field: 'store_ids', title: __('所属门店名称'),formatter:function (v,r,i) {
                            return v != null ? '<strong class="text-success">'+ Controller.substrPlanTyleNode(r.store_name) +'</strong>' : v;
                        }},
                        {field: 'name', title: __('Name')},
                        {field: 'category_note', title: __('方案类型备注')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {

            //门店
            $(document).on("change", "#c-city_id", function () {

                $('li.selected_tag').remove();
            });
            
            $("#c-store_ids").data("params", function (obj) {

                return {custom: {city_id: $('#c-city_id').val()}};

            });

            Controller.api.bindevent();
        },
        edit: function () {

            //门店
            $(document).on("change", "#c-city_id", function () {

                $('li.selected_tag').remove();
            });
            
            $("#c-store_ids").data("params", function (obj) {

                return {custom: {city_id: $('#c-city_id').val()}};

            });

            Controller.api.bindevent();
        },
        /**
         *   字符串按照指定长度换行
         * @param s   字符串
         * @param $length   长度
         * @returns { string}  返回新的数组
         */
        substrPlanTyleNode: function (s) {
            
            var re = "";
            var arr = s.split(',');
            
            var length = arr.length;
           
            for (var i = 0; i < length; i++) {

                re += arr[i];
                re += '<br />';
                
            }
            // console.log(arr);
            // return;
            return re;
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
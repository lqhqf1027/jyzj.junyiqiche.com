define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    /**
     * goeasy推送的key
     */
    // var goeasy = new GoEasy({
    //     appkey: 'BC-04084660ffb34fd692a9bd1a40d7b6c2'
    // });

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
             * 新车单
             */
            order_acar: function () {
                var orderAcar = $("#orderAcar");

                $(".btn-add").data("area", ["95%", "95%"]);
                $(".btn-edit").data("area", ["95%", "95%"]);
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "快速搜索:客户姓名";};

                /**
                 * 初始化表格
                 */
                orderAcar.bootstrapTable({
                    url: 'salesmanagement/Orderlisttabs/orderAcar',
                    extend: {
                        
                        add_url: 'salesmanagement/Orderlisttabs/add',
                        edit_url: 'salesmanagement/Orderlisttabs/edit',
                        newreserveedit_url: 'salesmanagement/Orderlisttabs/newreserveedit',
                        newcontroladd_url: 'salesmanagement/Orderlisttabs/newcontroladd',
                        newinformation_url: 'salesmanagement/Orderlisttabs/newinformation',
                        newinform_tube_url: 'salesmanagement/Orderlisttabs/newinformtube',
                        newcollectioninformation_url: 'salesmanagement/Orderlisttabs/newcollectioninformation',
                        del_url: 'salesmanagement/Orderlisttabs/del',
                        multi_url: 'order/salesorder/multi',
                        //table: 'sales_order',
                    },
                    toolbar: '#toolbar1',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            { checkbox: true },
                            { field: 'id', title: __('Id') ,operate:false},
                            { field: 'order_no', title: __('Order_no') },
                            { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime,datetimeFormat:"YYYY-MM-DD" },
                            {field: 'admin.nickname', title: __('销售员'),formatter: Controller.api.formatter.sales},

                            { field: 'newinventory.licensenumber', title: __('车牌号') },
                            { field: 'models.name', title: __('销售车型') },
                            { field: 'username', title: __('Username'),formatter: Controller.api.formatter.judge, operate: false },
                            { field: 'financial_name', title: __('金融平台') },

                            { field: 'phone', title: __('Phone') },
                            { field: 'id_card', title: __('Id_card') },
                            {
                                field: 'id', title: __('查看详细资料'), table: orderAcar, buttons: [
                                    {
                                        name: 'details', text: '查看详细资料', title: '查看订单详细资料', icon: 'fa fa-eye', classname: 'btn btn-xs btn-primary btn-dialog btn-details',
                                        url: 'Sharedetailsdatas/new_car_share_data', callback: function (data) {

                                        }
                                    }
                                ],

                                operate: false, formatter: Table.api.formatter.buttons
                            },
                            { field: 'planacar.payment', title: __('首付（元）') ,operate:false },
                            { field: 'planacar.monthly', title: __('月供（元）')  ,operate:false},
                            { field: 'planacar.nperlist', title: __('期数') ,operate:false},
                            { field: 'planacar.margin', title: __('保证金（元）'),operate:false },
                            { field: 'planacar.tail_section', title: __('尾款（元）'),operate:false },
                            { field: 'planacar.gps', title: __('GPS（元）'),operate:false },

                            {
                                field: 'operate', title: __('Operate'), table: orderAcar,
                                buttons: [
                                    /**
                                     * 提交内勤
                                     */
                                    {
                                        name: 'submit_audit', text: '提交给内勤', title: '提交到当前部门内勤,用户记录实收定金金额与装饰', icon: 'fa fa-share', extend: 'data-toggle="tooltip"', classname: 'btn btn-xs btn-info btn-submit_audit',
                                        url: 'salesmanagement/orderlisttabs/sedAudit',
                                        hidden: function (row) { /**提交给内勤 */
                                            if (row.review_the_data == 'send_to_internal') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'internal_over') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 删除 
                                     */
                                    {
                                        icon: 'fa fa-trash', name: 'del', icon: 'fa fa-trash', extend: 'data-toggle="tooltip"', title: __('Del'), classname: 'btn btn-xs btn-danger btn-delone',
                                       
                                        hidden: function (row) {
                                            if (row.review_the_data == 'send_to_internal') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {

                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        },

                                    },
                                    /**
                                     * 预定编辑 
                                     */
                                    {
                                        name: 'send_to_internal', text: '预定编辑', icon: 'fa fa-pencil', extend: 'data-toggle="tooltip"', title: __('编辑订车资料'), classname: 'btn btn-xs btn-success btn-newreserveeditone',
                                       
                                        hidden: function (row, value, index) {
                                            if (row.review_the_data == 'send_to_internal') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        },
                                    },
                                    /**
                                     * 内勤正在处理中
                                     */
                                    {
                                        name: 'inhouse_handling', text: '正在等待内勤录入实收定金和装饰',
                                        hidden: function (row) {
                                            if (row.review_the_data == 'inhouse_handling') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 录入审核资料
                                     */
                                    {
                                        name: 'newcontroladd', text: '录入审核资料', icon: 'fa fa-plus', extend: 'data-toggle="tooltip"', title: __('录入审核资料'), classname: 'btn btn-xs btn-success btn-newcontroladd',
                                        
                                        hidden: function (row, value, index) {
                                           
                                            if (!row.id_cardimages || !row.drivers_licenseimages || !row.bank_cardimages) {
                                                return false;
                                            }
                                            else if (row.id_cardimages && row.drivers_licenseimages && row.bank_cardimages) {
                                                return true;
                                            }
                                        },
                                    },
                                    /**
                                     * 车管正在处理中 
                                     */
                                    {
                                        name: 'send_car_tube', text: '正在等待车管将订车表发送给财务匹配金融',
                                        hidden: function (row) {
                                            if (row.review_the_data == 'send_car_tube') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 正在匹配金融 
                                     */
                                    {
                                        name: 'is_reviewing', text: '产品经理正在对销售方案匹配金融平台',
                                        hidden: function (row) {

                                            if (row.review_the_data == 'is_reviewing') {
                                                return false;
                                            }
                                            
                                            else if (row.review_the_data == 'the_financial') {

                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 风控正在审核中 
                                     */
                                    {
                                        name: 'is_reviewing_true', text: '风控正在审核该客户的资料',
                                        hidden: function (row) {
                                            if (row.review_the_data == 'is_reviewing_true') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 征信已通过 
                                     */
                                    {
                                        name: 'for_the_car', icon: 'fa fa-check-circle', text: '征信已通过', classname: ' text-info ',
                                        hidden: function (row) {
                                            if (row.review_the_data == 'for_the_car') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 客户在签订金融合同
                                     */
                                    {
                                        name: 'conclude_the_contract', icon: 'fa fa-check-circle', text: '客户在签订金融合同', classname: ' text-info ',
                                        hidden: function (row) {
                                            if (row.review_the_data == 'conclude_the_contract') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 征信已通过 
                                     */
                                    {
                                        name: 'is_reviewing_pass', icon: 'fa fa-check-circle', text: '征信已通过', classname: ' text-info ',
                                        hidden: function (row) {
                                            if (row.review_the_data == 'is_reviewing_pass') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 风控匹配车辆
                                     */
                                    {
                                        name: 'take_the_car', icon: 'fa fa-check-circle', text: '风控匹配车辆', classname: ' text-info ',
                                        hidden: function (row) {
                                            if (row.review_the_data == 'take_the_car') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 补全客户提车资料
                                     */
                                    {
                                        name: 'newinformation', text: '补全客户提车资料', icon: 'fa fa-pencil', extend: 'data-toggle="tooltip"', title: __('补全客户提车资料'), classname: 'btn btn-xs btn-success btn-newinformation',
                                        
                                        hidden: function (row, value, index) {
                                            if (row.review_the_data == 'take_the_data') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        },
                                    },
                                    /**
                                     * 资料补全，提交车管提车
                                     */
                                    {
                                        name: 'inform_the_tube', text: '资料补全，提交车管提车', icon: 'fa fa-share', extend: 'data-toggle="tooltip"', title: __('资料补全，提交车管提车'), classname: 'btn btn-xs btn-info btn-newinform_tube',
                                        
                                        hidden: function (row, value, index) {
                                            if (row.review_the_data == 'inform_the_tube') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        },
                                    },
                                    /**
                                     *提车资料编辑
                                     */
                                    {
                                        name: 'edit', text: '提车资料编辑', icon: 'fa fa-pencil', extend: 'data-toggle="tooltip"', title: __('提车资料编辑'), classname: 'btn btn-xs btn-success btn-editone',
                                        
                                        hidden: function (row, value, index) {
                                            if (row.review_the_data == 'inform_the_tube') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        },
                                    },
                                    /**
                                     * 等待提车
                                     */
                                    {
                                        name: 'send_the_car', icon: 'fa fa-check-circle', text: '等待提车', classname: ' text-info ',
                                        hidden: function (row) {  /**等待提车*/
                                            if (row.review_the_data == 'send_the_car') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 车管正在录入库存
                                     */
                                    {
                                        name: 'tube_into_stock', icon: 'fa fa-check-circle', text: '车管正在录入库存', classname: ' text-info ',
                                        hidden: function (row) {  /**车管正在录入库存 */
                                            if (row.review_the_data == 'tube_into_stock') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 征信不通过
                                     */
                                    {
                                        name: 'not_through', icon: 'fa fa-times', text: '征信未通过，订单已关闭', classname: ' text-danger ',
                                        hidden: function (row) {

                                            if (row.review_the_data == 'not_through') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 补录资料
                                     */
                                    {
                                        name: 'collection_data', text: '补录资料', icon: 'fa fa-pencil', extend: 'data-toggle="tooltip"', title: __('补录资料'), classname: 'btn btn-xs btn-success btn-newinformation',
                                        
                                        hidden: function (row, value, index) {
                                            if (row.review_the_data == 'collection_data') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                        },
                                    },
                                    /**
                                     * 需交保证金
                                     */
                                    {
                                        name: 'the_guarantor', icon: 'fa fa-upload', text: '需交保证金', extend: 'data-toggle="tooltip"', title: __('点击上传保证金收据'), classname: 'btn btn-xs btn-warning btn-the_guarantor',
                                        hidden: function (row) {

                                            if (row.review_the_data == 'the_guarantor') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 已提车
                                     */
                                    {

                                        name: 'the_car', icon: 'fa fa-automobile', text: '已提车', extend: 'data-toggle="tooltip"', title: __('订单已完成，客户已提车'), classname: ' text-success ',
                                        hidden: function (row) {
                                            if (row.review_the_data == 'the_car') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
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

                /**
                 * 刷新表格渲染
                 */
                orderAcar.on('load-success.bs.table', function (e, data) {
                    // $('#badge_order_acar').text(data.total);
                    $(".btn-details").data("area", ["95%", "95%"]);

                    var td = $("#orderAcar td:nth-child(7)");
    
                    for (var i = 0; i < td.length; i++) {
    
                        td[i].style.textAlign = "left";
    
                    }
                })
                // 为表格1绑定事件
                Table.api.bindevent(orderAcar);

                /**
                 * 通过---签订金融合同
                 */

                /**
                 * 销售预定新车
                 */
                $(document).on("click", ".btn-newreserve", function () {   
                        
                    var url = 'salesmanagement/Orderlisttabs/newreserve';
                    var options = {
                        shadeClose: false,
                        shade: [0.3, '#393D49'],
                        area:['95%','95%'],
                        callback:function(value){

                        }
                    }
                    Fast.api.open(url,'新车预定',options)
                })
            },

            /**
             * 租车单
             */
            order_rental: function () {
                var orderRental = $("#orderRental"); 
                 
                $(".btn-add").data("area", ["95%","95%"]); 
                $(".btn-edit").data("area", ["95%","95%"]);
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "快速搜索:客户姓名";};

                // 初始化表格
                orderRental.bootstrapTable({
                    url: 'salesmanagement/Orderlisttabs/orderRental',
                    extend: {
                        rentaladd_url: 'salesmanagement/Orderlisttabs/rentaladd',
                        rentaledit_url: 'salesmanagement/Orderlisttabs/rentaledit',
                        del_url: 'salesmanagement/Orderlisttabs/rentaldel',
                        rentalmulti_url: 'order/rentalorder/multi',
                        reserve_url: 'salesmanagement/Orderlisttabs/reserve',
                        rentalinformation_url: 'salesmanagement/Orderlisttabs/rentalinformation',
                        table: 'rental_order',
                    },
                    toolbar: '#toolbar2',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'),operate:false},
                            {field: 'order_no', title: __('Order_no')},

                            {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Controller.api.formatter.datetime},
                            {field: 'admin.nickname', title: __('销售员'),formatter: Controller.api.formatter.sales},
                            {field: 'models.name', title: __('车型')},
                            {field: 'carrentalmodelsinfo.licenseplatenumber', title: __('车牌号')},
                            {field: 'carrentalmodelsinfo.vin', title: __('车架号')},
                            {field: 'username', title: __('Username'),formatter:function(value,row,index){
                                if(row.order_no ==  null){ /**如果订单编号为空，就处于预定状态 */
                                    return row.username+' <span class="label label-success">预定中</span>'

                                }
                                else{
                                    return row.username
                                }
                            },formatter: Controller.api.formatter.judge1, operate: false}, 
                            {field: 'phone', title: __('Phone')},
                            {field: 'id', title: __('查看详细资料'), table: orderRental, buttons: [
                                {name: 'rentalDetails', text: '查看详细资料', title: '查看订单详细资料' ,icon: 'fa fa-eye',classname: 'btn btn-xs btn-primary btn-dialog btn-rentalDetails', 
                                    url: 'Sharedetailsdatas/rental_car_share_data', 
                                    hidden:function(row){
                                        if(row.order_no !== null){ 
                                            return false; 
                                        }  
                                        else if(row.order_no == null){
                                            return true;
                                        }
                                    }
                                },
                                {
                                    name: 'null',text: '暂无详细资料',title:'暂无详细资料',icon: 'fa fa-eye-slash',classname: 'btn btn-xs btn-danger btn-primary',
                                    hidden:function(row){  /**暂无详细资料 */
                                        if(row.order_no == null){ 
                                            return false; 
                                        }
                                        else if(row.order_no !== null){
                                            return true;
                                        }
                                    }
                                },
                                ],
                                
                                operate:false, formatter: Table.api.formatter.buttons
                            },
                            {field: 'cash_pledge', title: __('Cash_pledge'),operate:false},
                            {field: 'rental_price', title: __('Rental_price'),operate:false},
                            {field: 'tenancy_term', title: __('Tenancy_term'),operate:false},
                            {field: 'delivery_datetime', title: __('开始租车日期'),operate:false,formatter:Controller.api.formatter.datetime},
                            {field: 'delivery_datetime', title: __('应退车日期'),operate:false,formatter:Controller.api.formatter.car_back},
                            {field: 'operate', title: __('Operate'), table: orderRental, 
                            buttons: [
                                /**
                                 * 补全客户信息，开始提车
                                 */
                                {
                                    name:'customerInformation',text:'开始提车', title:'补全客户信息，开始提车', icon: 'fa fa-share',extend: 'data-toggle="tooltip"',classname: 'btn btn-xs btn-info btn-customerInformation',
                                
                                    hidden:function(row){
                                        if(row.review_the_data == 'is_reviewing_argee'){ 
                                            return false; 
                                        }  
                                        else if(row.review_the_data == 'is_reviewing_true'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_control'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_pass'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'for_the_car'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_false'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_nopass'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'retiring'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'collection_data'){
                                            return true;
                                        }
                                    }
                                },
                                /**
                                 * 提交风控审核
                                 */
                                {
                                    name:'control',text:'提交风控审核', title:'提交风控审核', icon: 'fa fa-share',extend: 'data-toggle="tooltip"',classname: 'btn btn-xs btn-info btn-control',
                                    
                                    hidden:function(row){ /** */
                                        if(row.review_the_data == 'is_reviewing_false'){ 
                                            return false; 
                                        }  
                                        else if(row.review_the_data == 'is_reviewing_true'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_pass'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'for_the_car'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_argee'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_control'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_nopass'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'retiring'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'collection_data'){
                                            return true;
                                        }
                                    }
                                },
                                /**
                                 * 取消预定
                                 */
                                { 
                                    icon: 'fa fa-trash', name: 'rentaldel', icon: 'fa fa-trash', extend: 'data-toggle="tooltip"',text:'取消预定', title: __('取消预定'),classname: 'btn btn-xs btn-danger btn-delone',
                                    
                                    hidden:function(row){
                                        if(row.review_the_data == 'is_reviewing_argee'){ 
                                            return false; 
                                        }
                                        else if(row.review_the_data == 'is_reviewing_true'){
                                        
                                            return true;
                                        } 
                                        else if(row.review_the_data == 'is_reviewing_control'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'for_the_car'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_false'){
                                        
                                            return true;
                                        } 
                                        else if(row.review_the_data == 'is_reviewing_pass'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_nopass'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'retiring'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'collection_data'){
                                            return true;
                                        }
                                    
                                    },
                                    
                                },
                                /**
                                 * 删除
                                 */
                                { 
                                    icon: 'fa fa-trash', name: 'rentaldel', icon: 'fa fa-trash', extend: 'data-toggle="tooltip"',text:'删除订单', title: __('删除订单'),classname: 'btn btn-xs btn-danger btn-delone',
                                    
                                    hidden:function(row){
                                        if(row.review_the_data == 'is_reviewing_false'){ 
                                            return false; 
                                        }
                                        else if(row.review_the_data == 'is_reviewing_true'){
                                        
                                            return true;
                                        } 
                                        else if(row.review_the_data == 'is_reviewing_argee'){
                                        
                                            return true;
                                        } 
                                        else if(row.review_the_data == 'for_the_car'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_control'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_pass'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_nopass'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'retiring'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'collection_data'){
                                            return true;
                                        }
                                    
                                    },
                                    
                                },
                                /**
                                 * 修改订单
                                 */
                                { 
                                    name: 'rentaledit',text: '',icon: 'fa fa-pencil',extend: 'data-toggle="tooltip"',text:'修改订单', title: __('修改订单'),classname: 'btn btn-xs btn-success btn-rentaleditone', 
                                    
                                    hidden:function(row,value,index){ 
                                        if(row.review_the_data == 'is_reviewing_false'){ 
                                            return false; 
                                        } 
                                        else if(row.review_the_data == 'is_reviewing_argee'){
                                        
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_control'){
                                            return true;
                                        } 
                                        else if(row.review_the_data == 'for_the_car'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_true'){ 
                                            return true;
                                        } 
                                        else if(row.review_the_data == 'is_reviewing_pass'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_nopass'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'retiring'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'collection_data'){
                                            return true;
                                        }
                                    }, 
                                },
                                /**
                                 * 车管正在处理中
                                 */
                                {
                                    name: 'is_reviewing_true',text: '车管正在处理中',title:'车管正在处理你的租车请求',extend: 'data-toggle="tooltip"',
                                    hidden:function(row){  /**车管正在处理中 */
                                        if(row.review_the_data == 'is_reviewing_true'){ 
                                            return false; 
                                        }
                                        else if(row.review_the_data == 'is_reviewing_false'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_pass'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_control'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'for_the_car'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_argee'){
                                        
                                            return true;
                                        } 
                                        else if(row.review_the_data == 'is_reviewing_nopass'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'retiring'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'collection_data'){
                                            return true;
                                        }
                                    }
                                },
                                /**
                                 * 风控正在处理中
                                 */
                                {
                                    name: 'is_reviewing_control',text: '风控正在处理中',title:'风控正在处理中',
                                    hidden:function(row){  /**风控正在处理中 */
                                        if(row.review_the_data == 'is_reviewing_control'){ 
                                            return false; 
                                        }
                                        else if(row.review_the_data == 'is_reviewing_false'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_pass'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_true'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_argee'){
                                        
                                            return true;
                                        } 
                                        else if(row.review_the_data == 'for_the_car'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_nopass'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'retiring'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'collection_data'){
                                            return true;
                                        }
                                    }
                                },
                                /**
                                 * 征信已通过，待提车
                                 */
                                {
                                    name: 'is_reviewing_pass', icon: 'fa fa-check-circle', text: '征信已通过，待提车', classname: ' text-info ',
                                    hidden: function (row) {  /**征信已通过，待提车 */
                                        if (row.review_the_data == 'is_reviewing_pass') {
                                            return false;
                                        }
                                        else if (row.review_the_data == 'is_reviewing_true') {
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_argee'){
                                        
                                            return true;
                                        } 
                                        else if(row.review_the_data == 'is_reviewing_control'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_false'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'for_the_car'){
                                            return true;
                                        }
                                        else if (row.review_the_data == 'is_reviewing_nopass') {
                                            return true;
                                        }
                                        else if(row.review_the_data == 'retiring'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'collection_data'){
                                            return true;
                                        }
                                    }
                                },
                                /**
                                 * 征信不通过
                                 */
                                {
                                    name: 'is_reviewing_nopass', icon: 'fa fa-times', text: '征信未通过，订单已关闭', classname: ' text-danger ',
                                    hidden: function (row) {  /**征信不通过 */

                                        if (row.review_the_data == 'is_reviewing_nopass') {
                                            return false;
                                        }
                                        else if (row.review_the_data == 'is_reviewing_pass') {
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_control'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_argee'){
                                        
                                            return true;
                                        } 
                                        else if(row.review_the_data == 'for_the_car'){
                                            return true;
                                        }
                                        else if (row.review_the_data == 'is_reviewing_true') {
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_false'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'retiring'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'collection_data'){
                                            return true;
                                        }
                                    }
                                },
                                /**
                                 * 征信不通过，待补录资料
                                 */
                                {
                                    name: 'collection_data',text: '',icon: 'fa fa-pencil',extend: 'data-toggle="tooltip"',text:'补录资料', title: __('补录资料'),
                                    classname: 'btn btn-xs btn-success btn-rentalinformation', 
                                    hidden: function (row) {  /**征信不通过，待补录资料 */

                                        if (row.review_the_data == 'collection_data') {
                                            return false;
                                        }
                                        else if (row.review_the_data == 'is_reviewing_pass') {
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_control'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_argee'){
                                        
                                            return true;
                                        } 
                                        else if(row.review_the_data == 'for_the_car'){
                                            return true;
                                        }
                                        else if (row.review_the_data == 'is_reviewing_true') {
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_false'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'retiring'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_nopass'){
                                            return true;
                                        }
                                    }
                                },
                                /**
                                 * 已提车
                                 */
                                {

                                    name: 'for_the_car', icon: 'fa fa-automobile', text: '已提车', extend: 'data-toggle="tooltip"', title: __('订单已完成，客户已提车'), classname: ' text-success ',
                                    hidden: function (row) {  /**已提车 */
                                        if (row.review_the_data == 'for_the_car') {
                                            return false;
                                        }
                                        else if (row.review_the_data == 'is_reviewing_pass') {
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_control'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_argee'){
                                        
                                            return true;
                                        } 
                                        else if(row.review_the_data == 'is_reviewing_nopass'){
                                            return true;
                                        }
                                        else if (row.review_the_data == 'is_reviewing_true') {
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_false'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'retiring'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'collection_data'){
                                            return true;
                                        }
                                    }
                                },
                                /**
                                 * 已退车
                                 */
                                {

                                    name: 'retiring', icon: 'fa fa-automobile', text: '已退车', extend: 'data-toggle="tooltip"', title: __('租期到期，已退车'), classname: ' text-danger ',
                                    hidden: function (row) {  /**已退车 */
                                        if (row.review_the_data == 'retiring') {
                                            return false;
                                        }
                                        else if (row.review_the_data == 'is_reviewing_pass') {
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_control'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_argee'){
                                        
                                            return true;
                                        } 
                                        else if(row.review_the_data == 'is_reviewing_nopass'){
                                            return true;
                                        }
                                        else if (row.review_the_data == 'is_reviewing_true') {
                                            return true;
                                        }
                                        else if(row.review_the_data == 'is_reviewing_false'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'for_the_car'){
                                            return true;
                                        }
                                        else if(row.review_the_data == 'collection_data'){
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

                // goeasy.subscribe({
                //     channel: 'demo3',
                //     onMessage: function(message){
                //
                //         $(".btn-refresh").trigger("click");
                //     }
                // });

                /**
                 * 车管同意预定---销售接受消息
                 */
                // goeasy.subscribe({
                //     channel: 'demo-rental_argee',
                //     onMessage: function(message){
                //         message = split('|',message.content);
                //         if(Config.ADMIN_JS.id==message[1]){
                //             Layer.alert('新消息：'+message[0].content,{ icon:0},function(index){
                //                 Layer.close(index);
                //                 $(".btn-refresh").trigger("click");
                //             });
                //         }
                //     }
                // });

                //
                /**
                 * 风控通过---可以提车
                 */
                // goeasy.subscribe({
                //     channel: 'demo-rental_pass',
                //     onMessage: function(message){
                //         message = split('|',message.content);
                //         if(Config.ADMIN_JS.id==message[1]){
                //             Layer.alert('新消息：'+message[0].content,{ icon:0},function(index){
                //                 Layer.close(index);
                //                 $(".btn-refresh").trigger("click");
                //             });
                //         }
                //
                //     }
                // });

                /**
                 * 不通过
                 */
                // goeasy.subscribe({
                //     channel: 'demo-rental_nopass',
                //     onMessage: function(message){
                //         message = split('|',message.content);
                //         if(Config.ADMIN_JS.id==message[1]){
                //             Layer.alert('新消息：'+message[0].content,{ icon:0},function(index){
                //                 Layer.close(index);
                //                 $(".btn-refresh").trigger("click");
                //             });
                //         }
                //
                //     }
                // });

                /**
                 * 不通过---待补录资料
                 */
                // goeasy.subscribe({
                //     channel: 'demo-rental_information',
                //     onMessage: function(message){
                //         message = split('|',message.content);
                //         if(Config.ADMIN_JS.id==message[1]){
                //             Layer.alert('新消息：'+message[0].content,{ icon:0},function(index){
                //                 Layer.close(index);
                //                 $(".btn-refresh").trigger("click");
                //             });
                //         }
                //
                //     }
                // });

                /**
                 * 表格刷新渲染
                 */
                orderRental.on('load-success.bs.table', function (e, data) {
                
                    // $('#badge_order_rental').text(data.total);
                    $(".btn-rentalDetails").data("area", ["95%", "95%"]);

                    var td = $("#orderRental td:nth-child(6)");
    
                    for (var i = 0; i < td.length; i++) {
    
                        td[i].style.textAlign = "left";
    
                    }
                })
                
                /**
                 * 为租车表格绑定事件
                 */
                Table.api.bindevent(orderRental);

                
                /**
                 * 销售预定租车
                 */
                $(document).on("click", ".btn-reserve", function () {   
                        
                    var url = 'salesmanagement/Orderlisttabs/reserve';
                    var options = {
                        shadeClose: false,
                        shade: [0.3, '#393D49'],
                        area:['90%','90%'],
                        callback:function(value){

                        }
                    }
                    Fast.api.open(url,'租车预定',options)
                })

            },

            /**
             * 二手车单
             */
            order_second: function () {
                var orderSecond = $("#orderSecond");

                $(".btn-add").data("area", ["95%", "95%"]);
                $(".btn-edit").data("area", ["95%", "95%"]);

                // 初始化表格
                orderSecond.bootstrapTable({
                    url: 'salesmanagement/Orderlisttabs/orderSecond',
                    extend: {
                        
                        secondadd_url: 'salesmanagement/Orderlisttabs/secondadd',
                        secondedit_url: 'salesmanagement/Orderlisttabs/secondedit',
                        secondaudit_url: 'salesmanagement/Orderlisttabs/secondaudit',
                        secondinformation_url: 'salesmanagement/Orderlisttabs/secondinformation',
                        del_url: 'salesmanagement/Orderlisttabs/seconddel',
                        multi_url: 'salesmanagement/Orderlisttabs/multi',
                        table: 'second_sales_order',
                    },
                    toolbar: '#toolbar3',
                    pk: 'id',
                    searchFormVisible: true,
                    sortName: 'id',
                    columns: [
                        [
                            { checkbox: true },
                            { field: 'id', title: __('Id') ,operate:false},
                            { field: 'order_no', title: __('Order_no') },
                            { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime ,datetimeFormat:"YYYY-MM-DD" },

                            { field: 'plansecond.licenseplatenumber', title: __('车牌号') },
                            { field: 'models.name', title: __('销售车型') },
                            {field: 'admin.nickname', title: __('销售员'),formatter: Controller.api.formatter.sales},
                            { field: 'username', title: __('Username'),formatter: Controller.api.formatter.judge, operate: false },
                            { field: 'phone', title: __('Phone') },
                            {
                                field: 'id', title: __('查看详细资料'), table: orderSecond, buttons: [
                                    {
                                        name: 'seconddetails', text: '查看详细资料', title: '查看订单详细资料', icon: 'fa fa-eye', classname: 'btn btn-xs btn-primary btn-dialog btn-seconddetails',
                                        url: 'Sharedetailsdatas/second_car_share_data', callback: function (data) {

                                        }
                                    }
                                ],

                                operate: false, formatter: Table.api.formatter.buttons
                            },
                            { field: 'id_card', title: __('Id_card') },
                            
                            { field: 'plansecond.newpayment', title: __('新首付（元）'),operate:false },
                            { field: 'plansecond.monthlypaymen', title: __('月供（元）'),operate:false },
                            { field: 'plansecond.periods', title: __('期数') , operate: false},
                            { field: 'plansecond.totalprices', title: __('总价（元）'), operate: false },
                            { field: 'plansecond.bond', title: __('保证金（元）') , operate: false},
                            { field: 'plansecond.tailmoney', title: __('尾款（元）'), operate: false },

                            {
                                field: 'operate', title: __('Operate'), table: orderSecond,
                                buttons: [
                                    /**
                                     * 提交内勤
                                     */
                                    {
                                        name: 'second_audit', text: '提交内勤', title: '提交内勤', icon: 'fa fa-share', extend: 'data-toggle="tooltip"', classname: 'btn btn-xs btn-info btn-second_audit',
                                        url: 'order/secondsalesorder/setAudit',
                                        //等于is_reviewing_true 的时候操作栏显示的是正在审核四个字，隐藏编辑和删除
                                        //等于is_reviewing 的时候操作栏显示的是提交审核按钮 四个字，显示编辑和删除 
                                        //....
                                        hidden: function (row) { /**提交内勤 */
                                            if (row.review_the_data == 'is_reviewing') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 删除
                                     */
                                    {
                                        icon: 'fa fa-trash', name: 'seconddel', icon: 'fa fa-trash', extend: 'data-toggle="tooltip"', title: __('Del'), classname: 'btn btn-xs btn-danger btn-delone',
                                       
                                        hidden: function (row) {
                                            if (row.review_the_data == 'is_reviewing') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {

                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        },

                                    },
                                    /**
                                     * 内勤正在处理中
                                     */
                                    {
                                        name: 'is_reviewing_true', text: '内勤正在处理中',
                                        hidden: function (row) {  /**内勤正在处理中 */
                                            if (row.review_the_data == 'is_reviewing_true') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 资料编辑
                                     */
                                    {
                                        name: 'secondedit', text: '资料编辑', icon: 'fa fa-pencil', extend: 'data-toggle="tooltip"', title: __('资料编辑'), classname: 'btn btn-xs btn-success btn-secondeditone',
                                        
                                        hidden: function (row, value, index) {
                                            if (row.review_the_data == 'is_reviewing') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }   
                                        },
                                    },
                                    /**
                                     * 补录资料
                                     */
                                    {
                                        name: 'secondinformation', text: '补录提车资料', icon: 'fa fa-pencil', extend: 'data-toggle="tooltip"', title: __('补录提车资料'), classname: 'btn btn-xs btn-success btn-secondinformation',
                                        
                                        hidden: function (row, value, index) {
                                            if (row.review_the_data == 'collection_data') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }   
                                        },
                                    },
                                    /**
                                     * 审核资料上传
                                     */
                                    {
                                        name: 'secondaudit', text: '审核资料上传', icon: 'fa fa-pencil', extend: 'data-toggle="tooltip"', title: __('审核资料上传'), classname: 'btn btn-xs btn-success btn-secondaudit',
                                        
                                        hidden: function (row, value, index) {
                                            if (!row.id_cardimages || !row.drivers_licenseimages) {
                                                return false;
                                            }
                                            else if (row.id_cardimages && row.drivers_licenseimages) {
                                                return true;
                                            }
                                            
                                        },
                                    },
                                    /**
                                     * 风控正在审核中
                                     */
                                    {
                                        name: 'is_reviewing_control', text: '风控正在审核中',
                                        hidden: function (row) {  /**风控正在审核中 */
                                            if (row.review_the_data == 'is_reviewing_control') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 正在匹配金融
                                     */
                                    {
                                        name: 'is_reviewing_finance', text: '正在匹配金融',
                                        hidden: function (row) {  /**正在匹配金融 */
                                            if (row.review_the_data == 'is_reviewing_finance') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 车管正在处理中
                                     */
                                    {
                                        name: 'send_car_tube', text: '车管正在处理中',
                                        hidden: function (row) {  /**车管正在处理中 */
                                            if (row.review_the_data == 'send_car_tube') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 风控正在匹配车辆
                                     */
                                    {
                                        name: 'is_reviewing_pass', icon: 'fa fa-check-circle', text: '风控正在匹配车辆', classname: ' text-info ',
                                        hidden: function (row) {  /**风控正在匹配车辆 */
                                            if (row.review_the_data == 'is_reviewing_pass') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 通知客户可以进行提车
                                     */
                                    {
                                        name: 'for_the_car', icon: 'fa fa-check-circle', text: '车管备车中，通知客户可以进行提车', classname: ' text-info ',
                                        hidden: function (row) {  /**通知客户可以进行提车 */
                                            if (row.review_the_data == 'for_the_car') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 征信不通过
                                     */
                                    {
                                        name: 'not_through', icon: 'fa fa-times', text: '征信未通过，订单已关闭', classname: ' text-danger ',
                                        hidden: function (row) {  /**征信不通过 */

                                            if (row.review_the_data == 'not_through') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 需交保证金
                                     */
                                    {
                                        name: 'the_guarantor', icon: 'fa fa-upload', text: '需交保证金', extend: 'data-toggle="tooltip"', title: __('点击上传保证金收据'), classname: 'btn btn-xs btn-warning btn-secondthe_guarantor',
                                        hidden: function (row) {  /**需交保证金 */

                                            if (row.review_the_data == 'the_guarantor') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 已提车
                                     */
                                    {

                                        name: 'the_car', icon: 'fa fa-automobile', text: '已提车', extend: 'data-toggle="tooltip"', title: __('订单已完成，客户已提车'), classname: ' text-success ',
                                        hidden: function (row) {  /**已提车 */
                                            if (row.review_the_data == 'the_car') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_car_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing') {


                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
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

                /**
                 * 刷新表格渲染
                 */
                orderSecond.on('load-success.bs.table', function (e, data) {
                    
                    // $('#badge_order_second').text(data.total);
                    $(".btn-seconddetails").data("area", ["95%", "95%"]);

                    var td = $("#orderSecond td:nth-child(6)");
    
                    for (var i = 0; i < td.length; i++) {
    
                        td[i].style.textAlign = "left";
    
                    }
                })
                /**
                 * 为二手车表格绑定事件
                 */
                Table.api.bindevent(orderSecond);

                /**
                 * 风控通过---通知客户可以提车
                 */
                // goeasy.subscribe({
                //     channel: 'demo-secondpass_inform',
                //     onMessage: function(message){
                //         message = split('|',message.content);
                //         if(Config.ADMIN_JS.id==message[1]){
                //             Layer.alert('新消息：'+message[0].content,{ icon:0},function(index){
                //                 Layer.close(index);
                //                 $(".btn-refresh").trigger("click");
                //             });
                //         }
                //
                //     }
                // });

                /**
                 * 提供保证金
                 */
                // goeasy.subscribe({
                //     channel: 'demo-second_data',
                //     onMessage: function(message){
                //         message = split('|',message.content);
                //         if(Config.ADMIN_JS.id==message[1]){
                //             Layer.alert('新消息：'+message[0].content,{ icon:0},function(index){
                //                 Layer.close(index);
                //                 $(".btn-refresh").trigger("click");
                //             });
                //         }
                //
                //     }
                // });

                /**
                 * 不通过
                 */
                // goeasy.subscribe({
                //     channel: 'demo-second_nopass',
                //     onMessage: function(message){
                //         message = split('|',message.content);
                //         if(Config.ADMIN_JS.id==message[1]){
                //             Layer.alert('新消息：'+message[0].content,{ icon:0},function(index){
                //                 Layer.close(index);
                //                 $(".btn-refresh").trigger("click");
                //             });
                //         }
                //     }
                // });

                /**
                 * 不通过,待补录资料
                 */
                // goeasy.subscribe({
                //     channel: 'demo-second_information',
                //     onMessage: function(message){
                //         message = split('|',message.content);
                //         if(Config.ADMIN_JS.id==message[1]){
                //             Layer.alert('新消息：'+message[0].content,{ icon:0},function(index){
                //                 Layer.close(index);
                //                 $(".btn-refresh").trigger("click");
                //             });
                //         }
                //     }
                // });

                /**
                 * 销售预定二手车
                 */
                $(document).on("click", ".btn-secondreserve", function () {   
                        
                    var url = 'salesmanagement/Orderlisttabs/secondreserve';
                    var options = {
                        shadeClose: false,
                        shade: [0.3, '#393D49'],
                        area:['95%','95%'],
                        callback:function(value){  

                        }
                    }
                    Fast.api.open(url,'二手车预定',options)
                })
            },

            /**
             * 全款单（新车）
             */
            order_full: function () {
                var orderFull = $("#orderFull");

                $(".btn-add").data("area", ["95%", "95%"]);
                $(".btn-edit").data("area", ["95%", "95%"]);

                // 初始化表格
                orderFull.bootstrapTable({
                    url: 'salesmanagement/Orderlisttabs/orderFull',
                    extend: {
                        fulladd_url: 'salesmanagement/Orderlisttabs/fulladd',
                        fulledit_url: 'salesmanagement/Orderlisttabs/fulledit',
                        del_url: 'salesmanagement/Orderlisttabs/fulldel',
                        multi_url: 'salesmanagement/Orderlisttabs/multi',
                        table: 'full_parment_order',
                    },
                    toolbar: '#toolbar4',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            { checkbox: true },
                            { field: 'id', title: __('Id') ,operate:false},
                            { field: 'order_no', title: __('Order_no') },
                            { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime, datetimeFormat:"YYYY-MM-DD" },
                            { field: 'models.name', title: __('销售车型') },
                            {field: 'admin.nickname', title: __('销售员'),formatter: Controller.api.formatter.sales},
                            { field: 'planfull.full_total_price', title: __('全款总价（元）') },

                            {
                                field: 'id', title: __('查看详细资料'), table: orderFull, buttons: [
                                    {
                                        name: 'fulldetails', text: '查看详细资料', title: '查看订单详细资料', icon: 'fa fa-eye', classname: 'btn btn-xs btn-primary btn-dialog btn-fulldetails',
                                        url: 'Sharedetailsdatas/full_car_share_data', callback: function (data) {

                                        }
                                    }
                                ],

                                operate: false, formatter: Table.api.formatter.buttons
                            },

                            {field: 'username', title: __('Username'),formatter: Controller.api.formatter.judge1, operate: false},
                            {field: 'phone', title: __('Phone')},
                            { field: 'delivery_datetime', title: __('Delivery_datetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,datetimeFormat:"YYYY-MM-DD" },

                            {
                                field: 'operate', title: __('Operate'), table: orderFull,
                                buttons: [
                                    /**
                                     * 提交内勤
                                     */
                                    {
                                        name: 'submitCar', text: '提交内勤', icon: 'fa fa-share', extend: 'data-toggle="tooltip"', title: __('提交内勤'), classname: 'btn btn-xs btn-info btn-submitCar',
                                       
                                        hidden: function (row) { /**提交内勤 */
                                            if (row.review_the_data == 'send_to_internal') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            
                                        }
                                    },
                                    /**
                                     * 删除
                                     */
                                    {
                                        icon: 'fa fa-trash', name: 'fulldel', icon: 'fa fa-trash', extend: 'data-toggle="tooltip"', title: __('Del'), classname: 'btn btn-xs btn-danger btn-delone',
                                        
                                        hidden: function (row) {
                                            if (row.review_the_data == 'send_to_internal') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            
                                        },

                                    },
                                    /**
                                     * 编辑
                                     */
                                    {
                                        name: 'fulledit', text: '', icon: 'fa fa-pencil', extend: 'data-toggle="tooltip"', title: __('Edit'), classname: 'btn btn-xs btn-success btn-fulleditone',
                                        
                                        hidden: function (row, value, index) {
                                            if (row.review_the_data == 'send_to_internal') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                        },
                                    },
                                    /**
                                     * 车管正在备车中
                                     */
                                    {
                                        name: 'is_reviewing_true', icon: 'fa fa-check-circle', text: '车管正在备车中', classname: ' text-info ',
                                        hidden: function (row) {  /**车管正在备车中 */
                                            if (row.review_the_data == 'is_reviewing_true') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                           
                                        }
                                    },
                                    /**
                                     * 内勤正在处理
                                     */
                                    {
                                        name: 'inhouse_handling', text: '内勤正在处理',
                                        hidden: function (row) {  /**内勤正在处理 */
                                            if (row.review_the_data == 'inhouse_handling') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 车管备车成功，等待提车
                                     */
                                    {
                                        name: 'is_reviewing_pass', icon: 'fa fa-check-circle', text: '车管备车成功，等待提车', classname: ' text-info ',
                                        hidden: function (row) {  /**车管备车成功，等待提车 */
                                            if (row.review_the_data == 'is_reviewing_pass') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 已提车
                                     */
                                    {
                                        name: 'for_the_car', icon: 'fa fa-automobile', text: '已提车', extend: 'data-toggle="tooltip"', title: __('订单已完成，客户已提车'), classname: ' text-success ',
                                        hidden: function (row) {  /**已提车 */
                                            if (row.review_the_data == 'for_the_car') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
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

                /**
                 * 刷新表格渲染
                 */
                orderFull.on('load-success.bs.table', function (e, data) {
                    
                    // $('#badge_order_full').text(data.total);
                    $(".btn-fulldetails").data("area", ["95%", "95%"]);

                    var td = $("#orderFull td:nth-child(5)");
    
                    for (var i = 0; i < td.length; i++) {
    
                        td[i].style.textAlign = "left";
    
                    }
                })
          
                /**
                 * 为全款单表格绑定事件
                 */
                Table.api.bindevent(orderFull);

                /**
                 * 车管发送---销售接收----可以进行提车
                 */
                // goeasy.subscribe({
                //     channel: 'demo-full_takecar',
                //     onMessage: function(message){
                //         message = split('|',message.content);
                //         if(Config.ADMIN_JS.id==message[1]){
                //             Layer.alert('新消息：'+message[0].content,{ icon:0},function(index){
                //                 Layer.close(index);
                //                 $(".btn-refresh").trigger("click");
                //             });
                //         }
                //
                //     }
                // });

                /**
                 * 新增全款单
                 */
                $(document).on("click", ".btn-fulladd", function () {   
                        
                    var url = 'salesmanagement/Orderlisttabs/fulladd';
                    var options = {
                        shadeClose: false,
                        shade: [0.3, '#393D49'],
                        area:['95%','95%'],
                        callback:function(value){ 

                        }
                    }
                    Fast.api.open(url,'新增全款单',options)
                })
            },
            /**
             * 全款单（二手车）
             */
            second_order_full: function () {
                var secondOrderFull = $("#secondOrderFull");

                $(".btn-add").data("area", ["95%", "95%"]);
                $(".btn-edit").data("area", ["95%", "95%"]);

                // 初始化表格
                secondOrderFull.bootstrapTable({
                    url: 'salesmanagement/Orderlisttabs/secondOrderFull',
                    extend: {
                        fulladd_url: 'salesmanagement/Orderlisttabs/fulladd',
                        secondfulledit_url: 'salesmanagement/Orderlisttabs/secondfulledit',
                        del_url: 'salesmanagement/Orderlisttabs/secondfulldel',
                        multi_url: 'salesmanagement/Orderlisttabs/multi',
                        table: 'full_parment_order',
                    },
                    toolbar: '#toolbar5',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            { checkbox: true },
                            { field: 'id', title: __('Id') ,operate:false},
                            { field: 'order_no', title: __('Order_no') },
                            { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime, datetimeFormat:"YYYY-MM-DD" },
                            { field: 'models.name', title: __('销售车型') },
                            {field: 'admin.nickname', title: __('销售员'),formatter: Controller.api.formatter.sales},
                            { field: 'plansecondfull.totalprices', title: __('全款总价（元）') },

                            {
                                field: 'id', title: __('查看详细资料'), table: secondOrderFull, buttons: [
                                    {
                                        name: 'fulldetails', text: '查看详细资料', title: '查看订单详细资料', icon: 'fa fa-eye', classname: 'btn btn-xs btn-primary btn-dialog btn-secondfulldetails',
                                        url: 'Sharedetailsdatas/secondfull_car_share_data', callback: function (data) {

                                        }
                                    }
                                ],

                                operate: false, formatter: Table.api.formatter.buttons
                            },

                            {field: 'username', title: __('Username'),formatter: Controller.api.formatter.judge1, operate: false},
                            {field: 'phone', title: __('Phone')},
                            { field: 'delivery_datetime', title: __('Delivery_datetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,datetimeFormat:"YYYY-MM-DD" },

                            {
                                field: 'operate', title: __('Operate'), table: secondOrderFull,
                                buttons: [
                                    /**
                                     * 提交内勤
                                     */
                                    {
                                        name: 'secondfullinternal', text: '提交内勤', icon: 'fa fa-share', extend: 'data-toggle="tooltip"', title: __('提交内勤'), classname: 'btn btn-xs btn-info btn-secondfullinternal',

                                        hidden: function (row) { /**提交内勤 */
                                            if (row.review_the_data == 'send_to_internal') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }

                                        }
                                    },
                                    /**
                                     * 删除
                                     */
                                    {
                                        icon: 'fa fa-trash', name: 'secondfulldel', icon: 'fa fa-trash', extend: 'data-toggle="tooltip"', title: __('Del'), classname: 'btn btn-xs btn-danger btn-delone',

                                        hidden: function (row) {
                                            if (row.review_the_data == 'send_to_internal') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }

                                        },

                                    },
                                    /**
                                     * 编辑
                                     */
                                    {
                                        name: 'secondfulledit', text: '', icon: 'fa fa-pencil', extend: 'data-toggle="tooltip"', title: __('Edit'), classname: 'btn btn-xs btn-success btn-secondfulledit',

                                        hidden: function (row, value, index) {
                                            if (row.review_the_data == 'send_to_internal') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                        },
                                    },
                                    /**
                                     * 车管正在备车中
                                     */
                                    {
                                        name: 'is_reviewing_true', icon: 'fa fa-check-circle', text: '车管正在备车中', classname: ' text-info ',
                                        hidden: function (row) {  /**车管正在备车中 */
                                            if (row.review_the_data == 'is_reviewing_true') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }

                                        }
                                    },
                                    /**
                                     * 内勤正在处理
                                     */
                                    {
                                        name: 'inhouse_handling', text: '内勤正在处理',
                                        hidden: function (row) {  /**内勤正在处理 */
                                            if (row.review_the_data == 'inhouse_handling') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 车管备车成功，等待提车
                                     */
                                    {
                                        name: 'is_reviewing_pass', icon: 'fa fa-check-circle', text: '车管备车成功，等待提车', classname: ' text-info ',
                                        hidden: function (row) {  /**车管备车成功，等待提车 */
                                            if (row.review_the_data == 'is_reviewing_pass') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 已提车
                                     */
                                    {
                                        name: 'for_the_car', icon: 'fa fa-automobile', text: '已提车', extend: 'data-toggle="tooltip"', title: __('订单已完成，客户已提车'), classname: ' text-success ',
                                        hidden: function (row) {  /**已提车 */
                                            if (row.review_the_data == 'for_the_car') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_to_internal') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inhouse_handling') {
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

                /**
                 * 刷新表格渲染
                 */
                secondOrderFull.on('load-success.bs.table', function (e, data) {

                    // $('#badge_second_order_full').text(data.total);
                    $(".btn-secondfulldetails").data("area", ["95%", "95%"]);

                    var td = $("#secondOrderFull td:nth-child(5)");
    
                    for (var i = 0; i < td.length; i++) {
    
                        td[i].style.textAlign = "left";
    
                    }
                })

                /**
                 * 为全款单表格绑定事件
                 */
                Table.api.bindevent(secondOrderFull);

                /**
                 * 车管发送---销售接收----可以进行提车
                 */
                // goeasy.subscribe({
                //     channel: 'demo-secondfull_takecar',
                //     onMessage: function(message){
                //         message = split('|',message.content);
                //         if(Config.ADMIN_JS.id==message[1]){
                //             Layer.alert('新消息：'+message[0].content,{ icon:0},function(index){
                //                 Layer.close(index);
                //                 $(".btn-refresh").trigger("click");
                //             });
                //         }
                //
                //     }
                // });

                /**
                 * 新增全款单
                 */
                $(document).on("click", ".btn-secondfulladd", function () {

                    var url = 'salesmanagement/Orderlisttabs/secondfulladd';
                    var options = {
                        shadeClose: false,
                        shade: [0.3, '#393D49'],
                        area:['95%','95%'],
                        callback:function(value){

                        }
                    }
                    Fast.api.open(url,'新增全款（二手车）预定',options)
                })
            },
        },
        /**
         * 租车预定
         */
        reserve:function(){
            
            Table.api.init({
               
            });
            Form.api.bindevent($("form[role=form]"), function(data, ret){
            
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
         * 二手车预定
         */
        secondreserve:function(){

            $("button[type='submit']").on('click',function (v) {
                if($("#c-customer_source").val()=='turn_to_introduce'){
                    //||$("#c-turn_to_introduce_phone").val()
                    if($("#c-turn_to_introduce_name").val()==''){
                        Layer.msg('介绍人姓名不能为空',{icon:5});

                        $("#c-turn_to_introduce_name").css({'border-color':'red'})
                        return false;
                    }
                    if($("#c-turn_to_introduce_phone").val()==''){
                        Layer.msg('介绍人电话不能为空',{icon:5});

                        $("#c-turn_to_introduce_phone").css({'border-color':'red'})
                        return false;
                    }
                }
            })
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
        secondadd:function(){
            
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
        secondedit:function(){
            
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
         * 二手车补录资料
         */
        secondinformation:function(){
            
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
         * 二手车审核
         */
        secondaudit:function(){
            
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
        secondfulladd:function(){
            
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
        secondfulledit:function(){
            
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
         * 新车预定
         */
        newreserve:function(){

            //门店
            $(document).on("change", "#c-city_id", function () {

                $('#c-store_ids_text').val('');
            });
            $("#c-store_ids").data("params", function (obj) {

                return {custom: {city_id: $('#c-city_id').val()}};

            });
            $(document).on("change", "#c-store_ids", function () {

                $('#c-category_id_text').val('');
            });
            //门店下类别
            $("#c-category_id").data("params", function (obj) {

                return {custom: {store_id: $('#c-store_ids').val()}};

            });

            // return;
            $("button[type='submit']").on('click',function (v) {
               if($("#c-customer_source").val()=='turn_to_introduce'){
                   //||$("#c-turn_to_introduce_phone").val()
                   if($("#c-turn_to_introduce_name").val()==''){
                       Layer.msg('介绍人姓名不能为空',{icon:5});

                       $("#c-turn_to_introduce_name").css({'border-color':'red'})
                       return false;
                   }
                   if($("#c-turn_to_introduce_phone").val()==''){
                       Layer.msg('介绍人电话不能为空',{icon:5});

                       $("#c-turn_to_introduce_phone").css({'border-color':'red'})
                       return false;
                   }
               }
            })
            Form.api.bindevent($("form[role=form]"), function(data, ret){
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据

                Fast.api.close(data);//这里是重点

                Toastr.success("成功");//这个可有可无
            }, function(data, ret){

                Toastr.error("失败");

            });



            // Controller.api.bindevent();
 
        },
        /**
         * 新车预定资料修改
         */
        newreserveedit:function(){
            
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
         * 新车审核资料添加
         */
        newcontroladd:function(){
            
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
         * 新车提车资料完善
         */
        newinformation:function(){
            
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
         * 新车资料添加
         */
        add: function () {

            Controller.api.bindevent();
        },
        /**
         * 新车资料修改
         */
        edit: function () {
 
            Table.api.init({
               
            });
            Form.api.bindevent($("form[role=form]"), function(data, ret){
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                
                Fast.api.close(data);//这里是重点
                
                Toastr.success("成功");//这个可有可无
            }, function(data, ret){
                
                Toastr.success("失败");
                
            });
            Controller.api.bindevent();
        },
        /**
         * 新车补录资料
         */
        newcollectioninformation:function(){
            
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
                     * 新车提交内勤审核
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-submit_audit': function (e, value, row, index) {

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
                            __('请确认资料完整，是否开始提交给内勤?'),
                            { icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true },

                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');


                                Fast.api.ajax({

                                    url: 'salesmanagement/orderlisttabs/sedAudit',
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

                    /**
                     * 新车补录资料
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-newinformation': function (e, value, row, index) { /**新车补录资料 */
                        $(".btn-newinformation").data("area", ["95%", "95%"]);

                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, { ids: ids });
                        var url = options.extend.newcollectioninformation_url;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('补录资料上传'), $(this).data() || {});
                    },

                    /**
                     * 租车客户信息的补全
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-customerInformation': function (e, value, row, index) {

                        $(".btn-customerInformation").data("area", ["95%", "95%"]);

                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = [options.pk];
                        row = $.extend({}, row ? row : {}, { ids:ids}); 
                        var url = 'salesmanagement/Orderlisttabs/rentaladd';

                        Fast.api.open(Table.api.replaceurl(url,row, table), __('补全客户信息'), $(this).data() || {});

                    },

                    /**
                     * 租车提交风控审核
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-control': function (e, value, row, index) {

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
                            __('请确认资料完整，是否开始提交风控审核?'),
                            { icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true },

                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');

                                Fast.api.ajax({

                                    url: 'salesmanagement/Orderlisttabs/control',
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

                    /**
                     * 二手车提交内勤
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-second_audit': function (e, value, row, index) {

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
                            __('请确认资料完整，是否开始提交审核?'),
                            { icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true },

                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');

                                Fast.api.ajax({

                                    url: 'salesmanagement/orderlisttabs/setAudit',
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

                    /**
                     * 全款新车提交内勤
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-submitCar': function (e, value, row, index) {

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
 
                            __('请确认资料完整并发送给内勤生成提车单?'),
 
                            { icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true },

                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');
                                Fast.api.ajax({

                                    url: 'salesmanagement/orderlisttabs/submitCar',
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

                    /**
                     * 全款二手车提交内勤
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-secondfullinternal': function (e, value, row, index) {

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
 
                            __('请确认资料完整并发送给内勤生成提车单?'),
 
                            { icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true },

                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');
                                Fast.api.ajax({

                                    url: 'salesmanagement/orderlisttabs/secondfullinternal',
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

                    /**
                     * 编辑预定按钮
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-newreserveeditone': function (e, value, row, index) { /**编辑预定按钮 */
                        $(".btn-newreserveeditone").data("area", ["95%", "95%"]);

                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, { ids: ids });
                        var url = options.extend.newreserveedit_url;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('编辑预定'), $(this).data() || {});
                    },

                    /**
                     * 录入审核资料按钮
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-newcontroladd': function (e, value, row, index) { /**录入审核资料按钮 */
                        $(".btn-newcontroladd").data("area", ["95%", "95%"]);

                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, { ids: ids });
                        var url = options.extend.newcontroladd_url;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('录入审核资料'), $(this).data() || {});
                    },

                    /**
                     * 录入客户提车资料按钮
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-newinformation': function (e, value, row, index) { /**录入客户提车资料按钮 */
                        $(".btn-newinformation").data("area", ["95%", "95%"]);

                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, { ids: ids });
                        var url = options.extend.newinformation_url;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('补全提车客户资料'), $(this).data() || {});
                    },

                    /**
                     * 资料补全，提交车管提车按钮
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                     'click .btn-newinform_tube': function (e, value, row, index) { /**资料补全，提交车管提车 */
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
                            __('请确认资料完整，是否开始提交给车管，进行提车?'),
                            { icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true },

                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');

                                Fast.api.ajax({

                                    url: 'salesmanagement/orderlisttabs/newinformtube',
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

                    /**
                     * 编辑按钮
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-editone': function (e, value, row, index) { /**编辑按钮 */
                        $(".btn-editone").data("area", ["95%", "95%"]);

                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, { ids: ids });
                        var url = options.extend.edit_url+'/posttype/edit';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
                    },

                    /**
                     * 租车编辑按钮
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-rentaleditone': function (e, value, row, index) { /**二手车编辑按钮 */
                        $(".btn-rentaleditone").data("area", ["95%", "95%"]);

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
                     * 租车补录资料
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-rentalinformation': function (e, value, row, index) { /**租车补录资料 */
                        $(".btn-rentalinformation").data("area", ["95%", "95%"]);

                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, { ids: ids });
                        var url = options.extend.rentalinformation_url;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('补录资料上传'), $(this).data() || {});
                    },


                    /**
                     * 二手车编辑按钮
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-secondeditone': function (e, value, row, index) { /**二手车编辑按钮 */
                        $(".btn-secondeditone").data("area", ["95%", "95%"]);

                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, { ids: ids });
                        var url = options.extend.secondedit_url+'/posttype/edit';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('Edit'), $(this).data() || {});
                    },

                    /**
                     * 二手车审核资料上传
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-secondaudit': function (e, value, row, index) { /**二手车审核资料上传 */
                        $(".btn-secondaudit").data("area", ["95%", "95%"]);

                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, { ids: ids });
                        var url = options.extend.secondaudit_url;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('审核资料上传'), $(this).data() || {});
                    },

                    /**
                     * 二手车补录资料
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-secondinformation': function (e, value, row, index) { /**二手车审核资料上传 */
                        $(".btn-secondinformation").data("area", ["95%", "95%"]);

                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, { ids: ids });
                        var url = options.extend.secondinformation_url;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('补录资料上传'), $(this).data() || {});
                    },

                    /**
                     * 全款新车编辑按钮
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-fulleditone': function (e, value, row, index) { /**二手车编辑按钮 */
                        $(".btn-fulleditone").data("area", ["95%", "95%"]);

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
                     * 全款二手车编辑按钮
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-secondfulledit': function (e, value, row, index) { /**二手车编辑按钮 */
                        $(".btn-secondfulledit").data("area", ["95%", "95%"]);

                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, { ids: ids });
                        var url = options.extend.secondfulledit_url;
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

                    /**
                     * 提交保证金
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-the_guarantor': function (e, value, row, index) { /**提交保证金 */
                        $(".btn-the_guarantor").data("area", ["95%", "95%"]); 
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = [options.pk];
                        row = $.extend({}, row ? row : {}, { ids:ids}); 
                        var url = options.extend.edit_url+'/posttype/the_guarantor';  
                        Fast.api.open(Table.api.replaceurl(url,row, table), __('请上传保证金收据'), $(this).data() || {});
                    },

                    /**
                     * 二手车提交保证金
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-secondthe_guarantor': function (e, value, row, index) { /**二手车提交保证金 */
                        $(".btn-secondthe_guarantor").data("area", ["95%", "95%"]); 
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = [options.pk];
                        row = $.extend({}, row ? row : {}, { ids:ids}); 
                        var url = options.extend.secondedit_url+'/posttype/the_guarantor';  
                        Fast.api.open(Table.api.replaceurl(url,row, table), __('请上传保证金收据'), $(this).data() || {});
                    },


                    'click .btn-delones': function (e, value, row, index) {
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
                            __('是否真的要删除该条订单(包括该条订单所有相关信息)?'),
                            {icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true},
                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');

                               var text = $('ul.nav-tabs li.active a[data-toggle="tab"]').text();

                                Layer.close(index);

                                layer.prompt({
                                    formType: 1,

                                    title: '请输入删除密码',
                                }, function(value, indexs, elem){

                                    if(value == 'aicheyide'){

                                        var flag = 1;
                                        if(text.indexOf('以租代购（新车）')>-1){
                                            flag = -1;
                                        }else if(text.indexOf('纯租')>-1){
                                            flag = -2;
                                        }else if(text.indexOf('以租代购（二手车）')>-1){
                                            flag = -3;
                                        }else if(text.indexOf('全款（新车）')>-1){

                                            flag = -4;
                                        }else{
                                            flag = -5;
                                        }

                                        Fast.api.ajax({
                                            url:'salesmanagement/Orderlisttabs/del_order',
                                            data:{
                                                flag:flag,
                                                id:row[options.pk]
                                            }
                                        },function (data,ret) {

                                            layer.msg('删除成功！');

                                            Layer.close(indexs);
                                            table.bootstrapTable('refresh');
                                        },function (data,ret) {


                                        })

                                    }else{
                                        layer.msg('密码输入错误');
                                    }
                                });


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

                    if(Config.ADMIN_JS.rule_message == 'message21'){
                        buttons.push({
                            name: 'del',
                            text: '管理员删除',
                            icon: 'fa fa-trash',
                            title: __('Del'),
                            extend: 'data-toggle="tooltip"',
                            classname: 'btn btn-xs btn-danger btn-delones'
                        });
                    }

                    return Table.api.buttonlink(this, buttons, value, row, index, 'operate');
                },
                sales: function (value, row, index) {
                    // console.log(row);

                    return value == null ? value : "<img src=" + Config.cdn_url + row.admin.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' + row.admin.department + ' - ' + value;
                },
                datetime: function (value, row, index) {

                    if(value){
                        return timestampToTime(value);
                    }
                    function timestampToTime(timestamp) {
                        var date = new Date(timestamp * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
                        var Y = date.getFullYear() + '-';
                        var M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
                        var D = date.getDate()<10? '0'+date.getDate():date.getDate();

                        return Y+M+D;
                    }
                },
                /**
                 * 退租时间
                 * @param value
                 * @param row
                 * @param index
                 * @returns {string}
                 */
                car_back:function (value, row, index) {
                    if(value && row.tenancy_term){
                        value = timestampToTime(value);

                         return GetNextMonthDay(value,row.tenancy_term);
                    }

                    //获取几个月后的日期
                     function GetNextMonthDay(date, monthNum){
                         var dateArr = date.split('-');
                         var year = dateArr[0]; //获取当前日期的年份
                         var month = dateArr[1]; //获取当前日期的月份
                         var day = dateArr[2]; //获取当前日期的日
                         var days = new Date(year, month, 0);
                         days = days.getDate(); //获取当前日期中的月的天数
                         var year2 = year;
                         var month2 = parseInt(month) + parseInt(monthNum);
                         if (month2 >12) {
                             year2 = parseInt(year2) + parseInt((parseInt(month2) / 12 == 0 ? 1 : parseInt(month2) / 12));
                             month2 = parseInt(month2) % 12;
                         }
                         var day2 = day;
                         var days2 = new Date(year2, month2, 0);
                         days2 = days2.getDate();
                         if (day2 > days2) {
                             day2 = days2;
                         }
                         if (month2 < 10) {
                             month2 = '0' + month2;
                         }

                         var t2 = year2 + '-' + month2 + '-' + day2;
                         return t2;
                     }

                    function timestampToTime(timestamp) {
                        var date = new Date(timestamp * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
                        var Y = date.getFullYear() + '-';
                        var M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
                        var D = date.getDate()<10? '0'+date.getDate():date.getDate();

                        return Y+M+D;
                    }
                },
                /**
                 * 提车返回√
                 * @param value
                 * @returns {string}
                 */
                judge: function (value, row, index) {

                    var res = "";
                    var color = "";
                   
                   if(row.review_the_data == 'the_car'){
                        res = "<i class='fa fa-check'></i>"
                        color = "success";
                    
                    }

                    //渲染状态
                    var html = '<span class="text-' + color + '"> ' + row.username +  __(res) + '</span>';

                    return html;

                },
                /**
                 * 提车返回√
                 * @param value
                 * @returns {string}
                 */
                judge1: function (value, row, index) {

                    var res = "";
                    var color = "";
                   
                   if(row.review_the_data == 'for_the_car'){
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
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    // var goeasy = new GoEasy({
    //     appkey: 'BC-04084660ffb34fd692a9bd1a40d7b6c2'
    // });

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
            })


        },


        table: {
            /**
             * 新车录入定金
             */
            newcar_entry: function () {
                //
                var newcarEntry = $("#newcarEntry");
               
                // 初始化表格
                newcarEntry.bootstrapTable({
                    url: 'backoffice/carreservation/newcarEntry',
                    extend: {
                        // edit_url: 'backoffice/carreservation/newactual_amount',
                        // table: 'sales_order',
                    },
                    toolbar: '#toolbar1',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: '编号',operate:false},
                            {field: 'createtime', title: __('订车日期'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime },
                            {field: 'admin.nickname', title: __('销售员')},
                            {field: 'username', title: __('客户姓名')},
                            // {field: 'id_card', title: __('身份证号')},
                            // {field: 'city', title: __('居住地址'),operate:false},
                            // {field: 'detailed_address', title: __('详细地址'),operate:false},
                            {field: 'phone', title: __('联系电话')},
                            {field: 'models.name', title: __('订车车型')},
                            {field: 'planacar.payment', title: __('首付(元)'),operate:false},
                            {field: 'planacar.monthly', title: __('月供(元)'),operate:false},
                            {field: 'planacar.nperlist', title: __('期数'),operate:false},
                            {field: 'planacar.margin', title: __('保证金(元)'),operate:false},
                            {field: 'planacar.tail_section', title: __('尾款(元)'),operate:false},
                            {field: 'planacar.gps', title: __('GPS(服务费)'),operate:false},
                            {field: 'car_total_price', title: __('车款总价(元)'),operate:false},
                            {field: 'downpayment', title: __('首期款(元)'),operate:false},
                            {field: 'newinventory.household', title: __('行驶证所有户')},
                            {field: 'newinventory.4s_shop', title: __('4S店')},
                            {
                                field: 'operate', title: __('Operate'), table: newcarEntry,
                                buttons: [
                                   
                                    {
                                        name: 'newactual_amount', text: '录入实际订车金额', title: __('录入实际订车金额'), icon: 'fa fa-pencil', extend: 'data-toggle="tooltip"', classname: 'btn btn-xs btn-danger btn-newactual_amount',
                                        url: 'backoffice/carreservation/newactual_amount',
                                        hidden: function (row) {  /**录入实际订车金额 */
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
                                    {
                                        name: 'send_car_tube', text: '已录入实际订车金额，车管正在处理中',
                                        hidden: function (row) {  /**车管正在处理中 */
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
                                    {
                                        name: 'is_reviewing_true', text: '风控正在审核中',
                                        hidden: function (row) {  /**风控正在审核中 */
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
                                    {
                                        name: 'is_reviewing', text: '正在匹配金融',
                                        hidden: function (row) {  /**正在匹配金融 */
                                            if (row.review_the_data == 'is_reviewing') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'the_financial') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
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
                                    {
                                        name: 'for_the_car', icon: 'fa fa-check-circle', text: '征信已通过', classname: ' text-info ',
                                        hidden: function (row) {  /**征信已通过 */
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
                                    {
                                        name: 'conclude_the_contract', icon: 'fa fa-check-circle', text: '客户在签订金融合同', classname: ' text-info ',
                                        hidden: function (row) {  /**客户在签订金融合同*/
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
                                    {
                                        name: 'is_reviewing_pass', icon: 'fa fa-check-circle', text: '征信已通过', classname: ' text-info ',
                                        hidden: function (row) {  /**征信已通过 */
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
                                    {
                                        name: 'take_the_car', icon: 'fa fa-check-circle', text: '风控匹配车辆', classname: ' text-info ',
                                        hidden: function (row) {  /**风控匹配车辆*/
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
                                    {
                                        name: 'take_the_data', text: '销售正在补全客户资料',  extend: 'data-toggle="tooltip"', title: __('销售正在补全客户资料'), classname: 'text-info',
                                        
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
                                    {
                                        name: 'inform_the_tube', text: '销售资料补全，准备提交车管',  extend: 'data-toggle="tooltip"', title: __('销售资料补全，准备提交车管'), classname: 'text-info',
                                        
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
                                            else if (row.review_the_data == 'is_reviewing') {
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
                                    {
                                        name: 'collection_data', icon: 'fa fa-times', text: '征信不通过，待补录资料', classname: ' text-danger ',
                                        hidden: function (row) {  /**征信不通过，待补录资料 */

                                            if (row.review_the_data == 'collection_data') {
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
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                        }
                                    },
                                    {
                                        name: 'the_guarantor', icon: 'fa fa-upload', text: '需交保证金', extend: 'data-toggle="tooltip"', title: __('点击上传保证金收据'), classname: 'text-info',
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
                                            else if (row.review_the_data == 'the_financial') {
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
                                            else if (row.review_the_data == 'the_financial') {
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
                // 为表格1绑定事件
                Table.api.bindevent(newcarEntry);

                //数据实时统计
                newcarEntry.on('load-success.bs.table',function(e,data){ 

                    $(".btn-newactual_amount").data("area", ["50%", "40%"]);
                    // var newcarEntry =  $('#badge_newcar_entry').text(data.total); 
                    // newcarEntry = parseInt($('#badge_newcar_entry').text());
                    
                   
                })

                //销售推送
                // goeasy.subscribe({
                //     channel: 'demo-sales',
                //     onMessage: function (message) {
                //         Layer.alert('新消息：' + message.content, {icon: 0}, function (index) {
                //             Layer.close(index);
                //             $(".btn-refresh").trigger("click");
                //         });
                //
                //     }
                // });


            },
            /**
             * 二手车录入定金
             */
            secondcar_entry: function () {

                var secondcarEntry = $("#secondcarEntry");
               
                // 初始化表格
                secondcarEntry.bootstrapTable({
                    url: 'backoffice/carreservation/secondcarEntry',
                    extend: {
                        // edit_url: 'backoffice/carreservation/secondactual_amount',
                        table: 'second_sales_order',
                    },
                    toolbar: '#toolbar2',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: '编号',operate:false},
                            {field: 'createtime', title: __('订车日期'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                            {field: 'admin.nickname', title: __('销售员')},
                            {field: 'plansecond.companyaccount', title: __('所属公司户')},
                            {field: 'username', title: __('客户姓名')},
                            // {field: 'id_card', title: __('身份证号')},
                            // {field: 'city', title: __('居住地址'),operate:false},
                            // {field: 'detailed_address', title: __('详细地址'),operate:false},
                            {field: 'phone', title: __('联系电话')},
                            {field: 'models.name', title: __('订车车型')},
                            {field: 'plansecond.newpayment', title: __('首付(元)'),operate:false},
                            {field: 'plansecond.monthlypaymen', title: __('月供(元)'),operate:false},
                            {field: 'plansecond.periods', title: __('期数'),operate:false},
                            {field: 'plansecond.bond', title: __('保证金(元)'),operate:false},
                            {field: 'plansecond.tailmoney', title: __('尾款(元)'),operate:false},
                            {field: 'plansecond.totalprices', title: __('车款总价(元)'),operate:false},
                            {field: 'downpayment', title: __('首期款(元)'),operate:false},
                            {
                                field: 'operate', title: __('Operate'), table: secondcarEntry,
                                buttons: [
                                    
                                    {
                                        name: 'secondactual_amount', text: '录入实际订车金额', title: '录入实际订车金额', icon: 'fa fa-pencil', extend: 'data-toggle="tooltip"', classname: 'btn btn-xs btn-info btn-secondactual_amount',
                                        url: 'backoffice/carreservation/secondactual_amount',
                                        hidden: function (row) {  /**录入实际订车金额 */
                                            if (row.review_the_data == 'is_reviewing_true') {
                                                return false;
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
                                    {
                                        name: 'is_reviewing_control', text: '风控正在审核中',
                                        hidden: function (row) {  /**风控正在审核中 */
                                            if (row.review_the_data == 'is_reviewing_control') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_finance') {
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
                                    {
                                        name: 'is_reviewing_finance', text: '正在匹配金融',
                                        hidden: function (row) {  /**正在匹配金融 */
                                            if (row.review_the_data == 'is_reviewing_finance') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
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
                                    {
                                        name: 'send_car_tube', text: '已录入实际订车金额,车管正在处理中',
                                        hidden: function (row) {  /**已录入实际订车金额,车管正在处理中 */
                                            if (row.review_the_data == 'send_car_tube') {
                                                return false;
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
                                    {
                                        name: 'is_reviewing_pass', icon: 'fa fa-check-circle', text: '风控正在匹配车辆', classname: ' text-info ',
                                        hidden: function (row) {  /**风控正在匹配车辆 */
                                            if (row.review_the_data == 'is_reviewing_pass') {
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
                                    {
                                        name: 'for_the_car', icon: 'fa fa-check-circle', text: '车管备车中，通知客户可以进行提车', classname: ' text-info ',
                                        hidden: function (row) {  /**通知客户可以进行提车 */
                                            if (row.review_the_data == 'for_the_car') {
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
                                    {
                                        name: 'collection_data', icon: 'fa fa-times', text: '征信不通过，待补录资料', classname: ' text-danger ',
                                        hidden: function (row) {  /**征信不通过，待补录资料 */

                                            if (row.review_the_data == 'collection_data') {
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
                                            else if (row.review_the_data == 'the_guarantor') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                        }
                                    },
                                    {
                                        name: 'the_guarantor', text: '需交保证金',  title: __('点击上传保证金收据'), classname: 'text-info',
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
                // 为表格1绑定事件
                Table.api.bindevent(secondcarEntry);

                //数据实时统计
                secondcarEntry.on('load-success.bs.table',function(e,data){ 

                    $(".btn-secondactual_amount").data("area", ["50%", "40%"]);
                    // var secondcarEntry =  $('#badge_secondcar_entry').text(data.total); 
                    // secondcarEntry = parseInt($('#badge_secondcar_entry').text());
                    
                   
                })

                //销售推送
                // goeasy.subscribe({
                //     channel: 'demo-second_backoffice',
                //     onMessage: function (message) {
                //         Layer.alert('新消息：' + message.content, {icon: 0}, function (index) {
                //             Layer.close(index);
                //             $(".btn-refresh").trigger("click");
                //         });
                //
                //     }
                // });

            },
            /**
             * 全款车录入定金
             */
            fullcar_entry: function () {

                var fullcarEntry = $("#fullcarEntry");
               
                // 初始化表格
                fullcarEntry.bootstrapTable({
                    url: 'backoffice/carreservation/fullcarEntry',
                    extend: {
                        table: 'full_parment_order',
                    },
                    toolbar: '#toolbar3',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: '编号',operate:false},
                            {field: 'createtime', title: __('订车日期'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                            {field: 'admin.nickname', title: __('销售员')},
                            {field: 'models.name', title: __('订车车型')},
                            {field: 'planfull.full_total_price', title: __('车款总价(元)'),operate:false},
                            {field: 'username', title: __('客户姓名')},
                            // {field: 'id_card', title: __('身份证号')},
                            {field: 'phone', title: __('联系电话')},
                            // {field: 'city', title: __('居住地址'),operate:false},
                            // {field: 'detailed_address', title: __('详细地址'),operate:false},
                           
                            {
                                field: 'operate', title: __('Operate'), table: fullcarEntry,
                                buttons: [
                                    {
                                        name: 'is_reviewing_true', icon: 'fa fa-check-circle', text: '已录入实际订车金额,车管正在备车中', classname: ' text-info ',
                                        hidden: function (row) {  /**已录入实际订车金额,车管正在备车中 */
                                            if (row.review_the_data == 'is_reviewing_true') {
                                                return false;
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
                                    {
                                        name: 'fullactual_amount', text: '录入实际订车金额', title: '录入实际订车金额', icon: 'fa fa-pencil', extend: 'data-toggle="tooltip"', classname: 'btn btn-xs btn-info btn-fullactual_amount',
                                        url: 'backoffice/carreservation/fullactual_amount',
                                       
                                        hidden: function (row) { /**录入实际订车金额 */
                                            if (row.review_the_data == 'inhouse_handling') {
                                                return false;
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
                                    {
                                        name: 'is_reviewing_pass', icon: 'fa fa-check-circle', text: '车管备车成功，等待提车', classname: ' text-info ',
                                        hidden: function (row) {  /**车管备车成功，等待提车 */
                                            if (row.review_the_data == 'is_reviewing_pass') {
                                                return false;
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
                // 为表格1绑定事件
                Table.api.bindevent(fullcarEntry);

                //数据实时统计
                fullcarEntry.on('load-success.bs.table',function(e,data){ 

                    $(".btn-fullactual_amount").data("area", ["50%", "40%"]);
                    // var fullcarEntry =  $('#badge_fullcar_entry').text(data.total); 
                    // fullcarEntry = parseInt($('#badge_fullcar_entry').text());
                    
                   
                })

                //销售推送
                // goeasy.subscribe({
                //     channel: 'demo-full_backoffice',
                //     onMessage: function (message) {
                //         Layer.alert('新消息：' + message.content, {icon: 0}, function (index) {
                //             Layer.close(index);
                //             $(".btn-refresh").trigger("click");
                //         });
                //
                //     }
                // });


            },
            /**
             * 租车录入定金
             */
            rentalcar_entry: function () {

                var rentalcarEntry = $("#rentalcarEntry");
               
                // 初始化表格
                rentalcarEntry.bootstrapTable({
                    url: 'backoffice/carreservation/rentalcarEntry',
                    extend: {
                        table: 'rental_order',
                    },
                    toolbar: '#toolbar4',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: '编号',operate:false},
                            {field: 'createtime', title: __('订车日期'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                            {field: 'admin.nickname', title: __('销售员')},
                            {field: 'models.name', title: __('车型')},
                            {field: 'carrentalmodelsinfo.licenseplatenumber', title: __('车牌号')},
                            {field: 'carrentalmodelsinfo.vin', title: __('车架号')},
                            {field: 'username', title: __('客户姓名')},
                            // {field: 'id_card', title: __('身份证号')},
                            {field: 'phone', title: __('联系电话')},
                            {field: 'models.name', title: __('订车车型')},
                            {field: 'cash_pledge', title: __('押金（元）'),operate:false},
                            {field: 'rental_price', title: __('租金（元）'),operate:false},
                            {field: 'tenancy_term', title: __('租期（元）'),operate:false},
                            {field: 'delivery_datetime', title: __('开始租车日期'),operate:false,formatter:Controller.api.formatter.datetime},
                            {field: 'delivery_datetime', title: __('应退车日期'),operate:false,formatter:Controller.api.formatter.car_back},
                            {
                                field: 'operate', title: __('Operate'), table: rentalcarEntry,
                                buttons: [
                                    /**
                                     * 销售正在补全客户资料
                                     */
                                    {
                                        name:'is_reviewing_argee',text:'销售正在补全客户资料', title:'销售正在补全客户资料', classname: 'text-info',
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
                                            else if(row.review_the_data == 'collection_data'){
                                                return true;
                                            }
                                            else if(row.review_the_data == 'retiring'){
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 资料补全，准备提交风控
                                     */
                                    {
                                        name:'control',text:'资料补全，准备提交风控', title:'资料补全，准备提交风控', classname: 'text-info',
                                        // url: 'order/rentalorder/control',  
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
                                            else if(row.review_the_data == 'collection_data'){
                                                return true;
                                            }
                                            else if(row.review_the_data == 'retiring'){
                                                return true;
                                            }
                                        }
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
                                            else if(row.review_the_data == 'collection_data'){
                                                return true;
                                            }
                                            else if(row.review_the_data == 'retiring'){
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
                                            else if(row.review_the_data == 'collection_data'){
                                                return true;
                                            }
                                            else if(row.review_the_data == 'retiring'){
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
                                            else if(row.review_the_data == 'collection_data'){
                                                return true;
                                            }
                                            else if(row.review_the_data == 'retiring'){
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
                                            else if(row.review_the_data == 'collection_data'){
                                                return true;
                                            }
                                            else if(row.review_the_data == 'retiring'){
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 征信不通过，待补录资料
                                     */
                                    {
                                        name: 'collection_data', icon: 'fa fa-times', text: '征信不通过，待补录资料', classname: ' text-danger ',
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
                                            else if(row.review_the_data == 'is_reviewing_nopass'){
                                                return true;
                                            }
                                            else if(row.review_the_data == 'retiring'){
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
                                            else if(row.review_the_data == 'collection_data'){
                                                return true;
                                            }
                                            else if(row.review_the_data == 'retiring'){
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
                                            else if(row.review_the_data == 'collection_data'){
                                                return true;
                                            }
                                            else if(row.review_the_data == 'for_the_car'){
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
                // 为表格1绑定事件
                Table.api.bindevent(rentalcarEntry);

                //数据实时统计
                rentalcarEntry.on('load-success.bs.table',function(e,data){ 

                    $(".btn-rentalactual_amount").data("area", ["50%", "40%"]);
                    // var secondcarEntry =  $('#badge_rentalcar_entry').text(data.total); 
                    // secondcarEntry = parseInt($('#badge_rentalcar_entry').text());
                    
                   
                })

                //销售推送
                // goeasy.subscribe({
                //     channel: 'demo-second_backoffice',
                //     onMessage: function (message) {
                //         Layer.alert('新消息：' + message.content, {icon: 0}, function (index) {
                //             Layer.close(index);
                //             $(".btn-refresh").trigger("click");
                //         });
                //
                //     }
                // });

            },
            /**
             * 全款二手车录入定金
             */
            secondfullcar_entry: function () {

                var secondfullcarEntry = $("#secondfullcarEntry");
               
                // 初始化表格
                secondfullcarEntry.bootstrapTable({
                    url: 'backoffice/carreservation/secondfullcarEntry',
                    extend: {
                        table: 'second_full_order',
                    },
                    toolbar: '#toolbar5',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: '编号',operate:false},
                            {field: 'createtime', title: __('订车日期'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                            {field: 'admin.nickname', title: __('销售员')},
                            {field: 'models.name', title: __('订车车型')},
                            {field: 'plansecondfull.totalprices', title: __('车款总价(元)'),operate:false},
                            {field: 'username', title: __('客户姓名')},
                            // {field: 'id_card', title: __('身份证号')},
                            {field: 'phone', title: __('联系电话')},
                            // {field: 'city', title: __('居住地址'),operate:false},
                            // {field: 'detailed_address', title: __('详细地址'),operate:false},
                           
                            {
                                field: 'operate', title: __('Operate'), table: secondfullcarEntry,
                                buttons: [
                                    {
                                        name: 'is_reviewing_true', icon: 'fa fa-check-circle', text: '已录入实际订车金额,车管正在备车中', classname: ' text-info ',
                                        hidden: function (row) {  /**已录入实际订车金额,车管正在备车中 */
                                            if (row.review_the_data == 'is_reviewing_true') {
                                                return false;
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
                                    {
                                        name: 'secondfullactual_amount', text: '录入实际订车金额', title: '录入实际订车金额', icon: 'fa fa-pencil', extend: 'data-toggle="tooltip"', classname: 'btn btn-xs btn-info btn-secondfullactual_amount',
                                        url: 'backoffice/carreservation/secondfullactual_amount',
                                       
                                        hidden: function (row) { /**录入实际订车金额 */
                                            if (row.review_the_data == 'inhouse_handling') {
                                                return false;
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
                                    {
                                        name: 'is_reviewing_pass', icon: 'fa fa-check-circle', text: '车管备车成功，等待提车', classname: ' text-info ',
                                        hidden: function (row) {  /**车管备车成功，等待提车 */
                                            if (row.review_the_data == 'is_reviewing_pass') {
                                                return false;
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
                // 为表格1绑定事件
                Table.api.bindevent(secondfullcarEntry);

                //数据实时统计
                secondfullcarEntry.on('load-success.bs.table',function(e,data){ 

                    $(".btn-secondfullactual_amount").data("area", ["50%", "40%"]);
                    // var secondfullcarEntry =  $('#badge_secondfullcar_entry').text(data.total); 
                    // secondfullcarEntry = parseInt($('#badge_fullcar_entry').text());
                    
                   
                })

                //销售推送
                // goeasy.subscribe({
                //     channel: 'demo-second_full_backoffice',
                //     onMessage: function (message) {
                //         Layer.alert('新消息：' + message.content, {icon: 0}, function (index) {
                //             Layer.close(index);
                //             $(".btn-refresh").trigger("click");
                //         });
                //
                //     }
                // });


            },


        },
        add: function () {
            Controller.api.bindevent();

        },
        edit: function () {
            Controller.api.bindevent();
        },

        newactual_amount: function () {
            Controller.api.bindevent();

            Table.api.init({});
            Form.api.bindevent($("form[role=form]"), function (data, ret) {


                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                Fast.api.close(data);//这里是重点
                // console.log(data);
                Toastr.success("成功");//这个可有可无
            }, function (data, ret) {


                Toastr.success("失败");

            });


        },
        secondactual_amount: function () {
            Controller.api.bindevent();
            
            // $(".btn-add").data("area", ["300px","200px"]);
            Table.api.init({});
            Form.api.bindevent($("form[role=form]"), function (data, ret) {


                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                Fast.api.close(data);//这里是重点
                Toastr.success("成功");//这个可有可无
            }, function (data, ret) {


                Toastr.success("失败");

            });


        },
        fullactual_amount: function () {
            Controller.api.bindevent();
            
            // $(".btn-add").data("area", ["300px","200px"]);
            Table.api.init({});
            Form.api.bindevent($("form[role=form]"), function (data, ret) {


                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                Fast.api.close(data);//这里是重点
                // console.log(data);
                Toastr.success("成功");//这个可有可无
            }, function (data, ret) {


                Toastr.success("失败");

            });
            // Controller.api.bindevent();
            // console.log(Config.id);

        },
        secondfullactual_amount: function () {
            Controller.api.bindevent();
            
            // $(".btn-add").data("area", ["300px","200px"]);
            Table.api.init({});
            Form.api.bindevent($("form[role=form]"), function (data, ret) {


                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                Fast.api.close(data);//这里是重点
                // console.log(data);
                Toastr.success("成功");//这个可有可无
            }, function (data, ret) {


                Toastr.success("失败");

            });
            // Controller.api.bindevent();
            // console.log(Config.id);

        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
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
                 * 退车时间
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


                    /**
                     * 获取几个月后的日期
                     * @param date
                     * @param monthNum
                     * @returns {string}
                     * @constructor
                     */
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

                }
            },
            
            events: {
                operate: {

                    /**
                     * 新车录入订车金额
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-newactual_amount': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];

                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'backoffice/carreservation/newactual_amount';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('录入实际订车金额'), $(this).data() || {});
                    },

                    /**
                     * 二手车录入订车金额
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-secondactual_amount': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];

                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'backoffice/carreservation/secondactual_amount';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('录入实际订车金额'), $(this).data() || {});
                    },

                    /**
                     * 全款新车录入订车金额
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-fullactual_amount': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];

                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'backoffice/carreservation/fullactual_amount';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('录入实际订车金额'), $(this).data() || {});
                    },

                    /**
                     * 全款二手车录入订车金额
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-secondfullactual_amount': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];

                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'backoffice/carreservation/secondfullactual_amount';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('录入实际订车金额'), $(this).data() || {});
                    },
                    
                }
            }
        }

    };
    return Controller;
});
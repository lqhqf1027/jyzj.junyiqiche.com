define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'echarts', 'echarts-theme', 'addtabs'], function ($, undefined, Backend, Table, Form, Echarts, undefined, Template) {
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
        },
        /**
         * 大数据
         */
        bigdata: function () {
            //欺诈评分图表

            var myChart = Echarts.init(document.getElementById('echart'));
            // 指定图表的配置项和数据
            var option = {
                tooltip: {
                    formatter: "{a} <br/><br/>{b} : {c}"
                },
                toolbox: {
                    feature: {
                        restore: {},
                        saveAsImage: {}
                    }
                },

                series: [
                    {
                        name: '欺诈评分',
                        type: 'gauge',
                        detail: {formatter: ' {value}'},
                        data: [{value: Config.zcFraudScore, name: '欺诈评分'}],
                        axisLine: {
                            lineStyle: {
                                color: [[0.2, 'lime'], [0.70, '#1e90ff'], [4, '#ff4500']],
                                width: 3,
                                shadowColor: '#fff', //默认透明
                                shadowBlur: 10

                            }
                        },
                        splitLine: {
                            show: false,
                        },
                        axisTick: {
                            show: false,
                            length: 0
                        },
                        axisLabel: {
                            show: false,
                            length: 0
                        }
                    }
                ],
            };

            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);
        },
        table: {
            /**新车 */
            newcar_audit: function () {
                // 待审核
                var newcarAudit = $("#newcarAudit");
                console.log(newcarAudit);
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "快速搜索:客户姓名";};
                // 初始化表格 
                newcarAudit.bootstrapTable({
                    url: 'riskcontrol/creditreview/newcarAudit',

                    extend: {

                        table: 'sales_order',
                    },
                    toolbar: '#toolbar1',
                    pk: 'id',
                    sortName: 'id',
                    // search: false,
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'), operate: false},
                            {field: 'order_no', title: __('Order_no')},
                            {
                                field: 'createtime',
                                title: __('Createtime'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime,
                                datetimeFormat: 'YYYY-MM-DD'
                            },

                            {field: 'financial_name', title: __('金融平台')},
                            {field: 'models.name', title: __('销售车型')},
                            {field: 'admin.nickname', title: __('销售员'), formatter: Controller.api.formatter.sales},
                            {
                                field: 'id', title: __('查看详细资料'), table: newcarAudit, buttons: [
                                    {
                                        name: 'newcardetails',
                                        text: '查看详细资料',
                                        title: '查看订单详细资料',
                                        icon: 'fa fa-eye',
                                        classname: 'btn btn-xs btn-primary btn-dialog btn-newcardetails',
                                        url: 'Sharedetailsdatas/new_car_share_data',
                                        callback: function (data) {
                                            // console.log(data)
                                        }
                                    }
                                ],

                                operate: false, formatter: Table.api.formatter.buttons
                            },
                            {field: 'username', title: __('Username'),formatter: Controller.api.formatter.judge, operate: false},
                            // { field: 'genderdata', title: __('Genderdata'), visible: false, searchList: { "male": __('genderdata male'), "female": __('genderdata female') } },
                            // { field: 'genderdata_text', title: __('Genderdata'), operate: false },
                            {field: 'phone', title: __('Phone')},
                            {field: 'id_card', title: __('Id_card')},
                            {
                                field: 'operate', title: __('Operate'), table: newcarAudit,
                                buttons: [
                                    {
                                        name: 'auditedit',
                                        text: '编辑资料',
                                        icon: 'fa fa-pencil',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('编辑资料'),
                                        classname: 'btn btn-xs btn-default btn-dialog btn-auditedit',
                                        url: 'riskcontrol/creditreview/auditedit',
                                    },
                                    {
                                        name: '',
                                        icon: 'fa fa-times',
                                        text: '审核资料还未完善，无法进行审核',
                                        classname: ' text-danger ',
                                        hidden: function (row) {  /**审核资料还未完善，无法进行审核 */

                                            if (!row.id_cardimages || !row.drivers_licenseimages || !row.bank_cardimages) {
                                                return false;
                                            }
                                            else if (row.id_cardimages && row.drivers_licenseimages && row.bank_cardimages) {
                                                return true;
                                            }
                                        },
                                    },
                                    {
                                        name: 'newauditResult',
                                        text: '审核',
                                        title: '审核征信',
                                        icon: 'fa fa-check-square-o',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-info btn-newauditResult ',
                                        // url: 'riskcontrol/creditreview/newauditResult',

                                        //等于is_reviewing_true 的时候操作栏显示的是正在审核四个字，隐藏编辑和删除
                                        //等于is_reviewing 的时候操作栏显示的是提交审核按钮 四个字，显示编辑和删除 
                                        //....
                                        hidden: function (row) { /**审核 */
                                            if ((row.review_the_data == 'is_reviewing_true') && row.id_cardimages && row.drivers_licenseimages && row.bank_cardimages) {
                                                return false;
                                            }
                                            else if ((row.review_the_data == 'is_reviewing_true') || !row.id_cardimages || !row.drivers_licenseimages || !row.bank_cardimages) {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
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
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    {
                                        name: 'bigData',
                                        text: '查看大数据',
                                        title: '查看大数据征信',
                                        icon: 'fa fa-eye',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-success btn-bigData btn-dialog',
                                        url: 'riskcontrol/creditreview/toViewBigData',
                                        /**查看大数据 */
                                        hidden: function (row) {
                                            if (row.bigdata) {
                                                return true;
                                            }
                                        }

                                    },
                                    {
                                        name: 'for_the_car',
                                        text: '金融已通过，提交给销售，通知客户签订金融合同',
                                        title: '提交到销售，通知客户签订金融合同',
                                        icon: 'fa fa-share',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-info btn-submit_newsales',
                                        hidden: function (row) {
                                            if (row.review_the_data == 'for_the_car') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
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
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 一汽租赁
                                     */
                                    {
                                        name: 'conclude_the_contract',
                                        text: '已签金融合同，通知车管 采购车辆入库',
                                        title: '提交车管，进行录入库存',
                                        icon: 'fa fa-share',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-info btn-submit_newtube',

                                        hidden: function (row) {
                                            if (row.review_the_data == 'conclude_the_contract') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
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
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    /**
                                     * 其他金融
                                     */
                                    {
                                        name: 'is_reviewing_pass',
                                        text: '通知车管 采购车辆入库',
                                        title: '通知车管 采购车辆入库',
                                        icon: 'fa fa-share',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-info btn-submit_newtube_finance',

                                        hidden: function (row) { //其他金融
                                            if (row.review_the_data == 'is_reviewing_pass') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
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
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    {
                                        name: 'tube_into_stock',
                                        text: '选择库存车',
                                        title: '选择库存车',
                                        icon: 'fa fa-arrows',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-danger btn-dialog btn-choosestock',
                                        url: 'riskcontrol/creditreview/recyclebin',

                                        hidden: function (row) {
                                            if (row.review_the_data == 'tube_into_stock') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_true') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    {
                                        name: 'not_through',
                                        icon: 'fa fa-times',
                                        text: '征信未通过，订单已关闭',
                                        classname: ' text-danger ',
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
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    {
                                        name: 'collection_data',
                                        icon: 'fa fa-times',
                                        text: '征信不通过，待补录资料',
                                        classname: ' text-danger ',
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
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                        }
                                    },
                                    {

                                        name: 'take_the_car',
                                        text: '车辆匹配完成',
                                        title: __('车辆匹配完成'),
                                        classname: ' text-info ',
                                        hidden: function (row) {  /**车辆匹配完成 */
                                            if (row.review_the_data == 'take_the_car') {
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
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    {

                                        name: 'take_the_data',
                                        text: '销售正在补全客户提车资料',
                                        title: __('销售正在补全客户提车资料'),
                                        classname: ' text-info ',
                                        hidden: function (row) {  /**销售正在补全客户提车资料 */
                                            if (row.review_the_data == 'take_the_data') {
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
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    {

                                        name: 'inform_the_tube',
                                        text: '资料补全，准备提交车管',
                                        title: __('资料补全，准备提交车管'),
                                        classname: ' text-info ',
                                        hidden: function (row) {  /**资料补全，准备提交车管 */
                                            if (row.review_the_data == 'inform_the_tube') {
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
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    {

                                        name: 'send_the_car', text: '等待提车', title: __('等待提车'), classname: ' text-info ',
                                        hidden: function (row) {  /**等待提车 */
                                            if (row.review_the_data == 'send_the_car') {
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
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'collection_data') {
                                                return true;
                                            }
                                        }
                                    },
                                    {

                                        name: 'the_car',
                                        icon: 'fa fa-automobile',
                                        text: '已提车',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('订单已完成，客户已提车'),
                                        classname: ' text-success ',
                                        hidden: function (row) {  /**已提车 */
                                            if (row.review_the_data == 'the_car') {
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
                                            else if (row.review_the_data == 'conclude_the_contract') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'tube_into_stock') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'take_the_data') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'inform_the_tube') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'send_the_car') {
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
                Table.api.bindevent(newcarAudit);
                //指定搜索条件
                $(document).on("click", ".btn-singlesearch", function () {

                    var options = newcarAudit.bootstrapTable('getOptions');
                    options.pageNumber = 1;
                    options.queryParams = function (params) {
                        return {
                            search: params.search,
                            sort: params.sort,
                            order: params.order,
                            filter: JSON.stringify({username: '测试客户'}),
                            op: JSON.stringify({username: '='}),
                            offset: params.offset,
                            limit: params.limit,
                        };
                    };
                    newcarAudit.bootstrapTable('refresh', {});
                    Toastr.info("当前执行的是自定义搜索");
                    return false;
                });
                //数据实时统计
                newcarAudit.on('load-success.bs.table', function (e, data) {
                    $(".btn-choosestock").data("area", ["95%", "95%"]);
                    $(".btn-newauditResult").data("area", ["95%", "95%"]);
                    $(".btn-auditedit").data("area", ["95%", "95%"]);
                    $(".btn-bigData").data("area", ["95%", "95%"]);
                    $(".btn-newcardetails").data("area", ["95%", "95%"]);
                    // var newcarAudit = $('#badge_newcar_audit').text(data.total);
                    newcarAudit = parseInt($('#badge_newcar_audit').text());

                    var td = $("#newcarAudit td:nth-child(6)");
    
                    for (var i = 0; i < td.length; i++) {
    
                        td[i].style.textAlign = "left";
    
                    }
                })

            },
            /**租车 */
            rentalcar_audit: function () {
                // 审核租车单
                var rentalcarAudit = $("#rentalcarAudit");
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "快速搜索:客户姓名";};
                rentalcarAudit.bootstrapTable({
                    url: 'riskcontrol/creditreview/rentalcarAudit',
                    extend: {

                        table: 'rental_order',
                    },
                    toolbar: '#toolbar2',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'), operate: false},
                            {field: 'order_no', title: __('Order_no')},
                            {
                                field: 'createtime',
                                title: __('提交时间'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime,
                                datetimeFormat: 'YYYY-MM-DD'
                            },
                            {field: 'models.name', title: __('租车车型')},
                            {field: 'admin.nickname', title: __('销售员'), formatter: Controller.api.formatter.sales},

                            {field: 'username', title: __('Username'),formatter: Controller.api.formatter.judge1, operate: false},
                            {field: 'phone', title: __('Phone')},
                            {field: 'id_card', title: __('Id_card')},
                            {field: 'cash_pledge', title: __('押金（元）'), operate: false},
                            {field: 'rental_price', title: __('租金（元）'), operate: false},
                            {field: 'tenancy_term', title: __('租期（月）'), operate: false},
                            {
                                field: 'id', title: __('查看详细资料'), table: rentalcarAudit, buttons: [
                                    {
                                        name: 'rentalcardetails',
                                        text: '查看详细资料',
                                        title: '查看订单详细资料',
                                        icon: 'fa fa-eye',
                                        classname: 'btn btn-xs btn-primary btn-dialog btn-rentalcardetails',
                                        url: 'Sharedetailsdatas/rental_car_share_data',
                                        callback: function (data) {
                                        }
                                    }
                                ],

                                operate: false, formatter: Table.api.formatter.buttons
                            },
                            {
                                field: 'operate', title: __('Operate'), table: rentalcarAudit,
                                buttons: [
                                    {
                                        name: 'rentalauditedit',
                                        text: '编辑资料',
                                        icon: 'fa fa-pencil',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('编辑资料'),
                                        classname: 'btn btn-xs btn-default btn-dialog btn-rentalauditedit',
                                        url: 'riskcontrol/creditreview/rentalauditedit',
                                    },
                                    {
                                        name: 'rentalauditResult',
                                        text: '审核',
                                        title: '审核征信',
                                        icon: 'fa fa-check-square-o',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-info btn-rentalauditResult btn-dialog',
                                        url: 'riskcontrol/creditreview/rentalauditResult',
                                        //等于is_reviewing_true 的时候操作栏显示的是审核两个字，
                                        //等于is_reviewing_pass 的时候操作栏显示的是通过审核四个字，
                                        //....
                                        hidden: function (row) { /**审核 */
                                            if (row.review_the_data == 'is_reviewing_control') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_nopass') {
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
                                        name: 'bigData',
                                        text: '查看大数据',
                                        title: '查看大数据征信',
                                        icon: 'fa fa-eye',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-success btn-bigData btn-dialog',
                                        url: 'riskcontrol/creditreview/toViewBigData',
                                        /**查看大数据 */
                                        hidden: function (row) {
                                            if (row.bigdata) {
                                                return true;
                                            }
                                        }

                                    },
                                    {
                                        name: 'is_reviewing_pass',
                                        icon: 'fa fa-check-circle',
                                        text: '征信已通过',
                                        classname: ' text-info ',
                                        hidden: function (row) {  /**征信已通过 */
                                            if (row.review_the_data == 'is_reviewing_pass') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_nopass') {
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
                                        name: 'is_reviewing_nopass',
                                        icon: 'fa fa-times',
                                        text: '征信未通过，订单已关闭',
                                        classname: ' text-danger ',
                                        hidden: function (row) {  /**征信不通过 */

                                            if (row.review_the_data == 'is_reviewing_nopass') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
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
                                        name: 'is_reviewing_nopass',
                                        icon: 'fa fa-times',
                                        text: '征信不通过，待补录资料',
                                        classname: ' text-danger ',
                                        hidden: function (row) {  /**征信不通过，待补录资料 */

                                            if (row.review_the_data == 'collection_data') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_nopass') {
                                                return true;
                                            }
                                        }
                                    },
                                    {
                                        name: 'for_the_car',
                                        icon: 'fa fa-automobile',
                                        text: '已提车',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('订单已完成，客户已提车'),
                                        classname: ' text-success ',
                                        hidden: function (row) {  /**已提车 */
                                            if (row.review_the_data == 'for_the_car') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_pass') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_nopass') {
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

                Table.api.bindevent(rentalcarAudit);
                //数据实时统计
                rentalcarAudit.on('load-success.bs.table', function (e, data) {
                    $(".btn-rentalauditResult").data("area", ["95%", "95%"]);
                    $(".btn-rentalcardetails").data("area", ["95%", "95%"]);
                    $(".btn-bigData").data("area", ["95%", "95%"]);
                    $(".btn-rentalauditedit").data("area", ["95%", "95%"]);
                    $(".btn-signature").data("area", ["80%", "80%"]);

                    var td = $("#rentalcarAudit td:nth-child(5)");
    
                    for (var i = 0; i < td.length; i++) {
    
                        td[i].style.textAlign = "left";
    
                    }
                })

            },
            /**二手车 */
            secondhandcar_audit: function () {
                // 待审核
                var secondhandcarAudit = $("#secondhandcarAudit");
                $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "快速搜索:客户姓名";};
                // 初始化表格
                secondhandcarAudit.bootstrapTable({
                    url: 'riskcontrol/creditreview/secondhandcarAudit',
                    extend: {

                        table: 'second_sales_order',
                    },
                    toolbar: '#toolbar3',
                    pk: 'id',
                    sortName: 'id',
                    searchFormVisible: true,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id'), operate: false},
                            {field: 'order_no', title: __('Order_no')},
                            {
                                field: 'createtime',
                                title: __('Createtime'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime,
                                datetimeFormat: 'YYYY-MM-DD'
                            },

                            {field: 'plansecond.licenseplatenumber', title: __('车牌号')},

                            {field: 'models.name', title: __('销售车型')},

                            {field: 'admin.nickname', title: __('销售员'), formatter: Controller.api.formatter.sales},
                            {
                                field: 'id', title: __('查看详细资料'), table: secondhandcarAudit, buttons: [
                                    {
                                        name: 'secondhandcardetails',
                                        text: '查看详细资料',
                                        title: '查看订单详细资料',
                                        icon: 'fa fa-eye',
                                        classname: 'btn btn-xs btn-primary btn-dialog btn-secondhandcardetails',
                                        url: 'Sharedetailsdatas/second_car_share_data',
                                        callback: function (data) {

                                        }
                                    }
                                ],

                                operate: false, formatter: Table.api.formatter.buttons
                            },
                            {field: 'username', title: __('Username'),formatter: Controller.api.formatter.judge, operate: false},
                            {
                                field: 'genderdata',
                                title: __('Genderdata'),
                                visible: false,
                                searchList: {"male": __('genderdata male'), "female": __('genderdata female')}
                            },
                            {field: 'genderdata_text', title: __('Genderdata'), operate: false},
                            {field: 'phone', title: __('Phone')},
                            {field: 'id_card', title: __('Id_card')},

                            // { field: 'plansecond.newpayment', title: __('新首付（元）'),operate:false },
                            // { field: 'plansecond.monthlypaymen', title: __('月供（元）'),operate:false },
                            // { field: 'plansecond.periods', title: __('期数（月）'),operate:false },
                            // { field: 'plansecond.totalprices', title: __('总价（元）'),operate:false },
                            // { field: 'plansecond.bond', title: __('保证金（元）'),operate:false },
                            // { field: 'plansecond.tailmoney', title: __('尾款（元）'),operate:false },
                            {
                                field: 'operate', title: __('Operate'), table: secondhandcarAudit,
                                buttons: [
                                    {
                                        name: 'secondauditedit',
                                        text: '编辑资料',
                                        icon: 'fa fa-pencil',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('编辑资料'),
                                        classname: 'btn btn-xs btn-default btn-dialog btn-secondauditedit',
                                        url: 'riskcontrol/creditreview/secondauditedit',
                                    },
                                    {
                                        name: '',
                                        icon: 'fa fa-times',
                                        text: '审核资料还未完善，无法进行审核',
                                        classname: ' text-danger ',
                                        hidden: function (row) {  /**审核资料还未完善，无法进行审核 */

                                            if (!row.id_cardimages || !row.drivers_licenseimages) {
                                                return false;
                                            }
                                            else if (row.id_cardimages && row.drivers_licenseimages) {
                                                return true;
                                            }
                                        },
                                    },
                                    {
                                        name: 'secondhandcarResult',
                                        text: '审核',
                                        title: '审核征信',
                                        icon: 'fa fa-check-square-o',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-info btn-secondhandcarResult btn-dialog',
                                        url: 'riskcontrol/creditreview/secondhandcarResult',
                                        //等于is_reviewing_true 的时候操作栏显示的是正在审核四个字，隐藏编辑和删除
                                        //等于is_reviewing 的时候操作栏显示的是提交审核按钮 四个字，显示编辑和删除
                                        //....
                                        hidden: function (row) { /**审核 */
                                            if (row.review_the_data == 'is_reviewing_control' && row.id_cardimages && row.drivers_licenseimages) {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control' || !row.id_cardimages || !row.drivers_licenseimages) {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
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
                                        name: 'bigData',
                                        text: '查看大数据',
                                        title: '查看大数据征信',
                                        icon: 'fa fa-eye',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-success btn-bigData btn-dialog',
                                        url: 'riskcontrol/creditreview/toViewBigData', /**查看大数据 */
                                        /**查看大数据 */
                                        hidden: function (row) {
                                            if (row.bigdata) {
                                                return true;
                                            }
                                        }

                                    },
                                    {
                                        name: 'is_reviewing_pass',
                                        text: '选择库存车',
                                        title: '选择库存车',
                                        icon: 'fa fa-arrows',
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-danger btn-dialog btn-secondchooseStock',
                                        hidden: function (row) {  /**征信已通过 */
                                            if (row.review_the_data == 'is_reviewing_pass') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
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
                                        name: 'for_the_car',
                                        icon: 'fa fa-check-circle',
                                        text: '车管备车中，客户可以提车',
                                        classname: ' text-info ',
                                        hidden: function (row) {  /**通知销售让客户提车 */
                                            if (row.review_the_data == 'for_the_car') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'not_through') {
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
                                        name: 'not_through',
                                        icon: 'fa fa-times',
                                        text: '征信未通过，订单已关闭',
                                        classname: ' text-danger ',
                                        hidden: function (row) {  /**征信不通过 */

                                            if (row.review_the_data == 'not_through') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
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
                                        name: 'collection_data',
                                        icon: 'fa fa-times',
                                        text: '征信不通过，待补录资料',
                                        classname: ' text-danger ',
                                        hidden: function (row) {  /**征信不通过,待补录资料 */

                                            if (row.review_the_data == 'collection_data') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
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

                                        name: 'the_car',
                                        icon: 'fa fa-automobile',
                                        text: '已提车',
                                        extend: 'data-toggle="tooltip"',
                                        title: __('订单已完成，客户已提车'),
                                        classname: ' text-success ',
                                        hidden: function (row) {  /**已提车 */
                                            if (row.review_the_data == 'the_car') {
                                                return false;
                                            }
                                            else if (row.review_the_data == 'not_through') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'for_the_car') {
                                                return true;
                                            }
                                            else if (row.review_the_data == 'is_reviewing_control') {
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
                Table.api.bindevent(secondhandcarAudit);

                /**
                 * 审核刷新页面
                 */


                /**
                 * 补录资料完成
                 */

                //数据实时统计
                secondhandcarAudit.on('load-success.bs.table', function (e, data) {
                    $(".btn-secondhandcarResult").data("area", ["95%", "95%"]);
                    $(".btn-bigData").data("area", ["95%", "95%"]);
                    $(".btn-secondhandcardetails").data("area", ["95%", "95%"]);
                    $(".btn-secondauditedit").data("area", ["95%", "95%"]);
                    secondhandcarAudit = parseInt($('#badge_secondhandcar_audit').text());

                    var td = $("#secondhandcarAudit td:nth-child(6)");
    
                    for (var i = 0; i < td.length; i++) {
    
                        td[i].style.textAlign = "left";
    
                    }
                })

            },
            
        },
        //新车库存
        recyclebin: function () {

            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });
    
            var table = $("#table");
            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "快速搜索:车型";};
            // 初始化表格
            table.bootstrapTable({
                url: 'riskcontrol/creditreview/recyclebin',
                pk: 'id',
                sortName: 'id',
                toolbar: '#toolbar',
                columns: [
                    [
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'models.name', title: __('车型名称')},
                        {field: 'licensenumber', title: __('车牌号')},
                        {field: 'frame_number', title: __('车架号')},
                        {field: 'engine_number', title: __('发动机号')},
                        {field: 'household', title: __('所属户')},
                        {field: '4s_shop', title: __('4S店')},
                        {field: 'note', title: __('备注'),operate:false},
                        {
                            field: 'operate', title: __('Operate'), table: table,
                            buttons: [
                                {
                                    name: 'auditedit',
                                    text: '选择匹配',
                                    icon: 'fa fa-pencil',
                                    extend: 'data-toggle="tooltip"',
                                    title: __('选择匹配'),
                                    classname: 'btn btn-xs btn-success btn-dialog btn-choose',
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
        // choosestock: function () {

        //     Table.api.init({});
        //     Form.api.bindevent($("form[role=form]"), function (data, ret) {
        //         //这里是表单提交处理成功后的回调函数，接收来自php的返回数据

        //         // console.log(data);
        //         // newAllocationNum = parseInt($('#badge_new_allocation').text());
        //         // num = parseInt(data);
        //         // $('#badge_new_allocation').text(num+newAllocationNum); 
        //         Fast.api.close(data);//这里是重点

        //         Toastr.success("成功");//这个可有可无
        //     }, function (data, ret) {
        //         // console.log(data);

        //         Toastr.success("失败");

        //     });
        //     // Controller.api.bindevent();

        // },
        auditedit: function () {

            Table.api.init({});
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据

                // console.log(data);
                // newAllocationNum = parseInt($('#badge_new_allocation').text());
                // num = parseInt(data);
                // $('#badge_new_allocation').text(num+newAllocationNum); 
                Fast.api.close(data);//这里是重点

                Toastr.success("成功");//这个可有可无
            }, function (data, ret) {
                // console.log(data);

                Toastr.success("失败");

            });


        },
        rentalauditedit: function () {

            Table.api.init({});
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据

                // console.log(data);
                // newAllocationNum = parseInt($('#badge_new_allocation').text());
                // num = parseInt(data);
                // $('#badge_new_allocation').text(num+newAllocationNum); 
                Fast.api.close(data);//这里是重点

                Toastr.success("成功");//这个可有可无
            }, function (data, ret) {
                // console.log(data);

                Toastr.success("失败");

            });


        },
        secondauditedit: function () {

            Table.api.init({});
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据

                // console.log(data);
                // newAllocationNum = parseInt($('#badge_new_allocation').text());
                // num = parseInt(data);
                // $('#badge_new_allocation').text(num+newAllocationNum); 
                Fast.api.close(data);//这里是重点

                Toastr.success("成功");//这个可有可无
            }, function (data, ret) {
                // console.log(data);

                Toastr.success("失败");

            });


        },
        secondchoosestock: function () {

            Table.api.init({});
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据

                // console.log(data);
                // newAllocationNum = parseInt($('#badge_new_allocation').text());
                // num = parseInt(data);
                // $('#badge_new_allocation').text(num+newAllocationNum); 
                Fast.api.close(data);//这里是重点

                Toastr.success("成功");//这个可有可无
            }, function (data, ret) {
                // console.log(data);

                Toastr.success("失败");

            });
            // Controller.api.bindevent();

        },
        edit: function () {
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

                    /**
                     * 审核新车单
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-newauditResult': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var bigdatatype = row.plan_acar_name ? 'sales_order' : row.plan_car_rental_name ? 'rental_order' : row.plan_car_second_name ? 'second_sales_order' : 0;
                        var url = 'riskcontrol/creditreview/newauditResult' + '/bigdatatype/' + bigdatatype;
                        // console.log(row);return;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('大数据'), $(this).data() || {
                            callback: function (value) {

                            }, success: function (ret) {
                            }
                        })
                    },

                    /**
                     * 新车提交销售，通知客户签金融合同
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-submit_newsales': function (e, value, row, index) {

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
                            __('是否提交销售，通知客户进行签订金融合同?'),
                            {icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true},

                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');
                                Fast.api.ajax({

                                    url: 'riskcontrol/creditreview/newsales',
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
                     * 新车提交车管，通知进行录入库存
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-submit_newtube': function (e, value, row, index) {

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
                            __('是否已签订完金融合同，提交车管录入库存?'),
                            {icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true},

                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');


                                Fast.api.ajax({

                                    url: 'riskcontrol/creditreview/newtube',
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
                     * 新车提交车管，通知进行录入库存---其他金融
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-submit_newtube_finance': function (e, value, row, index) {

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
                            __('审核已通过，是否提交车管录入库存?'),
                            {icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true},

                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');


                                Fast.api.ajax({

                                    url: 'riskcontrol/creditreview/newtubefinance',
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
                     * 选择库存车
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-choose': function (e, value, row, index) {

                        e.stopPropagation();
                        e.preventDefault();
                        var that = this;
                        Layer.confirm(
                            __("选择匹配的车型是：" + row.models.name + "，是否确认匹配"),
                            {icon: 3, title: __('Warning'), shadeClose: true},

                            function (index) {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');
                                var ids = $('#hidden1').val();
                                Fast.api.ajax({
                                    url: 'riskcontrol/creditreview/choose',
                                    data: {id: row[options.pk], ids:ids}
                                }, function (data, ret) {
                                    parent.$('#toolbar1 .btn-refresh', parent.document).trigger('click')
                                    Layer.close(index);
                                    var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                                    parent.layer.close(index);
                                    return false;
                                }, function (data, ret) {
                                    //失败的回调
                                    return false;
                                });

                            }
                        );

                    },

                    /**
                     * 选择库存车
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-chooseStock': function (e, value, row, index) {

                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'riskcontrol/creditreview/choosestock';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('选择库存车'), $(this).data() || {
                            callback: function (value) {

                            }, success: function (ret) {
                            }
                        })
                        
                    },

                    /**
                     * 审核租车单
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-rentalauditResult': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var bigdatatype = row.plan_acar_name ? 'sales_order' : row.plan_car_rental_name ? 'rental_order' : row.plan_car_second_name ? 'second_sales_order' : 0;
                        var url = 'riskcontrol/creditreview/rentalauditResult' + '/bigdatatype/' + bigdatatype;
                        // console.log(row);return;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('大数据'), $(this).data() || {
                            callback: function (value) {
                            }, success: function (ret) {
                            }
                        })
                    },


                    /**
                     * 审核二手车单
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-secondhandcarResult': function (e, value, row, index) {

                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var bigdatatype = row.plan_acar_name ? 'sales_order' : row.plan_car_rental_name ? 'rental_order' : row.plan_car_second_name ? 'second_sales_order' : 0;
                        var url = 'riskcontrol/creditreview/secondhandcarResult' + '/bigdatatype/' + bigdatatype;
                        // console.log(row);return;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('大数据'), $(this).data() || {
                            callback: function (value) {
                            }, success: function (ret) {
                            }
                        })

                    },

                    /**
                     * 选择二手车库存车
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-secondchooseStock': function (e, value, row, index) {

                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = 'riskcontrol/creditreview/secondchoosestock';
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('选择库存车'), $(this).data() || {
                            callback: function (value) {
                                //    在这里可以接收弹出层中使用`Fast.api.close(data)`进行回传的数据
                            }
                        })
                    },

                    /**
                     * 查看大数据  （封装）
                     * @param e
                     * @param value
                     * @param row
                     * @param index
                     */
                    'click .btn-bigData': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var bigdatatype = row.plan_acar_name ? 'sales_order' : row.plan_car_rental_name ? 'rental_order' : row.plan_car_second_name ? 'second_sales_order' : 0;
                        var url = 'riskcontrol/creditreview/bigdata' + '/bigdatatype/' + bigdatatype;
                        // console.log(row);return;
                        Fast.api.open(Table.api.replaceurl(url, row, table), __('大数据'), $(this).data() || {
                            callback: function (value) {
                            }, success: function (ret) {
                            }
                        })
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
                /**
                 * 销售员头像
                 * @param value
                 * @param row
                 * @param index
                 * @returns {string}
                 */
                sales: function (value, row, index) {
                    return value == null ? value : "<img src=" + Config.cdn_url + row.admin.avatar + " style='height:30px;width:30px;border-radius:50%'></img>" + '&nbsp;' + row.admin.department + ' - ' + value;
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
    }
    return Controller;

})
;
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/salesorder/index',
                    add_url: 'order/salesorder/add',
                    edit_url: 'order/salesorder/edit',
                    del_url: 'order/salesorder/del',
                    multi_url: 'order/salesorder/multi',
                    table: 'sales_order',
                }
            });

            var table = $("#table");

            $('form').removeClass('hidden');
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        { checkbox: true },
                        { field: 'id', title: __('Id') },
                        { field: 'plan_acar_name', title: __('Plan_acar_name') },
                        { field: 'sales_id', title: __('Sales_id') },
                        { field: 'backoffice_id', title: __('Backoffice_id') },
                        { field: 'control_id', title: __('Control_id') },
                        { field: 'new_car_id', title: __('New_car_id') },
                        { field: 'order_no', title: __('Order_no') },
                        { field: 'username', title: __('Username') },
                        { field: 'phone', title: __('Phone') },
                        { field: 'id_card', title: __('Id_card') },
                        { field: 'genderdata', title: __('Genderdata'), visible: false, searchList: { "male": __('genderdata male'), "female": __('genderdata female') } },
                        { field: 'genderdata_text', title: __('Genderdata'), operate: false },
                        { field: 'city', title: __('City') },
                        { field: 'detailed_address', title: __('Detailed_address') },
                        { field: 'emergency_contact_1', title: __('Emergency_contact_1') },
                        { field: 'emergency_contact_2', title: __('Emergency_contact_2') },
                        { field: 'family_members', title: __('Family_members') },
                        { field: 'customer_source', title: __('Customer_source'), visible: false, searchList: { "direct_the_guest": __('customer_source direct_the_guest'), "turn_to_introduce": __('customer_source turn_to_introduce') } },
                        { field: 'customer_source_text', title: __('Customer_source'), operate: false },
                        { field: 'turn_to_introduce_name', title: __('Turn_to_introduce_name') },
                        { field: 'turn_to_introduce_phone', title: __('Turn_to_introduce_phone') },
                        { field: 'turn_to_introduce_card', title: __('Turn_to_introduce_card') },
                        { field: 'id_cardimages', title: __('Id_cardimages'), formatter: Table.api.formatter.images },
                        { field: 'drivers_licenseimages', title: __('Drivers_licenseimages'), formatter: Table.api.formatter.images },
                        { field: 'residence_bookletimages', title: __('Residence_bookletimages'), formatter: Table.api.formatter.images },
                        { field: 'housingimages', title: __('Housingimages'), formatter: Table.api.formatter.images },
                        { field: 'bank_cardimages', title: __('Bank_cardimages'), formatter: Table.api.formatter.images },
                        { field: 'application_formimages', title: __('Application_formimages'), formatter: Table.api.formatter.images },
                        { field: 'call_listfiles', title: __('Call_listfiles') },
                        { field: 'credit_reportimages', title: __('Credit_reportimages'), formatter: Table.api.formatter.images },
                        { field: 'deposit_contractimages', title: __('Deposit_contractimages'), formatter: Table.api.formatter.images },
                        { field: 'deposit_receiptimages', title: __('Deposit_receiptimages'), formatter: Table.api.formatter.images },
                        { field: 'guarantee_id_cardimages', title: __('Guarantee_id_cardimages'), formatter: Table.api.formatter.images },
                        { field: 'guarantee_agreementimages', title: __('Guarantee_agreementimages'), formatter: Table.api.formatter.images },
                        { field: 'review_the_data', title: __('Review_the_data'), visible: false, searchList: { "not_through": __('review_the_data not_through'), "through": __('review_the_data through'), "credit_report": __('review_the_data credit_report'), "the_guarantor": __('review_the_data the_guarantor'), "for_the_car": __('review_the_data for_the_car'), "the_car": __('review_the_data the_car') } },
                        { field: 'review_the_data_text', title: __('Review_the_data'), operate: false },
                        { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime },
                        { field: 'delivery_datetime', title: __('Delivery_datetime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime },
                        { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },

        add: function () {
            
            //获取通话清单
            $(document).on('click', '.get_call_listfiles', function () {
                
                var username = $('#c-username').val();

                var id_card = $('#c-id_card').val();
                var phone = $('#c-phone').val();
                if (!/^[\u4e00-\u9fa5]{2,4}$/.test($.trim(username)) || !/(^1[3|4|5|7|8]\d{9}$)|(^09\d{8}$)/.test(phone) || !/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(id_card)) {
                    Toastr.error("姓名或手机或身份证格式错误");

                    return false;
                }
                else {
                    Layer.confirm(
                        __('请确定客户姓名、手机号、身份证号是否真实有效，并提醒客户手机稍后接收短信验证码'),
                        { icon: 3, title: __('Warning'), shadeClose: true },
                        function (index) {
                            // //输入客户手机服务密码
                            Layer.prompt(
                                // __('请输入客户手机服务密码'),测试服务密码：202304
                                { title: __('请输入客户手机服务密码'), shadeClose: true },
                                //text为输入的服务密码 
                                function (text, index) {
                                    Fast.api.ajax({
                                        url: 'order/salesorder/getCallListfiles',
                                        data: { name: username, idNumber: id_card, username: phone, password: text }
                                    }, function (data, ret) {
                                       
                                        //判断是否有返回get-data值，如果有就直接得到数据
                                        if (ret.data.errorcode == '0000' && ret.data.get_data == 'yes') {
                                            Toastr.success('获取数据成功');
                                            console.log(ret.data);
                                            return false;
                                        }
                                        else{
                                            Toastr.error(ret.msg);return false;  
                                        }
                                        //如果返回成功，errorcode =='2000'并且type存在，得到sid，并提示输入验证码 
                                        if (ret.data.errorcode == '0000' && ret.data.data.hasOwnProperty('type')) {  //如果存在type属性，那么就需要输入手机验证码
                                            
                                            Layer.msg(ret.data.extra);
                                            var code = Layer.prompt(
                                                { title: __('请输入发送到客户手机的短信验证码'), shadeClose: true },
                                                //text2为输入得验证码
                                                function (text2, index) {
                                                    Fast.api.ajax({
                                                        url: 'order/salesorder/getCallListfiles2',
                                                        data: { checkcode: text2, sid: ret.data.data.sid, username: ret.data.username }
                                                    }, function (data, ret) {
                                                        
                                                        var sid = ret.data.data.sid;
                                                        var username = ret.data.username;

                                                        if (ret.data.errorcode == '0000' && ret.data.get_data == 'yes') { // 如果需要再次接收验证码 
                                                            Toastr.success('获取数据成功');
                                                            console.log(ret.data);
                                                            return false; 
                                                        } 
                                                         //如果第二次返回成功，errorcode =='2000'并且type存在，得到sid，并提示再次输入验证码 
                                                        if(ret.data.errorcode == '0000' && ret.data.data.hasOwnProperty('type')){ 

                                                            Layer.msg(ret.data.extra);
                                                        }
                                                        else {
                                                            Toastr.success(ret.msg);
                                                            console.log(ret.data); 
                                                            return false;
                                                        }
                                                           
                                                           
                                                            Layer.prompt(
                                                                { title: __('请再次输入新的短信验证码'), shadeClose: true },
                                                                function (text3, index) {
                                                                    Fast.api.ajax({
                                                                        url: 'order/salesorder/getCallListfiles2',
                                                                        data: { checkcode: text3, sid: sid, username: username }
                                                                    }, function (data, ret) {
                                                                        console.log(1111);

                                                                        // Toastr.success(ret.msg);  
                                                                        console.log(data);
                                                                        console.log(ret.data);
                                                                        return false;
                                                                    }, function (data, ret) {
                                                                        console.log(2222);

                                                                        Toastr.error(ret.msg);
                                                                        return false;
                                                                    })
                                                                }

                                                            ) 

                                                      

                                                    }, function (data, ret) {
                                                        Toastr.error(ret.msg);
                                                        return false;
                                                    })
                                                }

                                            )
                                        } else {
                                            Toastr.error(ret.msg); 
                                            return false;
                                        }
                                        // Layer.close(index); 

                                    }, function (data, ret) {
                                        Toastr.error(ret.msg); 
                                        console.log(222)
                                        console.log(ret)
                                        // Layer.close(index); 
                                        return false;
                                    });
                                }
                            )

                        }

                    );
                }
            })

            function login(){
                
            }
            Controller.api.bindevent();
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
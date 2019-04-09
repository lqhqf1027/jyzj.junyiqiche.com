<?php
/**
 * Created by PhpStorm.
 * User: glen9
 * Date: 2018/10/8
 * Time: 10:29
 */

namespace app\admin\controller;

use app\common\controller\Backend;
use app\admin\model\SalesOrder as salesOrderModel;
use app\admin\model\SecondSalesOrder as secondSalesOrderModel;
use app\admin\model\RentalOrder as rentalOrderModel;
use app\admin\model\FullParmentOrder as fullParmentOrderModel;
use app\admin\model\SecondFullOrder as secondFullOrderModel;
use think\Config;
use think\Db;

class Sharedetailsdatas extends Backend
{
    /**
     * Ordertabs模型对象
     * @var \app\admin\model\Ordertabs
     */
    protected $model = null;

    protected $noNeedRight = [
        'new_car_share_data', 'second_car_share_data', 'rental_car_share_data', 'secondfull_car_share_data', 'full_car_share_data'
    ];
    protected $dataLimitField = 'admin_id'; //数据关联字段,当前控制器对应的模型表中必须存在该字段
    protected $dataLimit = 'auth'; //表示显示当前自己和所有子级管理员的所有数据
    // protected  $dataLimit = 'false'; //表示显示当前自己和所有子级管理员的所有数据
    // protected $relationSearch = true;
    static protected $token = null;

    public function _initialize()
    {
        parent::_initialize();

    }

    /**
     * 新车详细资料
     * @param null $ids
     * @return string
     * @throws \think\Exception
     */
    public function new_car_share_data($ids = null, $order_id = null)
    {

        $row = Db::name('sales_order')->alias('a')
            ->join('admin b', 'b.id=a.admin_id', 'LEFT')
            ->join('plan_acar c', 'c.id = a.plan_acar_name', 'LEFT')
            ->join('mortgage_registration d', 'd.id = a.mortgage_registration_id', 'LEFT')
            ->join('car_new_inventory e', 'e.id=a.car_new_inventory_id', 'LEFT')
            ->join('mortgage f', 'f.id=a.mortgage_id', 'LEFT')
            ->join('violation_inquiry g','a.violation_inquiry_id = g.id','LEFT')
            ->field('a.order_no,a.genderdata,a.models_id,a.username,a.delivery_datetime,a.createtime,a.plan_name,a.phone,a.id_card,a.financial_name,a.downpayment,a.difference,a.decorate,
                a.customer_source,a.detailed_address,a.city,a.emergency_contact_1,a.emergency_contact_2,a.family_members,a.turn_to_introduce_name,a.turn_to_introduce_phone,
                a.turn_to_introduce_card,a.id_cardimages,a.amount_collected,a.residence_bookletimages,a.bank_cardimages,a.drivers_licenseimages,a.housingimages,a.application_formimages,
                a.deposit_contractimages,a.deposit_receiptimages,a.guarantee_id_cardimages,a.guarantee_agreementimages,a.new_car_marginimages,a.call_listfiles,a.withholding_service,
                a.undertakingimages,a.accreditimages,a.faceimages,a.informationimages,a.mate_id_cardimages,a.financial_monthly,a.credit_reportimages,
                b.nickname as sales_name,
                c.tail_section,c.note,c.nperlist,c.payment,
                d.archival_coding,d.contract_total,d.end_money,d.yearly_inspection,d.next_inspection,d.transferdate,d.hostdate,d.ticketdate,d.supplier,d.tax_amount,d.no_tax_amount,d.pay_taxesdate,d.house_fee,
                d.luqiao_fee,d.insurance_buydate,d.car_boat_tax,d.insurance_policy,
                d.commercial_insurance_policy,d.registry_remark,
                e.licensenumber,e.engine_number,e.frame_number,e.household,e.note as nnote,
                f.car_imgeas,f.lending_date,f.bank_card,f.invoice_monney,f.registration_code,f.tax,f.business_risks,f.insurance,f.mortgage_type,
                g.year_checktime')
            ->where('a.id',$order_id==null?$ids:$order_id)
            ->find();

        if ($row['models_id']) {
            $row['models_name'] = Db::name('models')
                ->where('id', $row['models_id'])
                ->value('name');
        }

        $row['total_insurance'] = null;
        $row['service_charges'] = null;
        $row['down_payment_service'] = null;
        $row['first_money'] = null;
        //计算保险
        if ($row['business_risks'] && $row['insurance'] && $row['car_boat_tax']) {
            $insurance = floatval($row['business_risks']) + floatval($row['insurance']) + floatval($row['car_boat_tax']);

            $row['total_insurance'] = $insurance;
        }

        //计算服务费
        if ($row['contract_total'] && $row['invoice_monney'] && $insurance) {
            $service_charge = floatval($row['contract_total']) - floatval($insurance) - floatval($row['invoice_monney']);

            $row['service_charges'] = $service_charge;
        }

        //计算首期服务费
        if ($service_charge && $row['withholding_service'] && $row['nperlist'] && $row['payment']) {
            $down_payment = floatval($service_charge) - (floatval($row['withholding_service']) * floatval($row['nperlist']));

            $down_payment = $down_payment < $row['payment'] ? $down_payment : $row['payment'];

            $row['down_payment_service'] = $down_payment;
        }


        //计算首期款
        if ($down_payment && $row['payment']) {
            $first_money = floatval($row['payment']) - floatval($down_payment);

            $row['first_money'] = $first_money;

            Db::name('sales_order')
                ->where('id', $ids)
                ->update([
                    'downpayment' => $first_money,
                    'service_charge' => $down_payment
                ]);
        }


        if ($row['new_car_marginimages'] == "") {
            $row['new_car_marginimages'] = null;
        }
        if (!$row)
            $this->error(__('No Results were found'));
        //承诺书
        $undertakingimages = $row['undertakingimages'] == '' ? [] : explode(',', $row['undertakingimages']);
        //授权书
        $accreditimages = $row['accreditimages'] == '' ? [] : explode(',', $row['accreditimages']);
        //面签照
        $faceimages = $row['faceimages'] == '' ? [] : explode(',', $row['faceimages']);
        //征信报告
        $credit_reportimages = $row['credit_reportimages'] == '' ? [] : explode(',', $row['credit_reportimages']);
        //信息表
        $informationimages = $row['informationimages'] == '' ? [] : explode(',', $row['informationimages']);
        //配偶的身份证正反面（多图）
        $mate_id_cardimages = $row['mate_id_cardimages'] == '' ? [] : explode(',', $row['mate_id_cardimages']);
        //定金合同（多图）
        $deposit_contractimages = $row['deposit_contractimages'] == '' ? [] : explode(',', $row['deposit_contractimages']);
        //定金收据上传
        $deposit_receiptimages = $row['deposit_receiptimages'] == '' ? [] : explode(',', $row['deposit_receiptimages']);
        //身份证正反面（多图）
        $id_cardimages = $row['id_cardimages'] == '' ? [] : explode(',', $row['id_cardimages']);
        //驾照正副页（多图）
        $drivers_licenseimages = $row['drivers_licenseimages'] == '' ? [] : explode(',', $row['drivers_licenseimages']);
        //户口簿【首页、主人页、本人页】
        $residence_bookletimages = $row['residence_bookletimages'] == '' ? [] : explode(',', $row['residence_bookletimages']);
        //住房合同/房产证（多图）
        $housingimages = $row['housingimages'] == '' ? [] : explode(',', $row['housingimages']);
        //银行卡照（可多图）
        $bank_cardimages = $row['bank_cardimages'] == '' ? [] : explode(',', $row['bank_cardimages']);
        //申请表（多图）
        $application_formimages = $row['application_formimages'] == '' ? [] : explode(',', $row['application_formimages']);

        //通话清单（文件上传）
        $call_listfiles = $row['call_listfiles'] == '' ? [] : explode(',', $row['call_listfiles']);
        //保证金收据（多图）
        $new_car_marginimages = $row['new_car_marginimages'] == '' ? [] : explode(',', $row['new_car_marginimages']);
        //担保人身份证正反面（多图）
        $guarantee_id_cardimages = $row['guarantee_id_cardimages'] == '' ? [] : explode(',', $row['guarantee_id_cardimages']);
        //担保协议（多图）
        $guarantee_agreementimages = $row['guarantee_agreementimages'] == '' ? [] : explode(',', $row['guarantee_agreementimages']);
        //车辆所有的扫描件 (多图)
        $car_imgeas = $row['car_imgeas'] == '' ? [] : explode(',', $row['car_imgeas']);
        //滴滴授权承诺书
        $car_authorizationimages = $row['car_authorizationimages'] == '' ? [] : explode(',', $row['car_authorizationimages']);


        $this->view->assign([
            "row" => $row,
            'cdnurl' => Config::get('upload')['cdnurl'],
            'undertakingimages' => $undertakingimages,
            'accreditimages' => $accreditimages,
            'faceimages' => $faceimages,
            'informationimages' => $informationimages,
            'mate_id_cardimages' => $mate_id_cardimages,
            'deposit_contractimages' => $deposit_contractimages,
            'deposit_receiptimages' => $deposit_receiptimages,
            'id_cardimages' => $id_cardimages,
            'drivers_licenseimages' => $drivers_licenseimages,
            'residence_bookletimages' => $residence_bookletimages,
            'housingimages' => $housingimages,
            'bank_cardimages' => $bank_cardimages,
            'application_formimages' => $application_formimages,
            'call_listfiles' => $call_listfiles,
            'new_car_marginimages' => $new_car_marginimages,
            'guarantee_id_cardimages' => $guarantee_id_cardimages,
            'guarantee_agreementimages' => $guarantee_agreementimages,
            'car_imgeas' => $car_imgeas,
            'car_authorizationimages' => $car_authorizationimages,
            'credit_reportimages' => $credit_reportimages,
        ]);
        return $this->view->fetch();
    }

    /**
     * 二手车详细资料
     * @param null $ids
     * @return string
     * @throws \think\Exception
     */
    public function second_car_share_data($ids = null,$order_id = null)
    {
        $row = Db::name('second_sales_order')->alias('a')
            ->join('admin b', 'b.id=a.admin_id', 'LEFT')
            ->join('secondcar_rental_models_info c', 'c.id = a.plan_car_second_name', 'LEFT')
            ->join('mortgage_registration d', 'd.id = a.mortgage_registration_id', 'LEFT')
            ->join('violation_inquiry e','a.violation_inquiry_id = e.id','LEFT')
            ->field('a.genderdata,a.username,a.models_id,a.delivery_datetime,a.createtime,a.plan_name,a.phone,a.id_card,a.financial_name,a.downpayment,a.difference,a.decorate,
                a.customer_source,a.detailed_address,a.city,a.emergency_contact_1,a.emergency_contact_2,a.family_members,a.turn_to_introduce_name,a.turn_to_introduce_phone,
                a.turn_to_introduce_card,a.id_cardimages,a.residence_bookletimages,a.bank_cardimages,a.marriedimages,a.drivers_licenseimages,a.housingimages,a.application_formimages,
                a.deposit_contractimages,a.deposit_receiptimages,a.guarantee_id_cardimages,a.guarantee_agreementimages,a.new_car_marginimages,a.call_listfiles,a.bond,
                a.crime_undertakingimages,a.credit_reportimages,a.car_confirmationimages,a.informationimages,a.mate_id_cardimages,a.amount_collected,a.order_no,
                b.nickname as sales_name,
                c.licenseplatenumber,c.engine_number,c.vin,c.kilometres,c.companyaccount,c.tailmoney,c.drivinglicenseimages,
                d.contract_total,d.mortgage_people,d.end_money,d.yearly_inspection,d.next_inspection,d.transferdate,d.hostdate,d.ticketdate,d.supplier,d.tax_amount,d.no_tax_amount,
                d.pay_taxesdate,d.house_fee,d.luqiao_fee,d.insurance_buydate,d.car_boat_tax,d.insurance_policy,d.insurance,d.business_risks,
                d.commercial_insurance_policy,d.registry_remark,
                e.year_checktime')
            ->where('a.id', $order_id==null?$ids:$order_id)
            ->find();

        if ($row['models_id']) {
            $row['models_name'] = Db::name('models')
                ->where('id', $row['models_id'])
                ->value('name');
        }

        //无犯罪记录承诺书
        $crime_undertakingimages = $row['crime_undertakingimages'] == '' ? [] : explode(',', $row['crime_undertakingimages']);
        //购车确认书
        $car_confirmationimages = $row['car_confirmationimages'] == '' ? [] : explode(',', $row['car_confirmationimages']);
        //配偶的身份证正反面（多图）
        $mate_id_cardimages = $row['mate_id_cardimages'] == '' ? [] : explode(',', $row['mate_id_cardimages']);
        //结婚证复印件（非必须）
        $marriedimages = $row['marriedimages'] == '' ? [] : explode(',', $row['marriedimages']);
        //定金合同（多图）
        $deposit_contractimages = $row['deposit_contractimages'] == '' ? [] : explode(',', $row['deposit_contractimages']);
        //定金收据上传
        $deposit_receiptimages = $row['deposit_receiptimages'] == '' ? [] : explode(',', $row['deposit_receiptimages']);
        //身份证正反面（多图）
        $id_cardimages = $row['id_cardimages'] == '' ? [] : explode(',', $row['id_cardimages']);
        //驾照正副页（多图）
        $drivers_licenseimages = $row['drivers_licenseimages'] == '' ? [] : explode(',', $row['drivers_licenseimages']);
        //户口簿【首页、主人页、本人页】
        $residence_bookletimages = $row['residence_bookletimages'] == '' ? [] : explode(',', $row['residence_bookletimages']);
        //住房合同/房产证（多图）
        $housingimages = $row['housingimages'] == '' ? [] : explode(',', $row['housingimages']);
        //银行卡照（可多图）
        $bank_cardimages = $row['bank_cardimages'] == '' ? [] : explode(',', $row['bank_cardimages']);
        //申请表（多图）
        $application_formimages = $row['application_formimages'] == '' ? [] : explode(',', $row['application_formimages']);
        //通话清单（文件上传）
        $call_listfiles = $row['call_listfiles'] == '' ? [] : explode(',', $row['call_listfiles']);
        //保证金收据（多图）
        $new_car_marginimages = $row['new_car_marginimages'] == '' ? [] : explode(',', $row['new_car_marginimages']);
        //担保人身份证正反面（多图）
        $guarantee_id_cardimages = $row['guarantee_id_cardimages'] == '' ? [] : explode(',', $row['guarantee_id_cardimages']);
        //担保协议（多图）
        $guarantee_agreementimages = $row['guarantee_agreementimages'] == '' ? [] : explode(',', $row['guarantee_agreementimages']);
        //征信审核图片(多图)
        $credit_reportimages = $row['credit_reportimages'] == '' ? [] : explode(',', $row['credit_reportimages']);
        //行驶证照(多图)
        $drivinglicenseimages = $row['drivinglicenseimages'] == '' ? [] : explode(',', $row['drivinglicenseimages']);
        //车辆所有的扫描件 (多图)
        $car_imgeas = $row['car_imgeas'] == '' ? [] : explode(',', $row['car_imgeas']);
        //滴滴授权承诺书
        $car_authorizationimages = $row['car_authorizationimages'] == '' ? [] : explode(',', $row['car_authorizationimages']);

        $this->view->assign([
            "row" => $row,
            'cdnurl' => Config::get('upload')['cdnurl'],
            'mate_id_cardimages' => $mate_id_cardimages,
            'marriedimages' => $marriedimages,
            'deposit_contractimages' => $deposit_contractimages,
            'deposit_receiptimages' => $deposit_receiptimages,
            'id_cardimages' => $id_cardimages,
            'drivers_licenseimages' => $drivers_licenseimages,
            'residence_bookletimages' => $residence_bookletimages,
            'housingimages' => $housingimages,
            'bank_cardimages' => $bank_cardimages,
            'application_formimages' => $application_formimages,
            'call_listfiles' => $call_listfiles,
            'new_car_marginimages' => $new_car_marginimages,
            'guarantee_id_cardimages' => $guarantee_id_cardimages,
            'crime_undertakingimages' => $crime_undertakingimages,
            'guarantee_agreementimages' => $guarantee_agreementimages,
            'credit_reportimages' => $credit_reportimages,
            'drivinglicenseimages' => $drivinglicenseimages,
            'car_confirmationimages' => $car_confirmationimages,
            'car_imgeas' => $car_imgeas,
            'car_authorizationimages' => $car_authorizationimages,
        ]);
        return $this->view->fetch();
    }

    /**
     * 租车详细资料
     * @param null $ids
     * @return string
     * @throws \think\Exception
     */
    public function rental_car_share_data($ids = null, $order_id = null)
    {
        $row = Db::name('rental_order')->alias('a')
            ->join('admin b', 'b.id=a.admin_id', 'LEFT')
            ->join('car_rental_models_info c', 'c.id = a.plan_car_rental_name', 'LEFT')
            ->field('a.order_no,a.username,a.models_id,a.delivery_datetime,a.createtime,a.plan_name,a.phone,a.id_card,a.down_payment,a.bond,a.car_backtime,a.cash_pledge,
                a.rental_price,a.tenancy_term,a.customer_source,a.turn_to_introduce_name,a.turn_to_introduce_phone,a.turn_to_introduce_card,a.id_cardimages,
                a.residence_bookletimages,a.drivers_licenseimages,a.deposit_receiptimages,a.call_listfilesimages,a.customer_information_note,a.genderdata,
                b.nickname as sales_name,
                c.licenseplatenumber,c.engine_no,c.vin,c.kilometres,c.companyaccount,c.drivinglicenseimages,c.expirydate,c.annualverificationdate,c.carcolor,c.note,
                c.car_loss,c.back_kilometre,c.check_list')
            ->where('a.id', $order_id==null?$ids:$order_id)
            ->find();

        if ($row['models_id']) {
            $row['models_name'] = Db::name('models')
                ->where('id', $row['models_id'])
                ->value('name');
        }

        //身份证正反面（多图）
        $id_cardimages = $row['id_cardimages'] == '' ? [] : explode(',', $row['id_cardimages']);
        //驾照正副页（多图）
        $drivers_licenseimages = $row['drivers_licenseimages'] == '' ? [] : explode(',', $row['drivers_licenseimages']);
        //户口簿【首页、主人页、本人页】
        $residence_bookletimages = $row['residence_bookletimages'] == '' ? [] : explode(',', $row['residence_bookletimages']);
        //通话清单（文件上传）
        $call_listfilesimages = explode(',', $row['call_listfilesimages']);
        //定金收据上传
        $deposit_receiptimages = $row['deposit_receiptimages'] == '' ? [] : explode(',', $row['deposit_receiptimages']);
        //行驶证照(多图)
        $drivinglicenseimages = $row['drivinglicenseimages'] == '' ? [] : explode(',', $row['drivinglicenseimages']);
        //滴滴授权承诺书
        $car_authorizationimages = $row['car_authorizationimages'] == '' ? [] : explode(',', $row['car_authorizationimages']);
        //验车单
        $check_list = $row['check_list'] == '' ? [] : explode(',', $row['check_list']);

        $this->view->assign(
            [
                'row' => $row,
                'cdnurl' => Config::get('upload')['cdnurl'],
                'id_cardimages' => $id_cardimages,
                'drivers_licenseimages' => $drivers_licenseimages,
                'residence_bookletimages' => $residence_bookletimages,
                'call_listfilesimages' => $call_listfilesimages,
                'deposit_receiptimages' => $deposit_receiptimages,
                'car_authorizationimages' => $car_authorizationimages,
                'drivinglicenseimages' => $drivinglicenseimages,
                'check_list' => $check_list,
            ]
        );
        return $this->view->fetch();
    }


    /**
     * 全款（二手车）详细资料
     * @param null $ids
     * @return string
     * @throws \think\Exception
     */
    public function secondfull_car_share_data($ids = null,$order_id = null)
    {
        $row = Db::name('second_full_order')->alias('a')
            ->join('admin b', 'b.id=a.admin_id', 'LEFT')
            ->join('secondcar_rental_models_info c', 'c.id = a.plan_second_full_name', 'LEFT')
            ->join('mortgage d', 'd.id = a.mortgage_id', 'LEFT')
            ->field('a.order_no,a.models_id,a.username,a.delivery_datetime,a.createtime,a.plan_name,a.phone,a.id_card,a.customer_source,a.introduce_name,a.introduce_phone,
                a.introduce_card,a.id_cardimages,a.drivers_licenseimages,a.bank_cardimages,a.application_formimages,a.call_listfiles,a.genderdata,
                b.nickname as sales_name,
                c.licenseplatenumber,c.vin,c.kilometres,c.companyaccount,c.totalprices,c.drivinglicenseimages,c.engine_number,c.expirydate,c.annualverificationdate,c.carcolor,
                c.Parkingposition,
                d.car_imgeas,d.bank_card,d.invoice_monney,d.registration_code,d.tax,d.business_risks,d.insurance,d.lending_date,d.mortgage_type')
            ->where('a.id', $order_id==null?$ids:$order_id)
            ->find();

        if ($row['models_id']) {
            $row['models_name'] = Db::name('models')
                ->where('id', $row['models_id'])
                ->value('name');
        }

        //身份证正反面（多图）
        $id_cardimages = $row['id_cardimages'] == '' ? [] : explode(',', $row['id_cardimages']);
        //驾照正副页（多图）
        $drivers_licenseimages = $row['drivers_licenseimages'] == '' ? [] : explode(',', $row['drivers_licenseimages']);
        //申请表（多图）
        $application_formimages = $row['application_formimages'] == '' ? [] : explode(',', $row['application_formimages']);
        /**不必填 */
        //银行卡照（可多图）
        $bank_cardimages = $row['bank_cardimages'] == '' ? [] : explode(',', $row['bank_cardimages']);
        //通话清单（文件上传）
        $call_listfiles = $row['call_listfiles'] == '' ? [] : explode(',', $row['call_listfiles']);
        //车辆所有的扫描件
        $car_imgeas = $row['car_imgeas'] == '' ? [] : explode(',', $row['car_imgeas']);
        $this->view->assign(
            [
                'row' => $row,
                'cdnurl' => Config::get('upload')['cdnurl'],
                'id_cardimages' => $id_cardimages,
                'drivers_licenseimages' => $drivers_licenseimages,
                'application_formimages' => $application_formimages,
                'bank_cardimages' => $bank_cardimages,
                'call_listfiles' => $call_listfiles,
                'car_imgeas' => $car_imgeas,
            ]
        );
        return $this->view->fetch();
    }

    /**
     * 全款（新车）详细资料
     * @param null $ids
     * @return string
     * @throws \think\Exception
     */
    public function full_car_share_data($ids = null ,$order_id = null)
    {
        $row = Db::name('full_parment_order')->alias('a')
            ->join('admin b', 'b.id=a.admin_id', 'LEFT')
            ->join('plan_full c', 'c.id = a.plan_plan_full_name', 'LEFT')
            ->join('mortgage d', 'd.id = a.mortgage_id', 'LEFT')
            ->field('a.order_no,a.username,a.delivery_datetime,a.createtime,a.plan_name,a.phone,a.id_card,a.customer_source,a.introduce_name,a.introduce_phone,
                a.introduce_card,a.id_cardimages,a.drivers_licenseimages,a.bank_cardimages,a.application_formimages,a.call_listfiles,a.genderdata,
                b.nickname as sales_name,
                c.full_total_price,
                d.car_imgeas,d.bank_card,d.invoice_monney,d.registration_code,d.tax,d.business_risks,d.insurance,d.lending_date,d.mortgage_type')
            ->where('a.id', $order_id==null?$ids:$order_id)
            ->find();

        //身份证正反面（多图）
        $id_cardimages = $row['id_cardimages'] == '' ? [] : explode(',', $row['id_cardimages']);
        //驾照正副页（多图）
        $drivers_licenseimages = $row['drivers_licenseimages'] == '' ? [] : explode(',', $row['drivers_licenseimages']);
        //申请表（多图）
        $application_formimages = $row['application_formimages'] == '' ? [] : explode(',', $row['application_formimages']);
        /**不必填 */
        //银行卡照（可多图）
        $bank_cardimages = $row['bank_cardimages'] == '' ? [] : explode(',', $row['bank_cardimages']);
        //通话清单（文件上传）
        $call_listfiles = $row['call_listfiles'] == '' ? [] : explode(',', $row['call_listfiles']);
        //车辆所有的扫描件
        $car_imgeas = $row['car_imgeas'] == '' ? [] : explode(',', $row['car_imgeas']);
        $this->view->assign(
            [
                'row' => $row,
                'cdnurl' => Config::get('upload')['cdnurl'],
                'id_cardimages' => $id_cardimages,
                'drivers_licenseimages' => $drivers_licenseimages,
                'application_formimages' => $application_formimages,
                'bank_cardimages' => $bank_cardimages,
                'call_listfiles' => $call_listfiles,
                'car_imgeas' => $car_imgeas,
            ]
        );
        return $this->view->fetch();
    }


}
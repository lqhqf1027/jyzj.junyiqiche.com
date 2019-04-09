<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;

/**
 * 以租代购
 *
 * @icon fa fa-circle-o
 */
class Bigdata extends Backend
{
 
    protected $model = null;
    protected $userid = 'junyi_testusr'; //用户id
    protected $Rc4 = '12b39127a265ce21'; //apikey
    protected $sign = null; //sign  md5加密
    protected $noNeedRight =['toViewBigData','getBigData'];
    // protected $bigData = array();
    // protected $table,$ids,$username,$id_card,$phone;
    public function _initialize()

    {
        parent::_initialize();
        $this->sign = md5($this->userid . $this->Rc4);
    }

 
    public  function toViewBigData($ids,$table)
    {
       
        $row = Db::name($table)->find(function ($query) use ($ids) {
            $query->field('id,username,id_card,phone')->where('id', $ids);
        });
        // $row = $this->getTabledata();

        $params = array();
        $params['sign'] = $this->sign;
        $params['userid'] = $this->userid;
        $params['params'] = json_encode(
            [
                'tx' => '101',
                'data' => [
                    'name' => $row['username'],
                    'idNo' => $row['id_card'],
                    'queryReason' => '10',
                ],
            ]
        );
        // return $this->bigDataHtml(); 
        //判断数据库里是否有当前用户的大数据
        $data = $this->getBigData($row['id'],$table);
        if (empty($data)) {
            //如果数据为空，调取大数据接口
            $result[$table.'_id'] = $row['id'];
            $result['name'] = $row['username'];
            $result['phone'] = $row['phone'];
            $result['id_card'] = $row['id_card'];
            $result['createtime'] = time(); 
            // pr($result);die;
            $result['share_data'] = posts('https://www.zhichengcredit.com/echo-center/api/echoApi/v3', $params);
            /**共享数据接口 */
            //只有errorCode返回 '0000'  '0001'  '0005' 时为正确查询
            if ($result['share_data']['errorCode'] == '0000' || $result['share_data']['errorCode'] == '0001' || $result['share_data']['errorCode'] == '0005') {
                //风险数据接口 
                /**
                 * @params pricedAuthentification
                 * 收费验证环节
                 * 1-身份信息认证
                 * 2-手机号实名验证
                 * 3-银行卡三要素验证 
                 * 4-银行卡四要素 
                 * 当提交 3、4时 银行卡为必填项
                 */
                $params_risk['sign'] = $this->sign;
                $params_risk['userid'] = $this->userid;
                $params_risk['params'] = json_encode(
                    [
                        'data' => [
                            'name' => $row['username'],

                            'idNo' => $row['id_card'],
                            'mobile' => $row['phone'],
                        ],
                        'queryReason' => '10',//贷前审批s 
                        'pricedAuthentification' => '1,2'

                    ]
                );

                $result['risk_data'] = posts('https://www.zhichengcredit.com/echo-center/api/mixedRiskQuery/queryMixedRiskList/v3 ', $params_risk);
                /**风险数据接口 */
                if ($result['risk_data']['errorcode'] == '0000' || $result['risk_data']['errorcode'] == '0001' || $result['risk_data']['errorcode'] == '0005') {
                    //转义base64入库
                    $result['share_data'] = base64_encode(ARRAY_TO_JSON($result['share_data']));
                    $result['risk_data'] = base64_encode(ARRAY_TO_JSON($result['risk_data']));
                    // return $result;
                    $writeDatabases = Db::name('big_data')->insert($result);
                    if ($writeDatabases) {

                        return $this->getBigData($row['id'],$table);
            // $this->view->assign('bigdata', $this->getBigData($row['id']));

                    } else {
                        die('<h1><center>数据写入失败</center></h1>') ;
                    }
                } else {
                    die("<h1><center>风险接口-》{$result['risk_data']['message']}</center></h1>") ;

                }

            } else {
                 die("<h1><center>共享接口-》{$result['share_data']['message']}</center></h1>");

            }
        } else {
            return $data;
        }
    }
    /**
     * 查询大数据表
     * @param int $order_id
     * @return data
     */
    public function getBigData($order_id,$table)
    {
        $bigData = Db::name('big_data')->alias('a')
            ->join("{$table} b", "a.{$table}_id = b.id")
            ->where(["a.{$table}_id" => $order_id])
            ->field('a.*')
            ->find();

        if (!empty($bigData)) {
            $bigData['share_data'] = object_to_array(json_decode(base64_decode($bigData['share_data'])));
            $bigData['risk_data'] = object_to_array(json_decode(base64_decode($bigData['risk_data'])));
            return $bigData;

        } else {
            return [];
        }
    }



}
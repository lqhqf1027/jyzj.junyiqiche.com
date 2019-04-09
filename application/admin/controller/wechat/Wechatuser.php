<?php

namespace app\admin\controller\wechat;

use app\common\controller\Backend;
use think\Cache;
use think\Config;
/**
 * 用户信息
 *
 * @icon fa fa-circle-o
 */
class Wechatuser extends Backend
{   
    
    /**
     * WechatUser模型对象
     * @var \app\admin\model\WechatUser
     */
    protected $model = null;
    protected $searchFields = 'nickname';
    static public $token = null;



    public function _initialize()
    {
        parent::_initialize();

        $this->model = model('WechatUser');
        $this->view->assign("subscribeList", $this->model->getSubscribeList());
        $this->view->assign("sexList", $this->model->getSexList()); 


        self::$token= $this->getAccessToken();
        
    } 


    function https_request($url, $data = null,$time_out=60,$out_level="s",$headers=array())
    {  
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_NOSIGNAL, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        if($out_level=="s")
        {
            //超时以秒设置
            curl_setopt($curl, CURLOPT_TIMEOUT,$time_out);//设置超时时间
        }elseif ($out_level=="ms") 
        {
            curl_setopt($curl, CURLOPT_TIMEOUT_MS,$time_out);  //超时毫秒，curl 7.16.2中被加入。从PHP 5.2.3起可使用 
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if($headers)
        {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);//如果有header头 就发送header头信息
        }
        $output = curl_exec($curl);
        curl_close($curl);
        return json_decode($output);
    } 
    public function index()
    {  
        // Cache::rm('wechat_user_info');die;
        // dump(collection($this->selWechatUser())->toArray());die;
        // dump(self::getOpenid());die; 
        $token = self::$token;
        // pr($token);die;
        $openid = self::getOpenid(); 
        // pr( $this->getAccessToken());die;
//把你的openid 拿出来 测试
//'oklZR1J5BGScztxioesdguVsuDoY'  你开始
//无心  oklZR1JrHcr1KBZ2RGsToy_BoUZg
 
        // $sendmessage = new WechatMessage(Config::get('wechat')['APPID'],Config::get('wechat')['APPSECRET'], $token,'oklZR1J5BGScztxioesdguVsuDoY','测试测试5555');#;实例化    
 
        
        // dump($sendmessage->sendMsgToAll());exit; 
// pr( $openid);exit;

        // $url = "https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token=".$token;
        // $i = 0;
        // pr($openid); exit;
        // foreach($openid as $value){ 

        //     pr(json_encode($value));
        //     // echo $i++;
        //     $result=  $this->https_request($url,json_encode($value));  
        //     $user [] = $result;
        //     sleep(2);
        // } 
    //    die;


        ##(array)  强制转换数组  以防万一 是个空数组 要报错 
        // $newUser = array();
        
        // foreach((array)Cache::get('wechat_user_info') as $key=>$value){  
        //     $value['nickname'] = base64_encode($value['nickname']); 
        //     if(!empty($value['tagid_list'])){   
        //         $newUser[]=$value;  
        //     }  
        // } 
        // dump($newUser);die;
        // return  $this->model->allowField(true)->saveAll($newUser)?1:0;
     
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select(); 
            $list = collection($list)->toArray();
            foreach($list as $k=>$v){
                $list[$k]['nickname'] = base64_decode($v['nickname']);
            }
            $result = array("total" => $total, "rows" => $list); 
            return json($result);
        }
        return $this->view->fetch();
    }
    //获取openid
    public static function getOpenid(){
        $userlist = array();
       
        $result = gets("https://api.weixin.qq.com/cgi-bin/user/get?access_token=".self::$token)['data']['openid'];
        $num = 0;
        foreach($result as $k=>$v){  
            if($k%99==0){ 
                $num = $num+1;
            }
            $userlist[$num]['user_list'][$k]['openid'] = $v;
            $userlist[$num]['user_list'][$k]['lang']="zh_CN";
        }
        return $userlist;
    }
    //根据openid获取用户信息,批量获取
    public static function getUserInfo(){ 

        $user = array();
        $newUser = array();
        #这里是所有的opendi?en 
        return $openid = self::getOpenid();


        //pr($openid);exit;


        $token = self::$token;
        foreach($openid as $k=>$v){
            $oid = $v['openid'];

            //批量接口 
            $user[] = gets("https://api.weixin.qq.com/cgi-bin/user/info?access_token={$token}&openid={$oid}&lang=zh_CN");  
        } 
      
        Cache::set('wechat_user_info',$user);

        return Cache::get('wechat_user_info'); 

        ##这个接口？
        // return posts("https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token={$token}",self::getOpenid()); //批量获取用户接口
        // return gets("https://api.weixin.qq.com/cgi-bin/user/info?access_token={$token}&openid={$openid}&lang=zh_CN");//单个获取用户接口
   }
    //获取wechatuser表的数据  subscribe=>1为已关注的用户
    public  function selWechatUser(){
            $user =  $this->model::all(['subscribe'=>1]);
            foreach($user as $k=>$v){
                //base64转码
                $user[$k]['nickname'] =urldecode($v['nickname']);
            }
            return $user;
    }

    //拉取新用户
    public  function pullNewUser(){
        if($this->request->isAjax()){
            //首先判断是否有新员工关注公众号
            // 
            $this->error('',null,222); 
            // return 111;
            // return 111;
        }
    //    $this->success();
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    

}

<?php
//+----------------------------------------------------------------------
//| 版权所有 YI ，并保留所有权利
//| 这不是一个自由软件！禁止任何形式的拷贝、修改、发布
//+----------------------------------------------------------------------
//| 开发者: YI
//| 时间  : 9:32
//+----------------------------------------------------------------------
namespace  wechat;
/**微信类
 * 微信官方文档：https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1496904104_cfEfT
 * Class wx
 */
class Wx
{
    protected $appid;
    protected $secret;
    public function __construct($appid='',$secret='')
    {

        $this->appid = $appid;
        $this->secret = $secret;
    }

    /** 微信toke
     * @return array|mixed  返回Token
     */
    public function getWxtoken()
    {
        $appid = $this->appid;
        $secret = $this->secret;
        $token  = cache('Token');
        if(!$token['access_token'] || $token['expires_in'] <= time()){
            $rslt  = gets("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}");
            if($rslt){
                $accessArr = array(
                    'access_token'=>$rslt['access_token'],
                    'expires_in'=>time()+$rslt['expires_in']-200
                );
                cache('Token',$accessArr) ;
                $token = $accessArr;
            }
        }
        return $token;
    }


    /**微信授权用户
     * @param $backUrl 传入回调域名
     */
    public function getWxUser($backUrl)
    {
        //-------生成唯一随机串防CSRF攻击
        $state  = md5(uniqid(rand(), TRUE));
        session('wx_state',$state);
        $_SESSION["wx_state"]    =   $state; //存到SESSION
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appid}&redirect_uri={$backUrl}&response_type=code&scope=snsapi_userinfo&state={$state}#wechat_redirect";
        header('Location:'.$url);

        die();
    }
    /** 生成带参数的二维码
     *  @param $tsDats  数据
     * @param $is_temporary  是否是临时的
     * @return mixed
     */
    public function wxCode($tsDats,$is_temporary=false)
    {
        $token = $this->getWxtoken();
        $data['expire_seconds'] = 2592000;
        $data['action_name'] = $is_temporary ? 'QR_STR_SCENE' : 'QR_LIMIT_STR_SCENE';
        $data['action_info']['scene']['scene_str'] =$tsDats['id'];
        $ticketArray =  posts('https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$token['access_token'],json_encode($data,JSON_UNESCAPED_UNICODE));
        if(!$ticketArray['ticket']) ajax_return([],'生成有误',0);
        $backImgs = gets('https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ticketArray['ticket']));
        return $backImgs;
    }
    /**得到用户详情信息
     * @param $code  微信返回的code
     * @return mixed
     */
    public function WxUserInfo($code)
    {
        if(!session('rslt')){
            ##获取网页授权的access_token 和openid
            $rslt = gets("https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appid}&secret={$this->secret}&code={$code}&grant_type=authorization_code");
            session('rslt',$rslt);//保存，防止重复获取，
        }
        $rslt = session('rslt');
        ##通过access_token 和openid获取获取信息
        $user = gets("https://api.weixin.qq.com/sns/userinfo?access_token=" . $rslt['access_token'] . "&openid=" . $rslt['openid'] . "&lang=zh_CN ");
        return $user;
    }
    /**
     * 聚合支付
     */
    public function clientApiPay($payConfig)
    {
        \think\Loader::import('Payment.Client.Client', EXTEND_PATH,'.php');
        $clientApy = new \Client($this->config['clientApy'],$payConfig['openid']);
        $clientApy->setNotify('');
        $back = $clientApy->unifiedorder($payConfig['number'],$payConfig['total_fee'],$payConfig['desc']);
        if($back['code']) return $back;
    }
    /**
     * 聚合支付回调
     */
    public function ClientSetNotify()
    {
        $xml=file_get_contents('php://input');
        $xml =objectToArray(simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NOCDATA));
        $out_trade_no=$xml['out_trade_no'];##订单号
        $out_transaction_id = $xml['out_transaction_id'];##商户号
        $this->payOrderChk($out_trade_no,$out_transaction_id);
    }
    /**微信公众号支付
     * @param $payConfig  订单配置
     * @return JsApi   返回数据
     */
    public function wxJsApiPay($payConfig)
    {
        \think\Loader::import('Payment.Wxpay.example.jsapi',EXTEND_PATH,'.php');
        $wx = new \JsApi($payConfig);
        $jsApiParameters = $wx->jsApiParameters;##把这个值传到前端页面
        return $jsApiParameters;
    }
    /** 就是支付的审核填写的回调地址
     * 微信异步回调地址 检测微信结果
     */
    public function notifyWxPay()
    {
        \think\Loader::import('Payment.Wxpay.example.notify',EXTEND_PATH,'.php');
        $wx = new \Notify($this);
        $wx->Handle(false);
    }

    /**  支付成功回自动进行到这个页面
     * @param $order_num  订单号
     * @param $pay_num    流水单号
     * @return string  返回成功信息
     */
    function payOrderChk($order_num, $pay_num)
    {
        $order = db('order')->where('number', $order_num)->field('paystatus')->find();
        if(!$order['paystatus']) {
            $update['transaction_number'] = $pay_num;##交易流水号
            $update['api_time'] = time();##支付时间
            $update['paystatus'] =1;
            $update['shippingStatus'] =0;
            $update['orderState'] =1;
            db('order')->where('number',$order_num)->update($update);
        }
        return 'success';
    }



}
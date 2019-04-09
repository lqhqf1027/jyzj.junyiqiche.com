<?php
 
namespace app\admin\controller\wechat;  


  class WechatMessage{
    private $appId; 
    private $appSecret;
    private $access_token;
    private $openid;
    private $text;
    //
    public function __construct($appId, $appSecret,$access_token,$openid,$text) {
      $this->appId = $appId;
      $this->appSecret = $appSecret;
      $this->access_token = $access_token;
        $this->openid = $openid;
        $this->text = $text;

    }
    //
    function getData($url){
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
      curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      $data = curl_exec($ch);
      curl_close($ch);

      return $data;
    }
    //
    function postData($url,$data){
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $tmpInfo = curl_exec($ch);
      if (curl_errno($ch)) {
        return curl_error($ch);
      }
      curl_close($ch);
      return $tmpInfo;
    }
    //
    function getAccessToken(){
      $tock = cache('Token');
      return  $tock['access_token'];
    }
    //
    private function getUserInfo(){
      $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$this->access_token;
      
       $res= $this->getData($url);
      
      $jres = json_decode($res,true);
      //print_r($jres);
      $userInfoList = $jres['data']['openid'];
      return $userInfoList;
    }
    function sendMsgToAll(){

      $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$this->access_token;


        $data = '{
              "touser":"'.$this->openid.'",
              "msgtype":"text",
              "text":
              {
                "content":"'.$this->text.'",
              }
            }';
       // $this->postData($url,$data);

$a = posts($url,$data);
        ob_flush();
        return $a;
      }

  }
  
   
<?php
namespace Admin\Controller\YDX;

use Admin\Controller\YDX\SmsSingleSender;

class TenCentYunDuanXin
{
  // 短信应用SDK AppID
  private  $appid = 1400007;

  // 短信应用SDK AppKey
  private $appkey = "67b1eec*************890f0d74";

  // 短信模板ID，需要在短信应用中申请。NOTE: 这里的模板ID`7839`只是一个示例，真实的模板ID需要在短信控制台中申请
  private $templateId = 1137727;

  // 签名内容  NOTE: 这里的签名只是示例，请使用真实的已申请的签名，签名参数使用的是`签名内容`，而不是`签名ID`
  private $smsSign = "****sksks";

  /* TODO 腾讯云短信API(国内)--使用指定模板ID单发短信
   * 如果出现短信发送错误的情况,请查看AppID、AppKey、$templateId、$smsSign是否有误
   * 参数列：
   *        $phoneNumber  手机号码
   *       $params 短信内容中需要插入的参数，按顺序放入该一维数组中
   *        例如：您的验证码为{1}，请于{2}分钟内输入。 $params=[‘验证码’，‘有效时间’]
   * */
  public function Send($phoneNumber,array $params){
    // 指定模板ID单发短信
    $ssender = new SmsSingleSender($this->appid, $this->appkey);
    $result = $ssender->sendWithParam("86", $phoneNumber, $this->templateId,
    $params, $this->smsSign, "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信
    $rsp = json_decode($result,true);
    if($rsp['result']==0)
      return true;
    else
      return $rsp['errmsg'];
  }
}
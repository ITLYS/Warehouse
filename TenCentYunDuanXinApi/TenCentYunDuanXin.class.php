<?php
namespace Admin\Controller\YDX;

use Admin\Controller\YDX\SmsSingleSender;

class TenCentYunDuanXin
{
  // ����Ӧ��SDK AppID
  private  $appid = 1400007;

  // ����Ӧ��SDK AppKey
  private $appkey = "67b1eec*************890f0d74";

  // ����ģ��ID����Ҫ�ڶ���Ӧ�������롣NOTE: �����ģ��ID`7839`ֻ��һ��ʾ������ʵ��ģ��ID��Ҫ�ڶ��ſ���̨������
  private $templateId = 1137727;

  // ǩ������  NOTE: �����ǩ��ֻ��ʾ������ʹ����ʵ���������ǩ����ǩ������ʹ�õ���`ǩ������`��������`ǩ��ID`
  private $smsSign = "****sksks";

  /* TODO ��Ѷ�ƶ���API(����)--ʹ��ָ��ģ��ID��������
   * ������ֶ��ŷ��ʹ�������,��鿴AppID��AppKey��$templateId��$smsSign�Ƿ�����
   * �����У�
   *        $phoneNumber  �ֻ�����
   *       $params ������������Ҫ����Ĳ�������˳������һά������
   *        ���磺������֤��Ϊ{1}������{2}���������롣 $params=[����֤�롯������Чʱ�䡯]
   * */
  public function Send($phoneNumber,array $params){
    // ָ��ģ��ID��������
    $ssender = new SmsSingleSender($this->appid, $this->appkey);
    $result = $ssender->sendWithParam("86", $phoneNumber, $this->templateId,
    $params, $this->smsSign, "", "");  // ǩ������δ�ṩ����Ϊ��ʱ����ʹ��Ĭ��ǩ�����Ͷ���
    $rsp = json_decode($result,true);
    if($rsp['result']==0)
      return true;
    else
      return $rsp['errmsg'];
  }
}
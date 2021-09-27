<?php
namespace Admin\Controller\YDX;
use Admin\Controller\YDX\SmsSenderUtil;
/**
* ����������
*
 */
class SmsSingleSender
{
  private $url;
  private $appid;
  private $appkey;
  private $util;

  /**
   * ���캯��
   *
   * @param string $appid  sdkappid
   * @param string $appkey sdkappid��Ӧ��appkey
   */
  public function __construct($appid, $appkey)
  {
    $this->url = "https://yun.tim.qq.com/v5/tlssmssvr/sendsms";
    $this->appid =  $appid;
    $this->appkey = $appkey;
    $this->util = new SmsSenderUtil();
  }

  /**
   * ��ͨ����
   *
   * ��ͨ��������ȷָ�����ݣ�����ж��ǩ���������������ԡ����ķ�ʽ��ӵ���Ϣ�����У�����ϵͳ��ʹ��Ĭ��ǩ����
   *
   * @param int    $type        �������ͣ�0 Ϊ��ͨ���ţ�1 Ӫ������
   * @param string $nationCode  �����룬�� 86 Ϊ�й�
   * @param string $phoneNumber ������������ֻ���
   * @param string $msg         ��Ϣ���ݣ������������ģ���ʽһ�£����򽫷��ش���
   * @param string $extend      ��չ�룬����մ�
   * @param string $ext         �����ԭ�����صĲ���������մ�
   * @return string Ӧ��json�ַ�������ϸ���ݲμ���Ѷ��Э���ĵ�
   */
  public function send($type, $nationCode, $phoneNumber, $msg, $extend = "", $ext = "")
  {
    $random = $this->util->getRandom();
    $curTime = time();
    $wholeUrl = $this->url . "?sdkappid=" . $this->appid . "&random=" . $random;

    // ����Э����֯ post ����
    $data = new \stdClass();
    $tel = new \stdClass();
    $tel->nationcode = "".$nationCode;
    $tel->mobile = "".$phoneNumber;

    $data->tel = $tel;
    $data->type = (int)$type;
    $data->msg = $msg;
    $data->sig = hash("sha256",
      "appkey=".$this->appkey."&random=".$random."&time="
      .$curTime."&mobile=".$phoneNumber, FALSE);
    $data->time = $curTime;
    $data->extend = $extend;
    $data->ext = $ext;

    return $this->util->sendCurlPost($wholeUrl, $data);
  }

  /**
   * ָ��ģ�嵥��
   *
   * @param string $nationCode  �����룬�� 86 Ϊ�й�
   * @param string $phoneNumber ������������ֻ���
   * @param int    $templId     ģ�� id
   * @param array  $params      ģ������б���ģ�� {1}...{2}...{3}����ô��Ҫ����������
   * @param string $sign        ǩ���������մ���ϵͳ��ʹ��Ĭ��ǩ��
   * @param string $extend      ��չ�룬����մ�
   * @param string $ext         �����ԭ�����صĲ���������մ�
   * @return string Ӧ��json�ַ�������ϸ���ݲμ���Ѷ��Э���ĵ�
   */
  public function sendWithParam($nationCode, $phoneNumber, $templId = 0, $params,
                                $sign = "", $extend = "", $ext = "")
  {
    $random = $this->util->getRandom();
    $curTime = time();
    $wholeUrl = $this->url . "?sdkappid=" . $this->appid . "&random=" . $random;

    // ����Э����֯ post ����
    $data = new \stdClass();
    $tel = new \stdClass();
    $tel->nationcode = "".$nationCode;
    $tel->mobile = "".$phoneNumber;

    $data->tel = $tel;
    $data->sig = $this->util->calculateSigForTempl($this->appkey, $random,
      $curTime, $phoneNumber);
    $data->tpl_id = $templId;
    $data->params = $params;
    $data->sign = $sign;
    $data->time = $curTime;
    $data->extend = $extend;
    $data->ext = $ext;

    return $this->util->sendCurlPost($wholeUrl, $data);
  }
}
<?php
namespace Admin\Controller\NestingHTML;
use Admin\Controller\WeiXinController;
use Think\Controller;
class IndexController extends WeiXinController{
  //查询券、验券,撤销验券
  public function tuangou_hexiao(){
    //
    $qr_cpde = $_GET['qr_code'];
    $open_shop_uuid = $_GET['open_shop_uuid'];

    // $arr 为数组 里面为应用参数
    $appKey = "489fe7fd6307ec07";
    $secret = "18920c6ea05a6dabca8b8e4d8c28588c34669578"; //秘钥
    $timestamp = date('Y-m-d H:i:s');
    $format = 'json';
    $v = 1;
    $sign_method = 'MD5';
    $file = $this->readFile();
    $data = [
      'app_key' => $appKey,
      'timestamp' => $timestamp,
      'sign_method' => $sign_method,
      'format' => $format,
      'v' => $v,
      'session' => $file['session'],
    ];
    if($_GET['type']=='save'){
      $arr = [
        'requestid' => '123',
        'count'=>1,
        'receipt_code' => $qr_cpde,
        'open_shop_uuid' => $open_shop_uuid,
        'app_shop_account' => '13451943596',
        'app_shop_accountname' => '13451943596',
      ];
      $url = 'https://openapi.dianping.com/router/tuangou/receipt/consume';//验券(核销)
    }elseif ($_GET['type']=='cancel'){
       $arr = [
          'app_deal_id' => $_GET['deal_id'],
          'receipt_code' => $qr_cpde,
          'open_shop_uuid' => $open_shop_uuid,
          'app_shop_account' => '13451943596',
          'app_shop_accountname' => '13451943596',
        ];
      $url = 'https://openapi.dianping.com/router/tuangou/receipt/reverseconsume';//撤销
    }else{
      $arr = [
        'receipt_code' => $qr_cpde,
        'open_shop_uuid' => $open_shop_uuid,
      ];
      $url = 'https://openapi.dianping.com/router/tuangou/receipt/prepare';//查询券
    }

    $data = array_merge($data, $arr);
    ksort($data);
    $sign = $this->call_sign($secret, $data);//获取签名
    $data['sign'] = $sign;
    $data = array_merge($data, $arr);
    $postdata = http_build_query($data);
    //输码查询券
    //$url = 'https://openapi.dianping.com/router/tuangou/receipt/scanprepare';//扫码查询券
   //
    //
    $tmpInfo= $this->curl_post($url,$postdata);
    $this->ReturnSucess($tmpInfo);

  }

  //授权获取auth_code
  public function get_auth(){
    $auth_code = $_GET["auth_code"];
    $data = $this->readFile();
    $if_expires=strtotime($data['create_date'])+intval($data['expires_in']);
    if(strtotime('now')<$if_expires&&!empty($data)&&empty($auth_code)){
      $this->assign('shop_info',$data['shop_info']);
      $this->display('NestingHTML/MT/select_shop');die;
    }

    if(empty($auth_code)) {
      $app_key='489fe7fd6307ec07';
      $state='teststate';
      $redirect_url='https://gxkj.520ph.cn/SPACE/index.php/Admin/NestingHTML/index/get_auth';
      $scope='tuangou';
      $url='https://e.dianping.com/dz-open/merchant/auth?';
      $data=[
        'app_key' =>$app_key,
        'state' => $state,
        'redirect_url'=>$redirect_url
      ];
      $postdata = http_build_query($data);
      header("Location: $url$postdata");
    } else {
      trace('回调成功'.$auth_code);
      //根据auth_code 获取session的授权码
      $tmpInfo = $this->get_session($auth_code);
      $data['session']=$tmpInfo['access_token'];
      $data['bid']=$tmpInfo['bid'];
      $data['create_date']=date('Y-m-d H:i:s');
      $data['expires_in']=$tmpInfo['expires_in'];
      $data['bid']=$tmpInfo['bid'];
      //根据$session $bid 获取店铺id
      $shopInfo = $this->get_shopid($tmpInfo['access_token'],$tmpInfo['bid']);
      $data['shop_info']=$shopInfo['data'];
      $this->createFile($data);
      $this->assign('shop_info',$data['shop_info']);
      $this->display('NestingHTML/MT/select_shop');die;
    }
  }


  //授权获取session
  public function get_session($auth_code){
    $app_key='489fe7fd6307ec07';
    $app_secret='18920c6ea05a6dabca8b8e4d8c28588c34669578';
    $grant_type='authorization_code';
    $redirect_url='https://gxkj.520ph.cn/SPACE/index.php/Admin/NestingHTML/index/get_auth';
    $data=[
      'app_key' =>$app_key,
      'app_secret' => $app_secret,
      'redirect_url' =>$redirect_url,
      'auth_code' =>$auth_code,
      'grant_type' =>$grant_type
    ];
    $postdata = http_build_query($data);
    $url='https://openapi.dianping.com/router/oauth/token';
    $tmpInfo=$this->curl_post($url,$postdata);
    return $tmpInfo;
  }


  //获取所有店铺的id
  public function get_shopid($session,$bid){
    $app_key='489fe7fd6307ec07';
    $secret = "18920c6ea05a6dabca8b8e4d8c28588c34669578"; //秘钥
    $sign_method='MD5';
    $timestamp = date('Y-m-d H:i:s');
    $format = 'json';
    $v = 1;
    $offset =0;
    $limit = 20;
    $url='https://openapi.dianping.com/router/oauth/session/scope?';
    $data=[
      'app_key' =>$app_key,
      'sign_method' => $sign_method,
      'timestamp' =>$timestamp,
      'format' =>$format,
      'v' =>$v,
      'session' =>$session,
      'bid' =>$bid,
      'offset' =>$offset,
      'limit' =>$limit,
    ];
    ksort($data);
    $sign = $this->call_sign($secret, $data);
    $data['sign'] = $sign;
    $postdata = http_build_query($data);
    $tmpInfo=$this->curl_get($url.$postdata);
    return $tmpInfo;
  }
  /*
   * 写入文件
   * */
  public function createFile($data){
    //创建一个文件
    $file = fopen('MeiTuan.txt','w');

    //写入文件
    fwrite($file, json_encode($data));

    //关闭文件
    fclose($file);
  }
  /*
   * 读取文件
   * */
  public function readFile(){
    $file = fopen('MeiTuan.txt','r');

    $data = fread( $file , filesize('MeiTuan.txt') );

    fclose($file);

    return json_decode( $data , true );
  }

  /**
   * 计算签名
   *
   * @param $app_secret 三方app_secret
   * @param $req_param 请求参数集合，包括公共参数和业务参数
   * @return string md5签名
   */
  function call_sign($secret, $data)
  {
    // 排序所有请求参数
    ksort($data);
    $src_value = "";
    // 按照key1value1key2value2...keynvaluen拼接
    foreach ($data as $key => $value) {
      $src_value .= ($key . $value);
    }
    //计算md5
    return md5($secret . $src_value . $secret);
  }

  //post请求
  function curl_post($url,$postdata){
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检测
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      'Expect:'
    )); // 解决数据包大不能提交
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata); // Post提交的数据包
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
// curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
    $tmpInfo = curl_exec($curl); // 执行操作
    if (curl_errno($curl)) {
      echo 'Errno' . curl_error($curl);
    }
    curl_close($curl); // 关键CURL会话
    $tmpInfo=json_decode($tmpInfo,true);
    return $tmpInfo;
  }

  //get请求
  private function curl_get($url) {
    //初使化curl
    $curl = curl_init();
    //请求的url，由形参传入
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检测
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      'Expect:'
    )); // 解决数据包大不能提交
    //将得到的数据返回
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //不处理头信息
    curl_setopt($curl, CURLOPT_HEADER, 0);
    //连接超过10秒超时
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    //执行curl
    $output = curl_exec($curl);
    if (curl_errno($curl)) {
      echo 'Errno' . curl_error($curl);
    }
    //关闭资源
    curl_close($curl);
    //返回内容
    $tmpInfo=json_decode($output,true);
    return $tmpInfo;
  }
}



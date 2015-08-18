<?php

class edaijia
{
  public $from = '';
  public $appkey = '';
  public $url = 'http://open.d.api.edaijia.cn/';
  public $ver = 3;
  public $phone = '';

  private $secret = '';
  private $token = '';

  /**
   * Sig签名
   *
   * @param array $params
   */
  public function sig($params)
  {
    ksort($params);

    $str = '';
    foreach ($params as $key => $value) {
      $str .= $key.$value;
    }

    $sig = md5($str . $this->secret);

    return $sig;
  }

  /**
   * 生成query
   *
   * @param array $params
   */
  public function httpBuildQuery($params = [])
  {
    $systemParams = [
      'appkey' => $this->appkey,
      'timestamp' => date('Y-m-d H:i:s'),
      'ver' => $this->ver,
      'from' => $this->from
    ];

    $params = array_merge($systemParams, array_filter($params));
    $sig = $this->sig($params);
    $query = http_build_query(array_merge($params, ['sig' =>$sig]), null, ini_get('arg_separator.output'), PHP_QUERY_RFC3986);

    return $query;
  }

  /**
   * 请求
   *
   * @param string $uri
   * @param mix $query
   */
  public function get($uri, $query)
  {
    if (is_array($query)) {
      $query = $this->httpBuildQuery($query);
    }
    $content = file_get_contents($this->url . $uri . '?' . $query);

    return $this->decode($content);
  }

  public function post($uri, $params)
  {
    if (is_array($params)) {
      $query = $this->httpBuildQuery($params);
    }

    $context = stream_context_create([
      'http' => [
        'method' => 'POST',
        'header' => 'Content-type: application/x-www-form-urlencoded',
        'content' => $query
      ]
    ]);

    $content = file_get_contents($this->url . $uri, false, $context);

    return $this->decode($content);
  }

  /**
   * 解码
   */
  public function decode($content)
  {
    return json_decode($content, true);
  }
  /**
   * 获取开通城市列表
   */
  public function cityOpenList()
  {
    $uri = 'city/open/list';
    return $this->get($uri, $this->httpBuildQuery());
  }

  /**
   * 获取城市价格
   *
   * @param string $lat
   * @param string $lng
   */
  public function cityPriceGet($lat, $lng)
  {
    $uri = 'city/price/list';
    return $this->get($uri, [
      'longitude' => $lng,
      'latitude' =>$lat
    ]);
  }

  /**
   * 下单接口
   *
   * @param string $address
   * @param string $number
   * @param string $lat
   * @param string $lng
   * @param string $gpsType
   * @param string $cityId
   * @param string $cityeName
   * @param string $bookingTime
   * @param string $contackPhone
   */
  public function orderCommit($address, $lat, $lng, $number = 1, $gpsType = 'wgs84', $cityId = null, $cityName = null, $bookingTime = null, $contackPhone = null)
  {
    $uri = 'order/commit';

    return $this->post($uri, [
      'phone' => $this->phone,
      'contackPhone' => $contackPhone,
      'address' => $address,
      'number' => $number,
      'longitude' => $lng,
      'latitude' => $lat,
      'gpsType' => $gpsType,
      'token' => $this->token,
      'cityId' => $cityId,
      'cityName' => $cityName,
      'bookingTime' => $bookingTime
    ]);
  }

  /**
   * 获取当前订单的司机的位置
   */
  public function driverPosition($bookingId, $driverId, $orderId, $pollingCount, $gpsType = 'wgs84')
  {
    $uri = 'driver/position';

    return $this->get($uri, [
      'token' => $this->token,
      'bookingId' => $bookingId,
      'driverId' => $driverId,
      'orderId' => $orderId,
      'gpsType' => $gpsType,
      'pollingCount' => $pollingCount
    ]);
  }

  /**
   * 拉取订单信息
   */
  public function orderPolling($bookingType, $pollingStart, $pollingCount, $bookingId)
  {
    $uri = 'order/polling';

    return $this->post($uri, [
      'token' => $this->token,
      'bookingType' => $bookingType,
      'pollingStart' => $pollingStart,
      'pollingCount' => $pollingCount,
      'bookingId' => $bookingId
    ]);
  }

  /**
   * 获取为我服务的司机
   */
  public function customerInfoDrivers($pollingCount = 1, $gpsType = 'wgs84')
  {
    $uri = 'customer/info/drivers';

    return $this->get($uri, [
      'token' => $this->token,
      'pollingCount' => $pollingCount,
      'gpsType' => $gpsType
    ]);
  }

  /**
   * 订单取消接口
   *
   * @param string $token
   * @param string $pollingCount 请求次数
   * @param string $bookingId 预约id
   * @param string $type
   */
  public function orderCancel($pollingCount, $bookingId, $type = 1)
  {
    $uri = 'order/cancel';

    return $this->post($uri, [
      'token' => $this->token,
      'pollingCount' =>$pollingCount,
      'bookingId' => $bookingId,
      'type' => $type
    ]);
  }

  /**
   * 历史订单的详细信息
   *
   * @param string $orderId
   */
  public function orderDetail($orderId)
  {
    $uri = 'order/detail';

    return $this->get($uri, [
      'token' => $this->token,
      'orderId' => $orderId
    ]);
  }

  /**
   * 订单评价
   */
  public function customerCommentAdd($orderId, $driverId, $level, $content, $reason = null, $status = null)
  {
    $uri = 'customer/comment/add';

    return $this->get($uri, [
      'orderId' => $orderId,
      'driverId' => $driverId,
      'level' => $level,
      'content' => $content,
      'reason' => $reason,
      'status' => $status
    ]);
  }

  /**
   * 获取登录验证码
   *
   * @param string $udid
   * @param interge $type
   * @param string $phone
   */
  public function customerLoginper($udid, $type = 1)
  {
    $uri = 'customer/loginpre';
    return $this->get($uri, [
      'udid' =>$udid,
      'type' => $type,
      'phone' => $this->phone
    ]);
  }

  /**
   * 登录接口
   *
   * @param string $phone
   * @param string $passwd
   * @param string $type
   */
  public function customerLogin($udid, $passwd, $type = 1)
  {
    $uri = 'customer/login';
    return $this->get($uri, [
      'udid' =>$udid,
      'type' => $type,
      'phone' => $this->phone,
      'passwd' => $passwd
    ]);
  }
}
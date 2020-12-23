<?php

namespace Sxqibo\Logistics;

use SoapClient;

/**
 * 三态物流 类库
 */
class Santai
{
    private $appKey;
    private $token;
    private $userId;

    /**
     * 三态 constructor.
     *
     * @param string $appKey SFC提供给用户的密钥key
     * @param string $token SFC提供给用户的密钥token
     * @param string $userId 用户 code
     */
    public function __construct($appKey, $token, $userId)
    {
        try {
            $this->appKey = trim($appKey);
            $this->token  = trim($token);
            $this->userId = trim($userId);

            if (empty($appKey)) {
                throw new \Exception("appKey is empty");
            }
            if (empty($token)) {
                throw new \Exception("token is empty");
            }
            if (empty($userId)) {
                throw new \Exception("userId is empty");
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    public function soapClient()
    {
        $client = new SoapClient('http://www.sfcservice.com/ishipsvc/web-service?wsdl');

        return $client;

    }

    public function headerParam()
    {
        //每个接口固定必填参数HeaderRequest
        $header = [
            'appKey' => $this->appKey,
            'token'  => $this->token,
            'userId' => $this->userId,
        ];


        return $header;
    }

    /**
     * 01、获取运输方式列表  （说明：目前在用，栏目：物流公司-运输方式）
     *
     * @return mixed
     */
    public function getShipTypes()
    {
        $parameter['HeaderRequest'] = $this->headerParam();
        $parameter['divisionId']    = 1; //分拨中心ID，不填则默认等于1 (即:深圳分拨中心的ID)

        $result = ($this->soapClient())->getShipTypes($parameter);

        return $result;
    }

    /**
     * 02、获取费率列表 （说明：目前在用，栏目：物流公司-物流优选）
     * 注：原来是 getRates
     *
     * @param array $param 参数
     * @return mixed
     */
    public function getPrice($param = [])
    {
        $parameter['HeaderRequest']    = $this->headerParam();
        $parameter['ratesRequestInfo'] = $param;

        $result = ($this->soapClient())->getRates($parameter);
        return $result;
    }

    /**
     * 03、添加订单
     * 注：原来是 addOrder
     *
     * @param string $orderNo 客户订单号
     * @param string $channelCode 运输方式代码
     * @param string $totalValue 总申报价值(云途这个参数没用到)
     * @param string $receiverCountryCode 收件人所在国家
     * @param string $receiverName 收件人姓
     * @param string $receiverAddress 收件人详细地址
     * @param string $receiverCity 收件人所在城市
     * @param string $rProvince 收件人所在省
     * @param string $receiverPostCode 发件人邮编
     * @param string $receiverMobile 发件人手机号
     * @param array $goods 商品属性，二维数组， 有5个必填项，包裹申报名称(中文)，包裹申报名称(英文)，申报数量，申报价格(单价)，申报重量(单重)
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createOrder(
        $orderNo, $channelCode,
        $receiverCountryCode, $receiverName, $receiverAddress, $receiverCity, $rProvince, $receiverPostCode, $receiverMobile,
        $goods = [])
    {
        $param = [
            //step1（2）
            'opDivision'        => 1,               // required, 操作分拨中心 int, 详细请看：https://www.sfcservice.com/api-doc/right/lang/cn#division_list
            'orderStatus'       => 'preprocess',    // required, 订单状态:confirmed(已确认)、preprocess(预处理)、sumbmitted(已交寄)

            //step2:收件人信息（8）
            'recipientName'     => $receiverName,           // required, （传参）收件人
            'recipientCountry'  => $receiverCountryCode,            // required, （传参）国家
            'shippingMethod'    => $channelCode,          // required, （传参）运输方式
            'recipientState'    => $rProvince,            // required, （传参）收件州省
            'recipientCity'     => $receiverCity,        // required, （传参）收件城市
            'recipientAddress'  => $receiverAddress,   // required, （传参）收件地址
            'recipientZipCode'  => $receiverPostCode,    // required, （传参）收件邮编
            'recipientPhone'    => $receiverMobile,  // required, （传参）收件电话

            //step3:各种配置信息（1）
            'goodsDeclareWorth' => array_sum(array_map(function ($val) {
                return ($val['goods_number'] * $val['goods_single_worth']);
            }, $goods)),   //订单总申报价值 required, （目前不知道）总申报价值 float，备注：这个是否是订单价格.注：这个是订单总价格
        ];

        //step4:商品信息（4）
        $param['goodsDetails'] = [];   //array, 申报信息
        foreach ($goods as $k => $v) {
            $param['goodsDetails'][$k]['detailDescription']   = $v['goods_en_name'];      //string,包裹申报名称(英文)必填
            $param['goodsDetails'][$k]['detailDescriptionCN'] = $v['goods_cn_name'];      //string,包裹申报名称(中文)，不必填
            $param['goodsDetails'][$k]['detailQuantity']      = $v['goods_number'];       //int,申报数量,必填
            $param['goodsDetails'][$k]['detailWorth']         = $v['goods_single_worth']; //decimal( 18,2),申报价格(单价),单位 USD,必填
            $param['goodsDetails'][$k]['hsCode']              = rand(100000, 99999999);       //海关编码,option,填写时必须让写，先参照写个固定值吧
        }

        $parameter['HeaderRequest']       = $this->headerParam();
        $parameter['addOrderRequestInfo'] = $param;

        $result = ($this->soapClient())->addOrder($parameter);
        $result = json_decode(json_encode($result), true); //对象转数组

        return $result;
    }

    /**
     * 04、修改订单重量和长宽高
     *
     * @param array $param 参数
     * @return mixed
     */
    public function updateOrderVolumeWeight($param = [])
    {
        $parameter['HeaderRequest']           = $this->headerParam();
        $parameter['updateOrderVolumeWeight'] = $param;

        $result = ($this->soapClient())->updateOrderVolumeWeight($parameter);
        return $result;
    }

    /**
     * 05、删除订单 deleteOrder
     *
     * @param string $orderNo 订单号
     * @return mixed
     */
    public function deleteOrder($orderNo)
    {
        $parameter['HeaderRequest']                    = $this->headerParam();
        $parameter['delOrderRequestInfo']['orderCode'] = $orderNo;

        $result = ($this->soapClient())->deleteOrder($parameter);
        return $result;
    }

    /**
     * 06、修改订单状态
     *
     * @param string $orderNo 订单号
     * @param string $orderStatus 订单状态，修改状态:preprocess(预处理)、confirmed(已确认)、sumbmitted(已交寄)、send(已发货)、delete(已删除)
     * @return mixed
     */
    public function updateOrderStatus($orderNo, $orderStatus)
    {
        $parameter['HeaderRequest']                   = $this->headerParam();
        $parameter['orderCode']                       = $orderNo;
        $parameter['updateOrderInfo']['orderStatus']  = $orderStatus;
        $parameter['updateOrderInfo']['authenticate'] = md5($orderNo . $this->userId); // 单号+客户code
        $result                                       = ($this->soapClient())->updateOrderStatus($parameter);
        return $result;
    }

    /**
     * 07、通过订单号获取费用 getFeeByOrderCode
     *
     * @param array $orderNo 订单号
     * @return mixed
     */
    public function getFeeByOrderCode($orderNo)
    {
        $parameter['HeaderRequest'] = $this->headerParam();
        $parameter['orderCode']     = $orderNo;
        $result                     = ($this->soapClient())->getFeeByOrderCode($parameter);
        return $result;
    }

    /**
     * PHP发送Json对象数据
     *
     * @param $url string 请求url
     * @return array
     */
    public function httpGetJson($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return array($httpCode, $response);
    }

    /**
     * 08、地址标签打印
     *
     * @param string $orderNo SFC单号
     * @param string $printType 打印类型
     * @param string $printType2 标签类型
     * @param string $printSize 标签尺寸
     * @return string
     */
    public function addressPrint($orderNo, $printType, $printType2, $printSize)
    {
        $url    = 'http://www.sfcservice.com/order/print/index/?orderCodeList=' . $orderNo . '&printType=' . $printType . '&isPrintDeclare=1&declare=0&ismerge=1&urluserid=OTY5&print_type=' . $printType2 . '&printSize=' . $printSize;
        return $url;
    }

    /**
     * 09、获取订单信息
     * 注：原来是 searchOrder
     *
     * @param string $orderNo 订单号
     * @return mixed
     */
    public function getOrder($orderNo)
    {
        $parameter['HeaderRequest']                       = $this->headerParam();
        $parameter['searchOrderRequestInfo']['orderCode'] = $orderNo;

        $result = ($this->soapClient())->searchOrder($parameter);
        return $result;
    }

    /**
     * 10、获取时间段订单信息
     *
     * @param string $startTime 开始日期Y-m-d
     * @param string $endTime 结束时间Y-m-d
     * @return mixed
     */
    public function searchTimeOrder($startTime, $endTime)
    {
        $parameter['HeaderRequest']                           = $this->headerParam();
        $parameter['searchTimeOrderRequestInfo']['startTime'] = $startTime;
        $parameter['searchTimeOrderRequestInfo']['endTime']   = $endTime;

        $result = ($this->soapClient())->searchTimeOrder($parameter);
        return $result;
    }

    /**
     * 11、获取时间段订单费用信息
     * 说明：接口文档说是 startTime和endTime,其实是 startime,endtime
     *
     * @param string $startTime 开始日期Y-m-d H:i:s
     * @param string $endTime 结束时间Y-m-d H:i:s
     * @return mixed
     */
    public function getFeeByTime($startTime, $endTime)
    {
        $parameter['HeaderRequest'] = $this->headerParam();
        $parameter['startime']      = $startTime;
        $parameter['endtime']       = $endTime;
        $parameter['page']          = 1;

        $result = ($this->soapClient())->getFeeByTime($parameter);
        return $result;
    }

    /**
     * 12、获取批量订单跟踪信息
     * 说明：接口有这个，但实际上没有这个接口，垃圾接口文档
     *
     * @param array $param 订单号数组，如：['LE0000009779','LE0000009778']
     * @return mixed
     */
    public function batchGetTrackingInfo($param)
    {
        $parameter['HeaderRequest']                         = $this->headerParam();
        $parameter['batchGetTrackingInfoRequest']['number'] = $param;

        $result = ($this->soapClient())->batchGetTrackingInfo($parameter);
        return $result;
    }

    /**
     * 13、获取跟踪信息（新接口）
     *
     * @param array $data 订单跟踪号组成的数组，如：'UWQ817779901000930307', 'LO971629672CN'
     * @return mixed
     */
    public function getTrack($data)
    {
        $data = json_encode($data, true);
        $url  = "http://tracking.sfcservice.com/tracking/track-api/get-track?data=$data";

        $content    = $this->httpGetJson($url);

        $content[1] = json_decode($content[1], true);

        $result['code']    = $content[1]['code'];
        $result['content'] = $content[1]['data'][0];

        return $result;
    }

    /**
     * 14、新建国内快递交货单
     *
     * @param string $companyName 快递公司
     * @param string $packageId 快递单号
     * @param int $sfcNumber SFC包裹数量
     * @return mixed
     */
    public function createExpressWaybill($companyName, $packageId, $sfcNumber)
    {
        $parameter['HeaderRequest']        = $this->headerParam();
        $parameter['data']['company_name'] = $companyName;
        $parameter['data']['package_id']   = $packageId;
        $parameter['data']['sfc_number']   = $sfcNumber;

        $result = ($this->soapClient())->createExpressWaybill($parameter);
        return $result;
    }
}
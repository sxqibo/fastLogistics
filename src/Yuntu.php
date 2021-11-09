<?php

namespace Sxqibo\Logistics;

use GuzzleHttp\Client;

/**
 * 云途物流 类库
 */
class Yuntu
{
    private $code;
    private $apiSecret;
    private $client;

    /**
     * 云途
     *
     * @param string $code      客户编号：客户新注册时由业务部门提供的客户身份唯一标识编号
     * @param string $apiSecret ApiSecret：申请 API 接口服务时由业务部门提供的一个密钥
     */
    public function __construct($code, $apiSecret)
    {
        try {
            $this->code      = trim($code);
            $this->apiSecret = trim($apiSecret);
            $this->client    = new Client([
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->code . '&' . $this->apiSecret),
                    'Content-Type'  => 'application/json',
                    'charset'       => 'UTF-8'
                ],
            ]);

            if (empty($code)) {
                throw new \Exception("code is empty");
            }
            if (empty($apiSecret)) {
                throw new \Exception("apiSecret is empty");
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 云途不同的查询地址
     *
     * @return array
     */
    public function arrUrl()
    {
        $url = 'http://oms.api.yunexpress.com/api/';
        $arr = [
            '01' => $url . 'Common/GetCountry',              //01.查询国家简码
            '02' => $url . 'Common/GetShippingMethods',      //02.查询运输方式  （目前在用，栏目：物流公司-运输方式）
            '03' => $url . 'Common/GetGoodsType',            //03.查询货品类型
            '04' => $url . 'Freight/GetPriceTrial',          //04.查询价格 （目前在用，栏目：物流公司-运输方式）
            '05' => $url . 'Waybill/GetTrackingNumber',      //05.查询跟踪号
            '06' => $url . 'WayBill/GetSender',              //06.查询发件人信息
            '07' => $url . 'WayBill/CreateOrder',            //07.运单申请
            '08' => $url . 'WayBill/GetOrder',               //08.查询运单
            '09' => $url . 'WayBill/UpdateWeight',           //09.修改订单预报重量
            '10' => $url . 'WayBill/Delete',                 //10.订单删除
            '11' => $url . 'WayBill/Intercept',              //11.订单拦截
            '12' => $url . 'Label/Print',                    //12.标签打印
            '13' => $url . 'Freight/GetShippingFeeDetail',   //13.查询物流运费明细
            '14' => $url . 'Common/Register',                //14.用户注册
            '15' => $url . 'Tracking/GetTrackInfo',          //15.查询物流轨迹信息
            '16' => $url . 'Waybill/GetCarrier',             //16.查询末端派送商
            '17' => $url . 'WayBill/RegisterIoss',           //17.客户端向OMS请求IOSS
        ];
        return $arr;
    }

    public function option($data)
    {
        $option['json'] = $data;
        return $option;
    }

    /**
     * 外汇转换接口
     * 说明：易源数据-外汇牌价汇率查询转换
     *
     * @return mixed
     */
    public function waihuiTransform()
    {
        $host    = "https://ali-waihui.showapi.com";
        $path    = "/waihui-transform";
        $appcode = "4979dea4ce2f43f2ba046cc297f5414e";
        $querys  = "fromCode=CNY&money=1&toCode=USD";
        $url     = $host . $path . "?" . $querys;
        $headers = [
            'Authorization:APPCODE ' . $appcode,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_HEADER, true); //这个会输出头部的很多信息不需要
        if (1 == strpos("$" . $host, "https://")) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        $response = curl_exec($ch);
        $response = json_decode($response, true);
        curl_close($ch);
        $result = $response['showapi_res_body']['money'];
        return $result;
    }

    /**
     * 获取数据的方式
     *
     * @param string $url  请求的URL
     * @param array  $data 语求的参数
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return mixed
     */
    public function getData($url, $data = null)
    {
        if (isset($data) && !empty($data)) {
            $response = $this->client->request('POST', $url, $data);
        } else {
            $response = $this->client->request('GET', $url);
        }
        $returnContent = $response->getBody()->getContents();

        $returnContent     = json_decode($returnContent, true);
        $result['code']    = $response->getStatusCode();
        $result['content'] = $returnContent;
        return $result;
    }


    /**
     * 01.查询国家简码
     * 说明：我这里带不带countryCode都是268个国家
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return mixed
     */
    public function getCountry()
    {
        $url    = ($this->arrUrl())['01'];
        $result = $this->getData($url);
        return $result;
    }

    /**
     * 02.查询运输方式
     * 说明：目前在用,原来是： getShippingMethods
     *
     * @param string $countryCode 国家简码，未填写国家代表查询所有运输方式
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return mixed
     */
    public function getShipTypes($countryCode = null)
    {
        $url = ($this->arrUrl())['02'];
        if (!empty($countryCode)) {
            $url = $url . '?CountryCode=' . $countryCode;
        }
        $result = $this->getData($url);
        return $result;
    }

    /**
     * 03.查询货品类型
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return mixed
     */
    public function getGoodsType()
    {
        $url    = ($this->arrUrl())['03'];
        $result = $this->getData($url);
        return $result;
    }

    /**
     * 04.查询价格
     * 原来是 getPriceTrial
     *
     * @param string $countryCode 必须，国家简码
     * @param int    $weight      必须，包裹重量，单位 kg,支持 3 位小数
     * @param int    $length      包裹长度,单位 cm,不带小数,不填写默认 1
     * @param int    $width       包裹宽度,单位 cm,不带小数，不填写默认 1
     * @param int    $height      包裹高度,单位 cm,不带小数，不填写默认 1
     * @param int    $packageType 包裹类型，1-包裹，2-文件，3-防水袋，默认 1
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return mixed
     */
    public function getPrice($countryCode, $weight, $length = null, $width = null, $height = null, $packageType = null)
    {
        $url = ($this->arrUrl())['04'];
        $url = $url . '?CountryCode=' . $countryCode;

        //重量
        if (isset($weight)) {
            $url = $url . '&Weight=' . $weight;
        }

        //长度
        if (isset($length)) {
            $url = $url . '&Length=' . $length;
        } else {
            $url = $url . '&Length=1';
        }

        //宽度
        if (isset($width)) {
            $url = $url . '&Width=' . $width;
        } else {
            $url = $url . '&Width=1';
        }

        //宽度
        if (isset($height)) {
            $url = $url . '&Height=' . $height;
        } else {
            $url = $url . '&Height=1';
        }

        //包裹长度
        if (isset($packageType)) {
            $url = $url . '&ShippingTypeid=' . $packageType;
        } else {
            $url = $url . '&ShippingTypeid=1';
        }

        $result = $this->getData($url);
        return $result;
    }

    /**
     * 05.查询跟踪号
     *
     * @param string $customerOrderNumber 客户订单号,多个以逗号分开
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return mixed
     */
    public function getTrackingNumber($customerOrderNumber)
    {
        $url = ($this->arrUrl())['05'];
        $url = trim($url . "?CustomerOrderNumber=$customerOrderNumber");

        $result = $this->getData($url);
        return $result;
    }

    /**
     * 06. 查询发件人信息
     *
     * @param string $orderNumber 查询号码，可输入运单号、订单号、跟踪号
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return mixed
     */
    public function getSender($orderNumber)
    {
        $url = ($this->arrUrl())['06'];
        $url = $url . '?OrderNumber=' . $orderNumber;

        $result = $this->getData($url);
        return $result;
    }

    /**
     * 07.运单申请
     * 备注：支持一个包裹多个商品
     *
     * @param string $orderNo             客户订单号
     * @param string $channelCode         运输方式代码
     * @param string $totalValue          总申报价值(云途这个参数没用到)
     * @param string $receiverCountryCode 收件人所在国家
     * @param string $receiverName        收件人姓
     * @param string $receiverAddress     收件人详细地址
     * @param string $receiverCity        收件人所在城市
     * @param string $rProvince           收件人所在省
     * @param string $receiverPostCode    发件人邮编
     * @param string $receiverMobile      发件人手机号
     * @param array  $goods               商品属性，二维数组， 有5个必填项，包裹申报名称(中文)，包裹申报名称(英文)，申报数量，申报价格(单价)，申报重量(单重)
     * @param string $iossCode            云途备案识别码或IOSS号
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return mixed
     */
    public function createOrder(
        $orderNo, $channelCode,
        $receiverCountryCode, $receiverName, $receiverAddress, $receiverAddress1, $receiverAddress2, $receiverCity, $rProvince, $receiverPostCode, $receiverMobile,
        $goods = [], $iossCode = '', $remarks = '')
    {
        $url = ($this->arrUrl())['07'];

        //step1:订单
        $order = [
            'CustomerOrderNumber' => $orderNo,                      //string,客户订单号,不能重复，必填
            'ShippingMethodCode'  => $channelCode,                  //string,运输方式代码，必填
            'PackageCount'        => 1,                //string,运单包裹的件数，必须大于 0 的整数，必填，这里写成1，一般都是1件，如果有拆包的话，接口会返回正确的
            'Weight'              => array_sum(array_map(function ($val) {
                return ($val['goods_number'] * $val['goods_single_weight']);
            }, $goods)),   //decimal,预估包裹总重量，单位 kg,最多 3 位小数，必填,两个数字求和
        ];

        //step2:收件人
        $order['Receiver'] = [ //array, 收件人信息，必填
                               'CountryCode'    => $receiverCountryCode,              //string,收件人所在国家，填写国际通用标准 2 位简码， 可通过国家查询服务查询，必填
                               'FirstName'      => $receiverName,                     //string,收件人姓，必填
                               'Street'         => $receiverAddress,                  //string,收件人详细地址，必填
                               'StreetAddress1' => $receiverAddress1,                  //string,收件人详细地址1，必填
                               'StreetAddress2' => $receiverAddress2,                  //string,收件人详细地址2，必填
                               'City'           => $receiverCity,                     //string,收件人所在城市,非必填
                               'State'          => $rProvince,                        //string,发件人省/州,非必填
                               'Zip'            => $receiverPostCode,                 //string,发件人邮编,非必填
                               'Phone'          => $receiverMobile,                   //string,发件人电话,非必填
        ];

        //step3:产品信息
        $order['Parcels'] = [];   //array, 申报信息
        foreach ($goods as $k => $v) {
            $order['Parcels'][$k]['EName']        = $v['goods_en_name'];        //string,包裹申报名称(英文)必填
            $order['Parcels'][$k]['CName']        = $v['goods_cn_name'];        //string,包裹申报名称(中文)，不必填
            $order['Parcels'][$k]['Quantity']     = $v['goods_number'];         //int,申报数量,必填
            $order['Parcels'][$k]['UnitPrice']    = $v['goods_single_worth'] * $this->waihuiTransform();   //decimal( 18,2),申报价格(单价),单位 USD,必填
            $order['Parcels'][$k]['UnitWeight']   = $v['goods_single_weight'];  //decimal( 18,3),申报重量(单重)，单位 kg,,必填
            $order['Parcels'][$k]['Remark']       = $remarks;                   //订单备注，用于打印配货单
            $order['Parcels'][$k]['CurrencyCode'] = 'USD';                      //string,申报币种，默认：USD,必填
        }

        //step4:附加服务
        //add by hongwei 20210626根据《云途物流API接口开发规范OMS-20210625》
        //这里说是必填，但是填了会报错
        //$order['OrderExtra']['ExtraCode'] = 'V1'; //（必填）额外服务代码，G0代表关税预付，10代表报关件， V1代表云途预缴IOSS附加服务费。
        //$order['OrderExtra']['ExtraName'] = '云途预缴IOSS附加服务费'; //（必填）额外服务代码，G0代表关税预付，10代表报关件， V1代表云途预缴IOSS附加服务费。

        //step5:IossCode
        //add by hongwei 20210626根据《云途物流API接口开发规范OMS-20210625》
        $order['IossCode'] = $iossCode; //(非必填）云途备案识别码或IOSS号

        $response          = $this->client->request('POST', $url, [
            'json' => [
                $order
            ],
        ]);
        $returnContent     = $response->getBody()->getContents();
        $returnContent     = json_decode($returnContent, true);
        $result['code']    = $returnContent['Code'];
        $result['content'] = $returnContent['Item'];


        return $result;
    }

    /**
     * 08.查询运单
     *
     * @param string $orderNo 订单号
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return mixed
     */
    public function getOrder($orderNo)
    {
        $url = ($this->arrUrl())['08'];
        $url = $url . '?OrderNumber=' . $orderNo;

        $result = $this->getData($url);
        return $result;
    }

    /**
     * 09.修改订单预报重量
     *
     * @param string $orderNo 订单号
     * @param float  $weight  修改重量
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return mixed
     * @todo 待测试
     */
    public function updateWeight($orderNo, $weight)
    {
        $data   = [
            'OrderNumber' => $orderNo,
            'Weight'      => $weight,
        ];
        $url    = ($this->arrUrl())['09'];
        $result = $this->getData($url, $data);
        return $result;
    }

    /**
     * 10.订单删除
     *
     * @param string $orderNo 订单号
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return mixed
     * @todo 待测试
     */
    public function delete($orderNo)
    {
        $data   = [
            'OrderType'   => 2,  //单号类型：1-云途单号,2-客户订单号,3-跟踪号，我们这里选择客户订单号，方便快捷
            'OrderNumber' => $orderNo,
        ];
        $url    = ($this->arrUrl())['10'];
        $result = $this->getData($url, $data);
        return $result;
    }

    /**
     * 11.订单拦截
     *
     * @param string $orderNo 订单号
     * @param string $remark  拦截原因
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return mixed
     * @todo 待测试
     */
    public function intercept($orderNo, $remark)
    {
        $data   = [
            'OrderType'   => 2,         //单号类型：1-云途单号,2-客户订单号,3-跟踪号，我们这里选择客户订单号，方便快捷
            'OrderNumber' => $orderNo,
            'Remark'      => $remark,
        ];
        $url    = ($this->arrUrl())['11'];
        $result = $this->getData($url, $data);
        return $result;
    }

    /**
     * 12.标签打印
     * @param string $orderNo 订单号
     * @return mixed
     */
    public function labelPrint($orderNo)
    {
        $url               = ($this->arrUrl())['12'];
        $response          = $this->client->request('POST', $url, [
            'json' => [
                $orderNo
            ],
        ]);
        $returnContent     = $response->getBody()->getContents();
        $returnContent     = json_decode($returnContent, true);
        $result['code']    = $returnContent['Code'];
        $result['content'] = $returnContent['Item'];
        return $result;
    }

    /**
     * 13.查询物流运费明细
     *
     * @param string $wayBillNumber 运单号
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return mixed
     * @todo
     */
    public function getShippingFeeDetail($wayBillNumber)
    {
        $url = ($this->arrUrl())['13'];
        $url = $url . '?wayBillNumber=' . $wayBillNumber;

        $result = $this->getData($url);
        return $result;
    }

    /**
     * 14. 用户注册
     *
     * @param array $params
     * @return mixed
     */
    public function register(array $params)
    {
        $url               = ($this->arrUrl())['14'];
        $response          = $this->client->request('POST', $url, [
            'json' => [
                $params
            ],
        ]);
        $returnContent     = $response->getBody()->getContents();
        $returnContent     = json_decode($returnContent, true);
        $result['code']    = $returnContent['Code'];
        $result['content'] = $returnContent['Item'];


        return $result;
    }

    /**
     * 15. 查询物流轨迹信息
     * @param string $OrderNumber 物流系统运单号，客户订单或跟踪号
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return mixed
     */
    public function getTrackInfo($OrderNumber)
    {
        $url = ($this->arrUrl())['15'];
        $url = trim($url . "?OrderNumber=$OrderNumber");

        $result = $this->getData($url);
        return $result;
    }

    /**
     * 16. 查询末端派送商
     *
     * @param $OrderNumber string 查询号码，可输入运单号、订单号、跟踪号
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return mixed
     */
    public function getCarrier($OrderNumber)
    {
        $url    = ($this->arrUrl())['16'];
        $data   = [
            'OrderNumbers' => $OrderNumber
        ];
        $result = $this->getData($url, $data);
        return $result;
    }

    /**
     * 17. 客户端向 OMS 请求IOSS，
     * 说明：这里是进行注册用的，目前没用
     */
    public function registerIoss($iossNumber)
    {
        $url    = ($this->arrUrl())['17'];
        $data   = [
            'IossType'     => 0,     //(必填）“0”个人 “1”平台
            'PlatformName' => '',    //(非必填）平台名称，类型为“1”时需提供
            'IossNumber'   => 'xy1234567890',   //(必填）2位字母加10位数字，reg: ^[a-zA-Z]{2}[0-9]{10}$
            'Company'      => '',    //(非必填）IOSS号注册公司名称
            'Country'      => '',    //(非必填）2位国家简码
            'Street'       => '',    //(非必填）IOSS号街道地址
            'City'         => '',    //(非必填）IOSS号所在城市
            'Province'     => '',    //(非必填）IOSS号所在省/州
            'PostalCode'   => '',    //(非必填）IOSS号邮编
            'MobilePhone'  => '',    //(非必填）IOSS号手机号
            'Email'        => '',    //(非必填）IOSS号电子邮箱
        ];
        $result = $this->getData($url, $data);
        return $result;
    }
}
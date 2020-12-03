<?php

namespace Sxqibo\Logistics;

/**
 * 杰航物流 类库
 */
class Jiehang
{
    private $clientId;
    private $token;

    /**
     * 杰航
     * @param string $clientId 客户编码（由物流商提供）
     * @param string $token 验证 Token（由物流商提供）
     */
    public function __construct($clientId, $token)
    {
        try {
            $this->clientId = trim($clientId);
            $this->token    = trim($token);

            if (empty($clientId)) {
                throw new \Exception("clientId is empty");
            }
            if (empty($token)) {
                throw new \Exception("token is empty");
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 杰航不同的查询地址
     *
     * @return array
     */
    public function arrUrl()
    {
        $baseUrl = 'http://xt.jiehang.net/PostInterfaceService?method=';
        $arr     = [
            '01' => $baseUrl . 'createOrder',            //01、 创建快件订单,仓储订单,快递制单
            '02' => $baseUrl . 'updateOrder',            //02、 修改快件订单,仓储订单,快递制单
            '03' => $baseUrl . 'deleteOrder',            //03、 删除快件订单,仓储订单,快递制单
            '04' => $baseUrl . 'searchOrderTracknumber', //04、 根据公司单号提取转单号
            '05' => $baseUrl . 'searchStartHouse',       //05、 查询启用的仓库
            '06' => $baseUrl . 'searchStartChannel',     //06、 查询启用的入仓渠道 （目前在用，栏目：物流公司-运输方式）
            '07' => $baseUrl . 'printOrderLabel',        //07、 打印地址标签
            '08' => $baseUrl . 'searchPrintPaper',       //08、 根据渠道查询支持的打印纸张
            '09' => $baseUrl . 'searchInsuranceType',    //09、 查询保险类型
            '10' => $baseUrl . 'searchPrice',            //10、 查价格 （目前在用，栏目：物流公司-运输方式）
            '11' => $baseUrl . 'searchTrack',            //11、 查轨迹
        ];
        return $arr;
    }

    /**
     * PHP发送Json对象数据
     *
     * @param $url string 请求url
     * @param $jsonStr string 发送的json字符串
     * @return array
     */
    public function httpPostJson($url, $jsonStr)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $header = [
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($jsonStr)
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return array($httpCode, $response);
    }

    /**
     * 基本参数
     *
     * @return array
     */
    public function paramVerify()
    {
        $arr = [
            'Clientid' => $this->clientId,
            'Token'    => $this->token,
        ];
        return $arr;
    }

    /**
     * 杰航查询相关信息
     * @param string $urlSearch 查询网址
     * @param array $arr 查询数组
     * @return mixed
     */
    function getData($urlSearch, $arr = [])
    {
        $jsonStr = json_encode($arr);
        list($returnCode, $returnContent) = $this->httpPostJson($urlSearch, $jsonStr);

        $returnContent     = json_decode($returnContent, true);
        $result['code']    = $returnCode;
        $result['content'] = $returnContent;

        return $result;
    }

    /**
     * 01、 创建快件订单,仓储订单,快递制单
     * 15个参数
     *
     * @param string $orderNo 客户订单号
     * @param string $channelCode 运输方式代码
     * @param string $totalValue 总申报价值
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
        // step1.1:（参数）
        $data['Verify'] = $this->paramVerify();

        /**
         * step1.2:（参数）订单类型
         * 1：快件订单
         * 2：快递制单-非实时返回单号
         * 3：仓储订单
         * 4：快递制单-实时返回单号(等待时间较长)。此方法选择 4，后续如需调用其他法，例如调用删除接口，
         * 其他方法 OrderType 请选择 2。
         */
        $data['OrderType'] = 1;  //订单类型，旧系统就用这个

        // step1.3:（参数）订单数据
        $data['OrderDatas'] = [
            [
                'CustomerNumber' => $orderNo,               //客户订单号(可传入贵公司内部单号)
                'ChannelCode'    => $channelCode,           //渠道代码
                'CountryCode'    => $receiverCountryCode,   //国家二字代码
                'TotalWeight'    => array_sum(array_map(function ($val) {return ($val['goods_number'] * $val['goods_single_weight']);}, $goods)),  //订单总重量
                'TotalValue'     => array_sum(array_map(function ($val) {return ($val['goods_number'] * $val['goods_single_worth']);}, $goods)),   //订单总申报价值
                'Number'         => array_sum(array_map(function ($val) {return ($val['goods_number']);}, $goods))  //件数
            ]
        ];

        // step1.4:（参数）是否购买保险
        $data['Insurance'] = [];  //这里不是必须，我们暂不填

        // step1.5:（参数）运费支付信息，OrderType 为 [快递制单] 时必传字段，我们用的是“快件订单”
        $data['FeePayData'] = [];

        // step1.6:（参数）税金/关税支付信息，OrderType 为 [快递制单] 时必传字段
        $data['TaxPayData'] = [];

        // step1.7:（参数）收件人信息
        $data['Recipient'] = [
            'Name'     => $receiverName,        //名称
            'Addres1'  => $receiverAddress,     //地址
            'Mobi'     => $receiverMobile,      //手机
            'Province' => $rProvince,    //省州
            'City'     => $receiverCity,        //城市
            'Post'     => $receiverPostCode,    //邮编
        ];

        // step1.8:（参数）寄件人信息，不是必填信息，我们这里不传
        $data['Sender'] = [];

        // step1.9:（参数）订单明细产品信息
        $data['OrderItems'];   //array, 申报信息
        foreach ($goods as $k => $v) {
            $data['OrderItems'][$k]['Enname'] = $v['goods_en_name'];        //string,包裹申报名称(英文)必填
            $data['OrderItems'][$k]['Cnname'] = $v['goods_cn_name'];        //string,包裹申报名称(中文)，不必填
            $data['OrderItems'][$k]['Num']    = $v['goods_number'];         //int,申报数量,必填
            $data['OrderItems'][$k]['Price']  = $v['goods_single_worth'] ;         //decimal( 18,2),申报价格(单价),必填
            $data['OrderItems'][$k]['Weight'] = $v['goods_single_weight'];  //decimal( 18,3),申报重量(单重)，单位 kg,,必填
        }

        // step1.10:（参数）材积明细 (OrderType 为快递制单必传)，不是必填信息，我们这里不传
        $data['Volumes'] = [];

        // step2:网址
        $url = $this->arrUrl();
        $url = $url['01'];

        // step3:提交
        $result = $this->getData($url, $data);
        return $result;
    }


    /**
     *  02、 修改快件订单,仓储订单,快递制单
     * 16个参数，最后一个参数不一样，是创建订单是返回的
     *
     * @param string $orderNo 客户订单号(可传入贵公司内部单号)
     * @param string $channelCode 渠道代码
     * @param string $countryCode 国家二字代码
     * @param string $totalValue 订单总申报价值
     * @param string $number 件数
     * @param string $name 名称
     * @param string $address 地址
     * @param string $mobile 手机
     * @param string $province 省州
     * @param string $city 城市
     * @param string $postCode 邮编
     * @param string $cnname 产品中文名
     * @param string $enname 产品英文名
     * @param string $price 单价
     * @param string $weight 重量
     * @param string $corpBillid 订单号,注意：创建订单后返回，修改订单必传
     * @return mixed
     */
    public function updateOrder($orderNo, $channelCode, $countryCode, $totalValue, $number, $name, $address, $mobile, $province, $city, $postCode, $cnname, $enname, $price, $weight, $corpBillid)
    {
        // step1.1:（参数）
        $data['Verify'] = $this->paramVerify();

        /**
         * step1.2:（参数）订单类型
         * 1：快件订单
         * 2：快递制单-非实时返回单号
         * 3：仓储订单
         * 4：快递制单-实时返回单号(等待时间较长)。此方法选择 4，后续如需调用其他法，例如调用删除接口，
         * 其他方法 OrderType 请选择 2。
         */
        $data['OrderType'] = 1;  //订单类型，旧系统就用这个

        // step1.3:（参数）订单数据
        $data['OrderDatas'] = [
            [
                'CorpBillid'     => $corpBillid,           //订单号注意：创建订单后返回，修改订单必传
                'CustomerNumber' => $orderNo,        //客户订单号(可传入贵公司内部单号)
                'ChannelCode'    => $channelCode,       //渠道代码
                'CountryCode'    => $countryCode,       //国家二字代码
                'TotalWeight'    => $weight * $number,  //订单总重量
                'TotalValue'     => $totalValue,         //订单总申报价值
                'Number'         => $number                 //件数
            ]
        ];

        // step1.4:（参数）是否购买保险
        $data['Insurance'] = [];  //这里不是必须，我们暂不填

        // step1.5:（参数）运费支付信息，OrderType 为 [快递制单] 时必传字段，我们用的是“快件订单”
        $data['FeePayData'] = [];

        // step1.6:（参数）税金/关税支付信息，OrderType 为 [快递制单] 时必传字段
        $data['TaxPayData'] = [];

        // step1.7:（参数）收件人信息
        $data['Recipient'] = [
            'Name'     => $name,        //名称
            'Addres1'  => $address,     //地址
            'Mobi'     => $mobile,      //手机
            'Province' => $province,    //省州
            'City'     => $city,        //城市
            'Post'     => $postCode,    //邮编
        ];

        // step1.8:（参数）寄件人信息，不是必填信息，我们这里不传
        $data['Sender'] = [];

        // step1.9:（参数）订单明细产品信息
        $data['OrderItems'] = [
            'Cnname' => $cnname,    //产品中文名
            'Enname' => $enname,    //产品英文名
            'Price'  => $price,     //单价
            'Weight' => $weight,    //重量
            'Num'    => $number,    //数量
        ];

        // step1.10:（参数）材积明细 (OrderType 为快递制单必传)，不是必填信息，我们这里不传
        $data['Volumes'] = [];

        // step2:网址
        $url = $this->arrUrl();
        $url = $url['02'];

        // step3:提交
        $result = $this->getData($url, $data);
        return $result;
    }

    /**
     * 03、 删除快件订单,仓储订单,快递制单
     *
     * @param string $corpBillid 公司订单号 (创建订单时已返回)
     * @return mixed
     */
    public function deleteOrder($corpBillid)
    {
        // step1.1:参数
        $data['OrderType'] = 1;

        // step1.2:参数
        $data['Verify'] = $this->paramVerify();

        // step1.3:参数
        $data['CorpBillidDatas'] = [
            [
                'CorpBillid' => trim($corpBillid)
            ]
        ];

        // step2:网址
        $url = $this->arrUrl();
        $url = $url['03'];

        // step3:提交
        $result = $this->getData($url, $data);
        return $result;
    }

    /**
     * 04、 根据公司单号提取转单号
     *
     * @param string $corpBillid 公司订单号 (创建订单时已返回)
     * @return mixed
     */
    public function searchOrderTracknumber($corpBillid)
    {
        // step1.1:参数
        $data['OrderType'] = 1;

        // step1.2:参数
        $data['Verify'] = $this->paramVerify();

        // step1.3:参数
        $data['CorpBillidDatas'] = [
            [
                'CorpBillid' => trim($corpBillid)
            ]
        ];

        // step2:网址
        $url = $this->arrUrl();
        $url = $url['04'];

        // step3:提交
        $result = $this->getData($url, $data);
        return $result;
    }

    /**
     * 05、 查询启用的仓库
     *
     * @return mixed
     */
    public function searchStartHouse()
    {
        // step1:参数
        $data['Verify'] = $this->paramVerify();

        // step2:网址
        $url = $this->arrUrl();
        $url = $url['05'];

        // step3:提交
        $result = $this->getData($url, $data);
        return $result;
    }


    /**
     * 06、 查询启用的入仓渠道 （目前在用，栏目：物流公司-运输方式）
     * 注：原来是：searchStartChannel
     *
     * @return mixed
     */
    public function getShipTypes()
    {
        // step1:参数
        $data['Verify'] = $this->paramVerify();

        // step2:网址
        $url = $this->arrUrl();
        $url = $url['06'];

        // step3:提交
        $result = $this->getData($url, $data);
        return $result;
    }

    /**
     * 07、 打印地址标签
     *
     * @return mixed
     */
    public function printOrderLabel($corpBillid)
    {
        // step1.1:参数
        $data['Verify'] = $this->paramVerify();

        // step1.2:参数
        $data['CorpBillidDatas'] = [
            [
                'CorpBillid' => trim($corpBillid)
            ]
        ];

        // step1.3:参数
        $data['OrderType'] = 1;

        // step1.4:参数
        $data['PrintPaper'] = 'A4';  //打印纸张可以通过调用 [searchPrintPaper] 获取到的 paperCode,快递制单只固定为：label 和 A4

        // step1.5:参数
        $data['PrintContent'] = 1;  //打印内容,1：地址标签 2：报关单 3：配货信息 可自由组合用“,”号隔开 如：1,2,3

        // step2:网址
        $url = $this->arrUrl();
        $url = $url['07'];

        // step3:提交
        $result = $this->getData($url, $data);
        return $result;
    }

    /**
     * 08、 根据渠道查询支持的打印纸张
     *
     * @param $channelCode
     * @return mixed
     */
    public function searchPrintPaper($channelCode)
    {
        // step1.1:参数
        $data['Verify'] = $this->paramVerify();

        // step1.2:参数
        $data['ChannelCode'] = trim($channelCode);

        // step2:网址
        $url = $this->arrUrl();
        $url = $url['08'];

        // step3:提交
        $result = $this->getData($url, $data);
        return $result;
    }

    /**
     * 09、 查询保险类型
     *
     * @return mixed
     */
    public function searchInsuranceType()
    {
        // step1.1:参数
        $data['Verify'] = $this->paramVerify();

        // step2:网址
        $url = $this->arrUrl();
        $url = $url['09'];

        // step3:提交
        $result = $this->getData($url, $data);
        return $result;
    }

    /**
     * 10、 查价格 （目前在用，栏目：物流公司-运输方式）
     * 注：原来是 searchPrice
     *
     * @param string $countryCode 目的地国家(必填)
     * @param double $weight 实重(必填)
     * @param string $goodsType 货物类型（默认 WPX） WPX：包裹, DOC：文件, PAK：PAK 袋（不必填）
     * @param null $postCode 目的地邮编（不必填）
     *
     * @return mixed
     */
    public function getPrice($countryCode, $weight, $goodsType = 'WPX', $postCode = null)
    {
        // step1:参数1
        $data['Verify'] = $this->paramVerify();

        // step2:参数2
        $data['Data'] ['CountryCode'] = trim($countryCode);
        $data['Data'] ['Weight']      = trim($weight);
        $data['Data'] ['GoodsType']   = trim($goodsType);
        if (isset($postCode)) {
            $data['Data'] ['PostCode'] = trim($postCode);
        }

        // step3:网址
        $url = $this->arrUrl();
        $url = $url['10'];

        // step4:提交
        $result = $this->getData($url, $data);
        return $result;
    }

    /**
     * 11、 查轨迹
     * 原来是 searchTrack
     *
     * @param string $trackNumber 运单号,转单号
     * @return mixed
     */
    public function getTrack($trackNumber)
    {
        // step1.1:参数
        $data['Verify'] = $this->paramVerify();

        // step1.2:参数
        $data['Datas'] = [
            [
                'TrackNumber' => trim($trackNumber)
            ]
        ];

        // step2:网址
        $url = $this->arrUrl();
        $url = $url['11'];

        // step3:提交
        $result = $this->getData($url, $data);
        return $result;
    }

}
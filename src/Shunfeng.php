<?php


namespace Sxqibo\Logistics;

use SoapClient;
use Exception;
use Sxqibo\Logistics\common\Client;
use Sxqibo\Logistics\common\Utility;

/**
 * 顺丰国际物流类
 * Class Shunfeng
 * @package Sxqibo\Logistics
 */
class Shunfeng
{
    private $serviceEndPoint = 'http://sfapi.trackmeeasy.com/ruserver/webservice/sfexpressService?wsdl'; // 正式环境
    private $testServiceEndPoint = 'http://kts-api-uat.trackmeeasy.com/webservice/sfexpressService?wsdl'; // 测试环境

    private $labelEndPoint = 'http://sfapi.trackmeeasy.com/ruserver/api/getLabelUrl.action'; // 正式环境
    private $testLabelEndPoint = 'http://oms.uat.trackmeeasy.com/ruserver/api/getLabelUrl.action'; // 测试环境

    private $checkWord; // 接口校验码
    private $accessCode; // 接入编码(用户名)
    private $headers;

    public function __construct($accessCode, $checkWord)
    {
        $this->checkWord = $checkWord;
        $this->headers   = [
            'Content-Type' => 'text/xml; charset=UTF-8'
        ];

        $this->checkWord  = $checkWord;
        $this->accessCode = $accessCode;
    }

    /**
     * 获取请求节点信息
     *
     * @param $key
     * @param false $isDebug
     * @throws Exception
     * @return string[]
     */
    public function getEndPoint($key, $isDebug = false)
    {
        $endpoints = [
            'createOrder' => [
                'method' => 'POST',
                'url'    => $isDebug ? $this->testServiceEndPoint : $this->serviceEndPoint,
                'remark' => '创建订单'
            ],
            'labelPrint'  => [
                'method' => 'GET',
                'url'    => $isDebug ? $this->testLabelEndPoint : $this->labelEndPoint,
                'remark' => '单个标签生成'
            ],
        ];

        if (isset($endpoints[$key])) {
            return $endpoints[$key];
        } else {
            throw new Exception('未找到对应的接口信息 ' . $key);
        }
    }

    /**
     * 创建订单
     *
     * @param $data
     * @param false $isDebug
     * @throws \SoapFault
     * @return array
     */
    public function createOrder($data, $isDebug = false)
    {
        // 1.格式化数据
        $newData = $this->formatData($data);

        $customRoot = [
            'rootElementName' => 'Request',
            '_attributes'     => [
                'service' => 'OrderService',
                'lang'    => 'zh_CN'
            ],
        ];
        $body       = Utility::arrayToXml($newData, $customRoot);

        // 2.创建订单
        $verifyCode = $this->getSign($body);
        $endPoint   = $this->getEndPoint('createOrder', $isDebug);
        $result     = (new SoapClient($endPoint['url']))->sfKtsService($body, $verifyCode);

        $result = Utility::xmlToArray($result);

        if ($result['Head'] == 'ERR') {
            return ['code' => -1, 'message' => $result['ERROR'] ?? '', 'data' => []];
        } else {
            return ['code' => 0, 'message' => '成功', 'data' => []]; // todo
        }
    }

    /**
     * 标签打印
     *
     * @param $orderNo
     * @param $mailNo
     * @throws Exception
     */
    public function labelPrint($orderNo, $mailNo, $isDebug = false)
    {
        $client = new Client();
        $params = [
            'orderid'    => $orderNo,
            'mailno'     => $mailNo,
            'onepdf'     => '否', // 是否合成一个PDF
            'jianhuodan' => '否', // 是否打印拣货单
            'username'   => $this->accessCode, // 接入编码
            'signature'  => $this->getSign($this->accessCode)
        ];

        $endPoint = $this->getEndPoint('labelPrint', $isDebug);

        $headers = [
            'Content-Type' => 'application/json'
        ];

        $result = $client->requestApi($endPoint, $params, [], $headers, true);

        return $result;
    }

    /**
     * 查询物流发货渠道
     *
     * @return array[]
     */
    public function getShipTypes()
    {
        $arr = [
            [
                'code' => 9,
                'name' => '国际小包平邮'
            ],
            [
                'code' => 10,
                'name' => '国际小包挂号'
            ],
            [
                'code' => 23,
                'name' => '国际小包陆运平邮'
            ],
            [
                'code' => 24,
                'name' => '国际小包陆运挂号'
            ],
            [
                'code' => 25,
                'name' => '国际经济小包平邮'
            ],
            [
                'code' => 26,
                'name' => '国际经济小包挂号'
            ],
            [
                'code' => 27,
                'name' => '国际专线小包平邮'
            ],
            [
                'code' => 28,
                'name' => '国际专线小包挂号'
            ],
            [
                'code' => 29,
                'name' => '国际电商专递'
            ],
            [
                'code' => 32,
                'name' => '国际精品小包'
            ],
            [
                'code' => 38,
                'name' => '国际南美小包挂号'
            ],
            [
                'code' => 44,
                'name' => '国际卢邮小包挂号'
            ],
            [
                'code' => 47,
                'name' => '国际比邮小包平邮'
            ],
            [
                'code' => 48,
                'name' => '国际比邮小包特惠挂号'
            ], [
                'code' => 63,
                'name' => '国际特货小包平邮'
            ],
            [
                'code' => 64,
                'name' => '国际特货小包挂号'
            ],
            [
                'code' => 72,
                'name' => '国际电商专递-CD'
            ],
            [
                'code' => 93,
                'name' => '国际铁路经济小包平邮'
            ],
            [
                'code' => 94,
                'name' => '国际铁路经济小包挂号'
            ]

        ];

        return $arr;
    }

    /**
     * 格式化数据
     *
     * @param $data
     * @return string[]
     */
    public function formatData($data)
    {
        $goods = $data['goods'];

        $productList   = [];
        $packageWeight = 0; // 总重量公斤
        $packagePrice  = 0;

        foreach ($goods as $item) {
            $productList[] = [
                '_attributes' => [
                    'name'       => $item['goods_en_name'], // 商品（英文）报关品名 要求品名明确清楚，简洁明了，如：Earrings, Plastic film, Dress等，不接受无效品名，如：Home Commodities, ATMEGA328P, Outdoor sports等
                    'count'      => $item['goods_number'], // 货物数量
                    'weight'     => $item['goods_single_weight'], // 货物单位重量（不能小于0, 单位KG）
                    'amount'     => $item['goods_single_worth'], // 货物单价（不能小于0）
                    'currency'   => 'USD', // 货物单价的币别：USD: 美元
                    'cname'      => $item['goods_cn_name'],     // 商品（中文）报关品名 必须包含中文
                    'unit'       => 'piece', // 货物单位（英文）如：piece
                    'cargo_desc' => '', // 货物明细描述/拣货信息
                    'hscode'     => '', // hscode 海关编码
                    'order_url'  => '', // 商品网址链接url express_type为23,24，29时必填
                ],
            ];

            $packageWeight += ($item['goods_number'] * $item['goods_single_weight']);
            $packagePrice  += ($item['goods_number'] * $item['goods_single_worth']);
        }

        $senderInfo = $data['sender'] ?? []; // 发件人信息
        $newData    = [
            'Head' => $this->accessCode,
            'Body' => [
                'Order' => [
                    '_attributes' => [
                        'orderid'           => $data['orderNo'], // 订单号
                        'platform_order_id' => $data['orderNo'], // 平台订单号，不能重复（仅限：字母、数字、中划线、下划线 ）。无平台订单号，直接使用客户订单号即可。
                        'platform_code'     => '0003', // 电商平台简称 0003- 亚马逊平台
                        'erp_code'          => '0000', // ERP平台名称 未知ERP（默认值:0000）
                        // 'platform_merchant_id' => '', //电商平台ID字段 非必填
                        'express_type'      => $data['channelCode'], // 快件产品类别

                        // 发件人信息 - 必填项
                        'j_company'         => $senderInfo['company_name'] ?? '', // 寄方公司
                        'j_contact'         => $senderInfo['contact_name'] ?? '', // 寄方联系人
                        'j_mobile'          => $senderInfo['mobile'] ?? '', // 寄方手机号码
                        'j_tel'             => $senderInfo['phone'] ?? '', // 寄方电话号码
                        'j_province'        => $senderInfo['province'] ?? '', // 寄方所在省份 - 英文
                        'j_city'            => $senderInfo['city'] ?? '', // 寄方所在城市 - 英文
                        'j_address'         => $senderInfo['street'] ?? '', // 寄方详细地址- 校验规则 : a不能包含中文；b只能为英文字母、数字、及以下字符
                        'j_country'         => 'CN', // 始发地
                        'j_post_code'       => $senderInfo['postcode'] ?? '', // 寄方邮编

                        // 非必填
                        'j_county'          => '', // todo 寄件人所在县/区

                        // 收件人信息
                        'd_company'         => $data['receiverName'], // 到件方公司名称，如为空可填写到方联系人
                        'd_contact'         => $data['receiverName'], // 到件方联系人
                        'd_tel'             => $data['receiverPhone'] ?? "", // 到方电话号码
                        'd_mobile'          => $data['receiverMobile'] ?? '', // 到方手机号码
                        'd_province'        => $data['rProvince'], //到方所在省份
                        'd_city'            => $data['receiverCity'], //到方所在城市
                        'd_address'         => $data['receiverAddress'], //到方详细地址
                        'd_country'         => $data['receiverCountryCode'], // 到方国家
                        'd_post_code'       => $data['receiverPostCode'], // 到方邮编
                        // 'd_county'    => '', // 到方县/区

                        'parcel_quantity' => 1, // 包裹数（固定为1）
                        'pay_method'      => 1, // 付款方式：寄方付（固定为1）

                        'declared_value'          => $packagePrice, // 订单托寄物声明价值=货物单价*数量（必须为数字且大于零） 产品类型23货物申报价值不能大于2USD（美元）
                        'declared_value_currency' => 'USD',// 托寄物声明价值币别：USD: 美元


                        'cargo_total_weight' => $packageWeight, // 货物总重量 订单货物总重量单位KG，如果提供此值必须大于0且不能超过2KG，且该值要大于货物单位重量X货物数量总和。
                        'operate_flag'       => 1, // 操作标识 固定值：1（确认下单）
                        'isBat'              => 0, // 是否带电 0：不带电 ;1 带电

                        // 非必填
                        'custid'             => '', // 用户月结卡号
                        'remark'             => '', // 备注
                        'category'           => '', // 所属品类 订单所属品类，用于海关清关
                        'sendstarttime'      => '', // 要求上门收件时间
                        'cargo_length'       => '', // 货物长
                        'cargo_width'        => '', // 货物宽
                        'cargo_height'       => '', // 货物高
                        'tax_number'         => '', // 税号 说明：目的国为澳洲AU ，产品类型为国际小包挂号10，税号和ABN不能同时为空，其它国家和产品类型选填。
                        'abn'                => '', // 在澳洲有注册公司的企业，基于其ABN号走标准流程注册的号码 11位纯数字
                        'gst_exemption_code' => '', // 在澳洲有注册公司的企业, 在澳大利亚的商业编号
                        'd_email'            => '', // 产品类型23,24必填合法有效邮箱
                        'passport_id'        => '', // 韩国个人清关代码，如P561140689980，只接受英文和数字,长度100字符 专线小包韩国流向为必填
                    ],

                    'Cargo' => $productList

                ]
            ],
        ];

        return $newData;
    }

    /**
     * 获取调用签名
     *
     * @param $data
     * @return string
     */
    protected function getSign($data)
    {
        $md5       = md5($data . $this->checkWord, true);
        $signature = base64_encode($md5);

        return $signature;
    }

    /**
     * 处理返回结果
     * @param $result
     * @return array
     */
    private function handleResult($result)
    {
        $code           = 0;
        $message        = '成功';
        $callSuccess    = $result['CallSuccess'] ?? '';
        $responce       = $result['Response'] ?? '';
        $createdExpress = $result['CreatedExpress'] ?? '';

        if ($callSuccess == false) {
            $code    = -1;
            $message = $responce['ReasonMessage'] ?? '';
        }

        return [
            'code'        => $code,
            'message'     => $message,
            'data'        => [
                'ep_code' => $createdExpress['Epcode'] ?? '',
            ],
            'origin_data' => json_encode($result)
        ];
    }
}

<?php

namespace Sxqibo\Logistics;

use Exception;
use SoapClient;
use Sxqibo\Logistics\common\Client;

/**
 * 途运达物流类
 * Class TuYunDa
 * @package Sxqibo\Logistics
 */
class TuYunDa
{
    private $serviceEndPoint = 'http://120.77.236.96/default/svc/wsdl';
    private $endPoint;
    private $body;

    public function __construct($appToken, $appKey)
    {
        $this->endPoint = [
            'method' => 'POST',
            'url'    => $this->serviceEndPoint,
        ];

        $this->body    = [
            'appToken' => $appToken,
            'appKey'   => $appKey,
        ];
        $this->options = [
            "trace"              => true,
            "connection_timeout" => 1000,
            "encoding"           => "utf-8"
        ];

        $this->client = new Client();
    }

    /**
     * 获取请求节点信息
     *
     * @param $key
     * @return string
     * @throws Exception
     */
    protected function getEndPoint($key)
    {
        $endpoints = [
            'createOrder'           => [
                'service' => 'createOrder',
                'remark'  => '创建订单'
            ],
            'cancelOrder'           => [
                'service' => 'cancelOrder',
                'remark'  => '取消订单'
            ],
            'interceptOrder'        => [
                'service' => 'interceptOrder',
                'remark'  => '拦截订单,注：只有预报、已入库状态支持拦截'
            ],
            'modifyOrderWeight'     => [
                'service' => 'modifyOrderWeight',
                'remark'  => '修改订单重量'
            ],
            'getOrder'              => [
                'service' => 'getOrder',
                'remark'  => '查询订单明细'
            ],
            'feeTrail'              => [
                'service' => 'feeTrail',
                'remark'  => '运费试算'
            ],
            'getCargoTrack'         => [
                'service' => 'getCargoTrack',
                'remark'  => '轨迹查询'
            ],
            'getShippingMethodInfo' => [
                'service' => 'getShippingMethodInfo',
                'remark'  => '获取全部运输方式'
            ],
            'getLabelUrl'           => [
                'service' => 'getLabelUrl',
                'remark'  => '获取单个标签接口'
            ],
            'getTrackNumber'        => [
                'service' => 'getTrackNumber',
                'remark'  => '获取订单跟踪号'
            ],
            'getReceivingExpense'   => [
                'service' => 'getReceivingExpense',
                'remark'  => '查询订单运费明细',
            ],
            'getCountry'            => [
                'service' => 'getCountry',
                'remark'  => '获取国家信息',
            ],

            'getPrintTemplateName' => [
                'service' => 'getPrintTemplateName',
                'remark'  => '获取打印模板'
            ],
            'getLabelByTemplate'   => [
                'service' => 'getLabelByTemplate',
                'remark'  => '根据模板获取发票/配货单'
            ],
        ];

        if (isset($endpoints[$key])) {
            return $endpoints[$key]['service'];
        } else {
            throw new Exception('未找到对应的接口信息 ' . $key);
        }
    }

    /**
     * 创建订单
     *
     * @param $data
     * @return array|mixed
     * @throws Exception
     */
    public function createOrder($data)
    {
        $newData = $this->formatData($data);
        $body    = [
            'service'    => $this->getEndPoint('createOrder'),
            'paramsJson' => $newData
        ];

        return $this->handleRequest($body);
    }


    /**
     * 更新订单
     *
     * @param $orderNo
     * @param $orderWeight
     * @return array
     * @throws Exception
     */
    public function updateOrder($params)
    {
        $data = [
            'order_code' => $params['orderNo'],
            'weight'     => $params['weight']
        ];
        $body = [
            'service'    => $this->getEndPoint('modifyOrderWeight'),
            'paramsJson' => json_encode($data)
        ];

        return $this->handleRequest($body);
    }

    /**
     * 删除订单
     *
     * @param $orderNo
     * @return array
     * @throws Exception
     */
    public function deleteOrder($orderNo)
    {
        $body = [
            'service'    => $this->getEndPoint('deleteOrder'),
            'paramsJson' => json_encode(['reference_no' => $orderNo])
        ];

        return $this->handleRequest($body);
    }

    public function cancelOrder($orderNo)
    {
        $body = [
            'service'    => $this->getEndPoint('cancelOrder'),
            'paramsJson' => json_encode(['reference_no' => $orderNo])
        ];

        return $this->handleRequest($body);
    }

    public function interceptOrder($params)
    {
        $data = [
            'reference_no'   => $params['orderNo'],
            'type'           => $params['type'],
            'hold_on_remark' => $params['remark'],
        ];
        $body = [
            'service'    => $this->getEndPoint('interceptOrder'),
            'paramsJson' => json_encode($data)
        ];

        return $this->handleRequest($body);
    }

    public function getOrder($orderNo)
    {
        $body = [
            'service'    => $this->getEndPoint('getOrder'),
            'paramsJson' => json_encode(['reference_no' => $orderNo])
        ];

        return $this->handleRequest($body);
    }

    /**
     * 获取订单标签信息
     *
     * @return array
     * @throws Exception
     */
    public function getOrderLabel($orderNo)
    {
        $data = [
            'reference_no'       => $orderNo,
            'label_type'         => 2,
            'label_content_type' => 6
        ];
        $body = [
            'service'    => $this->getEndPoint('getLabelUrl'),
            'paramsJson' => json_encode($data)
        ];

        return $this->handleRequest($body);
    }

    /**
     * 获取订单跟踪单号
     *
     * @param $orderNo
     * @return array
     * @throws Exception
     */
    public function getTrackingNumber($orderNo)
    {
        $body = [
            'service'    => $this->getEndPoint('getTrackNumber'),
            'paramsJson' => json_encode(['reference_no' => $orderNo])
        ];

        return $this->handleRequest($body);
    }

    /**
     * 获取订单跟踪记录
     *
     * @param $trackNumber  string 服务商单号
     * @return array
     * @throws Exception
     */
    public function getTrack($trackNumber)
    {
        $body = [
            'service'    => $this->getEndPoint('getCargoTrack'),
            'paramsJson' => json_encode(['codes' => $trackNumber])
        ];

        return $this->handleRequest($body);
    }


    /**
     * 获取订单费用明细(业务的每笔费用变动数据)
     *
     * @param $orderNo
     * @return array
     * @throws Exception
     */
    public function getShippingFeeDetail($orderNo)
    {
        $body = [
            'service'    => $this->getEndPoint('getReceivingExpense'),
            'paramsJson' => json_encode(['reference_no' => $orderNo])
        ];

        return $this->handleRequest($body);
    }

    /**
     * 运费试算
     *
     * @param $params
     * @return array
     * @throws Exception
     */
    public function getPrice($params)
    {
        $data = [
            'weight'           => $params['weight'] ?? '', // 重量（KG） - 必填
            'shipping_type_id' => 'W', // 货物类型，D-文件，L-信封，W-包裹
            'country_code'     => $params['country_code'] ?? '', //目的国家二字代码 - 必填
            'city'             => '', // 城市
            'post_code'        => $params['postcode'] ?? '', // 邮编
            'length'           => $params['length'] ?? '', // 长（CM）
            'width'            => $params['width'] ?? '', // 宽（CM）
            'height'           => $params['height'] ?? '', // 高（CM）
        ];

        $body = [
            'service'    => $this->getEndPoint('feeTrail'),
            'paramsJson' => json_encode($data)
        ];

        return $this->handleRequest($body);
    }

    /**
     * 获取运输方式
     * @return array|mixed
     * @throws Exception
     */
    public function getShipTypes()
    {
        $data = [
            'service'    => $this->getEndPoint('getShippingMethodInfo'),
            'paramsJson' => ''
        ];

        return $this->handleRequest($data);
    }

    public function getCountry()
    {
        $data = [
            'service'    => $this->getEndPoint('getCountry'),
            'paramsJson' => ''
        ];

        return $this->handleRequest($data);
    }


    public function getDeclareUnit()
    {
        $data = [
            'service'    => $this->getEndPoint('getDeclareUnit'),
            'paramsJson' => ''
        ];

        return $this->handleRequest($data);
    }

    public function getExtraService()
    {
        $data = [
            'service'    => $this->getEndPoint('getExtraService'),
            'paramsJson' => ''
        ];

        return $this->handleRequest($data);
    }


    public function getPrintTemplateName()
    {
        $data = [
            'service' => $this->getEndPoint('getPrintTemplateName'),
        ];

        return $this->handleRequest($data);
    }

    public function getLabelByTemplate($params)
    {
        $data = [
            'service'    => $this->getEndPoint('getLabelByTemplate'),
            'paramsJson' => json_encode($params)
        ];

        return $this->handleRequest($data);
    }

    /**
     * 处理接口请求
     *
     * @param       $data
     * @param array $params
     * @return array
     * @throws Exception
     */
    protected function handleRequest($data, $params = [])
    {
        $body = array_merge($this->body, $data);

        $client = new SoapClient ($this->endPoint['url'], $this->options);
        $result = $client->callService($body);
        $result = \GuzzleHttp\json_decode($result->response, true);

        if ($result['ask'] === "Failure") {

            //兼容报错信息
            if (!empty($result['Error']['errMessage'])) {
                $errMessage = $result['Error']['errMessage'];
            } else if (!empty($result['Error']['cnMessage'])) {
                $errMessage = $result['Error']['cnMessage'];
            } else {
                $errMessage = '';
            }
            return ['code' => -1, 'message' => $result['message'] ?? '', 'errMessage' => $errMessage, 'data' => []];
        } else {

            //针对标签打印数据返回
            if ($body['service'] == 'getLabelUrl') {
                $result['data']['url'] = $result['url'];
            }
            return ['code' => 0, 'message' => '成功', 'data' => $result['data'] ?? []];
        }
    }

    /**
     * 格式化订单数据
     *
     * @param $data
     * @return false|string
     */
    protected function formatData($data)
    {
        $goods   = $data['goods'];
        $newData = [
            'order_pieces'       => 1, // 外包装件数,默认1
            'reference_no'       => $data['orderNo'], // 客户订单号
            'shipping_method'    => $data['channelCode'],// 运输方式代码
            'mail_cargo_type'    => $data['mail_cargo_type'] ?? 4, // 包裹申报种类 1：Gif礼品 2：CommercialSample 商品货样 3：Document 文件 4：Other 其他 默认4
            'country_code'       => $data['country_code'] ?? '', //目的国家二字代码 - 必填

            // 非必填
            'shipping_method_no' => '', // 服务商号,


            // 发件人信息
            'shipper'            => [
                // todo 必填
                'shipper_name'        => $data['shipper_name'] ?? '.', // 发件人姓名
                'shipper_countrycode' => $data['shipper_countrycode'] ?? 'CN', // 发件人国家二字代码
                'shipper_street'      => $data['shipper_street'] ?? '..', // 发件人街道地址
                'shipper_mobile'      => $data['shipper_mobile'] ?? '..', // 发件人手机 手机|电话 至少传一项
                'shipper_telephone'   => $data['shipper_telephone'] ?? '..', // 发件人电话 手机|电话 至少传一项

                // todo 非必填
                'shipper_province'    => '', // 发件人州/省
                'shipper_city'        => '', // 发件人城市
                'shipper_district'    => '', // 发件人区/县
                'shipper_postcode'    => '', // 发件人邮编
                'shipper_areacode'    => '', // 发件人区域代码
                'shipper_company'     => '', // 发件人公司
                'shipper_email'       => '', // 发件人邮箱
            ],

            // 收件人信息
            'Consignee'          => [
                'consignee_name'        => $data['receiverName'], // 收件人姓名
                'consignee_countrycode' => $data['receiverCountryCode'], // 收件人国家二字代码
                'consignee_province'    => $data['rProvince'], // 收件人州/省
                'consignee_city'        => $data['receiverCity'], // 收件人城市
                'consignee_street'      => $data['receiverAddress'], // 收件人区/县
                'consignee_postcode'    => $data['receiverPostCode'] ?? '', // 收件人邮编
                'consignee_telephone'   => $data['receiverPhone'] ?? '', // 收件人电话
                'consignee_mobile'      => $data['receiverMobile'] ?? '', // 收件人手机

                // 非必填
                'consignee_district'    => '', // 收件人区/县
                'consignee_company'     => '', // 收件人公司
                'consignee_areacode'    => '', // 收件人区域代码
                'consignee_doorplate'   => '', // 收件人门牌号
                'consignee_email'       => '', // 发件人邮箱
                'consignee_fax'         => '', // 收件人传真
            ],
        ];

        $ItemArr     = [];
        $orderWeight = 0;
        foreach ($goods as $item) {
            $ItemArr[] = [
                'invoice_cnname'     => $item['goods_cn_name'], // 商品中文品名
                'invoice_enname'     => $item['goods_en_name'], // 商品英文品名
                'invoice_quantity'   => $item['goods_number'], // 申报数量
                'invoice_unitcharge' => $item['goods_single_worth'], // // 申报单价
                'invoice_weight'     => $item['goods_single_weight'], // 申报重量，单位KG,最多三位小数
                'hs_code'            => $item['hs_code'] ?? '', // 海关协制编号
                'note'               => $data['remarks'] ?? '', // 订单备注

                // 非必填
                // 'sku'              => '', // SKU
                'unit_code'          => $item['unit_code'] ?? 'PCE', // 单位 MTR：米,PCE：件,PCE：件 默认PCE
            ];

            $orderWeight += ($item['goods_number'] * $item['goods_single_weight']);
        }

        $newData['ItemArr']      = $ItemArr;
        $newData['order_weight'] = $orderWeight;

        return json_encode($newData);
    }
}

<?php


namespace Sxqibo\Logistics;

use Exception;
use Sxqibo\Logistics\common\Client;

/**
 * 纵横讯通物流类
 * Class Zongheng
 * @package Sxqibo\Logistics
 */
class Zongheng
{
    private $serviceEndPoint = 'http://order.globleexpress.com:8051/webservice/PublicService.asmx/ServiceInterfaceUTF8';
    private $headers;
    private $client;
    private $endPoint;
    private $body;

    public function __construct($appToken, $appKey)
    {
        $this->endPoint = [
            'method' => 'POST',
            'url'    => $this->serviceEndPoint,
        ];

        $this->body = [
            'appToken' => $appToken,
            'appKey'   => $appKey,
        ];

        $this->headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];

        $this->client = new Client();
    }

    /**
     * 获取请求节点信息
     *
     * @param $key
     * @throws Exception
     * @return string
     */
    protected function getEndPoint($key)
    {
        $endpoints = [
            'createOrder'    => [
                'serviceMethod' => 'createorder',
                'remark'        => '创建订单'
            ],
            'submitForecast' => [
                'serviceMethod' => 'submitforecast',
                'remark'        => '提交预报(先创建草稿状态的订单才需要再调用此接口提交预报)'
            ],
            'updateOrder'    => [
                'serviceMethod' => 'updateorder',
                'remark'        => '更新订单'
            ],
            'deleteOrder'    => [
                'serviceMethod' => 'removeorder',
                'remark'        => '删除订单'
            ],
            'getOrderLabel'  => [
                'serviceMethod' => 'getnewlabel',
                'remark'        => '获取订单标签'
            ],

            'getTrackingNumber' => [
                'serviceMethod' => 'gettrackingnumber',
                'remark'        => '获取订单跟踪单号'
            ],
            'getTrack'          => [
                'serviceMethod' => 'gettrack',
                'remark'        => '获取订单跟踪记录'
            ],
            'getShippingFee'    => [
                'serviceMethod' => 'getbusinessfee',
                'remark'        => '获取订单费用(按费用种类分组合计费用)'
            ],

            'getShippingFeeDetail'      => [
                'serviceMethod' => 'getbusinessfee',
                'remark'        => '获取订单费用明细(业务的每笔费用变动数据)'
            ],
            'getOrderWeight'            => [
                'serviceMethod' => 'getbusinessweight',
                'remark'        => '获取订单重量'
            ],
            'getPrice'                  => [
                'serviceMethod' => 'feetrail',
                'remark'        => '费用试算'
            ],
            'getShipTypes'              => [
                'serviceMethod' => 'getshippingmethod',
                'remark'        => '获取运输方式'
            ],
            'getCustomerShippingMethod' => [
                'serviceMethod' => 'getcustomershippingmethod',
                'remark'        => '获取可用的运输方式'
            ],
            'getMailCargoType'          => [
                'serviceMethod' => 'getmailcargotype',
                'remark'        => '获取申报种类',
            ],
            'getCountry'                => [
                'serviceMethod' => 'getcountry',
                'remark'        => '获取国家',
            ],

            'getCertificateType' => [
                'serviceMethod' => 'getcertificatetype',
                'remark'        => '获取证件类型'
            ],
            'getDeclareUnit'     => [
                'serviceMethod' => 'getdeclareunit',
                'remark'        => '获取申报单位'
            ],
            'getExtraService'    => [
                'serviceMethod' => 'getextraservice',
                'remark'        => '获取额外服务'
            ],
            'getProductGroup'    => [
                'serviceMethod' => 'getproductgroup',
                'remark'        => '获取运输类型'
            ],
            'getLocation'        => [
                'serviceMethod' => 'getlocation',
                'remark'        => '获取起运地'
            ],
            'getLabelConfig'     => [
                'serviceMethod' => 'getlabelconfig',
                'remark'        => '获取标签纸张配置'
            ],
        ];

        if (isset($endpoints[$key])) {
            return $endpoints[$key]['serviceMethod'];
        } else {
            throw new Exception('未找到对应的接口信息 ' . $key);
        }
    }

    /**
     * 创建订单
     *
     * @param $data
     * @throws Exception
     * @return array|mixed
     */
    public function createOrder($data)
    {
        $newData = $this->formatData($data);
        $body    = [
            'serviceMethod' => $this->getEndPoint('createOrder'),
            'paramsJson'    => $newData
        ];

        return $this->handleRequest($body);
    }

    /**
     * 提交预报(先创建草稿状态的订单才需要再调用此接口提交预报)
     *
     * @param $orderNo
     * @param $orderWeight
     * @throws Exception
     * @return array
     */
    public function submitForecast($orderNo, $orderWeight)
    {
        $data = [
            'reference_no' => $orderNo,
            'order_weight' => $orderWeight
        ];
        $body = [
            'serviceMethod' => $this->getEndPoint('submitForecast'),
            'paramsJson'    => json_encode($data)
        ];

        return $this->handleRequest($body);
    }

    /**
     * 更新订单
     *
     * @param $orderNo
     * @param $orderWeight
     * @throws Exception
     * @return array
     */
    public function updateOrder($orderNo, $orderWeight)
    {
        $data = [
            'reference_no' => $orderNo,
            'order_weight' => $orderWeight
        ];
        $body = [
            'serviceMethod' => $this->getEndPoint('updateOrder'),
            'paramsJson'    => json_encode($data)
        ];

        return $this->handleRequest($body);
    }

    /**
     * 删除订单
     *
     * @param $orderNo
     * @throws Exception
     * @return array
     */
    public function deleteOrder($orderNo)
    {
        $body = [
            'serviceMethod' => $this->getEndPoint('deleteOrder'),
            'paramsJson'    => json_encode(['reference_no' => $orderNo])
        ];

        return $this->handleRequest($body);
    }

    /**
     * 获取订单标签信息
     *
     * @param $orderNos array 多个订单号
     * @param array $params 附加参数
     * @throws Exception
     * @return array
     */
    public function getOrderLabel($orderNos, $params = [])
    {
        $data = [
            'configInfo' => [
                'lable_file_type'    => $params['file_type'] ?? 2, // 标签文件类型 1：PNG文件 2：PDF文件
                'lable_paper_type'   => $params['paper_type'] ?? 1, // 纸张类型 1：标签纸 2：A4纸
                'lable_content_type' => $params['content_type'] ?? 1, // 标签内容类型代码 1：标签 2：报关单 3：配货单 4：标签+报关单 5：标签+配货单 6：标签+报关单+配货单
                'additional_info'    => [
                    'lable_print_invoiceinfo'               => $params['print_invoice_info'] ?? 'Y', // 标签上打印配货信息 (Y:打印 N:不打印) 默认 N:不打印
                    'lable_print_buyerid'                   => $params['print_buyer_id'] ?? 'N', // 标签上是否打印买家ID (Y:打印 N:不打印) 默认 N:不打印
                    'lable_print_datetime'                  => $params['print_datetime'] ?? 'Y', // 标签上是否打印日期 (Y:打印 N:不打印) 默认 Y:打印
                    'customsdeclaration_print_actualweight' => $params['print_actual_weight'] ?? 'N', // 报关单上是否打印实际重量 (Y:打印 N:不打印) 默认 N:不打印
                ],
            ],
        ];

        $listOrder = [];
        foreach ($orderNos as $orderNo) {
            $listOrder[] = [
                'reference_no' => $orderNo
            ];
        }

        $data['listorder'] = $listOrder;

        $body = [
            'serviceMethod' => $this->getEndPoint('getOrderLabel'),
            'paramsJson'    => json_encode($data)
        ];

        return $this->handleRequest($body);
    }

    /**
     * 获取订单跟踪单号
     *
     * @param $orderNo
     * @throws Exception
     * @return array
     */
    public function getTrackingNumber($orderNo)
    {
        $body = [
            'serviceMethod' => $this->getEndPoint('getTrackingNumber'),
            'paramsJson'    => json_encode(['reference_no' => $orderNo])
        ];

        return $this->handleRequest($body);
    }

    /**
     * 获取订单跟踪记录
     *
     * @param $trackNumber  string 服务商单号
     * @throws Exception
     * @return array
     */
    public function getTrack($trackNumber)
    {
        $body = [
            'serviceMethod' => $this->getEndPoint('getTrack'),
            'paramsJson'    => json_encode(['tracking_number' => $trackNumber])
        ];

        return $this->handleRequest($body);
    }

    /**
     * 获取订单费用(按费用种类分组合计费用)
     *
     * @param $orderNo
     * @throws Exception
     * @return array
     */
    public function getShippingFee($orderNo)
    {
        $body = [
            'serviceMethod' => $this->getEndPoint('getShippingFee'),
            'paramsJson'    => json_encode(['reference_no' => $orderNo])
        ];

        return $this->handleRequest($body);
    }

    /**
     * 获取订单费用明细(业务的每笔费用变动数据)
     *
     * @param $orderNo
     * @throws Exception
     * @return array
     */
    public function getShippingFeeDetail($orderNo)
    {
        $body = [
            'serviceMethod' => $this->getEndPoint('getShippingFeeDetail'),
            'paramsJson'    => json_encode(['reference_no' => $orderNo])
        ];

        return $this->handleRequest($body);
    }

    /**
     * 获取订单重量
     *
     * @param $orderNo
     * @throws Exception
     * @return array
     */
    public function getOrderWeight($orderNo)
    {
        $body = [
            'serviceMethod' => $this->getEndPoint('getShippingFeeDetail'),
            'paramsJson'    => json_encode(['reference_no' => $orderNo])
        ];

        return $this->handleRequest($body);
    }

    /**
     * 运费试算
     *
     * @param $params
     * @throws Exception
     * @return array
     */
    public function getPrice($params)
    {
        $data = [
            'country_code'    => $params['country_code'] ?? '', //目的国家二字代码 - 必填
            'weight'          => $params['weight'] ?? '', // 重量（KG） - 必填
            'shipping_method' => '', // 运输方式代码
            'post_code'       => $params['postcode'] ?? '', // 目的地邮编
            'location'        => '', // 起运地 默认为：用户绑定的起运地
            'cargo_type'      => 'W', // 货物类型（W:包裹 D:文件） 默认为：W
            'extra_service'   => [], // 额外服务代码集合
            'cargo_volume'    => [   // 材积信息
                'weight' => $params['weight'] ?? '',// 重量（KG） - 有cargo_volume字段则weight必填
                'length' => $params['length'] ?? '', // 长（CM）
                'width'  => $params['width'] ?? '', // 宽（CM）
                'height' => $params['height'] ?? '', // 高（CM）
            ],
        ];

        $body = [
            'serviceMethod' => $this->getEndPoint('getPrice'),
            'paramsJson'    => json_encode($data)
        ];

        return $this->handleRequest($body);
    }

    /**
     * 获取运输方式
     * @throws Exception
     * @return array|mixed
     */
    public function getShipTypes()
    {
        $data = [
            'serviceMethod' => $this->getEndPoint('getShipTypes'),
            'paramsJson'    => ''
        ];

        return $this->handleRequest($data);
    }

    /**
     * 获取可用的运输方式
     * @throws Exception
     * @return array
     */
    public function getCustomerShippingMethod()
    {
        $data = [
            'serviceMethod' => 'getcustomershippingmethod',
            'paramsJson'    => ''
        ];

        return $this->handleRequest($data);
    }

    /**
     * 获取申报种类
     *
     * @return array
     */
    public function getMailCargoType()
    {
        $data = [
            'serviceMethod' => 'getmailcargotype',
            'paramsJson'    => ''
        ];

        return $this->handleRequest($data);
    }

    public function getCountry()
    {
        $data = [
            'serviceMethod' => $this->getEndPoint('getCountry'),
            'paramsJson'    => ''
        ];

        return $this->handleRequest($data);
    }

    public function getCertificateType()
    {
        $data = [
            'serviceMethod' => $this->getEndPoint('getCertificateType'),
            'paramsJson'    => ''
        ];

        return $this->handleRequest($data);
    }

    public function getDeclareUnit()
    {
        $data = [
            'serviceMethod' => $this->getEndPoint('getDeclareUnit'),
            'paramsJson'    => ''
        ];

        return $this->handleRequest($data);
    }

    public function getExtraService()
    {
        $data = [
            'serviceMethod' => $this->getEndPoint('getExtraService'),
            'paramsJson'    => ''
        ];

        return $this->handleRequest($data);
    }

    public function getProductGroup()
    {
        $data = [
            'serviceMethod' => $this->getEndPoint('getProductGroup'),
            'paramsJson'    => ''
        ];

        return $this->handleRequest($data);
    }

    public function getLocation()
    {
        $data = [
            'serviceMethod' => $this->getEndPoint('getLocation'),
            'paramsJson'    => ''
        ];

        return $this->handleRequest($data);
    }

    public function getLabelConfig()
    {
        $data = [
            'serviceMethod' => $this->getEndPoint('getLabelConfig'),
            'paramsJson'    => ''
        ];

        return $this->handleRequest($data);
    }


    /**
     * 处理接口请求
     *
     * @param $data
     * @param array $params
     * @throws Exception
     * @return array
     */
    protected function handleRequest($data, $params = [])
    {
        $body   = array_merge($this->body, $data);
        $result = $this->client->requestApi($this->endPoint, $params, $body, $this->headers, true);

        if ($result['success'] === 0) {
            return ['code' => -1, 'message' => $result['cnmessage'] ?? '', 'en_message' => $result['enmessage'], 'data' => []];
        } else {
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

            // 非必填
            'shipping_method_no' => '', // 服务商号,
            'order_status'       => $data['order_status'] ?? "P", // 订单状态 - P：已预报 (默认) D：草稿 (如果创建草稿订单，则需要再调用submitforecast【提交预报】接口)
            'order_info'         => $data['memo'] ?? '', // 订单备注

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
            'consignee'          => [
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

        $invoice     = [];
        $orderWeight = 0;
        foreach ($goods as $item) {
            $invoice[] = [
                'invoice_cnname'     => $item['goods_cn_name'], // 商品中文品名
                'invoice_enname'     => $item['goods_en_name'], // 商品英文品名
                'invoice_quantity'   => $item['goods_number'], // 商品单重，2位小数 (1个数量的商品单重)
                'invoice_unitcharge' => $item['goods_single_worth'], // // 单价，2位小数 (1个数量的商品价格)
                'net_weight'         => $item['goods_single_weight'], // 商品单重，2位小数 (1个数量的商品单重)
                'hs_code'            => $item['hs_code'] ?? '', // 海关协制编号

                // 非必填
                // 'sku'              => '', // SKU
                'unit_code'          => $item['unit_code'] ?? 'PCE', // 单位 MTR：米,PCE：件,PCE：件 默认PCE
            ];

            $orderWeight += ($item['goods_number'] * $item['goods_single_weight']);
        }

        $newData['invoice']      = $invoice;
        $newData['order_weight'] = $orderWeight;

        return json_encode($newData);
    }
}

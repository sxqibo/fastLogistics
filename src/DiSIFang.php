<?php


namespace Sxqibo\Logistics;

use Exception;
use Sxqibo\Logistics\common\Client;

/**
 * 递四方物流类 4PX
 * Class DiSIFang
 * @package Sxqibo\Logistics
 */
class DiSIFang
{
    private $serviceEndPoint = 'http://open.4px.com/router/api/service'; // 正式环境
    private $testServiceEndPoint = 'http://open.sandbox.4px.com/router/api/service'; // 沙箱环境

    private $appKey;
    private $appSecret;
    private $client;
    private $headers;

    public function __construct($appKey, $appSecret)
    {
        $this->appKey    = $appKey;
        $this->appSecret = $appSecret;
        $this->headers   = [
            'Content-Type' => 'application/json'
        ];

        $this->client = new Client();
    }

    /**
     * 获取请求节点信息
     *
     * @param $key
     * @throws Exception
     * @return mixed
     */
    protected function getEndPoint($key, $isDebug = false)
    {
        $endpoints = [
            'createOrder'        => [
                'service_method' => 'ds.xms.order.create',
                'method'         => 'POST',
                'v'              => '1.0.0',
                'remark'         => '创建直发委托单'
            ],
            'updateOrder'        => [
                'service_method' => 'ds.xms.order.updateweight',
                'method'         => 'POST',
                'v'              => '1.0.0',
                'remark'         => '更新预报重量'
            ],
            'getPrice'           => [
                'service_method' => 'ds.xms.estimated_cost.get',
                'method'         => 'POST',
                'v'              => '1.0.0',
                'remark'         => '预估费用查询/运费试算'
            ],
            'deleteOrder'        => [
                'service_method' => 'ds.xms.order.cancel',
                'method'         => 'POST',
                'v'              => '1.0.0',
                'remark'         => '取消直发委托单'
            ],
            'holdOrder'          => [
                'service_method' => 'ds.xms.order.hold',
                'method'         => 'POST',
                'v'              => '1.0.0',
                'remark'         => '申请|取消拦截订单'
            ],
            'getOrder'           => [
                'service_method' => 'ds.xms.order.get',
                'method'         => 'POST',
                'v'              => '1.1.0',
                'remark'         => '查询直发委托单：备注：支持两种场景：1.单个查询（通过请求单号、单号类型查询） 2.批量查询（时间组合+委托单状态）
                 注意：调用logistics_channel_no接口时，物流渠道号码（logistics_channel_no）和物流渠道商（logistics_channel_name）如果有值则返回，如果没有则返回为空。
                 某些物流产品在4PX仓内换号的，需要在库内作业中才能查询到这两个字段。'
            ],

            // 打印标签
            'labelPrint'         => [
                'service_method' => 'ds.xms.label.get',
                'method'         => 'POST',
                'v'              => '1.0.0',
                'remark'         => '获取标签'
            ],
            // 多标签打印
            'multipleLabelPrint' => [
                'service_method' => 'ds.xms.label.getlist',
                'method'         => 'POST',
                'v'              => '1.0.0',
                'remark'         => '批量获取标签'
            ],

            'getMeasureUnit' => [
                'service_method' => 'com.basis.measureunit.getlist',
                'method'         => 'POST',
                'v'              => '1.0.0',
                'remark'         => '查询计量单位'
            ],
            'getCourseList'  => [
                'service_method' => 'com.basis.warehouse.getlist',
                'method'         => 'POST',
                'v'              => '1.0.0',
                'remark'         => '查询仓库信息'
            ],
            'getShipTypes'   => [
                'service_method' => 'ds.xms.logistics_product.getlist',
                'method'         => 'POST',
                'v'              => '1.0.0',
                'remark'         => '查询物流产品信息'
            ],

            'getCategory' => [
                'service_method' => 'com.basis.declare.getcategory',
                'method'         => 'POST',
                'v'              => '1.0.0',
                'remark'         => '查询申报产品种类'
            ],
            'getTrack'    => [
                'service_method' => 'tr.order.tracking.get',
                'method'         => 'POST',
                'v'              => '1.0.0',
                'remark'         => '物流轨迹查询'
            ],
        ];

        if (isset($endpoints[$key])) {
            if ($isDebug) {
                $path = $this->testServiceEndPoint;
            } else {
                $path = $this->serviceEndPoint;
            }

            $endpoints[$key]['url'] = $path;

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
     * @throws Exception
     * @return array
     */
    public function createOrder($data, $isDebug = false)
    {
        $newData = $this->formatData($data);

        $endPoint = $this->getEndPoint('createOrder', $isDebug);

        return $this->handleRequest($endPoint, $newData);
    }

    /**
     * 更新订单信息
     *
     * @param $orderNo
     * @param $orderWeight string 单位KG
     * @param false $isDebug
     * @throws Exception
     * @return array
     */
    public function updateOrder($orderNo, $orderWeight, $isDebug = false)
    {
        $endPoint = $this->getEndPoint('updateOrder', $isDebug);

        $newData = json_encode([
            'request_no' => $orderNo, // 请求单号(支持4PX单号/客户单号/服务商单号)
            'weight'     => $orderWeight * 1000, // 预报重量（g）
        ]);

        return $this->handleRequest($endPoint, $newData);
    }

    /**
     * 查价格
     *
     * @param $params
     * @param false $isDebug
     * @throws Exception
     * @return array
     */
    public function getPrice($params, $isDebug = false)
    {
        $endPoint = $this->getEndPoint('getPrice', $isDebug);

        $params = [
            'request_no'             => $params['orderNo'] ?? '', // 请求单号(支持4PX单号、面单号、客户单号)； 若填写了请求单号，则其余请求字段将不会生效
            'country_code'           => $params['country_code'] ?? '', // 目的国家二字码（未填写请求单号时，必填）
            'weight'                 => $params['weight'] ?? '', // 实重(单位g，未填写请求单号时，必填)，填写实重需小于1000000g
            'length'                 => $params['length'] ?? '', // 长(单位cm)；长宽高3个字段，填写了其中一个字段，其他2个字段需必填；小于1000cm并且保留2位小数
            'width'                  => $params['width'] ?? '', // 宽(单位cm)；长宽高3个字段，填写了其中一个字段，其他2个字段需必填；小于1000cm并且保留2位小数
            'height'                 => $params['height'] ?? '', // 高(单位cm)；长宽高3个字段，填写了其中一个字段，其他2个字段需必填；小于1000cm并且保留2位小数
            'cargocode'              => $params['cargo_type'] ?? 'P', // 货物类型(包裹：P；文件：D）默认值：P；
            'logistics_product_code' => $params['logistics_product_code'] ?? '', // 物流产品代码列表；如填写了产品代码，则只会返回填写的产品代码的试算结果，最大200个产品
        ];

        $newData = json_encode($params);

        return $this->handleRequest($endPoint, $newData);
    }

    /**
     * 删除订单
     *
     * @param $orderNo
     * @param string $cancelReason
     * @param false $isDebug
     * @throws Exception
     * @return array
     */
    public function deleteOrder($orderNo, $cancelReason = 'cancel', $isDebug = false)
    {
        $endPoint = $this->getEndPoint('deleteOrder', $isDebug);

        $newData = json_encode([
            'request_no'    => $orderNo,
            'cancel_reason' => $cancelReason,
        ]);

        return $this->handleRequest($endPoint, $newData);
    }

    /**
     * 拦截订单
     *
     * @param $orderNo
     * @param string $isHold
     * @param string $holdReason
     * @param false $isDebug
     * @throws Exception
     * @return array
     */
    public function holdOrder($orderNo, $isHold = 'Y', $holdReason = 'cancel', $isDebug = false)
    {
        $endPoint = $this->getEndPoint('holdOrder', $isDebug);

        $newData = json_encode([
            'request_no'  => $orderNo,
            'is_hold'     => $isHold,
            'hold_reason' => $holdReason,
        ]);

        return $this->handleRequest($endPoint, $newData);
    }

    /**
     * 根据条件查询快件信息
     *
     * @param $params
     * @param false $isDebug
     * @throws Exception
     * @return array
     */
    public function getOrder($params, $isDebug = false)
    {
        $newData = json_encode([
            'request_no'                       => $params['orderNo'] ?? '', // 请求单号
            'start_time_of_create_consignment' => $params['start_time'] ?? '', // 委托单创建时间-开始时间（*注：时间格式的传入值需要转换为long类型格式。）时间差为7天
            'end_time_of_create_consignment'   => $params['end_time'] ?? '', // 委托单创建时间-结束时间（（*注：时间格式的传入值需要转换为long类型格式。） 时间差为7天
            'consignment_status'               => $params['status'] ?? '', // 	委托单状态：已预报：P；已交接/已交货：V；库内作业中/已入库：H；已出库：C；已关闭：X；所有：ALL（默认）
        ]);

        $endPoint = $this->getEndPoint('getOrder', $isDebug);

        return $this->handleRequest($endPoint, $newData);
    }

    /**
     * 物流产品查询
     *
     * @param int $transportMode 运输方式：1 所有方式；2 国际快递；3 国际小包；4 专线；5 联邮通；6 其他；
     * @param false $isDebug
     * @throws Exception
     * @return array
     */
    public function getShipTypes($transportMode = 1, $isDebug = false)
    {
        $endPoint = $this->getEndPoint('getShipTypes', $isDebug);

        $body = json_encode([
            'transport_mode' => $transportMode,
        ]);

        return $this->handleRequest($endPoint, $body);
    }


    /**
     * 打印标签
     *
     * @param $orderNo
     * @param false $isDebug
     * @throws Exception
     * @return array
     */
    public function labelPrint($orderNo, $otherParams = [], $isDebug = false)
    {
        $endPoint = $this->getEndPoint('labelPrint', $isDebug);

        // 其他非必填参数
        // $params = [
        //     'response_label_format'     => '', // 返回面单的格式（PDF：返回PDF下载链接；IMG：返回IMG图片下载链接） 默认为PDF；
        //     'label_size'                => '', // 标签大小（label_80x90：标签纸80.5mm×90mm； label_90x100：标签纸85mm×98mm； label_100x100：标签纸98mm×98mm； label_100x150：标签纸100mm×150mm； label_100x200：标签纸100mm×200mm；）默认为label_80x90；
        //     'is_print_time'             => '', // 	是否打印当前时间（Y：打印；N：不打印） 默认为N；
        //     'is_print_buyer_id'         => '', // 	是否打印买家ID（Y：打印；N：不打印） 默认为N；
        //     'is_print_pick_info'        => '', // 是否在标签上打印配货信息（Y：打印；N：不打印）；默认为N。 注：只对4PX通用标签/普通标签的控制有效；这里的配货信息指是否在标签上打印配货信息。若需单独打印配货单，使用create_package_label字段控制。
        //     'is_print_declaration_list' => '', // 是否打印报关单（Y：打印；N：不打印） 默认为N；
        //     'is_print_customer_weight'  => '', // 报关单上是否打印客户预报重（Y：打印；N：不打印） 默认为N。 注：针对单独打印报关单功能；
        //     'create_package_label'      => '', // 	是否单独打印配货单（Y：打印；N：不打印） 默认为N。
        //     'is_print_pick_barcode'     => '', // 配货单上是否打印配货条形码（Y：打印；N：不打印） 默认为N。 注：针对单独打印配货单功能
        //     'is_print_merge'            => '' // 是否合并打印(Y：合并；N：不合并)默认为N； 注：合并打印，指若报关单和配货单打印为Y时，是否和标签合并到同一个URL进行返回
        // ];

        $params = [
            'request_no' => $orderNo
        ];
        if (!empty($otherParams)) {
            $params = array_merge($params, $otherParams);
        }

        $body = json_encode($params);

        return $this->handleRequest($endPoint, $body);
    }

    /**
     * 多标签打印
     *
     * @param $params
     * @param array $otherParams
     * @param false $isDebug
     * @throws Exception
     * @return array
     */
    public function multipleLabelPrint($params, $otherParams = [], $isDebug = false)
    {
        $newParams = [
            'request_no'             => $params['order_no_list'],
            'logistics_product_code' => $params[''] ?? 'A1', // 物流产品代码（logistics_product_code和label_size二选一。若同时填写了产品代码和标签大小，优先考虑产品代码）
            'label_size'             => $params['label_size'] ?? 'label_100x150' // 标签大小；（label_80x90：标签纸80.5mm×90mm； label_90x100：标签纸85mm×98mm； label_100x100：标签纸98mm×98mm； label_100x150：标签纸100mm×150mm；  label_100x200：标签纸100mm×200mm；） 默认为label_80x90；
        ];

        $endPoint = $this->getEndPoint('multipleLabelPrint', $isDebug);

        // 其他非必填参数
        // $params = [
        //     'is_print_time'             => '', // 	是否打印当前时间（Y：打印；N：不打印） 默认为N；
        //     'is_print_buyer_id'         => '', // 	是否打印买家ID（Y：打印；N：不打印） 默认为N；
        //     'is_print_pick_info'        => '', // 是否在标签上打印配货信息（Y：打印；N：不打印）；默认为N。 注：只对4PX通用标签/普通标签的控制有效；这里的配货信息指是否在标签上打印配货信息。若需单独打印配货单，使用create_package_label字段控制。
        //     'is_print_declaration_list' => '', // 是否打印报关单（Y：打印；N：不打印） 默认为N；
        //     'is_print_customer_weight'  => '', // 报关单上是否打印客户预报重（Y：打印；N：不打印） 默认为N。 注：针对单独打印报关单功能；
        //     'create_package_label'      => '', // 	是否单独打印配货单（Y：打印；N：不打印） 默认为N。
        //     'is_print_pick_barcode'     => '', // 配货单上是否打印配货条形码（Y：打印；N：不打印） 默认为N。 注：针对单独打印配货单功能
        // ];

        if (!empty($otherParams)) {
            $newParams = array_merge($newParams, $otherParams);
        }

        $body = json_encode($newParams);

        return $this->handleRequest($endPoint, $body);
    }

    /**
     * 查询计量单位
     *
     * @param false $isDebug
     * @throws Exception
     * @return array
     */
    public function getMeasureUnit($isDebug = false)
    {
        $endPoint = $this->getEndPoint('getMeasureUnit', $isDebug);

        return $this->handleRequest($endPoint, '');
    }

    /**
     * 查询仓库信息
     *
     * @param $serviceCode string 业务类型:F(订单履约)；S(自发服务)；T(转 运服务)；R(退件服务)
     * @param $countryCode string 国家二字码
     * @param false $isDebug
     * @throws Exception
     * @return array
     */
    public function getCourseList($serviceCode, $countryCode, $isDebug = false)
    {
        $endPoint = $this->getEndPoint('getCourseList', $isDebug);

        $body = json_encode([
            'service_code' => $serviceCode,
            'country'      => $countryCode,
        ]);

        return $this->handleRequest($endPoint, $body);
    }

    /**
     * 查询申报产品种类
     *
     * @param int $parentCode
     * @param string $businessType
     * @param false $isDebug
     * @throws Exception
     * @return array
     */
    public function getCategory($parentCode = 0, $businessType = 'E', $isDebug = false)
    {
        $endPoint = $this->getEndPoint('getCategory', $isDebug);

        $body = json_encode([
            'category_parent_code' => $parentCode, // 申报产品种类父类节点代码。(一级节点的父类节点代码为0)
            'business_type'        => $businessType, // 申报产品种类业务类型。可选值：I（进口业务）；E（出口业务。默认值为：E（出口业务）。
        ]);

        return $this->handleRequest($endPoint, $body);
    }

    /**
     * 获取订单跟踪记录
     *
     * @param $trackNumber
     * @param false $isDebug
     * @throws Exception
     * @return array
     */
    public function getTrack($trackNumber, $isDebug = false)
    {
        $endPoint = $this->getEndPoint('getTrack', $isDebug);

        $body = json_encode([
            'deliveryOrderNo' => $trackNumber,
        ]);

        return $this->handleRequest($endPoint, $body);
    }

    /**
     * 格式化订单数据
     *
     * @param $data
     * @return array
     */
    protected function formatData($data)
    {
        $goods      = $data['goods'];
        $senderInfo = $data['sender'] ?? [];
        $returnInfo = $data['return'] ?? [];
        $newData    = [
            'ref_no'                 => $data['orderNo'], // 客户订单号
            'business_type'          => 'BDS', // 业务类型(4PX内部调度所需，如需对接传值将说明，默认值：BDS。)
            'duty_type'              => 'U', // 税费费用承担方式(可选值：U、P); DDU由收件人支付关税：U; DDP 由寄件方支付关税：P; （如果物流产品只提供其中一种，则以4PX提供的为准）
            // 物流服务信息
            'logistics_service_info' => [
                'logistics_product_code' => $data['channelCode'], // 物流产品代码
            ],

            //  退件信息
            'return_info'            => [
                'is_return_on_domestic' => $data['is_return'] ?? 'Y', // 境内退件接收地址信息（处理策略为Y时必须填写地址信息）
                'domestic_return_addr'  => [
                    // 必填
                    'first_name'   => $returnInfo['contact_name'] ?? '',
                    'phone'        => $returnInfo['phone'] ?? '',
                    'post_code'    => $returnInfo['postcode'] ?? '',
                    'country'      => 'CN',
                    'city'         => $returnInfo['city'] ?? '',
                    'district'     => $returnInfo['district'] ?? '',

                    // 非必填
                    'last_name'    => '',
                    'company'      => $returnInfo['company_name'] ?? '',
                    'phone2'       => '',
                    'email'        => '',
                    'state'        => $returnInfo['province'] ?? '',
                    'street'       => $returnInfo['street'] ?? '',
                    'house_number' => '', // 门牌号

                ], // 境内退件接收地址信息（处理策略为Y时必须填写地址信息）
                'is_return_on_oversea'  => 'U', // 境外异常处理策略(退件：Y；销毁：N；其他：U；) 默认值：N；
                // 'oversea_return_addr'   => [], // 境外退件接收地址信息（处理策略为Y时必须填写地址信息）
            ],

            // 	包裹列表
            'parcel_list'            => [],
            // 保险
            'is_insure'              => 'N', // 是否投保(Y、N)
            // 'insurance_info'            => [],

            // 发件人信息
            'sender'                 => [
                // 必填
                'first_name'   => $senderInfo['contact_name'] ?? '',
                'country'      => 'CN',
                'city'         => $senderInfo['city'] ?? '',

                // 非必填
                'last_name'    => '',
                'company'      => $senderInfo['company_name'] ?? '',
                'phone'        => $senderInfo['phone'] ?? '',
                'phone2'       => '',
                'email'        => '',
                'post_code'    => $senderInfo['postcode'],
                'state'        => $senderInfo['province'] ?? '',
                'district'     => $senderInfo['district'] ?? '',
                'street'       => $senderInfo['street'] ?? '',
                'house_number' => '', // 门牌号
            ],
            // 收件人信息
            'recipient_info'         => [
                'first_name' => $data['receiverName'],
                'phone'      => $data['receiverMobile'],
                'country'    => $data['receiverCountryCode'],
                'city'       => $data['receiverCity'], // 城市
                'street'     => $data['receiverAddress'], // 街道/详细地址（可对应为adress 1）

                // 非必填
                'post_code'  => $data['receiverPostCode'] ?? '', // 邮编（部分产品需要填，具体以返回提示为准
                'district'   => $data['receiverAddress1'] ?? '', // 	区、县（可对应为adress 2）
                'state'      => $data['rProvince'], // 	州/省
            ],

            // 货物到仓方式信息
            'deliver_type_info'      => [
                'deliver_type' => 1, // 到仓方式（上门揽收：1；快递到仓：2；自送到仓:3；自送门店：5）
            ],

            // 投递信息
            // 'deliver_to_recipient_info' => [
            //
            // ],
        ];

        $packageWeight = 0; // 包裹重量 单位g
        $packagePrice  = 0; // 包裹申报价值（最多4位小数）

        $productList        = [];
        $declareProductInfo = [];
        foreach ($goods as $item) {
            $productList[] = [
                'product_name'             => $item['goods_en_name'], // 商品名称
                'product_description'      => $item['product_description'] ?? '', // 商品描述
                'product_unit_price'       => $item['goods_single_worth'], // 商品单价（按对应币别的法定单位，最多4位小数点）
                'currency'                 => 'USD', // 币别（按照ISO标准三字码，目前只支持USD）
                'qty'                      => $item['goods_number'], // 数量（单位为pcs）

                // 非必填
                'sku_code'                 => $item['sku_code'] ?? '', // SKU（客户自定义SKUcode）（数字或字母或空格）
                'standard_product_barcode' => $item['upc'] ?? '', // 商品标准条码（UPC、EAN、JAN…)

            ];
            // 海关申报列表信息(每个包裹的申报信息，方式1：填写申报产品代码和申报数量；方式2：填写其他详细申报信息)
            $declareProductInfo[] = [
                'declare_product_name_cn'   => $item['goods_cn_name'], // 申报品名(当地语言)
                'declare_product_name_en'   => $item['goods_en_name'], // 申报品名（英语）
                'declare_product_code_qty'  => $item['goods_number'], // 申报数量
                'declare_unit_price_export' => $item['goods_single_worth'], // 出口国申报单价（按对应币别的法定单位，最多4位小数点）
                'currency_export'           => 'USD',// 币别（按照ISO标准，目前只支持USD）
                'currency_import'           => 'USD', // 币别（按照ISO标准，目前只支持USD）
                'declare_unit_price_import' => $item['goods_single_worth'], // 进口国申报单价（按对应币别的法定单位，最多4位小数点）
                'brand_export '             => '无', // 	出口国品牌
                'brand_import'              => '无', // 进口国品牌
            ];

            $singleWeight  = $item['goods_single_weight'] * 1000; // 单位g
            $packageWeight += ($item['goods_number'] * $singleWeight);
            $packagePrice  += ($item['goods_number'] * $item['goods_single_worth']);
        }

        // 包裹信息
        $package                  = [
            'weight'               => $packageWeight,// 预报重量（g）
            'parcel_value'         => $packagePrice, // 包裹申报价值（最多4位小数）
            'currency'             => 'USD', // 币别（按照ISO标准三字码，目前只支持USD）
            'include_battery'      => 'N', // todo 是否含电池
            'battery_type'         => '', // 带电类型（内置电池PI966：966；配套电池PI967:967）
            'product_list'         => $productList,
            'declare_product_info' => $declareProductInfo
        ];
        $newData['parcel_list'][] = $package;

        return json_encode($newData);
    }

    /**
     * 处理接口请求
     *
     * @param $data
     * @param array $params
     * @throws Exception
     * @return array
     */
    protected function handleRequest($endPoint, $body)
    {
        $params = $this->getCommonParams($body, $endPoint['service_method']);

        $result = $this->client->requestApi($endPoint, $params, $body, $this->headers, true);

        if ($result['result'] == 0) {
            return ['code' => -1, 'message' => $result['msg'] ?? '', 'errors' => $result['errors'] ?? []];
        } else {
            return ['code' => 0, 'message' => '成功', 'data' => $result['data'] ?? []];
        }
    }

    protected function getCommonParams($body, $method)
    {
        $newParams = [
            'method'    => $method,
            'app_key'   => $this->appKey,
            'v'         => '1.0.0',
            'timestamp' => (int)(microtime(true) * 1000), // 毫秒时间戳
            'format'    => 'json',
            'sign'      => $this->getSign($body, $method),
            'language'  => 'cn', // 响应信息的语言，支持cn（中文），en（英文）
        ];

        return $newParams;
    }

    /**
     * 获取签名
     *
     * @param string $body
     * @param string $apiMethod
     * @return string
     */
    protected function getSign($body, $apiMethod = 'default')
    {
        $timestamp = (int)(microtime(true) * 1000);// // 毫秒时间戳

        $signatureStringBuffer = [
            'app_key', $this->appKey,
            'format', 'json',
            'method', $apiMethod,
            'timestamp', $timestamp,
            'v', '1.0.0',
            $body,
            $this->appSecret
        ];

        return strtoupper(md5(implode('', $signatureStringBuffer)));
    }

}

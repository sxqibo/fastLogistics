<?php

namespace Sxqibo\Logistics;

class Yiqifei
{
    private $config = [];
    private $baseUrl = 'http://api.17feia.com/eship-api/v1';

    public function __construct($config = [])
    {
        $this->config = $config;
        $this->validateConfig();
    }

    /**
     * 验证配置参数
     * @throws \Exception
     */
    private function validateConfig()
    {
        if (empty($this->config['apiName'])) {
            throw new \Exception('apiName 为空');
        }
        if (empty($this->config['apiToken'])) {
            throw new \Exception('apiToken 为空');
        }
    }

    /**
     * 使用curl发送HTTP请求
     */
    private function curlRequest(string $url, array $params = [], string $method = 'POST', array $headers = []): array
    {
        // 初始化curl
        $ch = curl_init();
        
        // 设置基本选项
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        // 设置请求方法
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($params)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if (!empty($params)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        // 设置请求头
        $requestHeaders = [
            'Content-Type: application/json; charset=utf-8',
            'Accept: application/json'
        ];
        foreach ($headers as $key => $value) {
            $requestHeaders[] = $key . ': ' . $value;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        
        // 执行请求
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        // 获取响应头信息
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $responseHeaders = substr($response, 0, $headerSize);
        $responseBody = substr($response, $headerSize);
        
        curl_close($ch);
        
        // 处理错误
        if ($error) {
            return [
                'Code' => -1,
                'Message' => 'CURL错误: ' . $error,
                'Data' => null
            ];
        }
        
        // 处理HTTP状态码
        if ($httpCode >= 400) {
            return [
                'Code' => $httpCode,
                'Message' => 'HTTP错误: ' . $httpCode,
                'Data' => null
            ];
        }
        
        // 解析JSON响应
        $data = json_decode($responseBody, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return [
                'Code' => -1,
                'Message' => 'JSON解析错误: ' . json_last_error_msg(),
                'Data' => null
            ];
        }
        
        // 如果响应为空，返回标准格式
        if ($data === null || !is_array($data)) {
            return [
                'Code' => -1,
                'Message' => 'API响应为空或格式错误',
                'Data' => null
            ];
        }
        
        // 转换一起飞的格式为标准格式
        if (isset($data['flag']) || isset($data['msg'])) {
            if ($data['flag'] === true || $data['flag'] === 'true' || $data['flag'] === 1) {
                return [
                    'Code' => 0,
                    'Message' => 'success',
                    'Data' => $data
                ];
            } else {
                return [
                    'Code' => -1,
                    'Message' => $data['msg'] ?? '未知错误',
                    'Data' => $data
                ];
            }
        }
        
        // 如果已经是标准格式，直接返回
        if (isset($data['Code']) || isset($data['Message'])) {
            return $data;
        }
        
        // 其他情况，包装为标准格式
        return [
            'Code' => 0,
            'Message' => 'success',
            'Data' => $data
        ];
    }

    /**
     * 获取可用的物流类型
     */
    public function getProducts()
    {
        $url = $this->baseUrl . '/products';
        $params = [
            'apiName'   => $this->config['apiName'],
            'apiToken'  => $this->config['apiToken'],
            'timestamp' => time(),
        ];

        return $this->curlRequest($url, $params, 'POST');
    }

    /**
     * 查询报价
     * @param array $params 查询参数
     * @return array
     */
    public function calFreight(array $params): array
    {
        $url = $this->baseUrl . '/calFreight';
        $requestParams = [
            'apiName'         => $this->config['apiName'],
            'apiToken'        => $this->config['apiToken'],
            'departureCode'   => $params['departureCode'] ?? '深圳',  // 出发地（可以使用中文"深圳"或英文"shenzhen"）
            'destinationCode' => $params['destinationCode'],          // 目的国编码
            'weight'          => $params['weight'],                   // 重量(KG)
            'length'          => $params['length'],                   // 长(CM)
            'width'           => $params['width'],                    // 宽(CM)
            'height'          => $params['height']                    // 高(CM)
        ];

        return $this->curlRequest($url, $requestParams, 'POST');
    }

    /**
     * 创建订单
     */
    public function createOrder($params)
    {
        $url = $this->baseUrl . '/orders';

        // 构建订单数据
        $orderData = [
            'apiName'   => $this->config['apiName'],
            'apiToken'  => $this->config['apiToken'],
            'apiOrders' => [
                [
                    'productCode'    => $params['productCode'],
                    'destinationNo'  => $params['countryCode'],
                    'takeAwayType'   => 'SELF',
                    'referenceNo'    => $params['referenceNo'],
                    'orderFromType'  => 'API',
                    'apiBoxes'       => [
                        [
                            'boxWeight' => $params['weight'],
                            'boxLength' => $params['length'],
                            'boxWidth'  => $params['width'],
                            'boxHeight' => $params['height'],
                            'apiGoodsList' => array_map(function($item) {
                                return [
                                    'nameEn'      => $item['nameEn'],
                                    'name'        => $item['name'],
                                    'quantity'    => $item['quantity'],
                                    'reportPrice' => $item['value'],
                                    'weight'      => $item['weight'],
                                ];
                            }, $params['items'])
                        ]
                    ],
                    'deliveryAddress' => [
                        'consignee'   => $params['recipientName'],
                        'province'    => $params['recipientState'],
                        'city'        => $params['recipientCity'],
                        'address'     => $params['recipientStreet'],
                        'postcode'    => $params['recipientPostcode'],
                        'cellphoneNo' => $params['recipientPhone'],
                        'email'       => $params['recipientEmail']
                    ],
                    'senderAddress' => [
                        'sender'      => '寄件人',
                        'province'    => '广东省',
                        'city'        => '深圳市',
                        'address'     => '福田区XX路XX号',
                        'postcode'    => '518000',
                        'cellphoneNo' => '13800138000',
                        'countryCode' => 'CN',
                        'email'       => 'sender@example.com'
                    ]
                ]
            ]
        ];

        return $this->curlRequest($url, $orderData, 'POST');
    }

    /**
     * 检查偏远地区
     */
    public function checkRemoteArea($params)
    {
        $url = $this->baseUrl . '/post-code/remote-check';
        $requestParams = [
            'apiName'     => $this->config['apiName'],
            'apiToken'    => $this->config['apiToken'],
            'productCode' => $params['productCode'],
            'country'     => $params['country'],
            'postCode'    => $params['postCode'],
            'weight'      => $params['weight'],
        ];

        return $this->curlRequest($url, $requestParams, 'POST');
    }

    /**
     * 更新订单重量和尺寸
     * 只有仓库未收货的订单才可以修改重量，只支持一票一件的订单修改重量
     */
    public function updateOrderInfo($params)
    {
        $url = $this->baseUrl . '/apiSearch/updateOrderInfo';
        $requestParams = [
            'apiName'      => $this->config['apiName'],
            'apiToken'     => $this->config['apiToken'],
            'insideNumber' => $params['insideNumber'] ?? '',      // 订单号
            'weight'       => $params['weight'],                  // 重量(KG)
            'boxLength'    => $params['boxLength'],              // 长(CM)
            'boxWidth'     => $params['boxWidth'],               // 宽(CM)
            'boxHeight'    => $params['boxHeight']               // 高(CM)
        ];

        return $this->curlRequest($url, $requestParams, 'POST');
    }

    /**
     * 获取派送单号和企业标签
     * @param array $orderNumbers 订单号数组
     * @return array
     */
    public function getDeliveryNo($orderNumbers)
    {
        $url = $this->baseUrl . '/apiSearch/requestDeliveryNo';
        $requestParams = [
            'apiName'      => $this->config['apiName'],
            'apiToken'     => $this->config['apiToken'],
            'orderNumbers' => $orderNumbers
        ];

        return $this->curlRequest($url, $requestParams, 'POST');
    }

    /**
     * 获取派送单号和派送标签
     * @param array $orderNumbers 订单号数组
     * @return array
     */
    public function getDeliveryLabel($orderNumbers)
    {
        $url = $this->baseUrl . '/apiSearch/requestPdfUrl';
        $requestParams = [
            'apiName'      => $this->config['apiName'],
            'apiToken'     => $this->config['apiToken'],
            'orderNumbers' => $orderNumbers
        ];

        return $this->curlRequest($url, $requestParams, 'POST');
    }

    /**
     * 获取订单追踪信息
     * @param array $orderNumbers 订单号数组
     * @return array
     */
    public function getTrackInfo($orderNumbers)
    {
        $url = $this->baseUrl . '/apiSearch/requestTrackInfo';
        $requestParams = [
            'apiName'      => $this->config['apiName'],
            'apiToken'     => $this->config['apiToken'],
            'orderNumbers' => $orderNumbers
        ];

        return $this->curlRequest($url, $requestParams, 'POST');
    }

    /**
     * 计算费用
     * @param array $params 订单参数
     * @return array
     */
    public function calculatePrice(array $params): array
    {
        $url = $this->baseUrl . '/product/price';

        // 构建请求数据
        $requestParams = [
            'apiName'   => $this->config['apiName'],
            'apiToken'  => $this->config['apiToken'],
            'apiOrders' => [
                [
                    'productCode'    => $params['productCode'] ?? '', // 必填， 表 示 产 品 的 code
                    'productName'    => $params['productName'], // 必填，表示产品(物流服务类型)的名称
                    'destinationNo'  => $params['destinationCode'], // 必填 ，目的地的国家二字简码
                    'takeAwayType'   => 'SELF',  // 必填，取件方式简单的将就是您已哪种方式将货物送往仓库。可用值：EXPRESS(“国内邮寄”), SELF(“自己送货”), ESHIP(“上门取货”);
                    'referenceNo'    => $params['referenceNo'] ?? '', // 必填，参考号，如果你们当前存在系统，那么通常表示你们系统的业务号。
                    'orderFromType'  => 'API',  // 必填，订单类型，【API 对接，填API 就行了】
                    'apiBoxes'       => [ // 箱子列表，包裹列表
                        [
                            'boxWeight' => $params['weight'], // （数字浮点型 必填）箱子的重量(单位：kg)
                            'boxLength' => $params['length'] ?? 0, // （数字整形 必填） 箱子的长 单位厘米(cm)
                            'boxWidth'  => $params['width'] ?? 0, // （数字整形 必填） 箱子的宽 单位厘米(cm)
                            'boxHeight' => $params['height'] ?? 0, // （数字整形 必填） 箱子的高 单位厘米(cm)
                            'apiGoodsList' => [ // 箱子里商品列表，预报列表
                                [
                                    'nameEn'      => $params['goods']['nameEn'] ?? '', // （字符串 必填）商品英文名
                                    'name'        => $params['goods']['name'] ?? '', // （字符串 必填）商品中文名
                                    'quantity'    => $params['goods']['quantity'] ?? 1, // （数字整形 必填）商品数量
                                    'reportPrice' => $params['goods']['value'] ?? 60.00, // （数字浮点型 必填）单个商品申报价值 (单位：美元)
                                    'weight'      => $params['goods']['weight'] ?? '', // （数字浮点型 必填）单个商品的重量(单位：kg)
                                ]
                            ]
                        ]
                    ],
                    'deliveryAddress' => [ // 表示寄送的地址信息
                        'consignee'   => $params['recipient']['name'] ?? '', // （字符串 必填）派送地址中的收货人不能为空
                        'province'    => $params['recipient']['state'] ?? '', // （字符串）,派送地址中的地区/州/省
                        'city'        => $params['recipient']['city'] ?? '', // （字符串 必填）,派送地址中的城市不能为空
                        'address'     => $params['recipient']['address'] ?? '', // （字符串 必填）派送地址中的地址不能为空
                        'postcode'    => $params['recipient']['postcode'], // （字符串 必填）派送地址中的邮编
                        'cellphoneNo' => $params['recipient']['phone'] ?? '', // （字符串 必填）派送地址中的电话
                    ],
                    // 'senderAddress'  => [ // 文档中说以下是必填，就如下模拟几个数据，但不填也可以
                        // 'sender'      => '寄件人', // （字符串 必填）寄件地址中的寄件人不能为空,
                        // 'province'    => '广东省', // （字符串 必填）寄件地址中的(地区/省/州)不能为空,
                        // 'city'        => '深圳市', // （字符串 必填）寄件地址中的(城市)不能为空,
                        // 'address'     => '福田区XX路XX号', // （字符串 必填）寄件地址中的(地址必填)不能为空,
                        // 'postcode'    => '518000', // （字符串 必填）寄件地址中的(邮编)不能为空,
                        // 'cellphoneNo' => '13800138000', // （字符串 必填）寄件地址中的(联系电话)不能为空,
                        // 'countryCode' => 'CN', // （字符串 必填）寄件地址中的(国家二字吗)不能为空
                    // ]
                ]
            ]
        ];

        return $this->curlRequest($url, $requestParams, 'POST');
    }
} 
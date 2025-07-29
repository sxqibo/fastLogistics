<?php

namespace Sxqibo\Logistics;

use Sxqibo\Logistics\common\Client;

class Yiqifei
{
    private $config = [];
    private $client;
    private $baseUrl = 'http://api.17feia.com/eship-api/v1';

    public function __construct($config = [])
    {
        $this->config = $config;
        $this->client = new Client();
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

        return $this->client->requestApi($url, $params);
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

        return $this->client->requestApi($url, $requestParams);
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

        return $this->client->requestApi($url, $orderData);
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

        return $this->client->requestApi($url, $requestParams);
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

        return $this->client->requestApi($url, $requestParams);
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

        return $this->client->requestApi($url, $requestParams);
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

        return $this->client->requestApi($url, $requestParams);
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

        return $this->client->requestApi($url, $requestParams);
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
                    'productCode'    => $params['productCode'],
                    'productName'    => $params['productName'],
                    'destinationNo'  => $params['destinationCode'],
                    'takeAwayType'   => 'SELF',
                    'referenceNo'    => $params['referenceNo'] ?? '',
                    'orderFromType'  => 'API',
                    'apiBoxes'       => [
                        [
                            'boxWeight' => $params['weight'],
                            'boxLength' => $params['length'],
                            'boxWidth'  => $params['width'],
                            'boxHeight' => $params['height'],
                            'apiGoodsList' => [
                                [
                                    'nameEn'      => $params['goods']['nameEn'],
                                    'name'        => $params['goods']['name'],
                                    'quantity'    => $params['goods']['quantity'],
                                    'reportPrice' => $params['goods']['value'],
                                    'weight'      => $params['goods']['weight']
                                ]
                            ]
                        ]
                    ],
                    'deliveryAddress' => [
                        'consignee'   => $params['recipient']['name'],
                        'province'    => $params['recipient']['state'],
                        'city'        => $params['recipient']['city'],
                        'address'     => $params['recipient']['address'],
                        'postcode'    => $params['recipient']['postcode'],
                        'cellphoneNo' => $params['recipient']['phone'],
                        'email'       => $params['recipient']['email'] ?? ''
                    ],
                    'senderAddress'  => [
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

        return $this->client->requestApi($url, $requestParams);
    }
} 
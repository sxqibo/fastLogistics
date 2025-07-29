<?php

namespace Sxqibo\Logistics;

use Sxqibo\Logistics\common\Client;

class Baotongda
{
    private $config;
    private $client;
    private $baseUrl = 'http://121.15.2.131:6005/webservice/PublicService.asmx';

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client();
    }

    /**
     * 构建基础参数
     */
    private function buildBaseParams($serviceMethod, $params = [])
    {
        return [
            'appToken'      => $this->config['appToken'],
            'appKey'        => $this->config['appKey'],
            'serviceMethod' => $serviceMethod,
            'paramsJson'    => json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ];
    }

    /**
     * 创建订单
     */
    public function createOrder(array $params): array
    {
        // 构建请求参数
        $requestParams = $this->buildBaseParams('createorder', $params);

        // 发送请求
        return $this->client->requestApi($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 提交预报
     */
    public function submitForecast(array $params): array
    {
        // 构建请求参数
        $requestParams = $this->buildBaseParams('submitforecast', $params);

        // 发送请求
        return $this->client->requestApi($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 修改订单
     */
    public function updateOrder(array $params): array
    {
        // 构建请求参数
        $requestParams = $this->buildBaseParams('updateorder', $params);

        // 发送请求
        return $this->client->requestApi($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 删除订单
     */
    public function deleteOrder(array $params): array
    {
        // 构建请求参数
        $requestParams = $this->buildBaseParams('removeorder', $params);

        // 发送请求
        return $this->client->requestApi($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 获取订单标签
     */
    public function getLabel(array $params): array
    {
        // 构建请求参数
        $requestParams = $this->buildBaseParams('getnewlabel', $params);

        // 发送请求
        return $this->client->requestApi($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 获取订单跟踪单号
     */
    public function getTrackingNumber(array $params): array
    {
        // 构建请求参数
        $requestParams = $this->buildBaseParams('gettrackingnumber', $params);

        // 发送请求
        return $this->client->requestApi($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 获取订单跟踪记录
     */
    public function getTrackingInfo(array $params): array
    {
        // 构建请求参数
        $requestParams = $this->buildBaseParams('gettrack', $params);

        // 发送请求
        return $this->client->requestApi($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 获取订单费用
     */
    public function getOrderFee(array $params): array
    {
        // 构建请求参数
        $requestParams = $this->buildBaseParams('getbusinessfee', $params);

        // 发送请求
        return $this->client->requestApi($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 获取订单费用明细
     */
    public function getOrderFeeDetail(array $params): array
    {
        // 构建请求参数
        $requestParams = $this->buildBaseParams('getbusinessfee_detail', $params);

        // 发送请求
        return $this->client->requestApi($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 获取订单重量
     */
    public function getWeight(array $params): array
    {
        // 构建请求参数
        $requestParams = $this->buildBaseParams('getbusinessweight', $params);

        // 发送请求
        return $this->client->requestApi($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 费用试算
     */
    public function calculateFee(array $params): array
    {
        // 构建请求参数
        $requestParams = $this->buildBaseParams('feetrail', $params);

        // 发送请求
        return $this->client->requestApi($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 获取基础数据
     */
    public function getBaseData(array $params): array
    {
        // 获取接口方法名
        $serviceMethod = $params['method'] ?? 'getshippingmethod';
        unset($params['method']);

        // 构建请求参数
        $requestParams = $this->buildBaseParams($serviceMethod, $params);

        // 发送请求
        return $this->client->requestApi($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }
} 
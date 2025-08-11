<?php

namespace Sxqibo\Logistics;

class Baotongda
{
    private $config;
    private $baseUrl = 'http://121.15.2.131:6005/webservice/PublicService.asmx';

    public function __construct(array $config)
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
        if (empty($this->config['appToken'])) {
            throw new \Exception('appToken 为空');
        }
        if (empty($this->config['appKey'])) {
            throw new \Exception('appKey 为空');
        }
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
     * 发送HTTP请求
     */
    private function sendRequest($url, $params, $method = 'POST', $headers = [])
    {
        // 设置默认请求头
        $defaultHeaders = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'User-Agent' => 'Sxqibo-Logistics/1.0'
        ];
        $headers = array_merge($defaultHeaders, $headers);

        // 初始化cURL
        $ch = curl_init();

        // 设置cURL选项
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => $this->buildHeaders($headers),
            CURLOPT_POST => ($method === 'POST'),
            CURLOPT_POSTFIELDS => ($method === 'POST') ? http_build_query($params) : null,
        ]);

        // 执行请求
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        // 检查错误
        if ($error) {
            throw new \Exception('cURL错误: ' . $error);
        }

        if ($httpCode !== 200) {
            throw new \Exception('HTTP请求失败，状态码: ' . $httpCode);
        }

        // 解析响应
        $result = $this->parseResponse($response);

        return $result;
    }

    /**
     * 构建请求头
     */
    private function buildHeaders($headers)
    {
        $headerArray = [];
        foreach ($headers as $key => $value) {
            $headerArray[] = $key . ': ' . $value;
        }
        return $headerArray;
    }

    /**
     * 解析响应
     */
    private function parseResponse($response)
    {
        // 尝试解析XML响应
        if (strpos($response, '<?xml') !== false || strpos($response, '<') === 0) {
            return $this->parseXmlResponse($response);
        }

        // 尝试解析JSON响应
        $jsonResult = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $jsonResult;
        }

        // 如果都不是，返回原始响应
        return [
            'success' => 0,
            'message' => '无法解析响应格式',
            'raw_response' => $response
        ];
    }

    /**
     * 解析XML响应
     */
    private function parseXmlResponse($xmlString)
    {
        try {
            $xml = simplexml_load_string($xmlString);
            if ($xml === false) {
                throw new \Exception('XML解析失败');
            }

            // 转换为数组
            $result = json_decode(json_encode($xml), true);

            // 检查是否包含错误信息
            if (isset($result['error'])) {
                return [
                    'success' => 0,
                    'message' => $result['error'],
                    'data' => null
                ];
            }

            // 检查是否包含数据
            if (isset($result['data']) || isset($result['result'])) {
                return [
                    'success' => 1,
                    'message' => '请求成功',
                    'data' => $result['data'] ?? $result['result'] ?? $result
                ];
            }

            return [
                'success' => 1,
                'message' => '请求成功',
                'data' => $result
            ];

        } catch (\Exception $e) {
            return [
                'success' => 0,
                'message' => 'XML解析异常: ' . $e->getMessage(),
                'raw_response' => $xmlString
            ];
        }
    }

    /**
     * 创建订单
     */
    public function createOrder(array $params): array
    {
        $requestParams = $this->buildBaseParams('createorder', $params);
        return $this->sendRequest($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 提交预报
     */
    public function submitForecast(array $params): array
    {
        $requestParams = $this->buildBaseParams('submitforecast', $params);
        return $this->sendRequest($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 修改订单
     */
    public function updateOrder(array $params): array
    {
        $requestParams = $this->buildBaseParams('updateorder', $params);
        return $this->sendRequest($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 删除订单
     */
    public function deleteOrder(array $params): array
    {
        $requestParams = $this->buildBaseParams('removeorder', $params);
        return $this->sendRequest($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 获取订单标签
     */
    public function getLabel(array $params): array
    {
        $requestParams = $this->buildBaseParams('getnewlabel', $params);
        return $this->sendRequest($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 获取订单跟踪单号
     */
    public function getTrackingNumber(array $params): array
    {
        $requestParams = $this->buildBaseParams('gettrackingnumber', $params);
        return $this->sendRequest($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 获取订单跟踪记录
     */
    public function getTrackingInfo(array $params): array
    {
        $requestParams = $this->buildBaseParams('gettrack', $params);
        return $this->sendRequest($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 获取订单费用
     */
    public function getOrderFee(array $params): array
    {
        $requestParams = $this->buildBaseParams('getbusinessfee', $params);
        return $this->sendRequest($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 获取订单费用明细
     */
    public function getOrderFeeDetail(array $params): array
    {
        $requestParams = $this->buildBaseParams('getbusinessfee_detail', $params);
        return $this->sendRequest($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 获取订单重量
     */
    public function getWeight(array $params): array
    {
        $requestParams = $this->buildBaseParams('getbusinessweight', $params);
        return $this->sendRequest($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 费用试算
     */
    public function calculateFee(array $params): array
    {
        $requestParams = $this->buildBaseParams('feetrail', $params);
        return $this->sendRequest($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }

    /**
     * 获取基础数据
     */
    public function getBaseData(array $params): array
    {
        $serviceMethod = $params['method'] ?? 'getshippingmethod';
        unset($params['method']);

        $requestParams = $this->buildBaseParams($serviceMethod, $params);
        return $this->sendRequest($this->baseUrl . '/ServiceInterfaceUTF8', $requestParams);
    }
}
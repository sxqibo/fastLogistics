<?php

namespace Sxqibo\Logistics;

/**
 * GoodCang API 客户端类
 */
class GoodCang
{
    /**
     * @var string API基础URL
     */
    private $baseUrl = 'https://oms.goodcang.net/public_open';

    /**
     * @var string App Token
     */
    private $appToken;

    /**
     * @var string App Key
     */
    private $appKey;

    /**
     * 构造函数
     * 
     * @param string $appToken App Token
     * @param string $appKey App Key
     */
    public function __construct($appToken, $appKey)
    {
        $this->appToken = $appToken;
        $this->appKey = $appKey;
    }

    /**
     * 发送HTTP请求的通用方法
     * 
     * @param string $endpoint API端点
     * @param array $params 请求参数
     * @return array 返回响应数据
     * @throws \Exception
     */
    private function sendRequest($endpoint, $params = [])
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => array(
                'app-token: ' . $this->appToken,
                'app-key: ' . $this->appKey,
                'Accept: application/json',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            throw new \Exception('CURL错误: ' . $error);
        }

        if ($httpCode !== 200) {
            $errorMsg = 'HTTP请求失败，状态码: ' . $httpCode;
            if ($response) {
                $errorMsg .= '，响应内容: ' . $response;
            }
            throw new \Exception($errorMsg);
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON解析失败: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * 获取成本流水列表
     * 
     * @param array $params 查询参数
     * @return array 返回响应数据
     * @throws \Exception
     */
    public function getCostFlowList($params = [])
    {
        $defaultParams = [
            'types_of_fee' => '0',
            'flow_type' => '0',
            'order_number' => '',
            'account_code' => '',
            'business_type' => '0',
            'currency_code' => 'USD',
            'charge_type' => '0',
            'search_after' => '',
            'page' => 1,
            'page_size' => 10,
            'happen_start_time' => date('Y-m-d H:i:s', strtotime('-30 days')),
            'happen_end_time' => date('Y-m-d H:i:s'),
            'number_type' => 'order_number'
        ];

        // 合并参数
        $requestParams = array_merge($defaultParams, $params);

        return $this->sendRequest('/finance/cost_flow_list', $requestParams);
    }

    /**
     * 获取产品库存
     * 
     * @param array $params 查询参数
     * @return array 返回响应数据
     * @throws \Exception
     */
    public function getProductInventory($params = [])
    {
        $defaultParams = [
            'page' => 1,
            'pageSize' => 20,
            'product_sku' => '',
            'product_sku_arr' => [],
            'warehouse_code' => '',
            'warehouse_code_arr' => []
        ];

        // 合并参数
        $requestParams = array_merge($defaultParams, $params);

        return $this->sendRequest('/inventory/get_product_inventory', $requestParams);
    }

    /**
     * 获取API基础URL
     * 
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * 设置API基础URL
     * 
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }
}

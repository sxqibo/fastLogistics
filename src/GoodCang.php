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
     * @doc https://open.goodcang.com/docs_api/finance/cost_flow_list
     * 
     * @param array $params 查询参数
     * @return array 返回响应数据
     * @throws \Exception
     */
    public function getCostFlowList($params = [])
    {
        // 必填参数默认值
        $defaultParams = [
            'page' => 1,
            'page_size' => 20,
            'happen_start_time' => date('Y-m-d 00:00:00', strtotime('-30 days')),
            'happen_end_time' => date('Y-m-d 23:59:59'),
        ];

        // 合并参数（用户传入的参数会覆盖默认值）
        $requestParams = array_merge($defaultParams, $params);

        // 清理空字符串的可选参数（避免传递不必要的参数）
        $optionalParams = ['account_code', 'business_type', 'charge_type', 'currency_code', 
                          'flow_type', 'next_page_token', 'prev_page_token', 'number_type', 
                          'order_number', 'types_of_fee'];
        
        foreach ($optionalParams as $key) {
            // 如果参数值为空字符串，则删除（不传递给API）
            if (isset($requestParams[$key]) && $requestParams[$key] === '') {
                // number_type 和 order_number 必须成对出现
                if ($key === 'number_type' && (empty($requestParams['order_number']) || $requestParams['order_number'] === '')) {
                    unset($requestParams['number_type']);
                } elseif ($key === 'order_number' && (empty($requestParams['number_type']) || $requestParams['number_type'] === '')) {
                    unset($requestParams['order_number']);
                } elseif ($key !== 'number_type' && $key !== 'order_number') {
                    unset($requestParams[$key]);
                }
            }
        }

        // 确保 business_type 和 charge_type 是整数类型（如果存在且不为空）
        if (isset($requestParams['business_type']) && $requestParams['business_type'] !== '') {
            $requestParams['business_type'] = (int)$requestParams['business_type'];
        }
        if (isset($requestParams['charge_type']) && $requestParams['charge_type'] !== '') {
            $requestParams['charge_type'] = (int)$requestParams['charge_type'];
        }

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
     * 获取库龄列表
     * 
     * @param array $params 查询参数
     * @return array 返回响应数据
     * @throws \Exception
     */
    public function getInventoryAgeList($params = [])
    {
        $defaultParams = [
            'page' => 1,
            'page_size' => 20,
            'age_from' => 1,
            'age_to' => 9999,
            'fifo_time_from' => date('Y-m-d H:i:s', strtotime('-180 days')),
            'fifo_time_to' => date('Y-m-d H:i:s'),
            'product_sku_list' => [],
            'product_title' => '',
            'product_title_en' => '',
            'quantity_from' => 1,
            'quantity_to' => 999999999,
            'warehouse_code' => '',
            'warning_age_type' => 1
        ];

        // 合并参数
        $requestParams = array_merge($defaultParams, $params);

        return $this->sendRequest('/inventory/inventory_age_list', $requestParams);
    }

    /**
     * 获取公司账户列表
     *
     * @return array 返回响应数据
     * @throws \Exception
     */
    public function getAccountList()
    {
        return $this->sendRequest('/base_data/get_account_list');
    }

    /**
     * 获取账户金额明细
     *
     * @param array $params 查询参数
     * @return array 返回响应数据
     * @throws \Exception
     */
    public function getAccountDetail($params = [])
    {
        $defaultParams = [
            'account_code' => '',
            'account_codes' => [],
        ];

        $requestParams = array_merge($defaultParams, $params);

        return $this->sendRequest('/finance/get_account_detail', $requestParams);
    }

    /**
     * 获取货币列表
     *
     * @return array 返回响应数据
     * @throws \Exception
     */
    public function getCurrencyRateList()
    {
        return $this->sendRequest('/finance/currency_rate_list');
    }

    /**
     * 获取仓库信息
     * 
     * @return array 返回响应数据
     * @throws \Exception
     */
    public function getWarehouse()
    {
        return $this->sendRequest('/base_data/get_warehouse');
    }

    /**
     * 获取订单列表
     * @doc https://open.goodcang.com/docs_api/order/get_order_list
     * 
     * @param array $params 查询参数
     * @return array 返回响应数据
     * @throws \Exception
     */
    public function getOrderList($params = [])
    {
        // 必填参数默认值
        $defaultParams = [
            'page' => 1,
            'pageSize' => 20,
        ];

        // 合并参数（用户传入的参数会覆盖默认值）
        $requestParams = array_merge($defaultParams, $params);

        // 清理空字符串的可选参数（避免传递不必要的参数）
        $optionalParams = ['code_type', 'create_date_from', 'create_date_to', 'date_shipping_from', 
                          'date_shipping_to', 'modify_date_from', 'modify_date_to', 'ooh_code', 
                          'order_code', 'order_code_arr', 'order_status', 'shipping_method', 'ship_status'];
        
        foreach ($optionalParams as $key) {
            // 如果参数值为空字符串或空数组，则删除
            if (isset($requestParams[$key])) {
                if ($requestParams[$key] === '' || (is_array($requestParams[$key]) && empty($requestParams[$key]))) {
                    unset($requestParams[$key]);
                }
            }
        }

        // 确保 page 和 pageSize 是整数类型
        if (isset($requestParams['page'])) {
            $requestParams['page'] = (int)$requestParams['page'];
        }
        if (isset($requestParams['pageSize'])) {
            $requestParams['pageSize'] = (int)$requestParams['pageSize'];
        }
        
        // 确保 ship_status 是整数类型（如果存在）
        if (isset($requestParams['ship_status']) && $requestParams['ship_status'] !== '') {
            $requestParams['ship_status'] = (int)$requestParams['ship_status'];
        }

        return $this->sendRequest('/order/get_order_list', $requestParams);
    }

    /**
     * 运费试算
     * 
     * @param array $params 查询参数
     * @return array 返回响应数据
     * @throws \Exception
     */
    public function getCalculateDeliveryFee($params = [])
    {
        $defaultParams = [
            'city' => '',
            'country_code' => '',
            'height' => 0,
            'insurance_amount' => 0,
            'is_insurance_service' => 1,
            'is_residential' => 1,
            'is_sign_server' => 1,
            'length' => 0,
            'postcode' => '',
            'property_label' => '',
            'sku' => [],
            'sm_code' => '',
            'state' => '',
            'warehouse_code' => '',
            'weight' => 0,
            'width' => 0
        ];

        // 合并参数
        $requestParams = array_merge($defaultParams, $params);

        return $this->sendRequest('/inventory/get_calculate_delivery_fee', $requestParams);
    }

    /**
     * 获取商品列表（SKU维度）
     * 对应接口：/product/get_product_sku_list
     *
     * @param array $params 查询参数
     * @return array
     * @throws \Exception
     */
    public function getProductSkuList($params = [])
    {
        $defaultParams = [
            'page' => 1,
            'pageSize' => 20,
            'product_sku' => '',
            'product_sku_arr' => [],
            'product_update_time_from' => '',
            'product_update_time_to' => '',
        ];

        // 合并参数
        $requestParams = array_merge($defaultParams, $params);

        // 清理可选参数中的空值
        $optionalParams = ['product_sku', 'product_sku_arr', 'product_update_time_from', 'product_update_time_to'];
        foreach ($optionalParams as $key) {
            if (!isset($requestParams[$key])) {
                continue;
            }

            // 空字符串
            if (is_string($requestParams[$key]) && $requestParams[$key] === '') {
                unset($requestParams[$key]);
                continue; // 已删除，跳过后续检查
            }

            // 空数组（需要再次检查键是否存在，因为可能在上一步被删除了）
            if (isset($requestParams[$key]) && is_array($requestParams[$key]) && empty($requestParams[$key])) {
                unset($requestParams[$key]);
            }
        }

        // 确保分页参数为整数
        if (isset($requestParams['page'])) {
            $requestParams['page'] = (int)$requestParams['page'];
        }
        if (isset($requestParams['pageSize'])) {
            $requestParams['pageSize'] = (int)$requestParams['pageSize'];
        }

        return $this->sendRequest('/product/get_product_sku_list', $requestParams);
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

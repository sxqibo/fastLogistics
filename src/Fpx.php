<?php

namespace Sxqibo\Logistics;

use Sxqibo\Logistics\common\Client;
use Exception;

/**
 * 递四方物流 类库
 * 基于递四方开放平台API
 * 文档地址: https://open.4px.com/v2/doc/detail?ids=54,67,114
 */
class Fpx
{
    private $appKey;
    private $appSecret;
    private $client;
    private $baseUrl;
    private $accessToken;

    /**
     * 递四方构造函数
     *
     * @param string $appKey     应用接入申请的AppKey
     * @param string $appSecret  App Secret
     * @param string $environment  环境：'sandbox' 或 'production'，默认sandbox
     */
    public function __construct($appKey, $appSecret, $environment = 'sandbox')
    {
        try {
            $this->appKey = trim($appKey);
            $this->appSecret = trim($appSecret);
            $this->client = new Client();

            if (empty($appKey)) {
                throw new Exception("appKey is empty");
            }
            if (empty($appSecret)) {
                throw new Exception("appSecret is empty");
            }

            // 根据环境设置不同的基础URL
            if ($environment === 'production') {
                $this->baseUrl = 'https://open.4px.com/router/api/service';
            } else {
                $this->baseUrl = 'https://open-test.4px.com/router/api/service';
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 生成签名
     * 使用MD5加密算法对参数进行签名
     * 按照递四方官方文档的签名规则
     *
     * @param array $commonParams 公共参数
     * @param string $bodyData 请求体数据
     * @return string
     */
    private function generateSign($commonParams, $bodyData = '')
    {
        // 1. 按首字母升序排序（access_token和language不参与签名）
        $signParams = [];
        foreach ($commonParams as $key => $value) {
            if (!in_array($key, ['sign', 'access_token', 'language']) && $value !== '') {
                $signParams[$key] = $value;
            }
        }
        ksort($signParams);
        
        // 2. 连接字符串（去掉所有=和&），连接参数名与参数值
        $signString = '';
        foreach ($signParams as $key => $value) {
            $signString .= $key . $value;
        }
        
        // 3. 在末尾加上body信息和appSecret
        $signString .= $bodyData . $this->appSecret;
        
        // 调试信息：打印签名字符串
        if (defined('DEBUG') && DEBUG) {
            echo "参与签名的参数: " . json_encode($signParams, JSON_UNESCAPED_UNICODE) . "\n";
            echo "Body数据: " . $bodyData . "\n";
            echo "签名字符串: " . $signString . "\n";
            echo "签名结果: " . md5($signString) . "\n";
        }
        
        // 4. 使用MD5加密生成32位小写签名值
        return md5($signString);
    }

    /**
     * 构建公共参数
     *
     * @param string $method API接口名称
     * @param array $businessData 业务数据
     * @return array
     */
    private function buildCommonParams($method, $businessData = [])
    {
        $bodyData = json_encode($businessData, JSON_UNESCAPED_UNICODE);
        
        $params = [
            'method' => $method,
            'app_key' => $this->appKey,
            'v' => '1.0.0',  // API协议版本
            'timestamp' => (string)(microtime(true) * 1000), // 时间戳(毫秒)
            'format' => 'json'
        ];

        // 生成签名（按照递四方文档要求）
        $params['sign'] = $this->generateSign($params, $bodyData);

        return $params;
    }

    /**
     * 通用请求方法
     * 按照递四方官方文档的请求格式
     *
     * @param string $method API接口名称
     * @param array $businessData 业务数据
     * @return array
     */
    private function request($method, $businessData = [])
    {
        $bodyData = json_encode($businessData, JSON_UNESCAPED_UNICODE);
        $params = $this->buildCommonParams($method, $businessData);
        
        // 如果有access_token，添加到参数中
        if (!empty($this->accessToken)) {
            $params['access_token'] = $this->accessToken;
        }

        // 构建请求URL（公共参数放在URL后面）
        $url = $this->baseUrl . '?' . http_build_query($params);
        
        $headers = [
            'Content-Type' => 'application/json'
        ];

        // 调试信息：打印请求参数
        if (defined('DEBUG') && DEBUG) {
            echo "请求URL: " . $url . "\n";
            echo "请求Body: " . $bodyData . "\n";
            echo "请求参数: " . json_encode($params, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        }

        // 使用自定义的请求方法
        $result = $this->customRequest($url, $bodyData, $headers);
        
        // 处理字符编码问题
        if (is_array($result)) {
            $result = $this->fixEncoding($result);
        }
        
        // 处理问号消息
        if (is_array($result)) {
            $result = $this->processMessage($result);
        }
        
        // 调试信息：打印响应结果
        if (defined('DEBUG') && DEBUG) {
            echo "响应结果: " . json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        }

        return $result;
    }

    /**
     * 自定义请求方法
     * 按照递四方文档要求的请求格式
     *
     * @param string $url 请求URL
     * @param string $bodyData 请求体数据
     * @param array $headers 请求头
     * @return array
     */
    private function customRequest($url, $bodyData, $headers)
    {
        try {
            $curl = curl_init();
            
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $bodyData,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);
            
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            
            curl_close($curl);
            
            if ($error) {
                throw new Exception("CURL错误: " . $error);
            }
            
            if ($httpCode !== 200) {
                throw new Exception("HTTP错误: " . $httpCode . ", 响应: " . $response);
            }
            
            $result = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("JSON解析错误: " . json_last_error_msg());
            }
            
            return $result;
            
        } catch (Exception $e) {
            throw new Exception("请求失败: " . $e->getMessage());
        }
    }

    /**
     * 处理消息字段
     * 将问号消息转换为有意义的描述
     *
     * @param array $data 响应数据
     * @return array
     */
    private function processMessage($data)
    {
        if (is_array($data)) {
            // 处理主消息字段
            if (isset($data['msg']) && $data['msg'] === '??????') {
                if (isset($data['result'])) {
                    switch ($data['result']) {
                        case '1':
                            $data['msg'] = '请求成功';
                            break;
                        case '0':
                            $data['msg'] = '请求失败';
                            break;
                        case '2':
                            $data['msg'] = '部分成功';
                            break;
                        default:
                            $data['msg'] = '未知状态';
                            break;
                    }
                } else {
                    $data['msg'] = '系统处理成功';
                }
            }
            
            // 处理错误消息字段
            if (isset($data['errors']) && is_array($data['errors'])) {
                foreach ($data['errors'] as $key => $error) {
                    if (isset($error['error_msg']) && $error['error_msg'] === '??????') {
                        if (isset($error['error_code'])) {
                            $data['errors'][$key]['error_msg'] = $this->getErrorMessage($error['error_code']);
                        } else {
                            $data['errors'][$key]['error_msg'] = '未知错误';
                        }
                    }
                }
            }
            
            // 递归处理嵌套数组
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $this->processMessage($value);
                }
            }
        }
        return $data;
    }

    /**
     * 根据错误码获取错误消息
     *
     * @param string $errorCode 错误码
     * @return string
     */
    private function getErrorMessage($errorCode)
    {
        $errorMessages = [
            '000012' => '签名验证错误',
            '000004' => 'JSON解析失败',
            '000013' => 'app_key校验失败',
            '000014' => '认证参数非法',
            '000011' => 'API接口不存在',
            '000015' => 'API接口不可用',
            '000016' => 'API接口未授权',
            '000017' => '访问太过频繁,请一分钟后再试',
            '000018' => 'APP不存在',
            '000019' => '限流策略未配置,请联系客服',
            '000020' => '请求超时或异常',
            '000021' => '用户和APP不匹配',
            '000022' => '用户未授权,请联系技术支持人员',
            '000023' => '用户不可用',
            '000024' => '服务商接口404',
            '000025' => '用户类型与接口不匹配,请联系客服'
        ];
        
        return $errorMessages[$errorCode] ?? '未知错误码: ' . $errorCode;
    }

    /**
     * 修复字符编码问题
     * 递归处理数组中的字符串编码
     *
     * @param array $data 需要处理的数据
     * @return array
     */
    private function fixEncoding($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->fixEncoding($value);
            }
        } elseif (is_string($data)) {
            // 尝试检测和转换编码
            $encoding = mb_detect_encoding($data, ['UTF-8', 'GBK', 'GB2312', 'ISO-8859-1'], true);
            if ($encoding && $encoding !== 'UTF-8') {
                $data = mb_convert_encoding($data, 'UTF-8', $encoding);
            }
        }
        return $data;
    }

    /**
     * 设置访问令牌
     * 通过OAuth授权方式获得，要求平台服务商、第三方软件商必须传入
     *
     * @param string $accessToken 访问令牌
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * 库存查询
     * 查询指定SKU或批次的库存信息
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getInventory($params = [])
    {
        // 参数验证
        $validatedParams = [];
        
        // 客户操作账号
        if (isset($params['customer_code'])) {
            $validatedParams['customer_code'] = $params['customer_code'];
        }
        
        // SKU编号列表（单次最大支持100种SKU种类查询）
        if (isset($params['lstsku']) && is_array($params['lstsku'])) {
            if (count($params['lstsku']) > 100) {
                throw new Exception('单次最大支持100种SKU种类查询');
            }
            $validatedParams['lstsku'] = $params['lstsku'];
        }
        
        // 批次号列表（单次最大支持100个批次号查询）
        if (isset($params['lstbatch_no']) && is_array($params['lstbatch_no'])) {
            if (count($params['lstbatch_no']) > 100) {
                throw new Exception('单次最大支持100个批次号查询');
            }
            $validatedParams['lstbatch_no'] = $params['lstbatch_no'];
        }
        
        // 仓库代码
        if (isset($params['warehouse_code'])) {
            $validatedParams['warehouse_code'] = $params['warehouse_code'];
        }
        
        // 分页参数
        if (isset($params['page_no'])) {
            $validatedParams['page_no'] = (int)$params['page_no'];
        }
        
        if (isset($params['page_size'])) {
            $pageSize = (int)$params['page_size'];
            if ($pageSize > 500) {
                throw new Exception('单次查询最大支持500条记录');
            }
            $validatedParams['page_size'] = $pageSize;
        }

        return $this->request('fu.wms.inventory.get', $validatedParams);
    }

    /**
     * 根据SKU查询库存
     * 便捷方法：根据SKU编号查询库存
     *
     * @param string|array $skuCodes SKU编号，可以是字符串或数组
     * @param string $customerCode 客户操作账号
     * @param string $warehouseCode 仓库代码
     * @param int $pageNo 页码，默认1
     * @param int $pageSize 每页记录数，默认50
     * @return array
     */
    public function getInventoryBySku($skuCodes, $customerCode = '', $warehouseCode = '', $pageNo = 1, $pageSize = 50)
    {
        $params = [
            'page_no' => $pageNo,
            'page_size' => $pageSize
        ];

        if (!empty($customerCode)) {
            $params['customer_code'] = $customerCode;
        }

        if (!empty($warehouseCode)) {
            $params['warehouse_code'] = $warehouseCode;
        }

        // 处理SKU编号
        if (is_string($skuCodes)) {
            $params['lstsku'] = [$skuCodes];
        } elseif (is_array($skuCodes)) {
            $params['lstsku'] = $skuCodes;
        } else {
            throw new Exception('SKU编号必须是字符串或数组');
        }

        return $this->getInventory($params);
    }

    /**
     * 根据批次号查询库存
     * 便捷方法：根据批次号查询库存
     *
     * @param string|array $batchNos 批次号，可以是字符串或数组
     * @param string $customerCode 客户操作账号
     * @param string $warehouseCode 仓库代码
     * @param int $pageNo 页码，默认1
     * @param int $pageSize 每页记录数，默认50
     * @return array
     */
    public function getInventoryByBatch($batchNos, $customerCode = '', $warehouseCode = '', $pageNo = 1, $pageSize = 50)
    {
        $params = [
            'page_no' => $pageNo,
            'page_size' => $pageSize
        ];

        if (!empty($customerCode)) {
            $params['customer_code'] = $customerCode;
        }

        if (!empty($warehouseCode)) {
            $params['warehouse_code'] = $warehouseCode;
        }

        // 处理批次号
        if (is_string($batchNos)) {
            $params['lstbatch_no'] = [$batchNos];
        } elseif (is_array($batchNos)) {
            $params['lstbatch_no'] = $batchNos;
        } else {
            throw new Exception('批次号必须是字符串或数组');
        }

        return $this->getInventory($params);
    }

    /**
     * 查询所有库存
     * 便捷方法：查询所有库存信息（不指定SKU或批次号）
     *
     * @param string $customerCode 客户操作账号
     * @param string $warehouseCode 仓库代码
     * @param int $pageNo 页码，默认1
     * @param int $pageSize 每页记录数，默认50
     * @return array
     */
    public function getAllInventory($customerCode = '', $warehouseCode = '', $pageNo = 1, $pageSize = 50)
    {
        $params = [
            'page_no' => $pageNo,
            'page_size' => $pageSize
        ];

        if (!empty($customerCode)) {
            $params['customer_code'] = $customerCode;
        }

        if (!empty($warehouseCode)) {
            $params['warehouse_code'] = $warehouseCode;
        }

        return $this->getInventory($params);
    }

    /**
     * 格式化库存数据
     * 将API返回的库存数据格式化为更易读的格式
     *
     * @param array $response API响应数据
     * @return array
     */
    public function formatInventoryData($response)
    {
        if (!isset($response['data']['data']) || !is_array($response['data']['data'])) {
            return $response;
        }

        $formattedData = [];
        foreach ($response['data']['data'] as $item) {
            $formattedData[] = [
                'customer_code' => $item['customer_code'] ?? '',
                'sku_code' => $item['sku_code'] ?? '',
                'warehouse_code' => $item['warehouse_code'] ?? '',
                'sku_id' => $item['sku_id'] ?? '',
                'batch_no' => $item['batch_no'] ?? '',
                'stock_quality' => $item['stock_quality'] ?? '',
                'available_stock' => (int)($item['available_stock'] ?? 0),      // 可用库存
                'pending_stock' => (int)($item['pending_stock'] ?? 0),          // 待出库库存
                'onway_stock' => (int)($item['onway_stock'] ?? 0),              // 在途库存
                'total_stock' => (int)($item['available_stock'] ?? 0) + (int)($item['pending_stock'] ?? 0) + (int)($item['onway_stock'] ?? 0)  // 总库存
            ];
        }

        return [
            'result' => $response['result'] ?? '',
            'msg' => $response['msg'] ?? '',
            'data' => [
                'page_no' => $response['data']['page_no'] ?? '',
                'page_size' => $response['data']['page_size'] ?? '',
                'total' => $response['data']['total'] ?? '',
                'inventory_list' => $formattedData
            ]
        ];
    }
}
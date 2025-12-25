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
    private $language = 'cn'; // 响应语言，默认中文

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
        
        // 如果有access_token，添加到参数中（不参与签名）
        if (!empty($this->accessToken)) {
            $params['access_token'] = $this->accessToken;
        }

        // 添加language参数（不参与签名）
        if (!empty($this->language)) {
            $params['language'] = $this->language;
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
                    'Content-Type: application/json; charset=utf-8',
                    'Accept: application/json; charset=utf-8'
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_ENCODING => '' // 自动处理响应编码
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
            
            // 保存原始响应用于调试和编码修复
            $originalResponse = $response;
            
            // 在JSON解码前，先检测响应字符串中是否包含乱码特征
            // 如果响应中包含 "?-" 后跟多个问号，可能是GBK编码的JSON
            if (preg_match('/"sku_name"\s*:\s*"\?-\?+/', $response) || 
                preg_match('/"[\w_]+"\s*:\s*"\?-\?+/', $response)) {
                // 检测到可能的乱码模式，尝试从GBK转换
                $response = @mb_convert_encoding($response, 'UTF-8', 'GBK');
                // 如果转换失败，尝试其他编码
                if ($response === false || $response === $originalResponse) {
                    $response = @mb_convert_encoding($originalResponse, 'UTF-8', 'GB2312');
                }
                if ($response === false || $response === $originalResponse) {
                    $response = @mb_convert_encoding($originalResponse, 'UTF-8', 'GB18030');
                }
                // 如果还是失败，使用原始响应
                if ($response === false) {
                    $response = $originalResponse;
                }
            }
            
            // 策略1：先尝试正常JSON解码
            $result = json_decode($response, true);
            $jsonError = json_last_error();
            
            // 如果JSON解码成功，检查是否包含乱码（连续问号）
            if ($jsonError === JSON_ERROR_NONE && is_array($result)) {
                $hasGarbled = false;
                $garbledCount = 0;
                array_walk_recursive($result, function($value) use (&$hasGarbled, &$garbledCount) {
                    if (is_string($value) && preg_match('/\?{2,}/', $value) && mb_strlen($value) > 2) {
                        $hasGarbled = true;
                        $garbledCount++;
                    }
                });
                
                // 如果检测到乱码，尝试多种编码转换方式
                if ($hasGarbled) {
                    // 策略2：尝试将响应从GBK转换为UTF-8，然后重新解码
                    $encodingsToTry = ['GBK', 'GB2312', 'GB18030', 'Windows-1252'];
                    
                    foreach ($encodingsToTry as $encoding) {
                        $responseFixed = @mb_convert_encoding($originalResponse, 'UTF-8', $encoding);
                        if ($responseFixed !== false && $responseFixed !== $originalResponse) {
                            $resultFixed = json_decode($responseFixed, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($resultFixed)) {
                                // 检查修复后的结果是否还有乱码
                                $stillGarbled = false;
                                $fixedGarbledCount = 0;
                                array_walk_recursive($resultFixed, function($value) use (&$stillGarbled, &$fixedGarbledCount) {
                                    if (is_string($value) && preg_match('/\?{2,}/', $value) && mb_strlen($value) > 2) {
                                        $stillGarbled = true;
                                        $fixedGarbledCount++;
                                    }
                                });
                                
                                // 如果修复后乱码减少了，使用修复后的结果
                                if ($fixedGarbledCount < $garbledCount) {
                                    $result = $resultFixed;
                                    $garbledCount = $fixedGarbledCount;
                                    if (!$stillGarbled) {
                                        // 完全没有乱码了，直接使用
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    
                    // 策略3：如果还有乱码，尝试使用iconv转换
                    if ($garbledCount > 0) {
                        foreach ($encodingsToTry as $encoding) {
                            $responseFixed = @iconv($encoding, 'UTF-8//IGNORE', $originalResponse);
                            if ($responseFixed !== false && $responseFixed !== $originalResponse) {
                                $resultFixed = json_decode($responseFixed, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($resultFixed)) {
                                    $stillGarbled = false;
                                    $fixedGarbledCount = 0;
                                    array_walk_recursive($resultFixed, function($value) use (&$stillGarbled, &$fixedGarbledCount) {
                                        if (is_string($value) && preg_match('/\?{2,}/', $value) && mb_strlen($value) > 2) {
                                            $stillGarbled = true;
                                            $fixedGarbledCount++;
                                        }
                                    });
                                    
                                    if ($fixedGarbledCount < $garbledCount) {
                                        $result = $resultFixed;
                                        if (!$stillGarbled) {
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } elseif ($jsonError !== JSON_ERROR_NONE) {
                // JSON解码失败，尝试从GBK转换后重新解码
                $responseFixed = @mb_convert_encoding($originalResponse, 'UTF-8', 'GBK');
                if ($responseFixed !== false) {
                    $result = json_decode($responseFixed, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new Exception("JSON解析错误: " . json_last_error_msg());
                    }
                } else {
                    throw new Exception("JSON解析错误: " . json_last_error_msg());
                }
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
     * 递归处理数组中的字符串编码，特别处理包含问号的乱码
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
            // 检查是否包含连续的问号（乱码特征）
            if (preg_match('/\?{2,}/', $data)) {
                // 包含乱码，尝试修复
                // 递四方API可能返回GBK编码的JSON，但被错误解析为UTF-8
                // 我们需要重新从原始字节转换
                // 但由于已经JSON解码，我们只能尝试从GBK转换
                $converted = @mb_convert_encoding($data, 'UTF-8', 'GBK');
                if ($converted !== false && !preg_match('/\?{2,}/', $converted)) {
                    // 转换后没有乱码，使用转换后的结果
                    $data = $converted;
                } else {
                    // 如果直接转换不行，尝试其他方式
                    // 将问号替换为可能的原始字节，然后转换
                    // 但这种方法不可靠，所以先尝试直接转换
                }
            } elseif (!mb_check_encoding($data, 'UTF-8')) {
                // 不是有效的UTF-8，尝试检测和转换编码
                $encoding = mb_detect_encoding($data, ['UTF-8', 'GBK', 'GB2312', 'ISO-8859-1', 'Windows-1252'], true);
                if ($encoding && $encoding !== 'UTF-8') {
                    $converted = @mb_convert_encoding($data, 'UTF-8', $encoding);
                    if ($converted !== false) {
                        $data = $converted;
                    }
                }
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
     * 设置响应语言
     * 设置API响应信息的语言
     *
     * @param string $language 语言代码，支持 'cn'（中文）或 'en'（英文），默认为 'cn'
     */
    public function setLanguage($language = 'cn')
    {
        if (in_array($language, ['cn', 'en'])) {
            $this->language = $language;
        } else {
            throw new Exception('语言参数必须是 "cn" 或 "en"');
        }
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

    /**
     * 查询库存库龄
     * 查询指定SKU的库存库龄信息，如果不传SKU则查询全部
     *
     * @param string|array|null $skuCodes SKU编号，可以是字符串或数组（单次最大支持100种SKU种类查询），传null或空字符串则查询全部
     * @param string $customerCode 客户操作账号（可选）
     * @param string $warehouseCode 仓库代码（可选）
     * @return array
     */
    public function getInventoryAge($skuCodes = null, $customerCode = '', $warehouseCode = '')
    {
        $params = [];

        // 客户操作账号
        if (!empty($customerCode)) {
            $params['customer_code'] = $customerCode;
        }

        // 仓库代码
        if (!empty($warehouseCode)) {
            $params['warehouse_code'] = $warehouseCode;
        }

        // 处理SKU编号
        // 如果 $skuCodes 为 null 或空字符串，则不传 lstsku 参数，查询全部
        if ($skuCodes !== null && $skuCodes !== '') {
            // 根据API文档，lstsku支持多个SKU（单次最大支持100种）
            // 参考 getInventory 方法，直接传递数组格式（会被JSON编码为数组）
            if (is_array($skuCodes)) {
                if (count($skuCodes) > 100) {
                    throw new Exception('单次最大支持100种SKU种类查询');
                }
                $params['lstsku'] = $skuCodes; // 直接传递数组，会被JSON编码为数组格式
            } elseif (is_string($skuCodes)) {
                // 如果是字符串，先尝试按逗号分割（支持逗号分隔的字符串）
                if (strpos($skuCodes, ',') !== false) {
                    $params['lstsku'] = array_map('trim', explode(',', $skuCodes));
                } else {
                    $params['lstsku'] = [$skuCodes]; // 单个SKU也转为数组
                }
            } else {
                throw new Exception('SKU编号必须是字符串或数组');
            }
        }
        // 如果 $skuCodes 为 null 或空字符串，则不添加 lstsku 参数，让API返回全部

        return $this->request('fu.wms.inventory.getdetail', $params);
    }

    /**
     * 批量查询SKU
     * 查询SKU信息集合
     *
     * @param string|array $skuCodes SKU编号，可以是字符串或数组（最大支持100个SKU查询）
     * @param string $customerCode 客户操作账号（可选）
     * @return array
     */
    public function getSkuList($skuCodes, $customerCode = '')
    {
        $params = [];

        // 客户操作账号
        if (!empty($customerCode)) {
            $params['customer_code'] = $customerCode;
        }

        // 处理SKU编号
        if (is_array($skuCodes)) {
            if (count($skuCodes) > 100) {
                throw new Exception('每次只能查询最多100个SKU');
            }
            if (empty($skuCodes)) {
                throw new Exception('SKU编号不能为空');
            }
            $params['lstsku'] = $skuCodes;
        } elseif (is_string($skuCodes)) {
            if (empty($skuCodes)) {
                throw new Exception('SKU编号不能为空');
            }
            // 如果是字符串，先尝试按逗号分割（支持逗号分隔的字符串）
            if (strpos($skuCodes, ',') !== false) {
                $skuArray = array_map('trim', explode(',', $skuCodes));
                if (count($skuArray) > 100) {
                    throw new Exception('每次只能查询最多100个SKU');
                }
                $params['lstsku'] = $skuArray;
            } else {
                $params['lstsku'] = [$skuCodes]; // 单个SKU也转为数组
            }
        } else {
            throw new Exception('SKU编号必须是字符串或数组');
        }

        return $this->request('fu.wms.sku.getlist', $params);
    }
}
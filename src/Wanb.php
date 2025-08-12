<?php

namespace Sxqibo\Logistics;

use Sxqibo\Logistics\common\Client;

class Wanb
{
    private $config;
    private $client;
    private $baseUrl;

    public function __construct(array $config)
    {
        $this->config = $config;
        // $this->baseUrl = 'http://api-sbx.wanbexpress.com/'; // 沙盒
        $this->baseUrl = 'https://api.wanbexpress.com/'; // 正式
        $this->client = new Client();
    }

    /**
     * 构建请求头
     */
    private function buildHeaders(): array
    {
        // 生成32位随机数（只包含字母、数字、短横线和下划线）
        $nonce = sprintf(
            '%s-%s-%s-%s',
            strtoupper(substr(md5(uniqid()), 0, 8)),
            strtoupper(substr(md5(microtime()), 0, 8)),
            strtoupper(substr(sha1(time()), 0, 8)),
            strtoupper(substr(sha1(rand()), 0, 8))
        );

        return [
            'Authorization' => sprintf(
                'Hc-OweDeveloper %s;%s;%s',
                trim($this->config['accountNo']),
                trim($this->config['token']),
                trim($nonce)
            ),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json; charset=utf-8'
        ];
    }

    /**
     * 使用curl发送HTTP请求
     */
    private function curlRequest(string $url, array $params = [], string $method = 'GET', array $headers = [], bool $raw = false): array
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
        curl_setopt($ch, CURLOPT_HEADER, true); // 启用响应头
        
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
        $requestHeaders = [];
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
        
        // 解析响应头，获取Content-Type
        $contentType = '';
        if (preg_match('/Content-Type:\s*([^\r\n]+)/i', $responseHeaders, $matches)) {
            $contentType = trim($matches[1]);
        }
        
        // 处理响应内容
        if ($raw) {
            return [
                'content' => $responseBody,
                'headers' => $responseHeaders,
                'httpCode' => $httpCode,
                'contentType' => $contentType
            ];
        }
        
        // 特殊处理：如果是标签接口且返回404，说明标签暂未生成
        if ($httpCode === 404) {
            return [
                'Code' => 404,
                'Message' => '标签暂未生成',
                'Data' => null
            ];
        }
    
        
        // 解析JSON响应
        $data = json_decode($responseBody, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            // 返回标准格式的错误信息，包含响应内容提示
            $responsePreview = substr($responseBody, 0, 100);
            return [
                'Code' => -1,
                'Message' => 'JSON解析错误: ' . json_last_error_msg() . ' (响应预览: ' . $responsePreview . ')',
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
        
        // 如果API返回的是万邦标准格式（Succeeded/Error），转换为标准格式
        if (isset($data['Succeeded']) || isset($data['Error'])) {
            if (isset($data['Succeeded']) && $data['Succeeded']) {
                return [
                    'Code' => 0,
                    'Message' => 'success',
                    'Data' => $data['Data'] ?? $data
                ];
            } else {
                return [
                    'Code' => isset($data['Error']['Code']) ? $data['Error']['Code'] : -1,
                    'Message' => isset($data['Error']['Message']) ? $data['Error']['Message'] : '未知错误',
                    'Data' => null
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
     * 验证API授权
     */
    public function validateAuth(): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/whoami',
            [],
            'GET',
            $this->buildHeaders()
        );
    }

    /**
     * 创建包裹
     */
    public function createParcel(array $params): array
    {
        // 首先尝试原始参数
        $result = $this->curlRequest(
            $this->baseUrl . 'api/parcels',
            $params,
            'POST',
            $this->buildHeaders()
        );
        
        // 如果失败且是"物流产品不存在"错误，尝试兼容性处理
        if (isset($result['Error']['Code']) && $result['Error']['Code'] === '0x100001') {
            // 尝试不同的产品代码
            $fallbackCodes = ['001', '002', '003', '004', '005', 'WANB', 'EXPRESS', 'STANDARD', 'WANB_USA', 'WANB_EU'];
            
            foreach ($fallbackCodes as $fallbackCode) {
                $testParams = $params;
                $testParams['ShippingMethod'] = $fallbackCode;
                
                $testResult = $this->curlRequest(
                    $this->baseUrl . 'api/parcels',
                    $testParams,
                    'POST',
                    $this->buildHeaders()
                );
                
                // 如果成功，返回结果并记录日志
                if (!isset($testResult['Error']['Code']) || $testResult['Error']['Code'] !== '0x100001') {
                    // 在返回结果中添加兼容性信息
                    if (isset($testResult['Succeeded']) && $testResult['Succeeded']) {
                        $testResult['_compatibility_note'] = "使用了兼容性产品代码: {$fallbackCode}";
                    }
                    return $testResult;
                }
            }
            
            // 如果所有代码都失败，返回原始错误，但添加兼容性提示
            if (isset($result['Succeeded']) && !$result['Succeeded'] && isset($result['Error'])) {
                return [
                    'Code' => isset($result['Error']['Code']) ? $result['Error']['Code'] : -1,
                    'Message' => isset($result['Error']['Message']) ? $result['Error']['Message'] : '未知错误',
                    'Data' => null
                ];
            }
        }
        
        // 转换万邦格式为标准格式
        if (isset($result['Succeeded']) || isset($result['Error'])) {
            if (isset($result['Succeeded']) && $result['Succeeded']) {
                return [
                    'Code' => 0,
                    'Message' => 'success',
                    'Data' => $result['Data'] ?? $result
                ];
            } else {
                return [
                    'Code' => isset($result['Error']['Code']) ? $result['Error']['Code'] : -1,
                    'Message' => isset($result['Error']['Message']) ? $result['Error']['Message'] : '未知错误',
                    'Data' => null
                ];
            }
        }
        
        return $result;
    }

    /**
     * 修改包裹预报重量
     */
    public function updateParcelWeight(string $processCode, array $params): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/parcels/' . $processCode . '/customerWeight',
            $params,
            'PUT',
            $this->buildHeaders()
        );
    }

    /**
     * 批量修改包裹预报重量
     */
    public function batchUpdateParcelWeight(array $params): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/parcels.customerWeights',
            $params,
            'PUT',
            $this->buildHeaders()
        );
    }

    /**
     * 确认交运包裹
     */
    public function confirmParcel(string $processCode): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/parcels/' . $processCode . '/confirmation',
            [],
            'POST',
            $this->buildHeaders()
        );
    }

    /**
     * 批量确认交运包裹
     */
    public function batchConfirmParcel(array $params): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/parcels/confirmation',
            $params,
            'POST',
            $this->buildHeaders()
        );
    }

    /**
     * 删除包裹
     */
    public function deleteParcel(string $processCode): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/parcels/' . $processCode,
            [],
            'DELETE',
            $this->buildHeaders()
        );
    }

    /**
     * 获取包裹
     */
    public function getParcel(string $processCode): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/parcels/' . $processCode,
            [],
            'GET',
            $this->buildHeaders()
        );
    }

    /**
     * 搜索包裹
     */
    public function searchParcels(array $params): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/parcels/search',
            $params,
            'POST',
            $this->buildHeaders()
        );
    }

    /**
     * 获取包裹标签
     */
    public function getParcelLabel(string $processCode): array
    {
        try {
            // 使用 raw=true 来获取原始响应，包括响应头
            $result = $this->curlRequest(
                $this->baseUrl . 'api/parcels/' . $processCode . '/label',
                [],
                'GET',
                $this->buildHeaders(),
                true // 获取原始响应
            );
            
            // 兼容性处理：确保返回有效的数组格式
            if ($result === null || !is_array($result)) {
                return [
                    'Code' => -1,
                    'Message' => '获取包裹标签失败：响应格式错误',
                    'Data' => null
                ];
            }
            
            // 如果返回的是PDF内容，保存到文件并返回文件路径
            if (isset($result['content']) && strpos($result['content'], '%PDF-') === 0) {
                // 创建标签目录
                $labelDir = dirname(__DIR__) . '/labels';
                if (!is_dir($labelDir)) {
                    mkdir($labelDir, 0755, true);
                }
                
                // 生成文件名
                $fileName = 'label_' . $processCode . '_' . date('YmdHis') . '.pdf';
                $filePath = $labelDir . '/' . $fileName;
                
                // 保存PDF文件
                if (file_put_contents($filePath, $result['content'])) {
                    return [
                        'Code' => 0,
                        'Message' => 'success',
                        'Data' => [
                            'ProcessCode' => $processCode,
                            'LabelUrl' => $this->baseUrl . 'api/parcels/' . $processCode . '/label',
                            'LabelFormat' => 'PDF',
                            'CreatedTime' => date('Y-m-d H:i:s'),
                            'LocalFilePath' => $filePath,
                            'FileName' => $fileName
                        ]
                    ];
                } else {
                    return [
                        'Code' => -1,
                        'Message' => '保存PDF文件失败',
                        'Data' => null
                    ];
                }
            }
            
            return $result;
        } catch (\Exception $e) {
            // 兼容性处理：捕获异常并返回有效格式
            return [
                'Code' => -1,
                'Message' => '获取包裹标签异常：' . $e->getMessage(),
                'Data' => null
            ];
        }
    }

    /**
     * 批量获取包裹标签
     */
    public function batchGetParcelLabel(array $params): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/parcels/label',
            $params,
            'POST',
            $this->buildHeaders()
        );
    }

    /**
     * 取消发货/截件
     */
    public function cancelParcel(string $processCode): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/parcels/' . $processCode . '/cancellation',
            [],
            'POST',
            $this->buildHeaders()
        );
    }

    /**
     * 取消截件/继续发货
     */
    public function resumeParcel(string $processCode): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/parcels/' . $processCode . '/resumption',
            [],
            'POST',
            $this->buildHeaders()
        );
    }

    /**
     * 上传尾程派送单号与面单
     */
    public function uploadLastMileInfo(string $processCode, array $params): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/parcels/' . $processCode . '/lastmile',
            $params,
            'POST',
            $this->buildHeaders()
        );
    }

    /**
     * 获取产品服务
     */
    public function getProducts(): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/services',
            [],
            'GET',
            $this->buildHeaders()
        );
    }

    /**
     * 获取仓库
     */
    public function getWarehouses(): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/warehouses',
            [],
            'GET',
            $this->buildHeaders()
        );
    }

    /**
     * 查询轨迹
     */
    public function getTracking(string $processCode): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/parcels/' . $processCode . '/tracking',
            [],
            'GET',
            $this->buildHeaders()
        );
    }

    /**
     * 下载POD
     */
    public function downloadPOD(string $processCode): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/parcels/' . $processCode . '/pod',
            [],
            'GET',
            $this->buildHeaders(),
            true // 返回原始内容
        );
    }

    /**
     * 下载投递照片
     */
    public function downloadDeliveryPhotos(string $processCode): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/parcels/' . $processCode . '/delivery-photos',
            [],
            'GET',
            $this->buildHeaders(),
            true // 返回原始内容
        );
    }

    /**
     * 创建来货/揽收袋
     */
    public function createBag(array $params): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/bags',
            $params,
            'POST',
            $this->buildHeaders()
        );
    }

    /**
     * 修改来货/揽收袋信息
     */
    public function updateBag(string $bagCode, array $params): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/bags/' . $bagCode,
            $params,
            'PUT',
            $this->buildHeaders()
        );
    }

    /**
     * 修改来货/揽收袋内包裹明细
     */
    public function updateBagParcels(string $bagCode, array $params): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/bags/' . $bagCode . '/parcels',
            $params,
            'PUT',
            $this->buildHeaders()
        );
    }

    /**
     * 确认交运来货/揽收袋
     */
    public function confirmBag(string $bagCode): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/bags/' . $bagCode . '/confirmation',
            [],
            'POST',
            $this->buildHeaders()
        );
    }

    /**
     * 删除来货/揽收袋
     */
    public function deleteBag(string $bagCode): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/bags/' . $bagCode,
            [],
            'DELETE',
            $this->buildHeaders()
        );
    }

    /**
     * 获取来货/揽收袋
     */
    public function getBag(string $bagCode): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/bags/' . $bagCode,
            [],
            'GET',
            $this->buildHeaders()
        );
    }

    /**
     * 搜索来货/揽收袋
     */
    public function searchBags(array $params): array
    {
        return $this->curlRequest(
            $this->baseUrl . 'api/bags/search',
            $params,
            'POST',
            $this->buildHeaders()
        );
    }
} 
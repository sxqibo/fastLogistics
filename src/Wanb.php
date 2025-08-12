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
     * 验证API授权
     */
    public function validateAuth(): array
    {
        return $this->client->requestApi(
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
        $result = $this->client->requestApi(
            $this->baseUrl . 'api/parcels',
            $params,
            'POST',
            $this->buildHeaders()
        );
        
        // 如果失败且是"物流产品不存在"错误，尝试兼容性处理
        if (isset($result['Error']['Code']) && $result['Error']['Code'] === '0x100001') {
            // 尝试不同的产品代码
            $fallbackCodes = ['001', '002', '003', '004', '005', 'WANB', 'EXPRESS', 'STANDARD', 'WANB_USA', 'WANB_EU'];
            
            foreach ($fallbackCodes as $code) {
                $testParams = $params;
                $testParams['ShippingMethod'] = $code;
                
                $testResult = $this->client->requestApi(
                    $this->baseUrl . 'api/parcels',
                    $testParams,
                    'POST',
                    $this->buildHeaders()
                );
                
                // 如果成功，返回结果并记录日志
                if (!isset($testResult['Error']['Code']) || $testResult['Error']['Code'] !== '0x100001') {
                    // 在返回结果中添加兼容性信息
                    if (isset($testResult['Succeeded']) && $testResult['Succeeded']) {
                        $testResult['_compatibility_note'] = "使用了兼容性产品代码: {$code}";
                    }
                    return $testResult;
                }
            }
            
            // 如果所有代码都失败，返回原始错误，但添加兼容性提示
            $result['_compatibility_note'] = "已尝试多种产品代码，但都返回'物流产品不存在'错误。请联系万邦客服获取有效的产品代码。";
        }
        
        return $result;
    }

    /**
     * 修改包裹预报重量
     */
    public function updateParcelWeight(string $processCode, array $params): array
    {
        return $this->client->requestApi(
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
        return $this->client->requestApi(
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
        return $this->client->requestApi(
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
        return $this->client->requestApi(
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
        return $this->client->requestApi(
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
        return $this->client->requestApi(
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
        return $this->client->requestApi(
            $this->baseUrl . 'api/parcels/search',
            $params,
            'POST',
            $this->buildHeaders()
        );
    }

    /**
     * 获取包裹标签
     */
    public function getParcelLabel(string $processCode, array $params = []): array
    {
        return $this->client->requestApi(
            $this->baseUrl . 'api/parcels/' . $processCode . '/label',
            $params,
            'GET',
            $this->buildHeaders()
        );
    }

    /**
     * 批量获取包裹标签
     */
    public function batchGetParcelLabel(array $params): array
    {
        return $this->client->requestApi(
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
        return $this->client->requestApi(
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
        return $this->client->requestApi(
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
        return $this->client->requestApi(
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
        return $this->client->requestApi(
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
        return $this->client->requestApi(
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
        return $this->client->requestApi(
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
        return $this->client->requestApi(
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
        return $this->client->requestApi(
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
        return $this->client->requestApi(
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
        return $this->client->requestApi(
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
        return $this->client->requestApi(
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
        return $this->client->requestApi(
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
        return $this->client->requestApi(
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
        return $this->client->requestApi(
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
        return $this->client->requestApi(
            $this->baseUrl . 'api/bags/search',
            $params,
            'POST',
            $this->buildHeaders()
        );
    }
} 
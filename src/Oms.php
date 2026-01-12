<?php

namespace Sxqibo\Logistics;

use Sxqibo\Logistics\common\Client;
use Exception;

/**
 * OMS API 客户端类
 * 基于 OMS 开放平台API
 */
class Oms
{
    private $appKey;
    private $appSecret;
    private $client;
    private $baseUrl = 'https://api.xlwms.com/openapi/v1';

    /**
     * OMS构造函数
     *
     * @param string $appKey     应用接入申请的AppKey
     * @param string $appSecret  App Secret
     */
    public function __construct($appKey, $appSecret)
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
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 生成签名
     * 按照OMS官方文档的签名规则
     *
     * @param array $data 业务数据
     * @param string $reqTime 请求时间戳（十位）
     * @param bool $debug 是否输出调试信息
     * @return string authcode（16进制字符串）
     */
    private function generateSign(array $data, string $reqTime, bool $debug = false): string
    {
        // 步骤 1：对 data 中的所有业务数据，按照字典顺序（不区分字母大小写）进行升序排列
        $sortedData = $this->sortArrayRecursive($data);
        $dataJson = json_encode($sortedData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($debug) {
            echo "步骤1 - 排序后的data: {$dataJson}\n";
        }

        // 步骤 2：把 appKey、排序后的 data、reqTime 按字典顺序排列并拼接
        $params = [
            'appKey' => $this->appKey,
            'data' => $dataJson,
            'reqTime' => $reqTime
        ];

        // 按字典顺序排序（不区分大小写）
        uksort($params, function($a, $b) {
            return strcasecmp($a, $b);
        });

        // 拼接字符串（只拼接值，不拼接键）
        $signString = '';
        foreach ($params as $key => $value) {
            $signString .= $value;
        }

        if ($debug) {
            echo "步骤2 - 拼接字符串: {$signString}\n";
        }

        // 步骤 3：使用 HmacSHA256 加密，密钥是 appSecret，结果转为16进制
        $authcode = hash_hmac('sha256', $signString, $this->appSecret);

        if ($debug) {
            echo "步骤3 - 生成的authcode: {$authcode}\n";
        }

        return $authcode;
    }

    /**
     * 递归排序数组（按字典顺序，不区分大小写）
     *
     * @param array $array 要排序的数组
     * @return array 排序后的数组
     */
    private function sortArrayRecursive(array $array): array
    {
        // 先对键进行排序（不区分大小写）
        uksort($array, function($a, $b) {
            return strcasecmp($a, $b);
        });

        // 递归处理值
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->sortArrayRecursive($value);
            }
        }

        return $array;
    }

    /**
     * 发送API请求
     *
     * @param string $endpoint API端点（不包含基础URL）
     * @param array $data 业务数据
     * @param bool $debug 是否输出调试信息
     * @return array 返回响应数据
     * @throws Exception
     */
    public function request(string $endpoint, array $data = [], bool $debug = false): array
    {
        // 生成请求时间戳（十位）
        $reqTime = (string)time();

        // 生成签名
        $authcode = $this->generateSign($data, $reqTime, $debug);

        // 构建完整URL（authcode通过GET方式传递）
        // 根据文档示例，authcode直接作为URL参数值，但服务器可能需要参数名
        // 尝试两种方式：1. 直接值 2. authcode=值
        $url = $this->baseUrl . $endpoint . '?authcode=' . $authcode;

        // 构建POST请求体
        $postData = [
            'appKey' => $this->appKey,
            'data' => $data,
            'reqTime' => $reqTime
        ];

        if ($debug) {
            echo "\n=== 调试信息 ===\n";
            echo "请求URL: {$url}\n";
            echo "POST数据: " . json_encode($postData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
            echo "生成的authcode: {$authcode}\n\n";
        }

        try {
            $response = $this->client->requestApi($url, $postData, 'POST');
            return $response;
        } catch (Exception $e) {
            throw new Exception("OMS API请求失败: " . $e->getMessage());
        }
    }

    /**
     * 分页查询库龄
     *
     * @param array $params 查询参数
     *   - whCodeList: array 仓库编码列表
     *   - stockType: int 库存类型属性：0-正品，1-次品
     *   - stockSku: string 商品sku，支持模糊搜索
     *   - stockItemType: int 库存颗粒度类型：0-产品库存；1-箱库存(暂不支持)；2-退货库存
     *   - timeType: string 统计时间类型：shelfDate-上架日期；statisticDate-库存统计日期（默认）
     *   - startTime: string 起始时间（yyyy-MM-dd）
     *   - endTime: string 结束时间（yyyy-MM-dd）
     *   - page: int 页码，默认第一页
     *   - pageSize: int 每页条数，默认50条
     * @param bool $debug 是否输出调试信息
     * @return array 返回响应数据
     * @throws Exception
     */
    public function pageStockAge(array $params = [], bool $debug = false): array
    {
        // 设置默认值
        $defaultParams = [
            'stockItemType' => 0,  // 必填：0-产品库存
            'timeType' => 'statisticDate',  // 默认：库存统计日期
            'page' => 1,
            'pageSize' => 50
        ];

        // 合并参数
        $data = array_merge($defaultParams, $params);

        // 清理空值（可选参数）
        $data = array_filter($data, function($value) {
            return $value !== '' && $value !== null;
        });

        return $this->request('/integratedInventory/pageStockAge', $data, $debug);
    }
}

<?php

namespace Sxqibo\Logistics\common;

use Exception;
use GuzzleHttp\Client as HttpClient;

class Client
{
    private $client;
    private $timeout = 30;

    public function __construct()
    {
        $this->client = new HttpClient();
    }

    /**
     * 请求API
     */
    public function requestApi(string $url, array $params = [], string $method = 'GET', array $headers = [], bool $raw = false): array
    {
        try {
            $options = [
                'headers'  => array_merge([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Host' => parse_url($url, PHP_URL_HOST)
                ], $headers),
                'timeout'  => $this->timeout,
                'verify'   => false  // 忽略SSL证书验证
            ];

            // 如果是GET请求且有参数，将参数添加到URL
            if ($method === 'GET' && !empty($params)) {
                $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
            } else if (in_array($method, ['POST', 'PUT']) && !empty($params)) {
                // POST/PUT请求，将参数放在body中
                // 检查Content-Type，如果是form-urlencoded则使用form_params，否则使用json
                $contentType = $options['headers']['Content-Type'] ?? '';
                if (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
                    $options['form_params'] = $params;
                } else {
                    $options['json'] = $params;
                }
            }

            $response = $this->client->request($method, $url, $options);
            $contents = $response->getBody()->getContents();

            // 处理字符编码
            $contents = $this->fixResponseEncoding($contents);

            // 如果是XML响应，转换为数组
            if (strpos($contents, '<?xml') !== false) {
                $xml = simplexml_load_string($contents);
                return $raw ? ['content' => $contents] : json_decode(json_encode($xml), true);
            }

            return $raw ? ['content' => $contents] : json_decode($contents, true);
        } catch (Exception $e) {
            $message = sprintf(
                '请求API失败，API:%s，参数:%s，错误信息:[%s]',
                $url,
                json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                $e->getMessage()
            );
            throw new Exception($message);
        }
    }

    /**
     * 修复响应内容的字符编码
     *
     * @param string $content 响应内容
     * @return string
     */
    private function fixResponseEncoding($content)
    {
        // 检测编码
        $encoding = mb_detect_encoding($content, ['UTF-8', 'GBK', 'GB2312', 'ISO-8859-1'], true);
        
        // 如果不是UTF-8，尝试转换
        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }
        
        return $content;
    }
}

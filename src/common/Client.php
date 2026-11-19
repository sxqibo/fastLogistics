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
     * 请求 API
     *
     * 兼容两种调用：
     * 1) 新签名：requestApi(string $url, array $params = [], string $method = 'GET', array $headers = [], bool $raw = false)
     * 2) 旧签名：requestApi(array $endPoint, array $params = [], string|array $body = [], array $headers = [], bool $raw = false)
     *    其中 $endPoint = ['url' => '...', 'method' => 'GET|POST', ...]
     *
     * @param string|array $urlOrEndpoint
     * @param array $params
     * @param string|array $methodOrBody
     * @param array $headers
     * @param bool $raw
     * @return array
     * @throws Exception
     */
    public function requestApi($urlOrEndpoint, array $params = [], $methodOrBody = 'GET', array $headers = [], bool $raw = false): array
    {
        $url = '';
        $method = 'GET';
        $rawBody = null;
        $jsonBody = null;

        if (is_array($urlOrEndpoint)) {
            $url = (string)($urlOrEndpoint['url'] ?? '');
            $method = strtoupper((string)($urlOrEndpoint['method'] ?? 'GET'));
            // 旧版第三参：XML 字符串 / 空数组 / 业务数组(作 POST body)
            if (is_string($methodOrBody)) {
                if (preg_match('/^(GET|POST|PUT|DELETE|PATCH)$/i', $methodOrBody)) {
                    $method = strtoupper($methodOrBody);
                } else {
                    $rawBody = $methodOrBody;
                    if ($method === 'GET' && $rawBody !== '') {
                        $method = 'POST';
                    }
                }
            } elseif (is_array($methodOrBody)) {
                if ($methodOrBody !== []) {
                    $jsonBody = $methodOrBody;
                    if ($method === 'GET') {
                        $method = 'POST';
                    }
                }
            }
        } else {
            $url = (string)$urlOrEndpoint;
            if (is_string($methodOrBody) && preg_match('/^(GET|POST|PUT|DELETE|PATCH)$/i', $methodOrBody)) {
                $method = strtoupper($methodOrBody);
            } elseif (is_array($methodOrBody)) {
                // 兼容：第三参直接传 body 数组
                $jsonBody = $methodOrBody;
                $method = 'POST';
            } elseif (is_string($methodOrBody) && $methodOrBody !== '') {
                $rawBody = $methodOrBody;
                $method = 'POST';
            }
        }

        if ($url === '') {
            throw new Exception('请求API失败：URL 为空');
        }

        // 部分旧代码只改了 uri 未同步 url
        if (is_array($urlOrEndpoint) && !empty($urlOrEndpoint['uri']) && isset($urlOrEndpoint['url'])) {
            // url 已由调用方维护时不强制覆盖；此处保持传入 url
        }

        try {
            $defaultHeaders = [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json; charset=utf-8',
            ];
            $host = parse_url($url, PHP_URL_HOST);
            if (is_string($host) && $host !== '') {
                $defaultHeaders['Host'] = $host;
            }

            $options = [
                'headers' => array_merge($defaultHeaders, $headers),
                'timeout' => $this->timeout,
                'verify'  => false,
            ];

            if ($method === 'GET' && !empty($params)) {
                $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
            } elseif (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
                if ($rawBody !== null && $rawBody !== '') {
                    $options['body'] = $rawBody;
                } elseif ($jsonBody !== null) {
                    $contentType = (string)($options['headers']['Content-Type'] ?? '');
                    if (stripos($contentType, 'application/x-www-form-urlencoded') !== false) {
                        $options['form_params'] = $jsonBody;
                    } elseif (stripos($contentType, 'xml') !== false) {
                        // XML Content-Type 但传入数组时，仍按 JSON 发出（调用方应自行转 XML 字符串）
                        $options['json'] = $jsonBody;
                    } else {
                        $options['json'] = $jsonBody;
                    }
                } elseif (!empty($params)) {
                    $contentType = (string)($options['headers']['Content-Type'] ?? '');
                    if (stripos($contentType, 'application/x-www-form-urlencoded') !== false) {
                        $options['form_params'] = $params;
                    } else {
                        $options['json'] = $params;
                    }
                }
            }

            $response = $this->client->request($method, $url, $options);
            $contents = $response->getBody()->getContents();
            $contents = $this->fixResponseEncoding($contents);

            if ($contents !== '' && (strpos($contents, '<?xml') !== false || (isset($contents[0]) && $contents[0] === '<'))) {
                $xml = simplexml_load_string($contents);
                if ($xml === false) {
                    throw new Exception('XML 解析失败');
                }
                return $raw ? ['content' => $contents] : json_decode(json_encode($xml), true);
            }

            if ($raw) {
                return ['content' => $contents];
            }

            $decoded = json_decode($contents, true);
            return is_array($decoded) ? $decoded : ['content' => $contents];
        } catch (Exception $e) {
            $message = sprintf(
                '请求API失败，API:%s，参数:%s，错误信息:[%s]',
                $url,
                json_encode($options ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
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
        $encoding = mb_detect_encoding($content, ['UTF-8', 'GBK', 'GB2312', 'ISO-8859-1'], true);

        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        return $content;
    }
}

<?php

namespace Sxqibo\Logistics\common;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Exception;

class Client
{
    private $timeout = 30;
    private $baseUri = '';
    public static $clientInstance;

    public function __construct()
    {
        $this->baseUri = '';
    }

    /**
     * 请求API接口
     *
     * @param string $url 请求地址
     * @param array $params 请求参数
     * @param array|null $body 请求体
     * @param array $headers 请求头
     * @param bool $raw 是否返回原始响应
     * @return array|mixed
     * @throws Exception
     */
    public function requestApi($url, $params = [], $body = null, $headers = [], $raw = false)
    {
        try {
            // 设置默认请求头
            $defaultHeaders = [
                'Content-Type' => 'application/json'
            ];
            $headers = array_merge($defaultHeaders, $headers);
            
            $options = [
                'headers' => $headers,
                'timeout' => $this->timeout,
            ];

            // 如果是 GET 请求，使用 query 参数
            if (empty($body)) {
                $options['json'] = $params;
            } else {
                $options['json'] = $body;
                $options['query'] = $params;
            }

            $client   = $this->getClient();
            $response = $client->request('POST', $url, $options);

            $body = $response->getBody();
            $content = $body->getContents();
            
            if ($raw) {
                return $content;
            }
            
            return json_decode($content, true);
            
        } catch (GuzzleException $e) {
            $paramString = json_encode($options, JSON_UNESCAPED_UNICODE);
            $errorMsg    = "请求API失败，API:{$url}，参数:{$paramString}，错误信息:[{$e->getMessage()}]";

            throw new Exception($errorMsg);
        }
    }

    /**
     * Convert an xml string to an array
     * @param string $xmlstring
     * @return array
     */
    protected function xmlToArray($xmlstring)
    {
        return json_decode(json_encode(simplexml_load_string($xmlstring)), true);
    }

    /**
     * @return mixed
     */
    protected function getClient()
    {
        if (!isset(static::$clientInstance)) {
            $handlerStack = HandlerStack::create(new CurlHandler());
            $handlerStack->push(Middleware::retry($this->retryDecider(), $this->retryDelay()));

            static::$clientInstance = new \GuzzleHttp\Client(['base_uri' => $this->baseUri, 'handler' => $handlerStack]);
        }

        return static::$clientInstance;
    }

    /**
     * 返回一个匿名函数，该匿名函数返回下次重试的时间（毫秒）
     * @return mixed
     */
    private function retryDelay()
    {
        return function ($numberOfRetries) {
            return 1000 * $numberOfRetries;
        };
    }

    /**
     * retryDecider
     * 返回一个匿名函数, 匿名函数若返回false 表示不重试，反之则表示继续重试
     * @return mixed
     */
    private function retryDecider()
    {
        return function (
            $retries,
            \GuzzleHttp\Psr7\Request $request,
            \GuzzleHttp\Psr7\Response $response = null,
            \GuzzleHttp\Exception\RequestException $exception = null
        ) {
            // Limit the number of retries to 3
            if ($retries >= 3) {
                return false;
            }

            // Retry connection exceptions
            if ($exception instanceof ConnectException) {
                return true;
            }

            if ($response) {
                // Retry on server errors
                if ($response->getStatusCode() >= 500) {
                    return true;
                }
            }

            return false;
        };
    }
}

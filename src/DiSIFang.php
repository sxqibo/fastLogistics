<?php


namespace Sxqibo\Logistics;

use Exception;
use Sxqibo\Logistics\common\Client;

/**
 * 递四方物流类 4PX
 * Class DiSIFang
 * @package Sxqibo\Logistics
 */
class DiSIFang
{
    private $serviceEndPoint     = 'http://open.4px.com/router/api/service'; // 正式环境
    private $testServiceEndPoint = 'http://open.sandbox.4px.com/router/api/service'; // 沙箱环境

    private $appKey;
    private $appSecret;
    private $client;

    public function __construct($appKey, $appSecret)
    {
        $this->appKey    = $appKey;
        $this->appSecret = $appSecret;

        $this->client = new Client();
    }

    /**
     * 获取请求节点信息
     *
     * @param $key
     * @return string
     * @throws Exception
     */
    protected function getEndPoint($key)
    {
        $endpoints = [
            'getAuthCode' => [
                'method' => 'GET',
                'uri'    => '/Users/' . $this->userId . '/GetChannels',
                'remark' => '获取发货渠道'
            ],
        ];

        if (isset($endpoints[$key])) {
            return $endpoints[$key]['serviceMethod'];
        } else {
            throw new Exception('未找到对应的接口信息 ' . $key);
        }
    }

    /**
     * 取得授权码authorization_code
     *
     * @return array|mixed
     * @throws Exception
     */
    public function getAuthCode()
    {
        $params   = [
            'client_id'     => $this->appKey,
            'response_type' => 'code',
            'redirect_uri'  => ''
        ];
        $endPoint = [
            'url'    => 'https://open.4px.com/authorize/get',
            'method' => 'GET'
        ];

        $result = $this->client->requestApi($endPoint, $params, []);

        return $result;
    }

    /**
     * 根据authorization_code获取access_token
     *
     * @param $authCode string 通过调用authorization_code接口返回的code值,一次有效
     * @return array|mixed
     * @throws Exception
     */
    public function geAccessToken($authCode)
    {
        $body = [
            'client_id'     => $this->appKey, // 注册App由系统生成的app_key
            'client_secret' => $this->appSecret, // 注册App由系统生成的app_secret
            'grant_type'    => 'authorization_code', // 默认值:authorization_code
            'code'          => $authCode, // 通过调用authorization_code接口返回的code值,一次有效
            'redirect_uri'  => '' // 可填写应用注册时回调地址域名。redirect_uri指的是应用发起请求时，所传的回调地址参数，
            //在用户授权后应用会跳转至redirect_uri。要求与应用注册时填写的回调地址域名一致或顶级域名一致
        ];

        $endPoint = [
            'url'    => 'https://open.4px.com/accessToken/get',
            'method' => 'POST'
        ];

        $result = $this->client->requestApi($endPoint, [], $body, ['Content-Type' => 'application/x-www-form-urlencoded'], true);

        return $result;
    }

    /**
     * 根据refresh_token换取access_token
     * 备注：
     * a)如果refreshToken有效并且accessToken已经过期，那么可以使用refresh_token换取access_token，不用重新进行授权，然后访问用户隐私数据。
     * refresh_token过期则需要重新走获取Oauth2.0令牌流程重新授权。
     *
     * @param $refreshToken
     * @return array|mixed
     * @throws Exception
     */
    public function getAccessTokenByRefreshToken($refreshToken)
    {
        $body = [
            'client_id'     => $this->appKey,
            'client_secret' => $this->appSecret,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
            'redirect_uri'  => 'https://www.baidu.com'
        ];

        $endPoint = [
            'url'    => 'https://open.4px.com/accessToken/get',
            'method' => 'POST'
        ];

        $result = $this->client->requestApi($endPoint, [], $body, ['Content-Type' => 'application/x-www-form-urlencoded'], true);

        return $result;
    }

    /**
     * 获取签名
     *
     * @param string $body
     * @param string $apiMethod
     * @return string
     */
    public function getSign($body = '', $apiMethod = 'default')
    {
        $params = [
            'app_key'   => $this->appKey,
            'format'    => 'json',
            'method'    => $apiMethod,
            'timestamp' => microtime(true) * 1000, // 毫秒时间戳
            'v'         => '1.0',
        ];

        $str = '';
        foreach ($params as $param) {
            $str .= $param;
        }

        if (is_array($body)) {
            $body = json_encode($body);
        }

        $sign = md5($str . $body . $this->appSecret);

        return $sign;
    }

}

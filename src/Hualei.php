<?php

namespace Sxqibo\Logistics;

use Exception;

/**
 * 华磊物流 API
 *
 * 授权：selectAuth.htm（username/password → customer_id / customer_userid）
 * 渠道：getProductList.htm
 * 下单：createOrderApi.htm
 * 标签：FastRpt/PDF_NEW.aspx
 *
 * 文档：http://doc.sz56t.com:8090/
 */
class Hualei
{
    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var string 下单基址，如 http://175.178.75.178:8082 */
    private $apiBase;

    /** @var string 打印基址，如 http://175.178.75.178:8089 */
    private $printBase;

    /** @var string */
    private $labelFormat;

    /** @var int */
    private $printType;

    /** @var array{customer_id:string,customer_userid:string}|null */
    private $authCache = null;

    /**
     * @param array{
     *   username?:string,
     *   password?:string,
     *   apiBase?:string,
     *   printBase?:string,
     *   labelFormat?:string,
     *   printType?:int
     * } $config
     * @throws Exception
     */
    public function __construct(array $config = [])
    {
        $this->username = trim((string)($config['username'] ?? ''));
        $this->password = trim((string)($config['password'] ?? ''));
        if ($this->username === '' || $this->password === '') {
            throw new Exception('华磊 username/password 不能为空');
        }

        $apiBase = trim((string)($config['apiBase'] ?? 'http://175.178.75.178:8082'));
        $this->apiBase = rtrim($apiBase !== '' ? $apiBase : 'http://175.178.75.178:8082', '/');

        $printBase = trim((string)($config['printBase'] ?? ''));
        if ($printBase === '') {
            $printBase = preg_replace('/:8082\b/', ':8089', $this->apiBase) ?: 'http://175.178.75.178:8089';
        }
        $this->printBase = rtrim($printBase, '/');

        $this->labelFormat = trim((string)($config['labelFormat'] ?? ''));
        $this->printType = max(1, (int)($config['printType'] ?? 1));
    }

    /**
     * 身份认证
     *
     * @param bool $force
     * @return array{customer_id:string,customer_userid:string,ack:bool,raw:array}
     * @throws Exception
     */
    public function selectAuth($force = false)
    {
        if (!$force && $this->authCache !== null) {
            return [
                'customer_id'     => $this->authCache['customer_id'],
                'customer_userid' => $this->authCache['customer_userid'],
                'ack'             => true,
                'raw'             => $this->authCache,
            ];
        }

        $raw = $this->postForm($this->apiBase . '/selectAuth.htm', http_build_query([
            'username' => $this->username,
            'password' => $this->password,
        ]));
        $data = $this->decodeLooseJson($raw);
        if (!is_array($data)) {
            throw new Exception('华磊授权返回异常');
        }

        $ack = strtolower((string)($data['ack'] ?? $data['Ack'] ?? ''));
        $customerId = trim((string)($data['customer_id'] ?? $data['customerId'] ?? ''));
        $customerUserId = trim((string)($data['customer_userid'] ?? $data['customerUserid'] ?? ''));
        if (($ack !== '' && !in_array($ack, ['true', '1', 'success', 'ok'], true)) || $customerId === '' || $customerUserId === '') {
            $msg = trim((string)($data['message'] ?? $data['msg'] ?? $data['Message'] ?? ''));
            if ($msg !== '') {
                $msg = urldecode($msg);
            }
            throw new Exception('华磊授权失败' . ($msg !== '' ? ('：' . $msg) : ''));
        }

        $this->authCache = [
            'customer_id'     => $customerId,
            'customer_userid' => $customerUserId,
        ];

        return [
            'customer_id'     => $customerId,
            'customer_userid' => $customerUserId,
            'ack'             => true,
            'raw'             => $data,
        ];
    }

    /**
     * 渠道/产品列表
     *
     * @return array{code:int,message:string,data:list<array{product_id:string,product_shortname:string}>}
     * @throws Exception
     */
    public function getProductList()
    {
        $auth = $this->selectAuth();
        $candidates = [
            http_build_query([
                'customer_id'     => $auth['customer_id'],
                'customer_userid' => $auth['customer_userid'],
            ]),
            '',
        ];

        $list = [];
        $lastError = '';
        foreach ($candidates as $body) {
            try {
                $raw = $this->postForm($this->apiBase . '/getProductList.htm', $body);
                $decoded = $this->decodeLooseJson($raw);
                $list = $this->normalizeProducts($decoded);
                if ($list !== []) {
                    break;
                }
            } catch (Exception $e) {
                $lastError = $e->getMessage();
            }
        }

        if ($list === [] && $lastError !== '') {
            throw new Exception($lastError);
        }

        return [
            'code'    => 0,
            'message' => '成功',
            'data'    => $list,
        ];
    }

    /**
     * 兼容其它物流类命名：获取运输方式/渠道
     *
     * @return array
     * @throws Exception
     */
    public function getShipTypes()
    {
        return $this->getProductList();
    }

    /**
     * 创建订单
     *
     * @param array $order 官方 createOrderApi 字段（可不传 customer_id/customer_userid）
     * @return array
     * @throws Exception
     */
    public function createOrder(array $order)
    {
        $auth = $this->selectAuth();
        $payload = $order;
        $payload['customer_id'] = $auth['customer_id'];
        $payload['customer_userid'] = $auth['customer_userid'];
        if (!isset($payload['trade_type']) || trim((string)$payload['trade_type']) === '') {
            $payload['trade_type'] = 'ZYXT';
        }

        $body = 'param=' . rawurlencode(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}');
        $raw = $this->postForm($this->apiBase . '/createOrderApi.htm', $body);
        $data = $this->decodeLooseJson($raw);
        if (!is_array($data)) {
            throw new Exception('华磊下单返回异常');
        }

        $ack = strtolower((string)($data['ack'] ?? $data['Ack'] ?? ''));
        $message = trim((string)($data['message'] ?? $data['Message'] ?? $data['msg'] ?? ''));
        if ($message !== '') {
            $message = urldecode($message);
            $data['message'] = $message;
        }
        if ($ack !== '' && !in_array($ack, ['true', '1', 'success', 'ok'], true)) {
            throw new Exception('华磊下单失败' . ($message !== '' ? ('：' . $message) : ''));
        }

        return [
            'code'    => 0,
            'message' => $message !== '' ? $message : '成功',
            'data'    => $data,
        ];
    }

    /**
     * 拼标签 PDF 地址
     *
     * @param string $orderId 华磊内部 order_id
     * @return string
     * @throws Exception
     */
    public function buildLabelUrl($orderId)
    {
        $orderId = trim((string)$orderId);
        if ($orderId === '') {
            throw new Exception('华磊打印缺少 order_id');
        }
        $query = [
            'PrintType' => $this->printType,
            'order_id'  => $orderId,
        ];
        if ($this->labelFormat !== '') {
            $query['Format'] = $this->labelFormat;
        }

        return $this->printBase . '/order/FastRpt/PDF_NEW.aspx?' . http_build_query($query);
    }

    /**
     * @param mixed $decoded
     * @return list<array{product_id:string,product_shortname:string}>
     */
    private function normalizeProducts($decoded)
    {
        if (!is_array($decoded)) {
            return [];
        }

        $rows = $decoded;
        if (!$this->isList($decoded)) {
            $rows = $decoded['data']
                ?? $decoded['Data']
                ?? $decoded['productList']
                ?? $decoded['list']
                ?? [];
            if (!is_array($rows)) {
                return [];
            }
        }

        $list = [];
        $seen = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $productId = trim((string)($row['product_id'] ?? $row['productId'] ?? $row['id'] ?? ''));
            $name = trim((string)($row['product_shortname'] ?? $row['product_name'] ?? $row['productName'] ?? $row['name'] ?? ''));
            if ($productId === '') {
                continue;
            }
            if (isset($seen[$productId])) {
                continue;
            }
            $seen[$productId] = true;
            if ($name === '') {
                $name = $productId;
            }
            $list[] = [
                'product_id'        => $productId,
                'product_shortname' => $name,
            ];
        }

        return $list;
    }

    /**
     * @param string $url
     * @param string $body
     * @return string
     * @throws Exception
     */
    private function postForm($url, $body)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept-Language: zh-cn',
            'Connection: Keep-Alive',
            'Cache-Control: no-cache',
            'Content-type: application/x-www-form-urlencoded;charset=UTF-8',
        ]);
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno) {
            throw new Exception('华磊请求失败：' . ($error !== '' ? $error : ('errno ' . $errno)));
        }
        if ($httpCode >= 400) {
            throw new Exception('华磊返回异常 HTTP ' . $httpCode);
        }

        return (string)$response;
    }

    /**
     * 华磊常返回单引号 JSON
     *
     * @param string $raw
     * @return mixed
     */
    private function decodeLooseJson($raw)
    {
        $raw = trim((string)$raw);
        if ($raw === '') {
            return null;
        }
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        $fixed = str_replace("'", '"', $raw);
        $decoded = json_decode($fixed, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param array $arr
     * @return bool
     */
    private function isList(array $arr)
    {
        if ($arr === []) {
            return true;
        }

        return array_keys($arr) === range(0, count($arr) - 1);
    }
}

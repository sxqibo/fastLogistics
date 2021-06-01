<?php


namespace Sxqibo\Logistics;

use Exception;
use Sxqibo\Logistics\common\Client;
use Sxqibo\Logistics\common\Utility;

/**
 * 燕文物流类
 * Class Yanwen
 * @package Sxqibo\Logistics
 */
class Yanwen
{
    private $serviceEndPoint     = 'HTTP://ONLINE.YW56.COM.CN/SERVICE';
    private $testServiceEndPoint = 'HTTP://47.96.220.163:802/SERVICE';
    private $userId              = '100000'; // $userId 客户号。正式环境：贵司在我司客户号；测试环境为100000。
    private $aipToken;
    private $headers;
    private $client;

    public function __construct($aipToken, $userId)
    {
        $this->aipToken = $aipToken;
        $this->userId   = $userId;
        $this->headers  = [
            'Authorization' => 'basic ' . $aipToken,
            // 'Accept'        => 'application/xml',
            'Content-Type'  => 'text/xml; charset=UTF-8'
        ];

        $this->client = new Client();
    }

    /**
     * 获取请求节点信息
     *
     * @param $key
     * @param false $isDebug
     * @return string[]
     * @throws Exception
     */
    protected function getEndPoint($key, $isDebug = false)
    {
        $endpoints = [
            'getShipTypes'       => [
                'method' => 'GET',
                'uri'    => '/Users/' . $this->userId . '/GetChannels',
                'remark' => '获取发货渠道'
            ],
            'createOrder'        => [
                'method' => 'POST',
                'uri'    => '/Users/' . $this->userId . '/Expresses',
                'remark' => '新建快件信息'
            ],
            'getOrder'           => [
                'method' => 'GET',
                'uri'    => '/Users/' . $this->userId . '/Expresses',
                'remark' => '按条件查询快件信息'
            ],
            'labelPrint'         => [
                'method' => 'GET',
                'uri'    => '/Users/' . $this->userId . '/Expresses/%s/%sLabel',
                'remark' => '单个标签生成'
            ],
            'multipleLabelPrint' => [
                'method' => 'POST',
                'uri'    => '/Users/' . $this->userId . '/Expresses/%sLabel',
                'remark' => '多标签生成'
            ],
            'createOnlineData'   => [
                'method' => 'POST',
                'uri'    => '/Users/' . $this->userId . '/OnlineData',
                'remark' => '新建线上数据信息'
            ],
            'changeStatus'       => [
                'method' => 'POST',
                'uri'    => '/Users/' . $this->userId . '/Expresses/ChangeStatus/%d',
                'remark' => '调整快件状态'
            ],
            'getCountry'         => [
                'method' => 'GET',
                'uri'    => '/Users/' . $this->userId . '/channels/%s/countries',
                'remark' => '获取产品可达国家：{ channelId }：渠道编号。必填'
            ],
            'getOnlineChannels'  => [
                'method' => 'GET',
                'uri'    => '/Users/' . $this->userId . '/GetOnlineChannels',
                'remark' => '获取线上发货渠道'
            ]
        ];

        if (isset($endpoints[$key])) {

            if ($isDebug) {
                $path = $this->testServiceEndPoint;
            } else {
                $path = $this->serviceEndPoint;
            }

            $temp                   = $endpoints[$key]['uri'];
            $endpoints[$key]['uri'] = $path . $temp;

            return $endpoints[$key];
        } else {
            throw new Exception('未找到对应的接口信息 ' . $key);
        }
    }

    /**
     * 1.查询物流发货渠道
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getShipTypes($isDebug = false)
    {
        $endPoint = $this->getEndPoint('getShipTypes', $isDebug);

        $result = $this->client->requestApi($endPoint, [], [], $this->headers);

        return $result;
    }

    /**
     * 创建订单
     *
     * @param $data
     * @param false $isDebug
     * @return array|mixed
     * @throws Exception
     */
    public function createOrder($data, $isDebug = false)
    {
        // 1.验证数据
        Utility::validateData($data);

        // 2.格式化数据
        $newData = $this->formatData($data);
        $body    = Utility::arrayToXml($newData, 'ExpressType');

        // 3.创建订单
        $endPoint = $this->getEndPoint('createOrder', $isDebug);
        $result   = $this->client->requestApi($endPoint, [], $body, $this->headers);

        return $this->handleResult($result);
    }

    /**
     * 3.根据条件查询快件信息
     *
     * @param $params
     * @param false $isDebug
     * @return array|mixed
     * @throws Exception
     */
    public function getOrder($params, $isDebug = false)
    {
        $query = [
            'page'     => $params['page'] ?? 1, // 页码数
            'code'     => $params['code'] ?? '', // 运单号
            'receiver' => $params['receiver'] ?? '', // 收货人姓名
            'channel'  => $params['channel'] ?? '', // 发货方式
            'start'    => $params['start'] ?? '', // 开始时间
            'end'      => $params['end'] ?? '', // 结束时间
            'isstatus' => $params['isstatus'] ?? ''
        ];

        $endPoint = $this->getEndPoint('getOrder', $isDebug);
        $result   = $this->client->requestApi($endPoint, $query, [], $this->headers);

        if ($result['CallSuccess'] == true) {
            $newResult = ['code' => 0, 'message' => '成功', 'data' => $result['ExpressCollection'] ?? []];
        } else {
            $newResult = ['code' => -1, 'message' => '获取失败', 'data' => []];
        }

        return $newResult;
    }

    /**
     * 4.标签打印
     *
     * @param $epCode string 运单号
     * @param string $labelSize {LabelSize}：标签大小。支持的值为：A4L, A4LI, A4LC, A4LCI, A6L, A6LI, A6LC, A6LCI, A10x10L, A10x10LI, A10x10LC, A10x10LCI。
     * (注：L为运单，C为报关签条，I为拣货单。)
     * @param false $isDebug 是否测试环境
     * @throws Exception
     */
    public function labelPrint($epCode, $labelSize = 'A4L', $isDebug = false)
    {
        $endPoint        = $this->getEndPoint('labelPrint', $isDebug);
        $uri             = $endPoint['uri'];
        $endPoint['uri'] = sprintf($uri, $epCode, $labelSize);

        $result = $this->client->requestApi($endPoint, [], [], $this->headers);

        return ['code' => 0, 'message' => '成功', 'data' => $result];
    }

    /**
     * 5.多标签打印
     *
     * @param $epCodes string 运单号,分割，如：YW862913494CN,RQ150332025SG
     * @param string $labelSize
     * @param false $isDebug
     * @return array|mixed
     * @throws Exception
     */
    public function multipleLabelPrint($epCodes, $labelSize = 'A4L', $isDebug = false)
    {
        $endPoint = $this->getEndPoint('multipleLabelPrint', $isDebug);

        if (is_array($epCodes)) {
            $epCodes = implode(',', $epCodes);
        }

        $uri             = $endPoint['uri'];
        $endPoint['uri'] = sprintf($uri, $labelSize);

        $body = "<string>" . $epCodes . "</string>";

        $result = $this->client->requestApi($endPoint, [], $body, $this->headers);

        return ['code' => 0, 'message' => '成功', 'data' => $result];
    }

    /**
     * 6.创建线上数据信息
     *
     * @param $data
     * @param false $isDebug
     * @return array|mixed
     * @throws Exception
     */
    public function createOnlineData($data, $isDebug = false)
    {
        $newData = [
            'Epcode'      => $data['ep_code'] ?? '',
            'Userid'      => $this->userId,
            'ChannelType' => $data['channelCode'] ?? '',
            'Country'     => $data['receiverCountryCode'] ?? '',
            'SendDate'    => $data['sendDate'],
            'Postcode'    => $data['receiverPostCode'],
        ];

        $goods = $data['goods'];
        foreach ($goods as $item) {
            $goodsNames[] = [
                'NameCh'           => $item['goods_cn_name'], // 商品中文品名
                'NameEn'           => $item['goods_en_name'], // 商品英文品名
                'DeclaredValue'    => $item['goods_single_worth'], // 申报价值
                'DeclaredCurrency' => isset($item['currency']) ?? 'USD', // 申报币种
            ];
        }

        $newData['GoodNames'] = $goodsNames;
        $endPoint             = $this->getEndPoint('createOnlineData', $isDebug);

        $body = Utility::arrayToXml($newData, 'OnlineDataType');

        $result = $this->client->requestApi($endPoint, [], $body, $this->headers);

        return $result;
    }

    /**
     * 7.更改运单状态
     *
     * @param $epCode string 运单号 每次请求只允许调整一个运单的快件状态
     * @param $status integer 快件状态。支持的值为：1 正常；0 删除。
     * @param false $isDebug
     * @return array|mixed
     * @throws Exception
     */
    public function changeStatus($epCode, $status, $isDebug = false)
    {
        $endPoint = $this->getEndPoint('changeStatus', $isDebug);

        $uri             = $endPoint['uri'];
        $endPoint['uri'] = sprintf($uri, $status);

        $body = "<string>" . $epCode . "</string>";

        $result = $this->client->requestApi($endPoint, [], $body, $this->headers);

        if ($result['CallSuccess'] == 'false') {
            $newResult = ['code' => -1, 'message' => $result['Message'] ?? ''];
        } else {
            $newResult = ['code' => 0, 'message' => '操作成功'];
        }

        return $newResult;
    }

    /**
     * 8.获取产品可达国家
     *
     * @param $channelId
     * @param false $isDebug
     * @return array|mixed
     * @throws Exception
     */
    public function getCountry($channelId, $isDebug = false)
    {
        $endPoint        = $this->getEndPoint('getCountry', $isDebug);
        $uri             = $endPoint['uri'];
        $endPoint['uri'] = sprintf($uri, $channelId);

        $result = $this->client->requestApi($endPoint, [], [], $this->headers);

        $list = $result['CountryCollection'] ?? [];
        $list = $list['CountryType'] ?? [];

        return ['code' => 0, 'message' => '成功', 'data' => $list];
    }

    /**
     * 获取线上发货渠道
     *
     * @param false $isDebug
     * @return array|mixed
     * @throws Exception
     */
    public function getOnlineChannels($isDebug = false)
    {
        $endPoint = $this->getEndPoint('getOnlineChannels', $isDebug);
        $result   = $this->client->requestApi($endPoint, [], [], $this->headers);

        $list = $result['OnlineChannelCollection'] ?? [];
        $list = $list['ChannelType'] ?? [];

        return ['code' => 0, 'message' => '成功', 'data' => $list];
    }

    /**
     * 格式化数据
     *
     * @param $data
     * @return array
     */
    protected function formatData($data)
    {
        $goods         = $data['goods'];
        $receiver      = [
            // 必填
            'Userid'   => $this->userId, // 客户号
            'Name'     => $data['receiverName'], // 收货人-姓名
            'Postcode' => $data['receiverPostCode'], // 收货人-邮编
            'State'    => $data['rProvince'], // 收货人-州
            'City'     => $data['receiverCity'], // 收货人-城市
            'Address1' => $data['receiverAddress'], // 收货人-地址1
            'Country'  => $data['receiverCountryCode'], // 收货人-国家

            // 收货人-座机，手机。美国专线至少填一项
            'Phone'    => $data['phone'] ?? '',
            'Mobile'   => $data['receiverMobile'] ?? '',
            'Company'  => $data['company'] ?? '', // 收货人-公司

            // 选填
            'Email'    => $data['email'] ?? '', // 收货人-邮箱


            'Address2'   => '',// 收货人-地址2
            'NationalId' => '' // 护照Id （当Channel为燕特快不含电，国家为巴西时，此属性必填）
        ];

        $goodsNames    = [];
        $totalQuantity = 0; // 货品总数量

        foreach ($goods as $item) {
            $goodsNames[]  = [
                'Userid'           => $this->userId, // 客户号
                'NameCh'           => $item['goods_cn_name'], // 商品中文品名
                'NameEn'           => $item['goods_en_name'], // 商品英文品名
                'Weight'           => $item['goods_single_weight'], // 包裹重量
                'DeclaredValue'    => $item['goods_single_worth'], // 申报价值
                'DeclaredCurrency' => isset($item['currency']) ?? 'USD', // 申报币种
                'ProductBrand'     => $item['product_brand'] ?? '', // 产品品牌，中俄SPSR专线此项必填
                'ProductSize'      => $item['product_size'] ?? '', // 产品尺寸，中俄SPSR专线此项必填
                'ProductColor'     => $item['product_color'] ?? '', // 产品颜色，中俄SPSR专线此项必填
                'ProductMaterial'  => $item['product_material'] ?? '', // 产品材质，中俄SPSR专线此项必填
                'MoreGoodsName'    => $item['extra'] ?? '', // 多品名 会出现在拣货单上
                'HsCode'           => $item['hs_code'] ?? '', // 商品海关编码（当Channel为【香港FedEx经济，中邮广州挂号小包，中邮广州平邮小包(专用)】时，该属性HsCode必填）
            ];
            $goodsNumber   = $item['goods_number'] ?? 0;
            $totalQuantity += $goodsNumber;
        }

        $body = [
            // 必填项
            'Userid'          => $this->userId,     // 客户号,
            'Channel'         => $data['channelCode'],     // 发货方式
            'UserOrderNumber' => $data['orderNo'],    // 客户订单号
            'Quantity'        => $totalQuantity, // 货品数量
            'SendDate'        => $data['sendDate'], // 发货日期，datetime类型

            // 可选项
            'Epcode'          => '',
            'YanwenNumber'    => '',    // 参考单号
            'PackageNo'       => '',   // 包裹号
            'Insure'          => '',  // 是否需要保险
            'Memo'            => '',  // 备注。会出现在拣货单上
            'MerchantCsName'  => '',   //店铺名称（当Channel为燕特快不含电，国家为巴西时，此属性必填）
            'Receiver'        => $receiver,
            'GoodsName'       => $goodsNames
        ];

        return $body;
    }

    /**
     * 处理返回结果
     * @param $result
     * @return array
     */
    private function handleResult($result)
    {
        $code           = 0;
        $message        = '成功';
        $callSuccess    = $result['CallSuccess'] ?? '';
        $responce       = $result['Response'] ?? '';
        $createdExpress = $result['CreatedExpress'] ?? '';

        if ($callSuccess == false) {
            $code    = -1;
            $message = $responce['ReasonMessage'] ?? '';
        }

        return [
            'code'        => $code,
            'message'     => $message,
            'data'        => [
                'ep_code' => $createdExpress['Epcode'] ?? '',
            ],
            'origin_data' => json_encode($result)
        ];
    }
}

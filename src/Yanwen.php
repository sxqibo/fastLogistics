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
     * 格式化数据
     *
     * @param $data
     * @return array
     */
    protected function formatData($data)
    {
        $goods    = $data['goods'];
        $receiver = [
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
            'Mobile'   => $data['mobile'] ?? '',
            'Company'  => $data['company'] ?? '', // 收货人-公司

            // 选填
            'Email'    => $data['email'] ?? '', // 收货人-邮箱


            'Address2'   => '',// 收货人-地址2
            'NationalId' => '' // 护照Id （当Channel为燕特快不含电，国家为巴西时，此属性必填）
        ];

        $goodsNames = [];
        foreach ($goods as $item) {
            $goodsNames[] = [
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
        }

        $body = [
            // 必填项
            'Userid'          => $this->userId,     // 客户号,
            'Channel'         => $data['channelCode'],     // 发货方式
            'UserOrderNumber' => $data['orderNo'],    // 客户订单号
            'Quantity'        => count($goods), // 货品数量
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
                'uri'    => '/Users/' . $this->userId . '/Expresses/{EPCODE}/{LabelSize}Label',
                'remark' => '单个标签生成'
            ],
            'multipleLabelPrint' => [
                'method' => 'POST',
                'uri'    => '/Users/' . $this->userId . '/Expresses/{LabelSize}Label',
                'remark' => '多标签生成'
            ],
            'createOnlineData'   => [
                'method' => 'POST',
                'uri'    => '/Users/' . $this->userId . '/OnlineData',
                'remark' => '新建线上数据信息'
            ],
            'changeStatus'       => [
                'method' => 'POST',
                'uri'    => '/Users/' . $this->userId . '/Expresses/ChangeStatus',
                'remark' => '调整快件状态'
            ],
            'getCountry'         => [
                'method' => 'GET',
                'uri'    => '/Users/' . $this->userId . '/channels/{ChannelId}/countries',
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

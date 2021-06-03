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
            ],
        ];

        if (isset($endpoints[$key])) {

            if ($isDebug) {
                $path = $this->testServiceEndPoint;
            } else {
                $path = $this->serviceEndPoint;
            }

            $temp                   = $endpoints[$key]['uri'];
            $endpoints[$key]['url'] = $path . $temp;

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
            'start'    => $params['start_time'] ?? '', // 开始时间
            'end'      => $params['end_time'] ?? '', // 结束时间
            'isstatus' => $params['status'] ?? ''
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
     * 获取订单跟踪记录
     *
     * @param $trackNumber
     * @param false $isDebug
     * @return array
     * @throws Exception
     */
    public function getTrack($trackNumber)
    {
        $endPoint = [
            'url'    => 'http://trackapi.yanwentech.com/api/tracking',
            'method' => 'GET'
        ];

        $header = [
            'Authorization' => $this->userId,
        ];

        $params = [
            'nums' => $trackNumber
        ];

        $result = $this->client->requestApi($endPoint, $params, [], $header, true);

        if ($result['code'] == 0) {
            return ['code' => 0, 'message' => '成功', 'data' => $result['result']];
        } else {
            return ['code' => -1, 'message' => $result['message'], 'data' => []];
        }
    }

    /**
     * 获取订单跟踪记录状态码信息
     *
     * @param $code
     * @return string|string[]
     */
    public function getTrackingStatusCodeInfo($code)
    {
        $arr = array(
            'OR10'  =>
                array(
                    'code' => 'OR10',
                    'cn'   => '订单已生成',
                    'en'   => 'Order processed by shipper',
                ),
            'OR30'  =>
                array(
                    'code' => 'OR30',
                    'cn'   => '订单已取消',
                    'en'   => 'Order cancelled by shipper',
                ),
            'PU10'  =>
                array(
                    'code' => 'PU10',
                    'cn'   => '燕文已揽收',
                    'en'   => 'Yanwen Pickup Scan',
                ),
            'PU30'  =>
                array(
                    'code' => 'PU30',
                    'cn'   => '揽收失败',
                    'en'   => 'Yanwen Pickup failed',
                ),
            'SC10'  =>
                array(
                    'code' => 'SC10',
                    'cn'   => '快件处理',
                    'en'   => 'Processing information input',
                ),
            'SC20'  =>
                array(
                    'code' => 'SC20',
                    'cn'   => '离开燕文处理中心',
                    'en'   => 'Yanwen facility - Outbound',
                ),
            'SC30'  =>
                array(
                    'code' => 'SC30',
                    'cn'   => '退件组包',
                    'en'   => 'Returned by Yanwen',
                ),
            'SC35'  =>
                array(
                    'code' => 'SC35',
                    'cn'   => '快递退回或客户自取',
                    'en'   => 'Returned by Yanwen, Process completed',
                ),
            'SC36'  =>
                array(
                    'code' => 'SC36',
                    'cn'   => '退件签收',
                    'en'   => 'Returned by Yanwen, Delivered with signature',
                ),
            'SC37'  =>
                array(
                    'code' => 'SC37',
                    'cn'   => '退件件签收失败',
                    'en'   => 'Returned by Yanwen, Delivered without signature',
                ),
            'SC40'  =>
                array(
                    'code' => 'SC40',
                    'cn'   => '目的国承运商：xxx，追踪单号：xxxx',
                    'en'   => 'Destination country carrier：xxx， Parcel tracking number：xxx',
                ),
            'SC45'  =>
                array(
                    'code' => 'SC45',
                    'cn'   => '收到预录单电子信息',
                    'en'   => 'Pre-shipment Info sent to carrier',
                ),
            'SC65'  =>
                array(
                    'code' => 'SC65',
                    'cn'   => '到达物流商仓库',
                    'en'   => 'Carrier facility - Inbound',
                ),
            'SC70'  =>
                array(
                    'code' => 'SC70',
                    'cn'   => '离开物流商仓库',
                    'en'   => 'Carrier facility - Outbound',
                ),
            'EC10'  =>
                array(
                    'code' => 'EC10',
                    'cn'   => '提交出口报关信息',
                    'en'   => 'Customs declaration information - Export',
                ),
            'EC20'  =>
                array(
                    'code' => 'EC20',
                    'cn'   => '出口报关放行',
                    'en'   => 'International shipment release - Export',
                ),
            'EC30'  =>
                array(
                    'code' => 'EC30',
                    'cn'   => '出口报关异常',
                    'en'   => 'Custom clearance failed - Export',
                ),
            'LH10'  =>
                array(
                    'code' => 'LH10',
                    'cn'   => '航空/班轮/铁路公司接收',
                    'en'   => 'Port of departure - Received by carrier',
                ),
            'LH20'  =>
                array(
                    'code' => 'LH20',
                    'cn'   => '航班起飞/班轮起航',
                    'en'   => 'Port of departure - Departure',
                ),
            'LH21'  =>
                array(
                    'code' => 'LH21',
                    'cn'   => '中转港接收',
                    'en'   => 'In transit - Arrival',
                ),
            'LH22'  =>
                array(
                    'code' => 'LH22',
                    'cn'   => '中转港起飞/起航',
                    'en'   => 'In transit - Departure',
                ),
            'LH35'  =>
                array(
                    'code' => 'LH35',
                    'cn'   => '航班/班轮延误原因',
                    'en'   => 'Flight/Voyage delay',
                ),
            'LH30'  =>
                array(
                    'code' => 'LH30',
                    'cn'   => '到达铁路运输终点站',
                    'en'   => 'Station of destination - Arrival',
                ),
            'LH40'  =>
                array(
                    'code' => 'LH40',
                    'cn'   => '到达物流商仓库',
                    'en'   => 'Carrier facility - Inbound',
                ),
            'LH45'  =>
                array(
                    'code' => 'LH45',
                    'cn'   => '离开目的港',
                    'en'   => 'Port of destination - Departure',
                ),
            'LH23'  =>
                array(
                    'code' => 'LH23',
                    'cn'   => '到达铁路运输途径站',
                    'en'   => 'Enroute - Arrival',
                ),
            'LH24'  =>
                array(
                    'code' => 'LH24',
                    'cn'   => '离开铁路运输途径站',
                    'en'   => 'Enroute - Departure',
                ),
            'LH26'  =>
                array(
                    'code' => 'LH26',
                    'cn'   => '到达铁路运输途径站',
                    'en'   => 'Enroute - Arrival',
                ),
            'LH27'  =>
                array(
                    'code' => 'LH27',
                    'cn'   => '离开铁路运输途径站',
                    'en'   => 'Enroute - Departure',
                ),
            'LH28'  =>
                array(
                    'code' => 'LH28',
                    'cn'   => '到达铁路运输途径站',
                    'en'   => 'Enroute - Arrival',
                ),
            'LH29'  =>
                array(
                    'code' => 'LH29',
                    'cn'   => '离开铁路运输途径站',
                    'en'   => 'Enroute - Departure',
                ),
            'LH31'  =>
                array(
                    'code' => 'LH31',
                    'cn'   => '离开铁路运输终点站',
                    'en'   => 'Station of destination - Departure',
                ),
            'LH33'  =>
                array(
                    'code' => 'LH33',
                    'cn'   => '抵达目的地仓库',
                    'en'   => 'LOCATION，Arrived at international hub',
                ),
            'IC50'  =>
                array(
                    'code' => 'IC50',
                    'cn'   => '开始进口清关',
                    'en'   => 'Custom Clearance in process',
                ),
            'IC60'  =>
                array(
                    'code' => 'IC60',
                    'cn'   => '进口清关完成',
                    'en'   => 'International shipment release - Import',
                ),
            'IC70'  =>
                array(
                    'code' => 'IC70',
                    'cn'   => '进口清关失败',
                    'en'   => 'Custom clearance failed - Import',
                ),
            'LH50'  =>
                array(
                    'code' => 'LH50',
                    'cn'   => '到达派送目的国',
                    'en'   => 'Destination Country - Arrival',
                ),
            'LM10'  =>
                array(
                    'code' => 'LM10',
                    'cn'   => '到达目的国派送处理中心',
                    'en'   => 'LOCATION，Distribution center - Inbound',
                ),
            'LM11'  =>
                array(
                    'code' => 'LM11',
                    'cn'   => '离开目的国派送处理中心，开始转运',
                    'en'   => 'LOCATION，Distribution center - Outbound',
                ),
            'LM15'  =>
                array(
                    'code' => 'LM15',
                    'cn'   => '目的国国内中转',
                    'en'   => 'In transit to next facility',
                ),
            'LM12'  =>
                array(
                    'code' => 'LM12',
                    'cn'   => '包裹到达目的国中转中心',
                    'en'   => 'In transit - Inbound',
                ),
            'LM13'  =>
                array(
                    'code' => 'LM13',
                    'cn'   => '包裹离开目的国中转中心',
                    'en'   => 'In transit - Outbound',
                ),
            'LM20'  =>
                array(
                    'code' => 'LM20',
                    'cn'   => '到达目的国最后派送点',
                    'en'   => 'Destination Scan',
                ),
            'LM25'  =>
                array(
                    'code' => 'LM25',
                    'cn'   => '离开目的国最后派送点',
                    'en'   => 'Out for delivery',
                ),
            'LM40'  =>
                array(
                    'code' => 'LM40',
                    'cn'   => '派送成功/妥投/签收，POD',
                    'en'   => 'Delivered with POD',
                ),
            'LM50'  =>
                array(
                    'code' => 'LM50',
                    'cn'   => '派送失败',
                    'en'   => 'Delivery failed',
                ),
            'LM51'  =>
                array(
                    'code' => 'LM51',
                    'cn'   => '妥投失败，再次尝试投递',
                    'en'   => 'Delivery attempt failed, will be arranged again',
                ),
            'LM52'  =>
                array(
                    'code' => 'LM52',
                    'cn'   => '妥投失败，收件人联系不上',
                    'en'   => 'Delivery failed, Consignee is not available',
                ),
            'LM53'  =>
                array(
                    'code' => 'LM53',
                    'cn'   => '妥投失败，包裹损坏',
                    'en'   => 'Delivery failed, Package damaged',
                ),
            'LM60'  =>
                array(
                    'code' => 'LM60',
                    'cn'   => '派送延迟',
                    'en'   => 'Delivery delay',
                ),
            'LM61'  =>
                array(
                    'code' => 'LM61',
                    'cn'   => '预约派送',
                    'en'   => 'Delivery delay and appointment',
                ),
            'LM65'  =>
                array(
                    'code' => 'LM65',
                    'cn'   => '签收延迟，会再次派送',
                    'en'   => 'Sign-off delay and will be delivered again',
                ),
            'LM30'  =>
                array(
                    'code' => 'LM30',
                    'cn'   => '到达待取',
                    'en'   => 'Available for pick up',
                ),
            'LM32'  =>
                array(
                    'code' => 'LM32',
                    'cn'   => '等待收件人支付关税',
                    'en'   => 'Waiting for paying Duty',
                ),
            'LM70'  =>
                array(
                    'code' => 'LM70',
                    'cn'   => '需要进⼀步确认收件⼈信息',
                    'en'   => 'Consignee information is required',
                ),
            'LM90'  =>
                array(
                    'code' => 'LM90',
                    'cn'   => '包裹退回',
                    'en'   => 'Package returned',
                ),
            'LM91'  =>
                array(
                    'code' => 'LM91',
                    'cn'   => '派送失败，包裹退回到物流商',
                    'en'   => 'Delivery failed and package returned to carrier',
                ),
            'LM92'  =>
                array(
                    'code' => 'LM92',
                    'cn'   => '派送失败，从海外退回',
                    'en'   => 'Delivery failed and package returned from overseas',
                ),
            'LM93'  =>
                array(
                    'code' => 'LM93',
                    'cn'   => '派送失败，包裹退回，包裹损坏',
                    'en'   => 'Delivery failed and return, package damaged',
                ),
            'LM75'  =>
                array(
                    'code' => 'LM75',
                    'cn'   => '收件⼈拒绝签收',
                    'en'   => 'Sign-off refused by consignee',
                ),
            'LM76'  =>
                array(
                    'code' => 'LM76',
                    'cn'   => '包裹损坏，收件⼈拒绝签收',
                    'en'   => 'Package damaged, consignee refused',
                ),
            'LM85'  =>
                array(
                    'code' => 'LM85',
                    'cn'   => '包裹丢失',
                    'en'   => 'Package lost',
                ),
            'OTHER' =>
                array(
                    'code' => 'OTHER',
                    'cn'   => '其他信息',
                    'en'   => 'OTHERS',
                ),
        );

        return $arr[$code] ?? '';
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
                'Weight'           => $item['goods_single_weight'] * 1000, // 包裹重量 单位g
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

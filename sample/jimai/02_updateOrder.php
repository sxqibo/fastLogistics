<?php
//命名空间
use Sxqibo\Logistics\Jimai;

require_once __DIR__.'/../../vendor/autoload.php';
$config = require_once __DIR__.'/config.php';

$clientId = $config['jimai']['clientId'];
$token    = $config['jimai']['token'];
$data     = new Jimai($clientId, $token);

/**
 * 02、 修改快件订单,仓储订单,快递制单
 */
$orderNo     = '1606033364';
$channelCode = 'GNPS';
$countryCode = 'DE';
$totalValue  = '100';
$number      = 1;
$name        = 'Duygu';
$address     = 'Wetterweg 83';
$mobile      = '01723683610';
$province    = 'Nrw';
$city        = 'Ahlen';
$postCode    = '59229';
$cnname      = 'LHNLY餐厅椅子曲棍球高脚凳Tresenhocker现代优雅天鹅绒装饰带靠背无扶手牛奶甜品店创意工作室厨房，高度60cm/70cm';
$enname      = 'LHNLY-Esszimmerstühle Hochhocker Barhocker Tresenhocker Moderne Eleganz Samt Polsterhocker Mit Rückenlehne Keine Armlehne Milch Tee Dessert Shop Kreative Studio Küche, Höhe 60cm / 70cm';
$price       = 100.00;
$weight      = 6;

$corpBillid  = 'JHLCN0112395382YQ';


$result = $data->updateOrder($orderNo, $channelCode, $countryCode, $totalValue, $number,
    $name, $address, $mobile, $province, $city, $postCode, $cnname, $enname, $price, $weight, $corpBillid);
print_r($result);


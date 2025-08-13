<?php
//命名空间
use Sxqibo\Logistics\Santai;

require_once __DIR__ . '/../../vendor/autoload.php';

// 获取配置
$config = require_once __DIR__ . '/config.php';

$appKey = $config['sanTai']['appKey'];
$token  = $config['sanTai']['token'];
$userId = $config['sanTai']['userId'];
$data   = new Santai($appKey, $token, $userId);

/**
 * 01、获取运输方式列表  （说明：目前在用，栏目：物流公司-运输方式）
 * @doc https://www.sfcservice.com/api-doc
 */
$result = $data->getShipTypes();
print_r($result);


<?php
//命名空间
use Sxqibo\Logistics\Santai;

require_once __DIR__ . '/../../vendor/autoload.php';
$config = require_once __DIR__ . '/config.php';

$appKey = $config['sanTai']['appKey'];
$token  = $config['sanTai']['token'];
$userId = $config['sanTai']['userId'];
$data   = new Santai($appKey, $token, $userId);

/**
 * 15、打印地址标签
 * 说明：根据三态物流文档，可以通过URL直接打印标签
 * @doc https://www.sfcservice.com/api-doc
 */
echo "=== 三态物流 - 打印地址标签测试 ===\n\n";

// 测试订单号（请替换为实际的订单号）
$orderNo = 'SFCAA0101869284YQ'; // 示例订单号，请根据实际情况修改

echo "订单号: {$orderNo}\n";
echo "正在生成打印标签...\n\n";

// 调用打印地址标签方法 - 设置参数：热敏 + PDF + 10*10
$result = $data->printAddressLabel($orderNo, 1, 'pdf', 1);

echo "=== 打印标签结果 ===\n";
print_r($result);

<?php

/**
 * GoodCang API 示例 - 获取账户金额明细
 */

// 引入配置文件
$config = require_once __DIR__ . '/config.php';

// 引入GoodCang类
require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\GoodCang;

$appToken = $config['goodcang']['app_token'];
$appKey  = $config['goodcang']['app_key'];

try {
    // 创建GoodCang实例
    $goodCang = new GoodCang($appToken, $appKey);

    echo "=== GoodCang API 获取账户金额明细测试 ===\n";
    echo "App Token: " . $appToken . "\n";
    echo "App Key: " . $appKey . "\n\n";

    // 设置查询参数（请根据实际情况替换账户编码）
    $params = [
        'account_code' => 'XXX',
        'account_codes' => [],
    ];

    echo "请求参数:\n";
    echo json_encode($params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    // 调用API获取账户金额明细
    echo "正在调用API获取账户金额明细...\n";
    $result = $goodCang->getAccountDetail($params);

    echo "API调用成功！\n";
    echo "响应数据:\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

    // 解析账户金额明细
    if (isset($result['code']) && $result['code'] === 0 && isset($result['data']) && is_array($result['data'])) {
        echo "\n=== 账户金额明细解析 ===\n";
        foreach ($result['data'] as $index => $detail) {
            echo "账户 " . ($index + 1) . ":\n";
            echo "  账户编码: " . ($detail['account_code'] ?? 'N/A') . "\n";

            if (isset($detail['currency_details']) && is_array($detail['currency_details'])) {
                echo "  币种明细数量: " . count($detail['currency_details']) . "\n";
                foreach ($detail['currency_details'] as $currencyIndex => $currencyDetail) {
                    echo "    币种 " . ($currencyIndex + 1) . ":\n";
                    echo "      货币: " . ($currencyDetail['currency'] ?? 'N/A') . "\n";
                    echo "      账户余额: " . ($currencyDetail['account_balance'] ?? 'N/A') . "\n";
                    echo "      可用金额: " . ($currencyDetail['available_amount'] ?? 'N/A') . "\n";
                    echo "      固定额度: " . ($currencyDetail['fixed_quota'] ?? 'N/A') . "\n";
                    echo "      临时额度: " . ($currencyDetail['temporary_quota'] ?? 'N/A') . "\n";
                    echo "      临时额度剩余天数: " . ($currencyDetail['remaining_days'] ?? 'N/A') . "\n";
                    echo "      冻结金额: " . ($currencyDetail['frozen_amount'] ?? 'N/A') . "\n";
                    echo "      押金金额: " . ($currencyDetail['deposit'] ?? 'N/A') . "\n";
                }
            }

            if (isset($detail['overall_financial']) && is_array($detail['overall_financial'])) {
                $overall = $detail['overall_financial'];
                echo "  === 汇总信息 ===\n";
                echo "    总可用余额: " . ($overall['total_available_balance'] ?? 'N/A') . "\n";
                echo "    账户总余额: " . ($overall['total_account_balance'] ?? 'N/A') . "\n";
                echo "    固定总额度: " . ($overall['total_fixed_quota'] ?? 'N/A') . "\n";
                echo "    总临时额度: " . ($overall['total_temporary_quota'] ?? 'N/A') . "\n";
                echo "    总冻结金额: " . ($overall['total_frozen_amount'] ?? 'N/A') . "\n";
            }
            echo "\n";
        }
    } else {
        echo "获取账户金额明细失败或返回数据格式异常\n";
        if (isset($result['message'])) {
            echo "错误信息: " . $result['message'] . "\n";
        }
    }

} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n";
    echo "错误文件: " . $e->getFile() . "\n";
    echo "错误行数: " . $e->getLine() . "\n";
}

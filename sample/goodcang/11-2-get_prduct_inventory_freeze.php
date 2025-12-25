<?php 

/**
 * GoodCang API 示例 - 获取仓库所有商品冻结数量及明细
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
    /** @var GoodCang $goodCang */
    $goodCang = new GoodCang($appToken, $appKey);

    echo "=== GoodCang API 获取仓库所有商品冻结数量及明细 ===\n";
    echo "App Token: " . $appToken . "\n";
    echo "App Key: " . $appKey . "\n\n";

    // 设置查询参数（不传 product_sku 和 warehouse_code，获取所有商品）
    $params = [
        // 必填参数
        'page' => 1,                                     // 当前页
        'pageSize' => 200,                               // 每页数据长度（最大200，设置为最大以提高效率）

        // 可选参数：如果需要查询特定仓库，可以设置 warehouse_code
        // 'warehouse_code' => 'USEA',                   // 仓库代码
    ];

    echo "请求参数:\n";
    echo json_encode($params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    // 存储所有冻结商品的明细
    $freezeDetails = [];
    $totalFreezeQty = 0;
    $currentPage = 1;
    $maxPages = 1000; // 最多查询1000页，防止无限循环
    $totalCount = 0;

    echo "开始查询所有商品的冻结数量...\n\n";

    // 循环查询所有页的数据
    while ($currentPage <= $maxPages) {
        $params['page'] = $currentPage;
        
        echo "正在查询第 {$currentPage} 页...\n";
        
        // 调用API获取产品库存
        $result = $goodCang->getProductInventory($params);

        // 检查响应状态（V1版本API，响应结构为ask, message, count, data）
        if (!isset($result['ask'])) {
            echo "警告: 响应数据缺少 ask 字段\n";
            break;
        }

        // 如果第一页，显示响应基本信息
        if ($currentPage === 1) {
            echo "API调用成功！\n\n";
            echo "=== 响应状态 ===\n";
            echo "状态: " . ($result['ask'] ?? 'N/A') . "\n";
            echo "消息: " . ($result['message'] ?? 'N/A') . "\n";
            
            if (isset($result['count'])) {
                $totalCount = (int)$result['count'];
                echo "总记录数: " . $totalCount . "\n";
                $totalPages = ceil($totalCount / $params['pageSize']);
                echo "预计总页数: " . $totalPages . "\n";
            }
            echo "\n";
        }

        // 解析响应数据
        if (isset($result['ask']) && $result['ask'] === 'Success' && isset($result['data']) && is_array($result['data'])) {
            $inventoryList = $result['data'];
            
            // 如果当前页没有数据，停止查询
            if (empty($inventoryList)) {
                echo "当前页无数据，停止查询\n\n";
                break;
            }

            echo "第 {$currentPage} 页商品数量: " . count($inventoryList) . "\n";

            // 遍历当前页的商品，提取冻结数量
            foreach ($inventoryList as $item) {
                $freezeQty = isset($item['pi_freeze']) ? (int)$item['pi_freeze'] : 0;
                
                // 只记录有冻结数量的商品
                if ($freezeQty > 0) {
                    $freezeDetails[] = [
                        'product_sku' => $item['product_sku'] ?? 'N/A',
                        'product_title' => $item['product_title'] ?? 'N/A',
                        'pi_freeze' => $freezeQty,
                        'warehouse_code' => $item['warehouse_code'] ?? 'N/A',
                        'warehouse_desc' => $item['warehouse_desc'] ?? 'N/A',
                    ];
                    $totalFreezeQty += $freezeQty;
                }
            }

            // 如果当前页数据少于 pageSize，说明已经是最后一页
            if (count($inventoryList) < $params['pageSize']) {
                echo "已查询完所有数据\n\n";
                break;
            }

            // 继续查询下一页
            $currentPage++;
        } else {
            echo "=== 错误信息 ===\n";
            echo "获取商品库存失败或返回数据格式异常\n";
            if (isset($result['ask'])) {
                echo "状态: " . $result['ask'] . "\n";
            }
            if (isset($result['message'])) {
                echo "错误消息: " . $result['message'] . "\n";
            }
            if (isset($result['errors']) && is_array($result['errors']) && !empty($result['errors'])) {
                echo "详细错误:\n";
                foreach ($result['errors'] as $error) {
                    if (is_array($error)) {
                        echo "  - " . json_encode($error, JSON_UNESCAPED_UNICODE) . "\n";
                    } else {
                        echo "  - " . $error . "\n";
                    }
                }
            }
            break;
        }
    }

    // 显示冻结数量汇总
    echo "=== 冻结数量汇总 ===\n";
    echo "已查询页数: " . ($currentPage - 1) . "\n";
    echo "有冻结数量的商品数: " . count($freezeDetails) . "\n";
    echo "冻结总数: " . number_format($totalFreezeQty) . " 件\n\n";

    // 显示冻结明细
    if (!empty($freezeDetails)) {
        echo "=== 冻结明细 ===\n";
        // 增加商品编码列宽度到50，确保完整显示
        $skuWidth = 50;
        $titleWidth = 50;
        $freezeQtyWidth = 15;
        $warehouseCodeWidth = 15;
        $warehouseDescWidth = 30;
        $totalWidth = 8 + $skuWidth + $titleWidth + $freezeQtyWidth + $warehouseCodeWidth + $warehouseDescWidth;
        
        echo str_pad("序号", 8, " ", STR_PAD_RIGHT) . 
             str_pad("商品编码", $skuWidth, " ", STR_PAD_RIGHT) . 
             str_pad("中文名称", $titleWidth, " ", STR_PAD_RIGHT) . 
             str_pad("冻结数量", $freezeQtyWidth, " ", STR_PAD_RIGHT) . 
             str_pad("仓库代码", $warehouseCodeWidth, " ", STR_PAD_RIGHT) . 
             str_pad("仓库描述", $warehouseDescWidth, " ", STR_PAD_RIGHT) . "\n";
        echo str_repeat("-", $totalWidth) . "\n";

        foreach ($freezeDetails as $index => $detail) {
            $seq = $index + 1;
            // 不截取商品编码，完整显示
            $sku = $detail['product_sku'];
            // 中文名称如果过长，可以适当截取
            $title = mb_strlen($detail['product_title']) > $titleWidth ? 
                     mb_substr($detail['product_title'], 0, $titleWidth - 3) . '...' : 
                     $detail['product_title'];
            $freezeQty = number_format($detail['pi_freeze']);
            $warehouseCode = $detail['warehouse_code'];
            $warehouseDesc = mb_strlen($detail['warehouse_desc']) > $warehouseDescWidth ? 
                           mb_substr($detail['warehouse_desc'], 0, $warehouseDescWidth - 3) . '...' : 
                           $detail['warehouse_desc'];

            echo str_pad($seq, 8, " ", STR_PAD_RIGHT) . 
                 str_pad($sku, $skuWidth, " ", STR_PAD_RIGHT) . 
                 str_pad($title, $titleWidth, " ", STR_PAD_RIGHT) . 
                 str_pad($freezeQty, $freezeQtyWidth, " ", STR_PAD_RIGHT) . 
                 str_pad($warehouseCode, $warehouseCodeWidth, " ", STR_PAD_RIGHT) . 
                 str_pad($warehouseDesc, $warehouseDescWidth, " ", STR_PAD_RIGHT) . "\n";
        }
        echo str_repeat("-", $totalWidth) . "\n";
        echo "总计: " . count($freezeDetails) . " 条记录，冻结总数: " . number_format($totalFreezeQty) . " 件\n";
    } else {
        echo "未找到有冻结数量的商品\n";
    }

} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n";
    echo "错误文件: " . $e->getFile() . "\n";
    echo "错误行数: " . $e->getLine() . "\n";
}

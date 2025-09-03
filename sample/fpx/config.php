<?php
/**
 * 递四方物流配置
 * 请根据实际情况填写您的客户端ID和密钥
 */

// 递四方API配置
$fpxConfig = [
    'app_key' => '',        // 应用接入申请的AppKey
    'app_secret' => '',    // App Secret
    'environment' => 'production', // 环境：sandbox(测试环境) 或 production(生产环境)
    'access_token' => '' // 访问令牌（可选，平台服务商、第三方软件商必须传入）
];

return $fpxConfig;

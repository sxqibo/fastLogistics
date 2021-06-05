<?php

require __DIR__ . '/vendor/autoload.php';


try {
    $accessCode = '';
    $checkWord  = '';

    $client = new \Sxqibo\Logistics\Shunfeng($accessCode, $checkWord);

    $result = $client->labelPrint('TE1622875810', 'SF6041146470069');

    dd($result);
} catch (\Exception $e) {
    dd($e->getMessage());
}



<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

namespace tests\tests;

use Ankio\PayConfig;
use Ankio\Vpay;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class VpayTest extends TestCase
{


    public function test__construct()
    {
        $this->testCreate();
    }

    public function testCreate()
    {
        include_once "../src/autoload.php";
        $config = new PayConfig([
            'host'=>'https://pay.dev-ankio.net',
            'key'=>'PrplYUFtiPyKRzqvJGjeQwn4IoHElaz0',
            'time'=>5,
            'id'=>3
        ]);
        $params = [
            'pay_type'=>'1',
            'app_item'=>"测试商品",
            'notify_url'=>'https://pay.dev-ankio.net',
            'return_url'=>'https://pay.dev-ankio.net',
            'price'=>0.02,
            'param'=>json_encode([
                'xxxxx'=>'xxxx','xxxxxxxx'=>'xxxxx'
            ]),
            'api'=>true,
        ];
        $pay  = new Vpay($config);
        $result = $pay->Create($params);


        assertEquals("",$pay->getError());
        var_dump($result);exit();
        assertEquals(true,$result);
    }
}

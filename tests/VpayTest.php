<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

namespace tests\tests;


use Ankio\PayConfig;
use Ankio\Vpay;
use Ankio\objects\PayCreateObject;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class VpayTest extends TestCase
{

    private PayConfig $config ;
    public function test__construct()
    {
        include_once "../src/autoload.php";
        $this->config = new PayConfig([
            'host'=>'https://pay.dev-ankio.net',
            'key'=>'PrplYUFtiPyKRzqvJGjeQwn4IoHElaz0',
            'time'=>5,
            'id'=>3
        ]);
        $this->testCreate();
    }

    public function testCreate()
    {
        $order = new PayCreateObject();
        $order->app_item = "测试商品";
        $order->appid = $this->config->id;
        $order->param = json_encode([
            'xxxxx'=>'xxxx','xxxxxxxx'=>'xxxxx'
        ]);
        $order->price = 2.2;
        $order->pay_type = Vpay::PAY_ALIPAY;
        $order->notify_url = 'https://pay.dev-ankio.net';
        $order->return_url = 'https://pay.dev-ankio.net';
        $pay  = new Vpay($this->config);
        $result = $pay->Create($order);


        assertEquals("",$pay->getError());
        var_dump($result);exit();
        assertEquals(true,$result);
    }
}

<?php

namespace Ankio\objects;

class OrderObject extends BaseObject
{
//  'order_id'=>$order->order_id,
//            'url'=>url("index","main","pay",["id"=>$order->order_id]),
//            'pay_image'=>$order->pay_image,
//            'create_time'=>$order->create_time,
//            'app_item'=>$order->app_item,
//            'app_name'=>$order->app_name,
//            'real_price'=>$order->real_price,
//            'price'=>$order->price,
//            'param'=>$order->param,
//        ];

public string $order_id = "";//订单ID
    public string $url = "";//跳转支付的Url
    public string $pay_image = "";//支付二维码的String
    public int $create_time = 0;//订单创建时间

    public string $app_item = "";//商品名

    public string $app_name = "";//商户名

    public float $real_price = 0.00;//真实支付金额

    public float $price = 0.00;

    public string $parma = "";
}
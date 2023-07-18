<?php
/*
 * Copyright (c) 2023. Ankio.  由CleanPHP4强力驱动。
 */

namespace Ankio\objects;
//数据构建的Object，富有表现力。
class PayNotifyObject extends BaseObject
{
    public int $pay_type = 0;//支付类型
    public float $price = 0.00;//支付金额
    public float $real_price = 0.00;//真实支付的金额
    public string $app_name = "";//商户，商户名称
    public string $app_item = "";//商户商品
    public int $appid = 0;//appid
    public string $order_id = "";//订单id
    public int $create_time = 0;//时间戳
    public int $pay_time = 0;//时间戳
    public string $param = "";//附加参数
    public string $pay_image = "";//支付二维码

}
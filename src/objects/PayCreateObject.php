<?php
/*
 * Copyright (c) 2023. Ankio.  由CleanPHP4强力驱动。
 */

namespace Ankio\objects;

use Ankio\Vpay;

class PayCreateObject extends BaseObject
{
    public int $pay_type = Vpay::PAY_WECHAT;//支付类型
    public string $app_item = "";//商户商品
    public string $notify_url = "";//异步通知链接
    public string $return_url = "";//异步通知链接
    public float $price = 0.00;//支付金额
    public string $param = "{}";//其他参数
    public int $t = 0;//订单创建时间
    /**
     * @var int|mixed
     */
    public string $appid = "0";//应用ID

    function hash(){
        return md5(join(",",get_object_vars($this)));
    }
}
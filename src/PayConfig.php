<?php
/*
 * Copyright (c) 2023. Ankio.  由CleanPHP4强力驱动。
 */
/**
 * Package: Ankio
 * Class PayConfig
 * Created By ankio.
 * Date : 2023/5/8
 * Time : 13:28
 * Description :
 */

namespace Ankio;

class PayConfig
{
    public string $host = "";
    public string $key = "";

    public int $id = 0;
    public int $time = 5;

    public function __construct($config)
    {
        $this->host = $config['host'];
        $this->key = $config['key'];
        $this->time = $config['time'];
        $this->id = $config['id'];
    }

    public function getCreateOrder(): string
    {
        return $this->host."/order/create";
    }

    public function getOrderState(): string
    {
        return $this->host."/api/pay/payState";
    }

    public function closeOrder(): string
    {
        return $this->host."/order/close";
    }

}
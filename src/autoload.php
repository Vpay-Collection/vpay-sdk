<?php
/*
 * Copyright (c) 2023. Ankio.  由CleanPHP4强力驱动。
 */
/**
 * File autoload.php
 * Created By ankio.
 * Date : 2023/5/8
 * Time : 11:07
 * Description :
 */
spl_autoload_register(function($class)
{
    $class = str_replace(['Ankio\\',"\\"],DIRECTORY_SEPARATOR, $class);
    $file =__DIR__ . $class . '.php';
    if (file_exists($file)) include_once $file;

}, true, true);
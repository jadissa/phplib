<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

use NikkenPHPLib\Fw;

try
{
    Fw::json_encode(Fw::getSettings());
}
catch(Exception $e)
{
    die($e->getTraceAsString());
}
print 'Ok' . PHP_EOL;
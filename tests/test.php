<?php
header('Content-type: application/json; charset=utf-8');
require_once __DIR__ . '/../vendor/autoload.php';

use JadissaPHPLib\Fw;
try
{
    Fw::json_encode(Fw::getSettings());
}
catch(Exception $e)
{
    die($e->getTraceAsString());
}
print 'Ok' . PHP_EOL;
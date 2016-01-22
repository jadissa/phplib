<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

use NikkenPHPLib\Fw;

echo Fw::json_encode(Fw::getSettings());
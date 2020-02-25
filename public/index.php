<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Kernel;


(new Kernel('dev', true))
    ->run();

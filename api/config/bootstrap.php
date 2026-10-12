<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

Dotenv::createImmutable(dirname(__DIR__), '.env')->safeLoad();

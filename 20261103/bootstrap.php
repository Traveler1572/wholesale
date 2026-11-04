<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Tokyo');
$configFile = getenv('WHOLESALE_CONFIG') ?: dirname(__DIR__) . '/config.php';
$config = require is_file($configFile) ? $configFile : dirname(__DIR__) . '/config.example.php';
?>
<?php

use Siushin\Util\Installer;

require_once __DIR__ . '/Loader.php';

test_log("Running tests...");

// 测试用例1
var_dump(getDateTimeArr());

// 测试用例2
Installer::postInstall();

// 测试用例3
var_dump(generateId());
<?php

use Siushin\Util\Installer;

require_once __DIR__ . '/Loader.php';

test_log("Running tests...");

// 测试用例1
dump(getDateTimeArr());

// 测试用例2
Installer::postInstall();

// 测试用例3
dump(generateId());

// 测试用例4
$oldData = ['name' => 'Tony', 'age' => 30];
$newData = ['name' => 'John', 'age' => 31];
$keyMapping = ['name' => '姓名'];
dump(generateDataChangeLog($oldData, $newData, '修改用户数据', $keyMapping));

// 测试用例5
$newData = [
    ['name' => 'Tom', 'age' => 25],           // 新增（无ID）
    ['id' => 1, 'name' => 'Jerry', 'age' => 30], // 更新（有ID且存在）
    ['id' => 2, 'name' => 'Bob', 'age' => 20]    // 更新（有ID且存在）
];
$oldData = [
    ['id' => 1, 'name' => 'Jerry', 'age' => 25], // 旧数据
    ['id' => 2, 'name' => 'Bob', 'age' => 18],   // 旧数据
    ['id' => 3, 'name' => 'Alice', 'age' => 22]  // 将被删除
];
dump(compareDbDataDiff($newData, $oldData, 'id'));

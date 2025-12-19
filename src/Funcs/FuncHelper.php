<?php

/**
 * 助手函数：通用助手函数
 */

/**
 * 生成雪花算法的UUID（全局唯一ID，纯数字）
 *
 * 雪花算法ID结构（48位，更短的长度）：
 * - 1位符号位（固定为0）
 * - 32位时间戳（秒级，从2025-01-01开始，可用约136年）
 * - 3位数据中心ID（0-7，支持8个数据中心）
 * - 3位机器ID（0-7，每个数据中心支持8台机器）
 * - 10位序列号（同一秒内可生成1024个ID）
 *
 * 参数说明：
 *
 * 数据中心ID（datacenterId）：
 * - 作用：用于区分不同的数据中心/机房
 * - 使用场景：多数据中心部署时，每个数据中心分配独立的ID（如：北京=0，上海=1，深圳=2）
 * - 为什么需要：避免跨数据中心的ID冲突，即使时间戳和序列号相同，不同数据中心的ID也不同
 * - 自动生成：如果未指定，基于主机名的CRC32哈希值取模8生成
 * - 环境变量：可通过 SNOWFLAKE_DATACENTER_ID 环境变量设置
 *
 * 机器ID（workerId）：
 * - 作用：用于区分同一数据中心内的不同机器/服务器
 * - 使用场景：同一数据中心内有多台服务器时，每台服务器分配独立的ID（如：服务器1=0，服务器2=1）
 * - 为什么需要：避免同一数据中心内不同机器的ID冲突，即使时间戳和序列号相同，不同机器的ID也不同
 * - 自动生成：如果未指定，基于进程ID取模8生成
 * - 环境变量：可通过 SNOWFLAKE_WORKER_ID 环境变量设置
 *
 * 组合使用示例：
 * - 数据中心0 + 机器0：datacenterId=0, workerId=0
 * - 数据中心0 + 机器1：datacenterId=0, workerId=1
 * - 数据中心1 + 机器0：datacenterId=1, workerId=0
 * - 总容量：8个数据中心 × 8台机器 = 64台机器可以同时生成唯一ID
 *
 * 错误处理：
 * - 遇到时钟回拨或时间早于起始时间时，会自动等待并重试，不会抛出异常
 * - 序列号溢出时会自动等待下一秒继续生成
 *
 * @param int|null $datacenterId 数据中心ID（0-7），用于区分不同的数据中心/机房。默认自动生成（基于主机名）
 * @param int|null $workerId     机器ID（0-7），用于区分同一数据中心内的不同机器。默认自动生成（基于进程ID）
 * @return string 返回纯数字字符串（最大14位）
 * @author siushin<siushin@163.com>
 */
function generateId(?int $datacenterId = null, ?int $workerId = null): string
{
    // 静态变量保存状态
    static $lastTimestamp = 0;
    static $sequence = 0;
    static $datacenterIdStatic = null;
    static $workerIdStatic = null;

    // 初始化数据中心ID和机器ID（如果未提供，则自动生成）
    if ($datacenterIdStatic === null) {
        if ($datacenterId !== null) {
            $datacenterIdStatic = $datacenterId & 0x7; // 确保在0-7范围内
        } else {
            $datacenterIdStatic = (int)(getenv('SNOWFLAKE_DATACENTER_ID') ?: (gethostname() ? crc32(gethostname()) % 8 : 0));
            $datacenterIdStatic = $datacenterIdStatic & 0x7; // 确保在0-7范围内
        }
    }

    if ($workerIdStatic === null) {
        if ($workerId !== null) {
            $workerIdStatic = $workerId & 0x7; // 确保在0-7范围内
        } else {
            $workerIdStatic = (int)(getenv('SNOWFLAKE_WORKER_ID') ?: (getmypid() % 8));
            $workerIdStatic = $workerIdStatic & 0x7; // 确保在0-7范围内
        }
    }

    // 自定义起始时间戳（2025-01-01 00:00:00 UTC）
    $epoch = 1735689600; // 2025-01-01 00:00:00 的秒级时间戳

    // 循环重试，直到成功生成ID
    while (true) {
        // 获取当前秒级时间戳
        $timestamp = (int)time();

        // 处理时间早于epoch的情况：等待时间到达epoch
        if ($timestamp < $epoch) {
            $waitTime = $epoch - $timestamp;
            sleep($waitTime + 1);
            continue; // 重新尝试
        }

        // 计算时间戳差值（相对于epoch）
        $timestampDiff = $timestamp - $epoch;

        // 处理时钟回拨：等待时间追上
        if ($timestamp < $lastTimestamp) {
            $diff = $lastTimestamp - $timestamp;
            sleep($diff + 1);
            continue; // 重新尝试
        }

        // 如果是同一秒内，序列号自增
        if ($timestamp === $lastTimestamp) {
            $sequence = ($sequence + 1) & 0x3FF; // 序列号范围0-1023
            // 如果序列号溢出，等待下一秒
            if ($sequence === 0) {
                // 等待下一秒
                while ($timestamp <= $lastTimestamp) {
                    usleep(100000); // 等待100毫秒
                    $timestamp = (int)time();
                }
                // 重新计算时间戳差值
                $timestampDiff = $timestamp - $epoch;
                $sequence = 0;
            }
        } else {
            // 新的秒，序列号重置为0
            $sequence = 0;
        }

        // 更新最后时间戳
        $lastTimestamp = $timestamp;

        // 组合48位ID
        // 使用位运算组合各部分：32位时间戳 + 3位数据中心ID + 3位机器ID + 10位序列号
        $id = ($timestampDiff << 16) | ($datacenterIdStatic << 13) | ($workerIdStatic << 10) | $sequence;

        // 返回字符串形式的数字（避免PHP整数溢出问题）
        return (string)$id;
    }
}

/**
 * 去除数组中值为空的元素
 *
 * 空的判断规则：
 * - 字符串类型：去除首尾空白字符后，如果为空字符串则视为空
 * - 其他类型：按照 PHP empty() 规则判断
 *
 * @param array $params 待处理的数组
 * @return array 处理后的数组
 * @author siushin<siushin@163.com>
 */
function trimParam(array $params): array
{
    $result = [];

    foreach ($params as $key => $value) {
        // 如果是字符串，先 trim 后判断是否为空
        if (is_string($value)) {
            $trimmedValue = trim($value);
            if ($trimmedValue !== '') {
                $result[$key] = $value;
            }
        } // 其他类型按照 empty() 规则判断
        elseif (!empty($value)) {
            $result[$key] = $value;
        }
    }

    return $result;
}
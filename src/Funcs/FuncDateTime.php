<?php

/**
 * 助手函数：日期时间
 */

/**
 * 获取当前时间、时间戳数组
 * @return array
 * @example
 * getDateTimeArr();
 * // ['time' => 1704067200, 'datetime' => '2025-01-01 00:00:00']
 * @author siushin<siushin@163.com>
 */
function getDateTimeArr(): array
{
    $time = time();
    $datetime = date('Y-m-d H:i:s', $time);
    return compact('time', 'datetime');
}

/**
 * 生成指定区间的所有日期清单
 * @param string $startDate
 * @param string $endDate
 * @param string $sort
 * @return array
 * @example
 * genDateListByRange('2025-01-01', '2025-01-05');
 * // ['2025-01-01', '2025-01-02', '2025-01-03', '2025-01-04', '2025-01-05']
 * genDateListByRange('2025-01-01', '2025-01-05', 'desc');
 * // ['2025-01-05', '2025-01-04', '2025-01-03', '2025-01-02', '2025-01-01']
 * @author siushin<siushin@163.com>
 */
function genDateListByRange(string $startDate, string $endDate, string $sort = 'asc'): array
{
    $startTime = strtotime($startDate);
    $endTime = strtotime($endDate);
    $data = [];
    while ($startTime <= $endTime) {
        $data[] = date('Y-m-d', $startTime);
        $startTime = strtotime('+1 day', $startTime);
    }
    return $sort == 'asc' ? $data : array_reverse($data);
}

/**
 * 验证是否是日期
 * @param $date
 * @return bool
 * @example
 * validateDate('2025-01-01'); // true
 * validateDate('2025-13-01'); // false
 * validateDate('2025/01/01'); // false
 * validateDate('invalid'); // false
 * @author siushin<siushin@163.com>
 */
function validateDate($date): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * 判断是否处于两个时间段当中
 * @param $startAt
 * @param $endAt
 * @return bool
 * @throws Exception
 * @example
 * isCurrentTimeWithinRange('2025-01-01 00:00:00', '2025-12-31 23:59:59');
 * // 如果当前时间在2025年内，返回 true，否则返回 false
 * isCurrentTimeWithinRange('2025-01-01', '2025-01-31');
 * // 判断当前日期是否在1月份
 * @author siushin<siushin@163.com>
 */
function isCurrentTimeWithinRange($startAt, $endAt): bool
{
    $startTime = new DateTime($startAt);
    $endTime = new DateTime($endAt);
    $now = new DateTime();
    return $now >= $startTime && $now <= $endTime;
}

/**
 * 生成日期文件路径
 * @param string $filename
 * @param string $suffix
 * @param bool   $with_timestamp_suffix
 * @return string
 * @example
 * buildDateFilePath('report', 'pdf');
 * // "/202501/report_20250101120000.pdf"
 * buildDateFilePath('log', 'txt', false);
 * // "/202501/log.txt"
 * @author siushin<siushin@163.com>
 */
function buildDateFilePath(string $filename, string $suffix, bool $with_timestamp_suffix = true): string
{
    $time = time();
    $date = date('Ym', $time);
    $array = [
        "/$date/$filename",
        $with_timestamp_suffix ? '_' . date('YmdHis') : '',
        ".$suffix"
    ];
    return implode('', $array);
}
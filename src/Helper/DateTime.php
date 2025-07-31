<?php
declare(strict_types=1);

/**
 * 助手函数：日期时间
 */

/**
 * 获取当前时间、时间戳数组
 * @return array
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
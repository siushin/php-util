<?php

/**
 * 助手函数：日志、调试
 */

/**
 * 打印调试（带 占位符 标识）
 * @param mixed  $data
 * @param string $flag
 * @param string $start_placeholder
 * @param string $end_placeholder
 * @param int    $char_num
 * @return void
 * @example
 * user_dump(['name' => 'Tom', 'age' => 20], 'User Info');
 * // 输出：
 * // <<<<<<<<<<<<<<<< User Info <<<<<<<<<<<<<<<<
 * // array(2) { ["name"]=> string(3) "Tom" ["age"]=> int(20) }
 * // >>>>>>>>>>>>>>>> User Info >>>>>>>>>>>>>>>>
 * user_dump('test', 'DEBUG', '=', '=', 10);
 * // 使用 = 作为占位符，10个字符
 * @author siushin<siushin@163.com>
 */
function user_dump(mixed $data, string $flag = '', string $start_placeholder = '<', string $end_placeholder = '>', int $char_num = 16): void
{
    $start_placeholder = str_repeat($start_placeholder, $char_num);
    $end_placeholder = str_repeat($end_placeholder, $char_num);
    echo "$start_placeholder $flag $start_placeholder" . PHP_EOL;
    var_dump($data);
    echo "$end_placeholder $flag $end_placeholder" . PHP_EOL;
}
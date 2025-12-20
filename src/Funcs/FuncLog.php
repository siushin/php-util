<?php

/**
 * 助手函数：日志、调试
 */

/**
 * 生成新旧数据对比的结构化日志字符串
 * @param array  $oldData        旧数据（任意维度数组）
 * @param array  $newData        新数据（任意维度数组）
 * @param string $prefixTitle    前缀标题（默认：修改数据），有修改时拼接在最前方，使用【】括起来
 * @param array  $keyMapping     键名映射数组，格式：['age' => '年龄', 'user.name' => '用户.姓名']，支持单键和完整路径映射
 * @param string $separator      日志项分隔符（默认分号）
 * @param string $levelSeparator 层级分隔符（默认点号，反映数据结构）
 * @return string 无变更返回空字符串
 *
 * @example
 * // 基本使用
 * $old = ['name' => 'Tom', 'age' => 30];
 * $new = ['name' => 'Tom', 'age' => 31];
 * generateDataChangeLog($old, $new);
 * // 输出：【修改数据】age: 30→31
 *
 * @example
 * // 使用键名映射
 * $old = ['age' => 30, 'status' => 1];
 * $new = ['age' => 31, 'status' => 2];
 * $mapping = ['age' => '年龄', 'status' => '状态'];
 * generateDataChangeLog($old, $new, '修改数据', $mapping);
 * // 输出：【修改数据】年龄: 30→31；状态: 1→2
 *
 * @example
 * // 多层嵌套数组
 * $old = ['user' => ['name' => 'Tom', 'age' => 30]];
 * $new = ['user' => ['name' => 'Jerry', 'age' => 30]];
 * $mapping = ['user' => '用户', 'name' => '姓名'];
 * generateDataChangeLog($old, $new, '用户信息变更', $mapping);
 * // 输出：【用户信息变更】用户.姓名: Tom→Jerry
 *
 * @example
 * // 自定义分隔符
 * $old = ['age' => 30, 'score' => 80];
 * $new = ['age' => 31, 'score' => 85];
 * generateDataChangeLog($old, $new, '修改数据', [], ' | ', '.');
 * // 输出：【修改数据】age: 30→31 | score: 80→85
 *
 * @example
 * // 无变更情况
 * $old = ['age' => 30];
 * $new = ['age' => 30];
 * generateDataChangeLog($old, $new);
 * // 输出：''（空字符串）
 *
 * @author siushin<siushin@163.com>
 */
function generateDataChangeLog(array $oldData, array $newData, string $prefixTitle = '修改数据', array $keyMapping = [], string $separator = '；', string $levelSeparator = '.'): string
{
    $changeItems = [];

    // 1. 精准对比值（递归比较数组，避免全局函数污染）
    $isEqual = function ($a, $b) use (&$isEqual) {
        // 类型不同，直接不相等
        if (gettype($a) !== gettype($b)) {
            return false;
        }
        // 非数组类型，直接比较
        if (!is_array($a)) {
            return $a === $b;
        }
        // 数组类型，递归比较
        if (count($a) !== count($b)) {
            return false;
        }
        foreach ($a as $key => $value) {
            if (!array_key_exists($key, $b)) {
                return false;
            }
            if (!$isEqual($value, $b[$key])) {
                return false;
            }
        }
        return true;
    };

    // 2. 格式化值展示
    $formatValue = function ($value) {
        if ($value === null) return '空值';
        if (is_bool($value)) return $value ? '是' : '否';
        if (is_array($value)) return json_encode($value, JSON_UNESCAPED_UNICODE);
        return (string)$value;
    };

    // 3. 获取映射后的键名
    $getMappedKey = function ($keyPath) use ($keyMapping, $levelSeparator) {
        // 如果完整路径在映射中，直接返回（去除前后空格）
        if (isset($keyMapping[$keyPath])) {
            return trim($keyMapping[$keyPath]);
        }
        // 如果键路径为空，返回空字符串
        if (empty($keyPath)) {
            return '';
        }
        // 按层级分隔符拆分，对每个键进行映射
        $keys = explode($levelSeparator, $keyPath);
        $mappedKeys = array_map(function ($key) use ($keyMapping) {
            $mapped = $keyMapping[$key] ?? $key;
            return trim($mapped); // 去除映射值的前后空格
        }, $keys);
        // 重新拼接
        return implode($levelSeparator, $mappedKeys);
    };

    // 4. 递归解析数据变更（内部闭包，避免全局函数污染）
    $parseChange = function ($parentKey, $oldValue, $newValue) use (&$parseChange, &$changeItems, &$isEqual, &$formatValue, &$getMappedKey, $levelSeparator) {
        // 精准对比值，无变化则跳过
        if ($isEqual($oldValue, $newValue)) {
            return;
        }

        // 处理叶子节点（非数组值）
        if (!is_array($oldValue) && !is_array($newValue)) {
            $fullKey = $parentKey ? $getMappedKey($parentKey) : '根节点';
            $changeItems[] = sprintf(
                '%s: %s→%s',
                $fullKey,
                $formatValue($oldValue),
                $formatValue($newValue)
            );
            return;
        }

        // 处理混合情况：一个是数组，另一个不是
        if (is_array($oldValue) !== is_array($newValue)) {
            $fullKey = $parentKey ? $getMappedKey($parentKey) : '根节点';
            $changeItems[] = sprintf(
                '%s: %s→%s',
                $fullKey,
                $formatValue($oldValue),
                $formatValue($newValue)
            );
            return;
        }

        // 统一转为数组，遍历所有键（保留层级）
        $oldArr = is_array($oldValue) ? $oldValue : [];
        $newArr = is_array($newValue) ? $newValue : [];
        $allKeys = array_unique(array_merge(array_keys($oldArr), array_keys($newArr)));

        foreach ($allKeys as $key) {
            $subOld = $oldArr[$key] ?? null;
            $subNew = $newArr[$key] ?? null;
            $currentKey = $parentKey ? $parentKey . $levelSeparator . $key : $key;
            $parseChange($currentKey, $subOld, $subNew); // 递归解析子层级
        }
    };

    // 启动递归解析
    $parseChange('', $oldData, $newData);

    // 拼接为纯字符串返回
    if (empty($changeItems)) {
        return '';
    }

    $result = implode($separator, $changeItems);
    // 如果有修改，在前面加上前缀标题（无空格，包括全角空格）
    $result = preg_replace('/^[\s　]+/u', '', $result); // 去除开头的所有空白字符（包括全角空格）
    return '【' . $prefixTitle . '】' . $result;
}

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
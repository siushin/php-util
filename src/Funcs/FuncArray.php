<?php

/**
 * 助手函数：数组
 */

/**
 * 索引数组 转 字符串（拼接）
 * @param array  $array     数组
 * @param string $joiner    连接符
 * @param string $separator 分隔符
 * @return string
 * @example
 * $arr = ['name' => 'Tom', 'age' => 20];
 * array_to_string_chain($arr); // "name:Tom,age:20"
 * array_to_string_chain($arr, '=', '&'); // "name=Tom&age=20"
 * @author siushin<siushin@163.com>
 */
function array_to_string_chain(array $array, string $joiner = ':', string $separator = ','): string
{
    return implode($separator, array_map(fn($key, $value) => $key . $joiner . $value, array_keys($array), $array));
}

/**
 * 枚举数组 转 字符串（拼接）
 * @param array  $array     枚举数组
 * @param string $joiner    连接符
 * @param string $separator 分隔符
 * @return string
 * @example
 * enum Status { case ACTIVE; case INACTIVE; }
 * $enums = [Status::ACTIVE, Status::INACTIVE];
 * enum_to_string_chain($enums); // "ACTIVE:ACTIVE,INACTIVE:INACTIVE"
 * @author siushin<siushin@163.com>
 */
function enum_to_string_chain(array $array, string $joiner = ':', string $separator = ','): string
{
    return implode($separator, array_map(fn($item) => $item->name . $joiner . $item->value, $array));
}

/**
 * 枚举转数组
 * @param array  $enum
 * @param string $response_type
 * @return array
 * @example
 * enum Status { case ACTIVE; case INACTIVE; }
 * $enums = [Status::ACTIVE, Status::INACTIVE];
 * enum_to_array($enums, 'object'); // ['ACTIVE' => 'ACTIVE', 'INACTIVE' => 'INACTIVE']
 * enum_to_array($enums, 'array'); // [['name' => 'ACTIVE', 'value' => 'ACTIVE'], ...]
 * @author siushin<siushin@163.com>
 */
function enum_to_array(array $enum, string $response_type = 'object'): array
{
    $array = [];
    foreach ($enum as $item) {
        match ($response_type) {
            'object' => $array[$item->name] = $item->value,
            'array' => $array[] = ['name' => $item->name, 'value' => $item->value],
            default => null
        };
    }
    return $array;
}

/**
 * 数组差集
 * @param array $array
 * @param array $arrays
 * @return array
 * @example
 * $arr1 = [1, 2, 3, 4, 5];
 * $arr2 = [2, 4];
 * user_array_diff($arr1, $arr2); // [1, 3, 5]
 * @author siushin<siushin@163.com>
 */
function user_array_diff(array $array, array $arrays): array
{
    return array_values(array_diff($array, $arrays));
}

/**
 * 返回数组指定列的值
 * @param array           $array
 * @param string|int|null $column_key
 * @param string|int|null $index_key
 * @return array
 * @example
 * $users = [
 *     ['id' => 1, 'name' => 'Tom', 'age' => 20],
 *     ['id' => 2, 'name' => 'Jerry', 'age' => 25],
 *     ['id' => 3, 'name' => 'Tom', 'age' => 30]
 * ];
 * user_array_column_unique($users, 'name'); // ['Jerry', 'Tom']
 * user_array_column_unique($users, 'age'); // [20, 25, 30]
 * @author siushin<siushin@163.com>
 */
function user_array_column_unique(array $array, string|int|null $column_key, string|int|null $index_key = null): array
{
    $data = array_values(array_unique(array_column($array, $column_key, $index_key)));
    sort($data);
    return $data;
}

/**
 * 关联数组指定key位置之后插入数组
 * @param array       $array
 * @param array       $data
 * @param string|null $key
 * @return array
 * @example
 * $arr = ['a' => 1, 'b' => 2, 'c' => 3];
 * $new = ['x' => 10, 'y' => 20];
 * array_push_by_key($arr, $new, 'b');
 * // ['a' => 1, 'b' => 2, 'x' => 10, 'y' => 20, 'c' => 3]
 * array_push_by_key($arr, $new); // ['a' => 1, 'b' => 2, 'c' => 3, 'x' => 10, 'y' => 20]
 * @author siushin<siushin@163.com>
 */
function array_push_by_key(array $array, array $data = [], string $key = null): array
{
    $offset = $key ? array_search($key, array_keys($array)) : false;
    return ($offset !== false) ?
        array_merge(array_slice($array, 0, $offset + 1), $data, array_slice($array, $offset + 1)) :
        array_merge($array, $data);
}

/**
 * 获取指定元素的数组
 * @param array $array
 * @param array $get_keys
 * @return array
 * @example
 * $arr = ['id' => 1, 'name' => 'Tom', 'age' => 20, 'email' => 'tom@example.com'];
 * user_get_fields_array($arr, ['name', 'age']); // ['name' => 'Tom', 'age' => 20]
 * @author siushin<siushin@163.com>
 */
function user_get_fields_array(array $array, array $get_keys): array
{
    return array_intersect_key($array, array_flip($get_keys));
}

/**
 * 过滤数组元素
 * @param array $array
 * @param array $remove_keys
 * @return array
 * @example
 * $arr = ['id' => 1, 'name' => 'Tom', 'age' => 20, 'password' => '123456'];
 * user_filter_array($arr, ['password', 'age']); // ['id' => 1, 'name' => 'Tom']
 * @author siushin<siushin@163.com>
 */
function user_filter_array(array $array, array $remove_keys): array
{
    return array_diff_key($array, array_flip($remove_keys));
}

/**
 * 过滤二维数组里的每个子数组的数组元素
 * @param array $array2
 * @param array $remove_keys
 * @return array
 * @example
 * $arr2 = [
 *     ['id' => 1, 'name' => 'Tom', 'password' => '123'],
 *     ['id' => 2, 'name' => 'Jerry', 'password' => '456']
 * ];
 * user_filter_array2($arr2, ['password']);
 * // [['id' => 1, 'name' => 'Tom'], ['id' => 2, 'name' => 'Jerry']]
 * @author siushin<siushin@163.com>
 */
function user_filter_array2(array $array2, array $remove_keys): array
{
    foreach ($array2 as &$array) {
        $array = user_filter_array($array, $remove_keys);
    }
    return $array2;
}

/**
 * 比较两个数组
 * @param array $newData
 * @param array $oldData
 * @return array
 * @example
 * $new = ['id' => 1, 'name' => 'Tom', 'age' => 25];
 * $old = ['id' => 1, 'name' => 'Tom', 'age' => 20];
 * compareArray($new, $old); // ['new' => ['age' => 25], 'old' => ['age' => 20]]
 * compareArray($new, $new); // []
 * @author siushin<siushin@163.com>
 */
function compareArray(array $newData, array $oldData): array
{
    $new = [];
    $old = [];
    foreach ($newData as $key => $value) {
        if (isset($oldData[$key]) && $oldData[$key] != $value) {
            $new[$key] = $value;
            $old[$key] = $oldData[$key];
        }
    }
    return $new && $old ? compact('new', 'old') : [];
}
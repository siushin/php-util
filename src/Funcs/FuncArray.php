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
 *
 * @example
 * $arr = ['name' => 'Tom', 'age' => 20];
 * array_to_string_chain($arr); // "name:Tom,age:20"
 * array_to_string_chain($arr, '=', '&'); // "name=Tom&age=20"
 *
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
 *
 * @example
 * enum Status { case ACTIVE; case INACTIVE; }
 * $enums = [Status::ACTIVE, Status::INACTIVE];
 * enum_to_string_chain($enums); // "ACTIVE:ACTIVE,INACTIVE:INACTIVE"
 *
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
 *
 * @example
 * enum Status { case ACTIVE; case INACTIVE; }
 * $enums = [Status::ACTIVE, Status::INACTIVE];
 * enum_to_array($enums, 'object'); // ['ACTIVE' => 'ACTIVE', 'INACTIVE' => 'INACTIVE']
 * enum_to_array($enums, 'array'); // [['name' => 'ACTIVE', 'value' => 'ACTIVE'], ...]
 *
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
 *
 * @example
 * $arr1 = [1, 2, 3, 4, 5];
 * $arr2 = [2, 4];
 * user_array_diff($arr1, $arr2); // [1, 3, 5]
 *
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
 *
 * @example
 * $users = [
 *     ['id' => 1, 'name' => 'Tom', 'age' => 20],
 *     ['id' => 2, 'name' => 'Jerry', 'age' => 25],
 *     ['id' => 3, 'name' => 'Tom', 'age' => 30]
 * ];
 * user_array_column_unique($users, 'name'); // ['Jerry', 'Tom']
 * user_array_column_unique($users, 'age'); // [20, 25, 30]
 *
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
 *
 * @example
 * $arr = ['a' => 1, 'b' => 2, 'c' => 3];
 * $new = ['x' => 10, 'y' => 20];
 * array_push_by_key($arr, $new, 'b');
 * // ['a' => 1, 'b' => 2, 'x' => 10, 'y' => 20, 'c' => 3]
 * array_push_by_key($arr, $new); // ['a' => 1, 'b' => 2, 'c' => 3, 'x' => 10, 'y' => 20]
 *
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
 *
 * @example
 * $arr = ['id' => 1, 'name' => 'Tom', 'age' => 20, 'email' => 'tom@example.com'];
 * user_get_fields_array($arr, ['name', 'age']); // ['name' => 'Tom', 'age' => 20]
 *
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
 *
 * @example
 * $arr = ['id' => 1, 'name' => 'Tom', 'age' => 20, 'password' => '123456'];
 * user_filter_array($arr, ['password', 'age']); // ['id' => 1, 'name' => 'Tom']
 *
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
 *
 * @example
 * $arr2 = [
 *     ['id' => 1, 'name' => 'Tom', 'password' => '123'],
 *     ['id' => 2, 'name' => 'Jerry', 'password' => '456']
 * ];
 * user_filter_array2($arr2, ['password']);
 * // [['id' => 1, 'name' => 'Tom'], ['id' => 2, 'name' => 'Jerry']]
 *
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
 *
 * @example
 * $new = ['id' => 1, 'name' => 'Tom', 'age' => 25];
 * $old = ['id' => 1, 'name' => 'Tom', 'age' => 20];
 * compareArray($new, $old); // ['new' => ['age' => 25], 'old' => ['age' => 20]]
 * compareArray($new, $new); // []
 *
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

/**
 * 比较新旧数组数据，返回新增、更新、删除三个分组数据
 * @param array  $newData    新数组数据
 * @param array  $oldData    旧数组数据（必须包含主键ID）
 * @param string $primaryKey 主键ID字段名
 * @return array 返回包含 'add'、'update'、'delete' 三个键的数组
 *
 * @example
 * $newData = [
 *     ['name' => 'Tom', 'age' => 25],           // 新增（无ID）
 *     ['id' => 1, 'name' => 'Jerry', 'age' => 30], // 更新（有ID且存在）
 *     ['id' => 2, 'name' => 'Bob', 'age' => 20]    // 更新（有ID且存在）
 * ];
 * $oldData = [
 *     ['id' => 1, 'name' => 'Jerry', 'age' => 25], // 旧数据
 *     ['id' => 2, 'name' => 'Bob', 'age' => 18],   // 旧数据
 *     ['id' => 3, 'name' => 'Alice', 'age' => 22]  // 将被删除
 * ];
 * compareArrayDiff($newData, $oldData, 'id');
 * [
 *   'add' => [['id' => '生成的ID', 'name' => 'Tom', 'age' => 25]],
 *   'update' => [
 *     ['id' => 1, 'name' => 'Jerry', 'age' => 30],
 *     ['id' => 2, 'name' => 'Bob', 'age' => 20]
 *   ],
 *   'delete' => [3]
 * ]
 *
 * @author siushin<siushin@163.com>
 */
function compareDbDataDiff(array $newData, array $oldData, string $primaryKey = 'id'): array
{
    // 将旧数据按主键索引
    $oldDataMap = [];
    foreach ($oldData as $item) {
        if (isset($item[$primaryKey])) {
            $oldDataMap[$item[$primaryKey]] = $item;
        }
    }

    // 初始化结果数组
    $added = [];
    $updated = [];
    $newDataIds = [];

    // 遍历新数据
    foreach ($newData as $item) {
        // 检查是否有主键ID
        if (!empty($item[$primaryKey])) {
            $id = $item[$primaryKey];
            $newDataIds[] = $id;

            // 如果旧数据中存在该ID，检查是否需要更新
            if (isset($oldDataMap[$id])) {
                // 比较内容是否不同（排除主键比较）
                $oldItem = $oldDataMap[$id];
                $oldItemWithoutKey = $oldItem;
                $newItemWithoutKey = $item;
                unset($oldItemWithoutKey[$primaryKey], $newItemWithoutKey[$primaryKey]);

                if ($oldItemWithoutKey != $newItemWithoutKey) {
                    $updated[] = $item;
                }
            } else {
                // 新数据中有ID但旧数据中不存在，视为新增
                $added[] = $item;
            }
        } else {
            // 新数据中没有主键ID，视为新增，自动生成ID
            $item[$primaryKey] = generateId();
            $added[] = $item;
        }
    }

    // 找出删除的数据（旧数据中有，新数据中没有的）
    $deleted = [];
    foreach ($oldDataMap as $id => $item) {
        if (!in_array($id, $newDataIds)) {
            $deleted[] = $id;
        }
    }

    return [
        'add'    => $added,
        'update' => $updated,
        'delete' => $deleted
    ];
}
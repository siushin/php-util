<?php

namespace Siushin\Util\Traits;

use Exception;

/**
 * 工具类：常用Param参数处理助手函数
 */
trait ParamTool
{
    /**
     * 获取整型参数值
     * @param array  $params
     * @param string $field
     * @param int    $default
     * @return int
     * @author siushin<siushin@163.com>
     */
    public static function getIntValue(array $params, string $field, int $default = 0): int
    {
        if (array_key_exists($field, $params)) {
            $value = $params[$field];
            if (filter_var($value, FILTER_VALIDATE_INT) !== false) {
                return intval($value);
            }
        }
        return $default;
    }

    /**
     * 获取整数（没有返回null）
     * @param array  $params
     * @param string $param_key
     * @param bool   $throw_exp
     * @return int|null
     * @throws Exception
     * @author siushin<siushin@163.com>
     */
    public static function getIntValOrNull(array $params, string $param_key, bool $throw_exp = true): ?int
    {
        $data = null;
        if (!empty($params[$param_key]) && preg_match("/^\d*$/", (string)$params[$param_key])) {
            $data = intval($params[$param_key]);
        } elseif (!empty($params[$param_key]) && $throw_exp) {
            throw_exception("参数有误：$param_key");
        }
        return $data;
    }

    /**
     * 获取请求参数
     * @param array       $params
     * @param string      $param_key
     * @param mixed|null  $default
     * @param string      $exp_type
     * @param string|null $param_type
     * @return mixed
     * @throws Exception
     * @author siushin<siushin@163.com>
     */
    public static function getQueryParam(array $params, string $param_key, mixed $default = null, string $exp_type = '', string $param_type = null): mixed
    {
        $result = $default;
        switch ($exp_type) {
            case '-':
            case '_':
            case ',':
                $param = trim($params[$param_key] ?? '', $exp_type);
                $param && $result = explode($exp_type, $param);
                // 逗号,切割，默认正整数检测，并转换数组的各个切割后的值为对应的值类型
                $param_type = $param_type ?: 'int';
                foreach ($result as &$item) {
                    if ($param_type == 'int') {
                        if (!preg_match('/^[0-9]\d*$/', $item)) {
                            throw_exception("参数有误：$param_key");
                        }
                        $item = (int)$item;
                    }
                }
                break;
            case '@':
            case '&':
                // 将 前端 拼接符 @ 转回 &
                $param = str_replace('@', '&', trim($params[$param_key] ?? '', $exp_type));
                // 如果是xx=vv&xx=vv，则二次切割，返回键值对数组
                // 匹配保留包含点号的键值对格式，如 name.id=1&name.name=2，将转换成 ["name.id" => 1, "name.name" => 2]
                $pattern = '/^([\w\-.%]+=[\w\-.%]+(&?))+$/';
                if (preg_match($pattern, $param)) {
                    $result = [];
                    // 先按 & 分割成多个键值对
                    $pairs = explode('&', $param);
                    foreach ($pairs as $pair) {
                        // 再按 = 分割键和值
                        list($key, $value) = explode('=', $pair, 2);
                        $result[$key] = $value;
                    }
                    break;
                }
                $param && $result = explode($exp_type, $param);
                break;
            default:
                $result = $params[$param_key] ?? $result;
                break;
        }
        return $result;
    }

    /**
     * 批量获取请求参数（没有，则返回默认值，一般是null）
     * @param array $params
     * @param array $param_keys
     * @return array
     * @throws Exception
     * @author siushin<siushin@163.com>
     */
    public static function getQueryParams(array $params, array $param_keys): array
    {
        $result = [];
        foreach ($param_keys as $key => $value) {
            // 如果是字符串，则取value数组内容
            if (is_string($key)) {
                $default = $value[0] ?? null;
                $param_type = $value[1] ?? '';
                $result[$key] = self::getQueryParam($params, $key, $default, $param_type);
            } // 是整型，直接取value作为key值，其它默认
            elseif (is_int($key)) {
                $result[$value] = self::getQueryParam($params, $value);
            }
        }
        return $result;
    }

    /**
     * 检测空参数
     * @param array $params           参数
     * @param array $check_fields     检测是否被定义
     * @param bool  $strict_check     是否严格检测：true：有定义且有值，false：有定义
     * @param array $only_empty_check 指定空检测字段
     * @return void
     * @throws Exception
     * @author siushin<siushin@163.com>
     */
    public static function checkEmptyParam(array $params, array $check_fields, bool $strict_check = true, array $only_empty_check = []): void
    {
        // 是否严格模式：true全部检测为空，false不检测为空，存在key即可
        $is_strict_check_empty = $params['is_strict_check_empty'] ?? $strict_check;
        // 只检测字段
        $check_fields = $params['check_fields'] ?? $check_fields;
        $only_empty_check = $params['only_empty_check'] ?? $only_empty_check;
        foreach ($check_fields as $field) {
            $only_empty_check && ($is_strict_check_empty = in_array($field, $only_empty_check));
            if (!array_key_exists($field, $params)) {
                throw_exception("缺少{$field}参数值");
            }
            if ($is_strict_check_empty) {
                $value = $params[$field];
                // 如果是数组，判断是否为空数组
                if (is_array($value)) {
                    if (empty($value)) {
                        throw_exception("缺少{$field}参数值");
                    }
                } else {
                    // 非数组，按原逻辑判断是否为空字符串
                    if (!strlen((string)$value)) {
                        throw_exception("缺少{$field}参数值");
                    }
                }
            }
        }
    }

    /**
     * 获取字段数组（不存在字段，赋初始值）
     * @param array $array
     * @param array $fields
     * @param mixed $default
     * @return array
     * @author siushin<siushin@163.com>
     */
    public static function getFieldsWithDefault(array $array, array $fields, mixed $default = null): array
    {
        foreach ($fields as $field) {
            $array[$field] = $array[$field] ?? $default;
        }
        return $array;
    }

    /**
     * 从原始数组中筛选出指定的键值对
     *
     * 通过键名白名单的方式，仅保留数组中指定的键值对。适用于：
     * - API响应字段过滤
     * - 数据脱敏处理
     * - 数据库查询结果字段筛选
     *
     * @param array $data 原始数据数组（关联数组或索引数组）
     * @param array $keys 需要保留的键名列表（数组键名白名单）
     *                    - 如果键不存在于原数组，则会被忽略
     *                    - 支持字符串键名和数字索引
     *
     * @return array 过滤后的新数组，仅包含$keys中存在的键名对应的值
     *              - 保留原始数组中的键值顺序
     *              - 不会修改原始数组
     *
     * @example
     *   getArrayByKeys(
     *     ['id' => 1, 'name' => 'Tom', 'age' => 20],
     *     ['name', 'gender']  // 'gender'不存在会被忽略
     *   );
     *   // 返回：['name' => 'Tom']
     *
     * @author siushin<siushin@163.com>
     */
    public static function getArrayByKeys(array $data, array $keys): array
    {
        return array_intersect_key($data, array_flip($keys));
    }

    /**
     * 从数组中移除指定的值（直接修改原数组）
     * @param array &$array     要处理的原始数组（引用传递）
     * @param array  $keys      只在指定的键中检查（空数组表示检查所有键）
     * @param array  $values    需要移除的值列表（默认移除：'', null, 'null'）
     * @param bool   $recursive 是否递归处理嵌套数组（默认false）
     * @param bool   $strict    是否严格比较键（默认false）。值的比较始终使用严格比较（===）以避免将0误判为空字符串
     * @return array 处理后的数组（同时会修改原数组）
     * @author siushin<siushin@163.com>
     */
    public static function trimValueArray(
        array &$array,
        array $keys = [],
        array $values = ['', null, 'null'],
        bool  $recursive = false,
        bool  $strict = false
    ): array
    {
        foreach ($array as $key => $item) {
            // 处理嵌套数组（仅在递归模式下）
            if (is_array($item) && $recursive) {
                $array[$key] = self::trimValueArray($item, $keys, $values, true, $strict);
                continue;
            }

            // 检查是否需要处理当前键
            $shouldProcess = empty($keys) || in_array($key, $keys, $strict);

            // 如果值在移除列表中，则移除该键值对
            // 使用严格比较（===）避免将0误判为空字符串等
            if ($shouldProcess) {
                foreach ($values as $removeValue) {
                    if ($item === $removeValue) {
                        unset($array[$key]);
                        break;
                    }
                }
            }
        }

        return $array;
    }
}
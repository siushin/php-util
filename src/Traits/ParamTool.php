<?php
declare(strict_types=1);

namespace Siushin\Util\Traits;

use Exception;

/**
 * 工具类：参数
 */
trait ParamTool
{
    /**
     * 获取整型参数值
     * @param array  $params
     * @param string $field
     * @param int    $default
     * @return int
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
                        !preg_match('/^[0-9]\d*$/', $item) && throw_exception("参数有误：$param_key");
                        $item = (int)$item;
                    }
                }
                break;
            case '@':
            case '&':
                // 将 前端 拼接符 @ 转回 &
                $param = str_replace('@', '&', trim($params[$param_key] ?? '', $exp_type));
                // 如果是xx=vv&xx=vv，则二次切割，返回键值对数组
                $pattern = '/^([\w\-.%]+=[\w\-.%]+(&?))+$/';
                if (preg_match($pattern, $param)) {
                    parse_str($param, $result);
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
            if (
                !array_key_exists($field, $params) ||
                ($is_strict_check_empty && !strlen((string)$params[$field]))
            ) {
                throw_exception("缺少{$field}参数值");
            }
        }
    }

    /**
     * 获取字段数组（不存在字段，赋初始值）
     * @param array $array
     * @param array $fields
     * @param mixed $default
     * @return array
     */
    public static function getFieldsWithDefault(array $array, array $fields, mixed $default = null): array
    {
        foreach ($fields as $field) {
            $array[$field] = $array[$field] ?? $default;
        }
        return $array;
    }

    /**
     * 筛选字段数组
     * @param array $data
     * @param array $fields
     * @return array
     */
    public static function filterFieldArray(array $data, array $fields): array
    {
        return array_intersect_key($data, array_flip($fields));
    }
}
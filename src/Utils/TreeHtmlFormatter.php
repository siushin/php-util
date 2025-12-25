<?php

namespace Siushin\Util\Utils;

/**
 * 树数据HTML格式化工具类
 * 用于将树形结构数据转换为带层级占位符的HTML格式数据
 *
 * 使用示例：
 *
 * // 示例1：基本使用（使用默认配置）
 * $treeData = [
 *     [
 *         'id' => 1,
 *         'title' => '中国',
 *         'children' => [
 *             [
 *                 'id' => 2,
 *                 'title' => '广东省',
 *                 'children' => [
 *                     ['id' => 3, 'title' => '深圳市', 'children' => []]
 *                 ]
 *             ]
 *         ]
 *     ]
 * ];
 * $formatter = new TreeHtmlFormatter();
 * $result = $formatter->format($treeData);
 * // 返回：
 * // [
 * //     ['id' => 1, 'title' => '中国'],
 * //     ['id' => 2, 'title' => '├─ 广东省'],
 * //     ['id' => 3, 'title' => '│  └─ 深圳市']
 * // ]
 *
 * // 示例2：自定义字段名和返回字段
 * $formatter = new TreeHtmlFormatter([
 *     'id_field'       => 'organization_id',
 *     'title_field'    => 'organization_name',
 *     'children_field' => 'children',
 *     'output_title'   => 'organization_name',
 *     'fields'         => ['organization_id', 'organization_name'],
 * ]);
 * $result = $formatter->format($treeData);
 * // 返回：
 * // [
 * //     ['organization_id' => 1, 'organization_name' => '中国'],
 * //     ['organization_id' => 2, 'organization_name' => '├─ 广东省'],
 * //     ['organization_id' => 3, 'organization_name' => '│  └─ 深圳市']
 * // ]
 *
 * // 示例3：使用静态方法快速格式化
 * $result = TreeHtmlFormatter::formatTree($treeData, [
 *     'title_field' => 'name',
 *     'fields'      => ['id', 'name'],
 * ]);
 *
 * @author siushin<siushin@163.com>
 */
class TreeHtmlFormatter
{
    /**
     * 默认配置
     */
    private const array DEFAULT_CONFIG = [
        'id_field'       => 'id',           // ID字段名
        'title_field'    => 'title',        // 标题字段名
        'children_field' => 'children',     // 子节点字段名
        'output_title'   => 'title',        // 输出标题字段名
        'indent_prefix'  => '│  ',          // 缩进前缀
        'branch_middle'  => '├─ ',          // 中间分支符号
        'branch_last'    => '└─ ',          // 最后分支符号
        'fields'         => null,           // 指定返回的字段列表，null表示返回所有字段
    ];

    /**
     * 配置项
     */
    private array $config;

    /**
     * 构造函数
     *
     * @param array $config 配置项，支持以下字段：
     *                      - id_field: ID字段名，默认 'id'
     *                      - title_field: 标题字段名，默认 'title'
     *                      - children_field: 子节点字段名，默认 'children'
     *                      - output_title: 输出标题字段名，默认 'title'
     *                      - indent_prefix: 缩进前缀，默认 '│  '
     *                      - branch_middle: 中间分支符号，默认 '├─ '
     *                      - branch_last: 最后分支符号，默认 '└─ '
     *                      - fields: 指定返回的字段列表（数组），null表示返回所有字段，默认 null
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge(self::DEFAULT_CONFIG, $config);
    }

    /**
     * 将树形数据转换为HTML格式的扁平数组
     *
     * @param array $treeData 树形数据数组
     * @return array 扁平化的数组，每个元素包含原始数据和带占位符的标题
     * @author siushin<siushin@163.com>
     */
    public function format(array $treeData): array
    {
        $result = [];
        $this->traverseTree($treeData, $result);
        return $result;
    }

    /**
     * 递归遍历树形数据
     *
     * @param array   $treeData 树形数据
     * @param array  &$result   结果数组（引用传递）
     * @param string  $prefix   当前层级的前缀
     * @param bool    $isRoot   是否为根层级（用于判断是否添加分支符号）
     * @author siushin<siushin@163.com>
     */
    private function traverseTree(array $treeData, array &$result, string $prefix = '', bool $isRoot = true): void
    {
        $count = count($treeData);

        foreach ($treeData as $index => $item) {
            $isLastItem = ($index === $count - 1);
            $currentPrefix = $prefix;

            // 如果不是根层级，添加分支符号
            if (!$isRoot) {
                $currentPrefix .= $isLastItem ? $this->config['branch_last'] : $this->config['branch_middle'];
            }

            // 获取标题字段的值
            $titleField = $this->config['title_field'];
            $originalTitle = $item[$titleField] ?? '';

            // 创建新的数据项，包含原始数据（但移除children字段，因为这是扁平化列表）
            $formattedItem = $item;
            $childrenField = $this->config['children_field'];

            // 移除children字段，因为扁平化列表不需要树形结构
            unset($formattedItem[$childrenField]);

            // 设置格式化后的标题
            $formattedItem[$this->config['output_title']] = $currentPrefix . $originalTitle;

            // 如果指定了字段列表，只保留指定的字段
            $fields = $this->config['fields'];
            if (is_array($fields) && !empty($fields)) {
                // 确保 output_title 字段始终包含在返回的字段中
                $outputTitle = $this->config['output_title'];
                if (!in_array($outputTitle, $fields)) {
                    $fields[] = $outputTitle;
                }

                // 只保留指定的字段
                $formattedItem = array_intersect_key($formattedItem, array_flip($fields));
            }

            // 添加到结果数组
            $result[] = $formattedItem;

            // 如果有子节点，递归处理
            if (is_array($item[$childrenField]) && !empty($item[$childrenField])) {
                // 计算下一层的前缀
                $nextPrefix = $prefix;
                if (!$isRoot) {
                    // 如果不是根层级，需要添加缩进前缀
                    $nextPrefix .= $isLastItem ? '   ' : $this->config['indent_prefix'];
                }

                // 递归处理子节点（子节点不再是根层级）
                $this->traverseTree($item[$childrenField], $result, $nextPrefix, false);
            }
        }
    }

    /**
     * 静态方法：快速格式化树形数据
     *
     * @param array $treeData 树形数据数组
     * @param array $config   配置项（可选）
     * @return array 格式化后的扁平数组
     * @author siushin<siushin@163.com>
     */
    public static function formatTree(array $treeData, array $config = []): array
    {
        $formatter = new self($config);
        return $formatter->format($treeData);
    }
}
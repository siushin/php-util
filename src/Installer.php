<?php

namespace Siushin\Util;

class Installer
{
    /**
     * composer命令运行安装钩子
     * @return void
     * @author siushin<siushin@163.com>
     */
    public static function postInstall(): void
    {
        $packageName = self::getPackageName();
        $authors = self::getPackageAuthors();

        $startTag = str_repeat('*', 64);
        echo "\n\033[32m$startTag\033[0m";
        echo "\n\033[32m✨ \033[1m$packageName 安装成功！\033[0m\033[32m ✨\033[0m\n";
        echo "\n\033[33m📦 温馨提示：\033[0m\n";
        echo "\t\033[36m• 感谢使用 {$packageName} 扩展包\033[0m\n";
        echo "\t\033[36m• 如有问题请参考文档或联系作者\033[0m\n";

        if (!empty($authors)) {
            echo "\n\033[34m🖋️ 作者信息：\033[0m\n";
            $author = $authors[0]; // 直接取第一个作者
            echo "\t\033[90m姓名：\033[0m " . ($author['name'] ?? '') . "\n";

            if (!empty($author['email'])) {
                echo "\t\033[90m邮箱：\033[0m \033[4m{$author['email']}\033[0m\n";
            }

            if (!empty($author['homepage'])) {
                echo "\t\033[90m网址：\033[0m \033[4;94m{$author['homepage']}\033[0m\n";
            }

            if (!empty($author['role'])) {
                echo "\t\033[90m角色：\033[0m {$author['role']}\n";
            }
        }

        echo "\n\033[32m🚀 祝您使用愉快！\033[0m";
        echo "\n\033[32m$startTag\033[0m\n\n";
    }

    /**
     * 获取包名
     * @return string
     * @author siushin<siushin@163.com>
     */
    private static function getPackageName(): string
    {
        if (file_exists($file = __DIR__ . '/../composer.json')) {
            $data = json_decode(file_get_contents($file), true);
            return $data['name'] ?? '';
        }
        return '';
    }

    /**
     * 获取作者信息
     * @return array
     * @author siushin<siushin@163.com>
     */
    private static function getPackageAuthors(): array
    {
        if (file_exists($file = __DIR__ . '/../composer.json')) {
            $data = json_decode(file_get_contents($file), true);
            return $data['authors'] ?? [];
        }
        return [];
    }
}
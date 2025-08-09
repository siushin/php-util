<?php

namespace Siushin\Util;

class Installer
{
    /**
     * 检测当前包版本
     * @return void
     * @author siushin<siushin@163.com>
     */
    public static function postInstall(): void
    {
        $version = self::detectVersion();

        if ($version === 'dev-main') {
            echo "\n\033[33m注意：您正在使用 siushin/util 的开发版本 (dev-main)\033[0m";
            echo "\n\033[33m生产环境建议使用稳定版本：composer require siushin/util:^1.0\033[0m\n\n";
        } elseif (strpos($version, 'dev') !== false) {
            echo "\n\033[33m注意：您正在使用 siushin/util 的开发版本 ($version)\033[0m\n\n";
        }
    }

    /**
     * 从 composer.json 获取版本号
     * @return string
     * @author siushin<siushin@163.com>
     */
    private static function detectVersion(): string
    {
        if (file_exists(__DIR__ . '/../composer.json')) {
            $composer = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);

            // 优先读取显式声明的 version 字段
            if (isset($composer['version'])) {
                return $composer['version'];
            }

            // 兼容旧版 composer（某些情况下 version 可能不存在）
            if (isset($composer['extra']['branch-alias']['dev-main'])) {
                return $composer['extra']['branch-alias']['dev-main'];
            }
        }

        return 'dev-main'; // 默认返回值
    }
}
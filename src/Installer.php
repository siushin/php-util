<?php

namespace Siushin\Util;

class Installer
{
    public static function postInstall(): void
    {
        $version = self::detectVersion();

        if (strpos($version, 'dev') !== false) {
            echo "\n\033[33m注意：您正在使用 siushin/util 的开发版本 ($version)\033[0m";
            echo "\n\033[33m生产环境建议使用稳定版本：composer require siushin/util:^1.0\033[0m\n\n";
        }
    }

    private static function detectVersion(): string
    {
        // 尝试从Git标签获取版本
        if (file_exists(__DIR__ . '/../composer.json')) {
            $composer = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
            if (isset($composer['extra']['branch-alias']['dev-main'])) {
                return $composer['extra']['branch-alias']['dev-main'];
            }
        }
        return 'dev-main';
    }
}
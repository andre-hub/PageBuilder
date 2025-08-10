<?php

declare(strict_types=1);

/**
 * Site configuration class for Page Builder static site builder
 * PHP 8 compatible
 */
class SiteConfig
{
    public static function load(): array
    {
        return [
            'SiteName' => 'Page Builder',
            'SiteUrl' => 'pagebuilder.projekt-matrix.de',
            'StaticPath' => '../static/',
            'BuildPagePath' => '../html/',
            'SourcePath' => '../pages/',
            'MarkdownParser' => 'CommonMark',
            'VendorLib' => 'vendor'
        ];
    }

    public static function loadPlugins(): array
    {
        return ['Share'];
    }

    public static function getPluginSettings(): array
    {
        return [
            'Share' => [
            ]
        ];
    }
}

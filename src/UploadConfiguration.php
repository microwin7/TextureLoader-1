<?php

namespace Gravita\JsonTextureProvider;

use TypeError;

use function Gravita\JsonTextureProvider\str_ends_with_slash;

class UploadConfiguration
{
    private static string|null $baseUrl;
    private static string|null $baseDir;
    public int $maxUploadWidth = 16 * 64;
    public int $maxUploadHeight = 16 * 64;
    public int $maxUploadSize = 16 * 64 * 1024;
    public bool $generateAvatar = true;

    public function __construct(string $baseUrl, string $baseDir)
    {
        self::setBaseUrl($baseUrl);
        self::setBaseDir($baseDir);
    }
    public static function getBaseUrl(): string
    {
        if (self::$baseUrl === null) throw new LoaderException('Please initialize $baseUrl before calling this method');
        return self::$baseUrl;
    }
    public static function setBaseUrl(string $baseUrl): void
    {
        if (empty($baseUrl)) throw new TypeError('Param $baseUrl cannot be empty');
        self::$baseUrl = $baseUrl;
    }
    public static function getBaseDir(): string
    {
        if (self::$baseDir === null) throw new LoaderException('Please initialize $baseDir before calling this method');
        return self::$baseDir;
    }
    public static function setBaseDir(string $baseDir): void
    {
        if (empty($baseDir)) throw new TypeError('Param $baseDir cannot be empty');
        self::$baseDir = str_ends_with_slash($baseDir);
    }
}

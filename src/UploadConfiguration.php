<?php
namespace Gravita\JsonTextureProvider;

class UploadConfiguration {
    public $baseUrl;
    public $baseDir;
    public $maxUploadWidth = 16*64;
    public $maxUploadHeight = 16*64;
    public $maxUploadSize = 16*64 * 1024;
    public $generateAvatar = true;
}
<?php

namespace Gravita\JsonTextureProvider\Config;

class Config
{
    public static $dbsystem = "pgsql"; //mysql or pgsql
    public static $port = "5432"; // default mysql port 3306 | default pgsql port 5432
    public static $host = 'localhost'; // database host
    public static $db = 'launchserver'; // database name
    public static $user = 'launchserver'; // user name
    public static $password = '1111'; // password
    public static $baseUrl = "http://example.com/assets/";
    public static $baseDir = "assets/";

    public static $maxUploadWidth = 16*64;
    public static $maxUploadHeight = 16*64;
    public static $maxUploadSize = 16*64 * 1024;

    public static $persistent = true;
    public static $generateAvatar = true;

    public static $ecdsaPublicKeyPath = "/ecdsa_id.pub";
    public static $allowedTypes = ["SKIN", "CAPE"];
}

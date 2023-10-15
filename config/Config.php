<?php

namespace Gravita\JsonTextureProvider\Config;

class Config
{
    public static $dbsystem = "pgsql"; //mysql or pgsql
    public static $port = "5432"; // default mysql port 3306 | default pgsql port 5432
    public static $host = 'localhost'; // database host
    public static $db = 'launchserver'; // database name
    public static $user = 'launchserver'; // user name
    public static $password = '12345'; // password
    public static $baseUrl = "http://test.gravita.local/my/public/assets/";
    public static $baseDir = "assets/";

    public static $maxUploadWidth = 64;
    public static $maxUploadHeight = 64;
    public static $maxUploadSize = 64 * 1024;

    public static $persistent = true;

    public static $ecdsaPublicKeyPath = "/ecdsa_id.pub";
    public static $allowedTypes = ["SKIN", "CAPE"];

    public static function getDSN(): string {
        return self::$dbsystem . ":host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$db . ";";
    }
    public static function getEcdsaPublicKeyPath(): string {
        return dirname(__FILE__) . self::$ecdsaPublicKeyPath;
    }
}

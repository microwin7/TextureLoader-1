<?php

use Gravita\JsonTextureProvider\Base;
use Gravita\JsonTextureProvider\DAO;
use Gravita\JsonTextureProvider\Config\Config;
use Gravita\JsonTextureProvider\Loader;
use Gravita\JsonTextureProvider\LoaderException;
use function Gravita\JsonTextureProvider\json_response;

// ini_set('error_reporting', E_ALL); // FULL DEBUG 
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

require_once(__DIR__ . '/../vendor/autoload.php');

$uuid = $_GET["uuid"] ?? null;

if (!$uuid) {
    json_response(400, [
        "error" => "Property uuid not found"
    ]);
    exit(0);
}
try {
    // make a database connection
    $pdo = new PDO(Config::getDSN(), Config::$user, Config::$password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => Config::$persistent]);

    if (!$pdo) {
        json_response(500, [
            "error" => "Database error"
        ]);
        exit(0);
    }
} catch (PDOException $e) {
    json_response(500, [
        "error" => "Database error"
    ]);
    exit(0);
}

$dao = new DAO($pdo, Config::$dbsystem);
try {
    $loader = new Loader($dao);
    $result = $loader->get($uuid);
    json_response(200, $result);
} catch(LoaderException $e) {
    json_response(400, [
        "error" => $e->getMessage()
    ]);
    exit(0);
}

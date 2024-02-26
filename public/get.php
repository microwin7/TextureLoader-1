<?php

use Gravita\JsonTextureProvider\Base;
use Gravita\JsonTextureProvider\DAO;
use Gravita\JsonTextureProvider\Config\Config;
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

$base = new Base();
$pdo = $base->pdo;
$dao = new DAO($pdo, Config::$dbsystem);
$result = $dao->getAllByUserUuid($uuid);
json_response(200, $result);

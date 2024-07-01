<?php

use Gravita\JsonTextureProvider\Base;
use Gravita\JsonTextureProvider\DAO;
use Gravita\JsonTextureProvider\Loader;
use Gravita\JsonTextureProvider\LoaderException;
use Gravita\JsonTextureProvider\Config\Config;
use Gravita\JsonTextureProvider\UploadConfiguration;
use function Gravita\JsonTextureProvider\json_response;
use function Gravita\JsonTextureProvider\get_bearer_token;
use function Gravita\JsonTextureProvider\parse_public_key;
use function Gravita\JsonTextureProvider\parse_jwt_and_verify;

// ini_set('error_reporting', E_ALL); // FULL DEBUG 
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

require_once(__DIR__ . '/../vendor/autoload.php');

$jwtToken = get_bearer_token();

$publicKey = parse_public_key(Config::getEcdsaPublicKeyPath());

if (!$publicKey) {
    json_response(500, [
        "error" => "Public key incorrect"
    ]);
    exit(0);
}
if (!$jwtToken) {
    json_response(401, [
        "error" => "Missing AccessToken"
    ]);
    exit(0);
}

try {
$jwt = parse_jwt_and_verify($jwtToken, $publicKey);
} catch(Exception $e) {
    json_response(400, [
        "error" => $e->getMessage()
    ]);
    exit(0);
}


$fileinfo = $_FILES["file"] ?? null;
$options = json_decode($_POST["options"], true);
$assetType = $_GET["type"] ?? null;
if (!isset($fileinfo)) {
    json_response(400, [
        "error" => "Part file not found"
    ]);
    exit(0);
}

if (!isset($options)) {
    json_response(400, [
        "error" => "Part options not found"
    ]);
    exit(0);
}

if (!in_array($assetType, Config::$allowedTypes)) {
    json_response(400, [
        "error" => "Asset type not allowed"
    ]);
    exit(0);
}

$uuid = $jwt->uuid;

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

if (!$uuid) {
    json_response(400, [
        "error" => "Wrong AccessToken (missing uuid)"
    ]);
    exit(0);
}

try {
    $uploadConfiguration = new UploadConfiguration();
    $uploadConfiguration->baseDir = Config::$baseDir;
    $uploadConfiguration->baseUrl = Config::$baseUrl;
    $uploadConfiguration->generateAvatar = Config::$generateAvatar;
    $uploadConfiguration->maxUploadHeight = Config::$maxUploadHeight;
    $uploadConfiguration->maxUploadWidth = Config::$maxUploadWidth;
    $uploadConfiguration->maxUploadSize = Config::$maxUploadSize;
    $loader = new Loader($dao);
    $result = $loader->upload($uuid, $fileinfo, $options, $assetType, $uploadConfiguration);
    json_response(200, $result);
} catch(LoaderException $e) {
    json_response(400, [
        "error" => $e->getMessage()
    ]);
    exit(0);
}

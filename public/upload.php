<?php

use Gravita\JsonTextureProvider\Base;
use Gravita\JsonTextureProvider\Config\Config;
use function Gravita\JsonTextureProvider\json_response;
use function Gravita\JsonTextureProvider\get_bearer_token;
use function Gravita\JsonTextureProvider\parse_public_key;

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

$base = new Base();

$jwt = $base->parse_jwt_and_verify($jwtToken, $publicKey);

$fileinfo = $_FILES["file"];
$options = json_decode($_POST["options"], true);
$assetType = $_GET["type"];
if (!$fileinfo) {
    json_response(400, [
        "error" => "Part file not found"
    ]);
    exit(0);
}

if (!$options) {
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

if (!$uuid) {
    json_response(400, [
        "error" => "Wrong AccessToken (missing uuid)"
    ]);
    exit(0);
}

if ($fileinfo['size'] > Config::$maxUploadSize) {
    json_response(400, [
        "error" => "Image too big: Size limit"
    ]);
    exit(0);
}

$content = file_get_contents($fileinfo['tmp_name']);

$size = getimagesizefromstring($content);

if (!$size) {
    json_response(400, [
        "error" => "Upload file not a image"
    ]);
    exit(0);
}

$width = $size[0];
$height = $size[1];
$imgType = $size[2];

if ($imgType != IMAGETYPE_PNG) {
    json_response(400, [
        "error" => "Image is not a png format"
    ]);
    exit(0);
}

if ($height > Config::$maxUploadHeight) {
    json_response(400, [
        "error" => "Image too big: Height limit"
    ]);
    exit(0);
}

if ($width > Config::$maxUploadWidth) {
    json_response(400, [
        "error" => "Image too big: Width limit"
    ]);
    exit(0);
}
$hash = hash("sha256", $content);
$filePath = Config::$baseDir . $hash . ".png";

if (!file_exists($filePath)) {
    file_put_contents($filePath, $content);
}
$metadata = [];
if ($assetType == "SKIN" && $options["modelSlim"] == true) {
    $metadata["model"] = "slim";
}
$metadata_json = json_encode($metadata);
$pdo = $base->pdo;
$sql = match(Config::$dbsystem) {
    "mysql" => "INSERT INTO user_assets (uuid, name, hash, metadata) VALUES (:uuid, :name, :hash, :metadata) ON DUPLICATE KEY DO UPDATE SET hash = :hash, metadata = :metadata",
    "pgsql" => "INSERT INTO user_assets (uuid, name, hash, metadata) VALUES (:uuid, :name, :hash, :metadata) ON CONFLICT (uuid, name) DO UPDATE SET hash = :hash, metadata = :metadata"
};
$stmt = $pdo->prepare($sql);
$stmt->execute(['uuid' => $uuid, 'name' => $assetType, 'hash' => $hash, 'metadata' => $metadata_json]);
json_response(200, [
    "url" => Config::$baseUrl . $hash . ".png",
    "digest" =>  $hash,
    "metadata" => $metadata
]);
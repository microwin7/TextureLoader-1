<?php

use Gravita\JsonTextureProvider\Base;
use Gravita\JsonTextureProvider\DAO;
use Gravita\JsonTextureProvider\Config\Config;
use function Gravita\JsonTextureProvider\json_response;
use function Gravita\JsonTextureProvider\get_bearer_token;
use function Gravita\JsonTextureProvider\parse_public_key;

ini_set('error_reporting', E_ALL); // FULL DEBUG 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

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

$pdo = $base->pdo;
$dao = new DAO($pdo, Config::$dbsystem);
if (!file_exists($filePath)) {
    file_put_contents($filePath, $content);
}
if($assetType == 'SKIN' && Config::$generateAvatar) {
    $scale = $width / 64;
    $skinSize = $scale * 8;
    $image = imagecreatefromstring($content);
    $newImage = imagecreatetruecolor($skinSize, $skinSize);
    imagecopyresized($newImage, $image, 0, 0, $skinSize, $skinSize, $skinSize, $skinSize, $skinSize, $skinSize);
    imagecopyresized($newImage, $image, 0, 0, 5*$skinSize, $skinSize, $skinSize, $skinSize, $skinSize, $skinSize);
    imagepng($newImage, $fileinfo['tmp_name']);
    imagedestroy($image);
    imagedestroy($newImage);
    $avatarContent = file_get_contents($fileinfo['tmp_name']);
    $avatarHash = hash("sha256", $avatarContent);
    $avatarFilePath = Config::$baseDir . $avatarHash . ".png";
    file_put_contents($avatarFilePath, $avatarContent);
    $dao->update($uuid, "AVATAR", $avatarHash, "{}");
}
$metadata = [];
if ($assetType == "SKIN" && $options["modelSlim"] == true) {
    $metadata["model"] = "slim";
}
$metadata_json = json_encode($metadata);
$dao->update($uuid, $assetType, $hash, $metadata_json);
json_response(200, [
    "url" => Config::$baseUrl . $hash . ".png",
    "digest" =>  $hash,
    "metadata" => $metadata
]);
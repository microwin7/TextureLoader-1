<?php

use Gravita\JsonTextureProvider\Base;
use Gravita\JsonTextureProvider\Config\Config;
use function Gravita\JsonTextureProvider\json_response;

ini_set('error_reporting', E_ALL); // FULL DEBUG 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); // !!! Потом убрать

require_once(__DIR__ . '/../vendor/autoload.php');

$uuid = $_GET["uuid"];

if (!$uuid) {
    json_response(400, [
        "error" => "Property uuid not found"
    ]);
    exit(0);
}

$base = new Base();
$pdo = $base->pdo;

$stmt = $pdo->prepare("SELECT hash,name,metadata FROM user_assets WHERE uuid=:uuid");
$stmt->execute(['uuid' => $uuid]);
$result = [];
while (($entity = $stmt->fetch(PDO::FETCH_ASSOC))) {
    $result[$entity["name"]] = [
        "url" => Config::$baseUrl . $entity["hash"] . ".png",
        "hash" =>  str_replace('=', '', strtr(base64_encode(hex2bin($entity["hash"])), '+/', '-_')),
        "metadata" => json_decode($entity["metadata"], true)
    ];
}
json_response(200, $result);

<?php
namespace Gravita\JsonTextureProvider;

use \PDO;
use Gravita\JsonTextureProvider\Config\Config;
use \ArrayObject;

class DAO {
    private readonly PDO $pdo;
    private readonly string $dialect;

    function __construct(PDO $pdo, string $dialect)
    {
        $this->pdo = $pdo;
        $this->dialect = $dialect;
    }

    function update(string $uuid, string $assetType, string $hash, $metadata_json) {
        $sql = match($this->dialect) {
            "mysql" => "INSERT INTO user_assets (uuid, name, hash, metadata) VALUES (:uuid, :name, :hash, :metadata) ON DUPLICATE KEY DO UPDATE SET hash = :hash, metadata = :metadata",
            "pgsql" => "INSERT INTO user_assets (uuid, name, hash, metadata) VALUES (:uuid, :name, :hash, :metadata) ON CONFLICT (uuid, name) DO UPDATE SET hash = :hash, metadata = :metadata"
        };
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['uuid' => $uuid, 'name' => $assetType, 'hash' => $hash, 'metadata' => $metadata_json]);
    }

    function getAllByUserUuid(string $uuid) : ArrayObject {
        $stmt = $this->pdo->prepare("SELECT hash,name,metadata FROM user_assets WHERE uuid=:uuid");
        $stmt->execute(['uuid' => $uuid]);
        $result = new ArrayObject();
        while (($entity = $stmt->fetch(PDO::FETCH_ASSOC))) {
            $result[$entity["name"]] = [
                "url" => Config::$baseUrl . $entity["hash"] . ".png",
                "digest" =>  $entity["hash"],
                "metadata" => json_decode($entity["metadata"], true)
            ];
        }
        return $result;
    }

    function getByUserUuid(string $uuid, string $assetType) : array|null {
        $stmt = $this->pdo->prepare("SELECT hash,name,metadata FROM user_assets WHERE uuid=:uuid AND name = :name");
        $stmt->execute(['uuid' => $uuid, 'name' => $assetType]);
        while (($entity = $stmt->fetch(PDO::FETCH_ASSOC))) {
            return [
                "url" => Config::$baseUrl . $entity["hash"] . ".png",
                "digest" =>  $entity["hash"],
                "metadata" => json_decode($entity["metadata"], true)
            ];
        }
        return null;
    }
}
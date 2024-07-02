<?php

namespace Gravita\JsonTextureProvider;

use \PDO;
use \ArrayObject;
use Gravita\JsonTextureProvider\Config\Config;

class DAO
{
    private readonly PDO $pdo;
    private readonly string $dialect;

    function __construct(PDO $pdo, string $dialect)
    {
        $this->pdo = $pdo;
        $this->dialect = $dialect;
    }

    function update(string $uuid, string $assetType, string $hash, string|false $metadata_json): void
    {
        $sql = match ($this->dialect) {
            "mysql" => "INSERT INTO user_assets (uuid, name, hash, metadata) VALUES (:uuid, :name, :hash, :metadata) ON DUPLICATE KEY UPDATE hash = :hash, metadata = :metadata",
            "pgsql" => "INSERT INTO user_assets (uuid, name, hash, metadata) VALUES (:uuid, :name, :hash, :metadata) ON CONFLICT (uuid, name) DO UPDATE SET hash = :hash, metadata = :metadata"
        };
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['uuid' => $uuid, 'name' => $assetType, 'hash' => $hash, 'metadata' => $metadata_json]);
    }

    function getAllByUserUuid(string $uuid): ArrayObject
    {
        $stmt = $this->pdo->prepare("SELECT hash,name,metadata FROM user_assets WHERE uuid=:uuid");
        $stmt->execute(['uuid' => $uuid]);
        $result = new ArrayObject();
        while (($entity = $stmt->fetch(PDO::FETCH_ASSOC))) {
            $result[$entity["name"]] = [
                "url" => UploadConfiguration::getBaseUrl() . $entity["hash"] . ".png",
                "digest" =>  $entity["hash"],
                "metadata" => json_decode($entity["metadata"], true)
            ];
        }
        return $result;
    }

    function getByUserUuid(string $uuid, string $assetType): array|null
    {
        $stmt = $this->pdo->prepare("SELECT hash,name,metadata FROM user_assets WHERE uuid=:uuid AND name = :name");
        $stmt->execute(['uuid' => $uuid, 'name' => $assetType]);
        while (($entity = $stmt->fetch(PDO::FETCH_ASSOC))) {
            return [
                "url" => UploadConfiguration::getBaseUrl() . $entity["hash"] . ".png",
                "digest" =>  $entity["hash"],
                "metadata" => json_decode($entity["metadata"], true)
            ];
        }
        return null;
    }

    function getAvatarHashBySkinHash(string $skinHash, int $scale): string|null
    {
        $stmt = $this->pdo->prepare("SELECT avatarHash FROM user_assets_avatarcache WHERE skinHash = :skinHash AND scale = :scale");
        $stmt->execute(['skinHash' => $skinHash, 'scale' => $scale]);
        while (($entity = $stmt->fetch(PDO::FETCH_ASSOC))) {
            return $entity["avatarHash"];
        }
        return null;
    }

    function updateAvatarCache(string $skinHash, string $avatarHash, int $scale): void
    {
        $sql = match ($this->dialect) {
            "mysql" => "INSERT INTO user_assets_avatarcache (skinHash, avatarHash, scale) VALUES (:skinHash, :avatarHash, :scale) ON DUPLICATE KEY DO UPDATE SET avatarHash = :avatarHash",
            "pgsql" => "INSERT INTO user_assets_avatarcache (skinHash, avatarHash, scale) VALUES (:skinHash, :avatarHash, :scale) ON CONFLICT (skinHash, scale) DO UPDATE SET avatarHash = :avatarHash"
        };
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['skinHash' => $skinHash, 'avatarHash' => $avatarHash, 'scale' => $scale]);
    }
}

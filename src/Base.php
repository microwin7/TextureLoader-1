<?php

namespace Gravita\JsonTextureProvider;

use PDO;
use stdClass;
use PDOException;
use Gravita\JsonTextureProvider\Config\Config;
use function Gravita\JsonTextureProvider\json_response;

class Base
{
    public readonly PDO $pdo;

    function __construct()
    {
        $this->connect();
    }
    private function connect()
    {
        try {
            // make a database connection
            return $this->pdo = new PDO(Config::getDSN(), Config::$user, Config::$password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => Config::$persistent]);

            if (!$this->pdo) {
                json_response(400, [
                    "error" => "Database error"
                ]);
                exit(0);
            }
        } catch (PDOException $e) {
            json_response(400, [
                "error" => "Database error"
            ]);
            exit(0);
        }
    }
}

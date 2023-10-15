<?php

namespace Gravita\JsonTextureProvider;

use PDO;
use stdClass;
use PDOException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
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

    function parse_jwt_and_verify($jwt, $publicKey): stdClass
    {
        try {
            $decoded = JWT::decode(
                $jwt,
                new Key($publicKey, 'ES256')
            );
        } catch (\InvalidArgumentException $e) {
            die(json_response(400, [
                "error" => "InvalidArgumentException"
            ]));
            // provided key/key-array is empty or malformed.
        } catch (\DomainException $e) {
            die(json_response(400, [
                "error" => "DomainException"
            ]));
            // provided algorithm is unsupported OR
            // provided key is invalid OR
            // unknown error thrown in openSSL or libsodium OR
            // libsodium is required but not available.
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            die(json_response(400, [
                "error" => "SignatureInvalidException"
            ]));
            // provided JWT signature verification failed.
        } catch (\Firebase\JWT\BeforeValidException $e) {
            die(json_response(400, [
                "error" => "BeforeValidException"
            ]));
            // provided JWT is trying to be used before "nbf" claim OR
            // provided JWT is trying to be used before "iat" claim.
        } catch (\Firebase\JWT\ExpiredException $e) {
            die(json_response(400, [
                "error" => "AccessToken expire"
            ]));
            // provided JWT is trying to be used after "exp" claim.
        } catch (\UnexpectedValueException $e) {
            die(json_response(400, [
                "error" => "UnexpectedValueException"
            ]));
            // provided JWT is malformed OR
            // provided JWT is missing an algorithm / using an unsupported algorithm OR
            // provided JWT algorithm does not match provided key OR
            // provided key ID in key/key-array is empty or invalid.
        } catch (\Throwable $e) {
            die(json_response(400, [
                "error" => "Unknown error"
            ]));
        }
        return $decoded;
    }
}

<?php

namespace Gravita\JsonTextureProvider;

use stdClass;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function json_response($code, $data): void
{
    http_response_code($code);
    header("Content-Type: application/json");
    echo json_encode($data);
}
function parse_public_key($path): \OpenSSLAsymmetricKey|false
{
    return openssl_pkey_get_public("-----BEGIN PUBLIC KEY----- \n" . base64_encode(file_get_contents($path)) . "\n-----END PUBLIC KEY----- ");
}
/**
 * Get header Authorization
 *
 * @return null|string
 */
function get_authorization_header(): string|null
{
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

/**
 * get access token from header
 *
 * @return null|string
 */
function get_bearer_token(): string|null
{
    $headers = get_authorization_header();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}



function parse_jwt_and_verify($jwt, $publicKey): stdClass
{
    return JWT::decode(
        $jwt,
        new Key($publicKey, 'ES256')
    );
}

function str_ends_with_slash(string $string, bool $needle_ends_with_slash = TRUE): string
{
    if (str_ends_with($string, DIRECTORY_SEPARATOR)) {
        if (!$needle_ends_with_slash) $string =  substr($string, 0, -1);
    } else {
        if ($needle_ends_with_slash) $string .=  DIRECTORY_SEPARATOR;
    }
    return $string;
}

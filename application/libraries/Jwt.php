<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * JWT Library untuk CodeIgniter
 * Menggunakan firebase/php-jwt
 */
class Jwt
{
    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        require_once APPPATH . '../vendor/firebase/php-jwt/src/JWT.php';
        require_once APPPATH . '../vendor/firebase/php-jwt/src/Key.php';
    }

    /**
     * Encode JWT token
     */
    public function encode($payload, $key, $alg = 'HS256')
    {
        return \Firebase\JWT\JWT::encode($payload, $key, $alg);
    }

    /**
     * Decode JWT token
     */
    public function decode($jwt, $key, $alg = ['HS256'])
    {
        return \Firebase\JWT\JWT::decode($jwt, new \Firebase\JWT\Key($key, $alg[0]));
    }
}
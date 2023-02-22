<?php

declare(strict_types=1);

namespace Enjoys\Session\Handler;

use Enjoys\Session\RuntimeException;
use Exception;
use SessionHandler;

/**
 * Class SecureHandler
 * @see https://github.com/ezimuel/PHP-Secure-Session
 * @package Enjoys\Session\Handler
 */
class SecureHandler extends SessionHandler
{
    /**
     * Encryption and authentication key
     * @var string
     */
    protected string $key = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        if (!extension_loaded('openssl')) {
            throw new RuntimeException(
                sprintf(
                    "You need the OpenSSL extension to use %s",
                    __CLASS__
                )
            );
        }
        if (!extension_loaded('mbstring')) {
            throw new RuntimeException(
                sprintf(
                    "You need the Multibyte extension to use %s",
                    __CLASS__
                )
            );
        }
    }

    /**
     * Open the session
     *
     * @param string $path
     * @param string $name
     * @return bool
     * @throws Exception
     */
    public function open($path, $name): bool
    {
        $this->key = $this->getKey('KEY_' . $name);
        return parent::open($path, $name);
    }

    /**
     * Read from session and decrypt
     *
     * @param string $id
     * @return string
     * @throws RuntimeException
     */
    public function read($id): string
    {
        $data = parent::read($id);
        return empty($data) ? '' : $this->decrypt($data, $this->key);
    }

    /**
     * Encrypt the data and write into the session
     *
     * @param string $id
     * @param string $data
     * @throws Exception
     */
    public function write($id, $data): bool
    {
        return parent::write($id, $this->encrypt($data, $this->key));
    }

    /**
     * Encrypt and authenticate
     *
     * @param string $data
     * @param string $key
     * @return string
     * @throws Exception
     */
    protected function encrypt(string $data, string $key): string
    {
        $iv = random_bytes(16); // AES block size in CBC mode
        // Encryption
        $ciphertext = openssl_encrypt(
            $data,
            'AES-256-CBC',
            mb_substr($key, 0, 32, '8bit'),
            OPENSSL_RAW_DATA,
            $iv
        );
        // Authentication
        $hmac = hash_hmac(
            'SHA256',
            $iv . $ciphertext,
            mb_substr($key, 32, null, '8bit'),
            true
        );
        return $hmac . $iv . $ciphertext;
    }

    /**
     * Authenticate and decrypt
     *
     * @param string $data
     * @param string $key
     * @return string
     * @throws RuntimeException
     */
    protected function decrypt(string $data, string $key): string
    {
        $hmac = mb_substr($data, 0, 32, '8bit');
        $iv = mb_substr($data, 32, 16, '8bit');
        $ciphertext = mb_substr($data, 48, null, '8bit');
        // Authentication
        $hmacNew = hash_hmac(
            'SHA256',
            $iv . $ciphertext,
            mb_substr($key, 32, null, '8bit'),
            true
        );
        if (!hash_equals($hmac, $hmacNew)) {
            throw new RuntimeException('Authentication failed');
        }
        // Decrypt
        return openssl_decrypt(
            $ciphertext,
            'AES-256-CBC',
            mb_substr($key, 0, 32, '8bit'),
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    /**
     * Get the encryption and authentication keys from cookie
     *
     * @param string $name
     * @return string
     * @throws Exception
     */
    protected function getKey(string $name): string
    {
        if (empty($_COOKIE[$name])) {
            $key = random_bytes(64); // 32 for encryption and 32 for authentication
            /** @var array{"lifetime":int, "path": string, "domain":string, "secure": bool, "httponly": bool, "samesite":string} $cookieParam */
            $cookieParam = session_get_cookie_params();
            $encKey = base64_encode($key);
            // if session cookie lifetime > 0 then add to current time
            // otherwise leave it as zero, honoring zero's special meaning
            // expire at browser close.
            $expires = ($cookieParam['lifetime'] > 0) ? time() + $cookieParam['lifetime'] : 0;


            // PHP 7.3.0+ can use options as array,
            // however session_get_cookie_params() returns 'lifetime',
            // but setting the options via array requires you to use 'expires'
            $cookieParam['expires'] = $expires;
            unset($cookieParam['lifetime']);
            setcookie($name, $encKey, $cookieParam);

            $_COOKIE[$name] = $encKey;
        } else {
            $key = base64_decode($_COOKIE[$name]);
        }
        return $key;
    }
}
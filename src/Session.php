<?php

declare(strict_types=1);

namespace Enjoys\Session;

/**
 * Class Session
 * @see https://www.php.net/manual/ru/session.security.ini.php
 * @see http://www.acros.si/papers/session_fixation.pdf
 * @package Enjoys\Session
 */
class Session
{

    private static array $options = [
        "serialize_handler" => 'php_serialize',
        "use_cookies" => 1,
        "use_only_cookies" => 1,
        "cookie_httponly" => 1,
        "gc_probability" => 1,
        "gc_divisor" => 100,
        "gc_maxlifetime" => 1440
    ];

    public function __construct(\SessionHandlerInterface $handler = null, array $options = [])
    {
        if (session_status() != \PHP_SESSION_ACTIVE) {
            $this->setOptions($options);
            if ($handler !== null) {
                session_set_save_handler($handler, true);
            }
            session_start($this->getOptions());
        }
    }

    public function getSessionId(){
        return session_id();
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return self::$options;
    }

    /**
     * @param array $options
     */
    private function setOptions(array $options): void
    {
        foreach ($options as $key => $option) {
            self::$options[$key] = $option;
        }
    }


    public static function set(array $params)
    {
        foreach ($params as $key => $param) {
            $_SESSION[$key] = $param;
        }
    }

    public static function get($key, $default = null)
    {
        if (self::has($key)) {
            return $_SESSION[$key];
        }
        return $default;
    }

    public static function delete($key)
    {
        if (self::has($key)) {
            unset($_SESSION[$key]);
        }
    }

    public static function has($key)
    {
        return array_key_exists($key, $_SESSION);
    }
}

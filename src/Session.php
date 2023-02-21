<?php

declare(strict_types=1);

namespace Enjoys\Session;

use SessionHandlerInterface;

use const PHP_SESSION_ACTIVE;

/**
 * Class Session
 * @see https://www.php.net/manual/ru/session.security.ini.php
 * @see http://www.acros.si/papers/session_fixation.pdf
 * @package Enjoys\Session
 */
class Session
{

    /**
     * @var array<string, string|int>
     */
    private static array $options = [
        "serialize_handler" => 'php_serialize',
        "use_cookies" => 1,
        "use_only_cookies" => 1,
        "cookie_httponly" => 1,
        "gc_probability" => 1,
        "gc_divisor" => 100,
        "gc_maxlifetime" => 1440
    ];

    /**
     * Session constructor.
     * @param SessionHandlerInterface|null $handler
     * @param array $options
     * @param array|null $data
     */
    public function __construct(SessionHandlerInterface $handler = null, array $options = [], array $data = null)
    {
        if ( session_status() != PHP_SESSION_ACTIVE) {
            $this->setOptions($options);
            if ($handler !== null) {
                session_set_save_handler($handler, true);
            }
            session_start($this->getOptions());
        }
        $_SESSION = $data ?? $_SESSION;
    }


    public function getSessionId(): string
    {
        return session_id();
    }

    /**
     * @return array<string, string|int>
     */
    private function getOptions(): array
    {
        return self::$options;
    }

    /**
     * @param array<string, string|int> $options
     * @return void
     */
    private function setOptions(array $options): void
    {
        foreach ($options as $key => $option) {
            self::$options[$key] = $option;
        }
    }


    /**
     * @param array<string, mixed> $params
     */
    public  function set(array $params): void
    {
        foreach ($params as $key => $param) {
            /** @var array<string, mixed> $_SESSION */
            $_SESSION[$key] = $param;
        }

    }

    /**
     * @template TDefault
     * @param string $key
     * @param TDefault $default
     * @return mixed|TDefault
     */
    public  function get(string $key, $default = null)
    {
        if (self::has($key)) {
            /** @var array<string, mixed> $_SESSION */
            return $_SESSION[$key];
        }
        return $default;
    }

    public  function delete(string $key): void
    {
        if (self::has($key)) {
            unset($_SESSION[$key]);
        }
    }

    public  function clear(): void
    {
        $_SESSION = null;
    }

    public  function has(string $key): bool
    {
        /** @var array<string, mixed> $_SESSION */
        return array_key_exists($key, $_SESSION);
    }

}

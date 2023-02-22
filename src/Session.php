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
    private array $options = [
        "serialize_handler" => 'php_serialize',
        "use_cookies" => 1,
        "use_only_cookies" => 1,
        "cookie_httponly" => 1,
        "gc_probability" => 1,
        "gc_divisor" => 100,
        "gc_maxlifetime" => 1440
    ];

    private ?array $data;

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
        $this->data = $data ?? $_SESSION;
    }




    /**
     * @return string|false
     */
    public function getSessionId()
    {
        return session_id();
    }

    /**
     * @param array<string, string|int> $options
     * @return void
     */
    private function setOptions(array $options): void
    {
        foreach ($options as $key => $option) {
           $this->options[$key] = $option;
        }
    }

    public function getOptions(): array
    {
        return $this->options;
    }


    /**
     * @param array<string, mixed> $params
     */
    public  function set(array $params): void
    {
        foreach ($params as $key => $param) {
            /** @var array<string, mixed> $_SESSION */
            $this->data[$key] = $param;
        }
        $this->emit();

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
            return $this->data[$key];
        }
        return $default;
    }

    public  function delete(string $key): void
    {
        if (self::has($key)) {
            unset($this->data[$key]);
        }
        $this->emit();
    }

    public  function clear(): void
    {
        $this->data = null;
        $this->emit();
    }

    public  function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    private function emit(): void
    {
        $_SESSION = $this->data;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

}

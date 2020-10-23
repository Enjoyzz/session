<?php

/**
 * Session handler.
 * 
 * use Enjoys\Base\Session\Session as Session;
 * 
 * http://php.net/manual/ru/session.configuration.php
 *  
 * @ini_set('session.save_path', _CACHEDIR_.'/session');
 * 
 * --PHP
 * Session::setStore(Enjoys\Core\Config::getConfig('session', 'handler')); //php or file - sinonyms
 * Session::start();
 * запуск с параметрами
 * Session::start(['session.serialize_handler'=>'php_serialize',
  'session.save_path'=> _CACHEDIR_.'/session']);
 * 
 * --Memcache
 * Session::setStore('Memcache');
 * Session::start(['localhost', 11211, true]);
 * 
 * --MySQL
 * Session::setStore('mysql');
 * Session::start();
 * 
 * 
 * 
 */

namespace Enjoys\Session;

/**
 * session.serialize_handler 
 * определяет имя обработчика, который используется для сериализации/десериализации данных. 
 * Используйте php_serialize, чтобы обойти ошибки числовых и строковых индексов 
 * при завершении скрипта. Значение по умолчанию php.
 * 
 * php|php_binary|php_serialize(php5.5.4)|wddx(в том случае, если PHP скомпилирован с поддержкой WDDX)
 */
ini_set("session.serialize_handler", 'php_serialize');

/**
 * session.use_cookies 
 * определяет, будет ли модуль использовать cookies для хранения идентификатора сессии на стороне клиента
 * 
 * dafault: 1
 * 
 * session.use_only_cookies
 * использовать только cookies для хранения идентификатора сессии на стороне клиента. 
 * Включение этого параметра предотвращает атаки с использованием идентификатора 
 * сессии, размещенного в URL. 
 * 
 * default: 1
 */
ini_set("session.use_cookies", 1);
ini_set("session.use_only_cookies", 1);

/**
 * session.cookie_httponly boolean
 * Отметка, согласно которой доступ к cookies может быть получен только 
 * через HTTP-протокол. Это означает, что cookies не будут доступны через 
 * скриптовые языки, такие как JavaScript. Данная настройка позволяет эффективно 
 * защитить от XSS-атак (к сожалению, эта функция поддерживается не всеми 
 * браузерами).
 */
ini_set("session.cookie_httponly", 1);

/**
 * session.gc_probability integer
 * session.gc_probability в сочетании с session.gc_divisor определяет вероятность 
 * запуска функции сборщика мусора (gc, garbage collection). 
 * По умолчанию равен 1. См. подробнее в session.gc_divisor.
 * 
 * session.gc_divisor integer
 * session.gc_divisor в сочетании с session.gc_probability определяет вероятность 
 * запуска функции сборщика мусора (gc, garbage collection) при каждой инициализации 
 * сессии. Вероятность рассчитывается как gc_probability/gc_divisor, 
 * то есть 1/100 означает, что функция gc запускается в одном случае из ста, 
 * или 1% при каждом запросе. session.gc_divisor по умолчанию имеет значение 100.
 * 
 * session.gc_maxlifetime integer
 * session.gc_maxlifetime задает отсрочку времени в секундах, после которой 
 * данные будут рассматриваться как "мусор" и потенциально будут удалены. Сбор 
 * мусора может произойти в течение старта сессии (в зависимости от значений 
 * session.gc_probability и session.gc_divisor).
 * 
 * Замечание: Если разные скрипты имеют разные значения session.gc_maxlifetime, 
 * но при этом одни и те же места для хранения данных сессии, то скрипт с минимальным 
 * значением уничтожит все данные. В таком случае следует использовать эту директиву 
 * вместе с session.save_path.
 */
ini_set("session.gc_probability", 1);
ini_set("session.gc_divisor", 100);
ini_set("session.gc_maxlifetime", 1440);

/*
  if (!defined("PATH_SEPARATOR"))
  define("PATH_SEPARATOR", getenv("COMSPEC")? ";" : ":");
  ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.__DIR__.'/SessionHandler');
 */
class Session
{
    use \Enjoys\Traits\Singleton;

    private static $store = 'php';

    /**
     * 
     * @param string $store php|file|mysql|memcache
     */
    public static function setStore(string $store = 'php')
    {
        self::$store = $store;
    }

    static public function start($options = array())
    {
        // Load database driver and create its instance.
        $class = '\Enjoys\Session\Handler\\' . ucfirst(self::$store);

        if (is_null(self::$instance)) {
            self::$instance = new $class($options);
            // Start session
            session_start();

            return self::$instance;
        } else {
            throw new \Enjoys\Session\Exception(
                    sprintf(
                            'Close the existing instance of the class %s. Or use %s::getInstance()',
                            __CLASS__,
                            __CLASS__
                    )
            );
        }
    }

    public static function set($params)
    {

        if (!is_array($params)) {
            throw new \Enjoys\Session\Exception(
                    'Неверно переданный параметр в Session::set()'
            );
        }
        foreach ($params as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }

    public static function get($key, $default = null)
    {
        if (self::contains($key)) {
            return $_SESSION[$key];
        }
        return $default;
    }

    public static function delete($key)
    {
        if (self::contains($key)) {
            unset($_SESSION[$key]);
        }
    }

    public static function contains($key)
    {
        return isset($_SESSION[$key]);
    }
}

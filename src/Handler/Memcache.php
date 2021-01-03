<?php

namespace Enjoys\Session\Handler;

/**
 * Description of xSessionMemcache
 *
 * Session::setStore('Memcache');
 * Session::start(array($host, $port, $compress)); // ['localhost', 11211, true]
 *
 * @author Root
 */
class Memcache
{

    private static $memcache;
    private static $lifeTime;
    private static $compress = 0;

    public function __construct($args = null)
    {
        $host = (isset($args[0])) ? $args[0] : 'localhost';
        $port = (isset($args[1])) ? $args[1] : 11211;
        $compress = (isset($args[2])) ? $args[2] : 0;

        self::$memcache = new \Memcache();
        if (!self::$memcache->connect($host, $port)) {
            throw new \Enjoys\Core\Exception(sprintf('Error. Connection to <b>%s:%s</b> failed.', $host, $port));
        }

        self::$compress = ($compress) ? MEMCACHE_COMPRESSED : 0;

        self::$lifeTime = get_cfg_var("session.gc_maxlifetime");

        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );
    }

    public static function open()
    {
        return true;
    }

    public static function read($id)
    {
        return self::$memcache->get("sessions/{$id}");
    }

    public static function write($id, $data)
    {
        return self::$memcache->set("sessions/{$id}", $data, self::$compress, self::$lifeTime);
    }

    public static function destroy($id)
    {
        return self::$memcache->delete("sessions/{$id}");
    }

    public static function gc()
    {
        return true;
    }

    public static function close()
    {
        return true;
    }

    public function __destruct()
    {
        session_write_close();
    }
}

?>

<?php

namespace Enjoys\Session\Handler;

class Php
{

    /**
     *
     * http://php.net/manual/ru/session.configuration.php
     *
     * Session::start(['session.serialize_handler'=>'php_serialize',
     * 'session.save_path'=> _CACHEDIR_.'/session']);
     *
     * тоже самое что и
     * ini_set("session.serialize_handler", 'php_serialize');
     * ini_set('session.save_path', _CACHEDIR_.'/session');
     *
     * @param type $options
     */
    public function __construct($options = array())
    {
        if (is_array($options)) {
            foreach ($options as $key => $value) {
                ini_set($key, $value);
            }
        }
    }
}


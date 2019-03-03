<?php

namespace MageArab\MegaFramework\Traits;
use MageArab\MegaFramework\Core\Exceptions\MultitonException;

/**
 * Singleton design pattern
 *
 * This restricts the class to a single instance. This is usually a
 * bad idea as it limits testability and encourages the global
 * state. Use dependency injection instead of this.
 */
trait Multiton
{

    /**
     * @throws MultitonException
     */
    final private  function __clone()
    {
        throw new MultitonException('You can not clone a Multiton.');
    }
    /**
     * @throws MultitonException
     */
    final private  function __sleep()
    {
        throw new MultitonException('You can not serialize a Multiton.');
    }
    /**
     * @throws MultitonException
     */
    final private  function __wakeup()
    {
        throw new MultitonException('You can not deserialize a Multiton.');
    }

    public static function instance($key)
    {
        static $instance = [];
        $calledClass  = get_called_class();
        if ( !array_key_exists($key, $instance)) {
            $instance[$key] = new $calledClass;
        }
        return $instance[$key];
    }
}
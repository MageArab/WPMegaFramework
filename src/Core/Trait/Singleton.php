<?php

namespace MageArab\MegaFramework\Traits;
use MageArab\MegaFramework\Exceptions\SingletonException;

/**
 * Singleton design pattern
 *
 * This restricts the class to a single instance. This is usually a
 * bad idea as it limits testability and encourages the global
 * state. Use dependency injection instead of this.
 */
trait Singleton
{
    /**
     * @throws SingletonException
     */
    final public function __clone()
    {
        throw new SingletonException('You can not clone a singleton.');
    }
    /**
     * @throws SingletonException
     */
    final public function __sleep()
    {
        throw new SingletonException('You can not serialize a singleton.');
    }
    /**
     * @throws SingletonException
     */
    final public function __wakeup()
    {
        throw new SingletonException('You can not deserialize a singleton.');
    }

    public static function instance()
    {
        static $instance;
        $calledClass  = get_called_class();
        if ( is_null( $instance ) || !isset($instance)) {
            $instance = new $calledClass;
        }
        return $instance;
    }
}
<?php

namespace MageArab\MegaFramework\Traits;

use MageArab\MegaFramework\Exceptions;
use MageArab\MegaFramework\Utils;


/**
 * Static class.
 */
trait StaticClass
{

    /**
     * @throws \Error
     */
    final public function __construct()
    {
        throw new \Error('Class ' . get_class($this) . ' is static and cannot be instantiated.');
    }


    /**
     * Call to undefined static method.
     * @param string $name
     * @param array $args
     * @throws \ReflectionException
     */
    public static function __callStatic(string $name, array $args)
    {
        Utils\ObjectHelpers::strictStaticCall(get_called_class(), $name);
    }
}

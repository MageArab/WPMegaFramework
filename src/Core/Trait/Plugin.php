<?php

namespace MageArab\MegaFramework\Traits;
/**
 * Singleton design pattern
 *
 * This restricts the class to a single instance. This is usually a
 * bad idea as it limits testability and encourages the global
 * state. Use dependency injection instead of this.
 */
trait Plugin
{
   use SmartObject, Singleton, Autoload;
}
<?php

namespace MageArab\MegaFramework\Traits;
use MageArab\MegaFramework\Core\Autoloader;

/**
 * Singleton design pattern
 *
 * This restricts the class to a single instance. This is usually a
 * bad idea as it limits testability and encourages the global
 * state. Use dependency injection instead of this.
 */
trait Autoload
{
    public $_loader;


    public function __construct()
    {
        Autoloader::instance();
        Autoloader::addDirectory(plugin_dir_path( __FILE__ ));
        Autoloader::register();
        $this->run();
    }
}
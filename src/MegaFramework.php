<?php
namespace MageArab\MegaFramework;

use MageArab\MegaFramework\Core\Autoloader;
use MageArab\MegaFramework\Traits\Singleton;

if (!defined('ABSPATH') || !defined('WPINC')) {
    die;
}

if (!class_exists('MegaFramework')) {
    class MegaFramework
    {
        use Singleton;

        const VERSION = '0.0.1';

        protected $basePath;
        protected $publicPath;

        public function __construct()
        {
            Autoloader::instance();
            Autoloader::addDirectory(plugin_dir_path( __FILE__ ));
            $namespaces = apply_filters('megacore_autoload', array());
            foreach ($namespaces as $path) {
                Autoloader::addDirectory($path);
            }
            Autoloader::setTempDirectory('temp');
            Autoloader::register();
        }
    }
}

<?php

/*
Plugin Name: MegaFramework
Plugin URI: http://hadyfayed.com/plugins/megacore
Description: This is a framework for wordpress to serve both theme and plugins to make it a bit easier to do your job
Version: 1.0
Author: Hady Fayed
Author URI: http://hadyfayed.com
License: GPL2
*/

use MageArab\MegaFramework\Core\Autoloader;
use MageArab\MegaFramework\MegaFramework;

if (!defined('ABSPATH') || !defined('WPINC')) {
    die;
}

require_once __DIR__ . '/src/Trait/Singleton.php';
require_once __DIR__ . '/src/Trait/SmartObject.php';
require_once __DIR__ . '/src/Trait/StaticClass.php';
require_once __DIR__ . '/src/Utils/FileSystem.php';
require_once __DIR__ . '/src/Utils/Finder.php';
require_once __DIR__ . '/src/Exceptions/MemberAccessException.php';

require_once __DIR__.'/src/Core/Autoloader.php';
require_once __DIR__ . '/src/MegaFramework.php';

function load_core_first()
{
    $path = plugin_basename(dirname(__FILE__)).'/'.basename(__FILE__);
    if ( $plugins = get_option( 'active_plugins' ) ) {
        if ( $key = array_search( $path, $plugins ) ) {
            array_splice( $plugins, $key, 1 );
            array_unshift( $plugins, $path );
            update_option( 'active_plugins', $plugins );
        }
    }
}
add_action( 'activated_plugin','load_core_first' );


Autoloader::instance();
MegaFramework::instance();


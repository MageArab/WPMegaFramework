<?php

namespace MageArab\MegaFramework\Factories;

use MageArab\MegaFramework\Core\PluginObject;
use MageArab\MegaFramework\Traits\Singleton;

class AdminMenuFactory extends PluginObject
{
    use Singleton;

    public function done(){
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
    }

    public function add_admin_menu()
    {
        add_menu_page(
            esc_html__('Page Title', $this->textDomain),
            esc_html__('Test Title', $this->textDomain),
            'manage_options',
            'test-options',
            array($this, 'page_layout'),
            '',
            99
        );
    }
}
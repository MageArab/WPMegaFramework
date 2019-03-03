<?php

namespace MageArab\MegaFramework\Helpers;

class Helper
{
    function admin_tabs( $current = 'homepage' ) {
        $tabs = array( 'homepage' => 'Home Settings', 'general' => 'General', 'footer' => 'Footer' );
        echo '<div id="icon-themes" class="icon32"><br></div>';
        echo '<h2 class="nav-tab-wrapper">';
        foreach( $tabs as $tab => $name ){
            $class = ( $tab == $current ) ? ' nav-tab-active' : ’;
            echo "<a class='nav-tab$class' href='?page=theme-settings&tab=$tab'>$name</a>";

        }
        echo '</h2>';
    }

}
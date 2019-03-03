<?php

namespace MageArab\MegaFramework\Factories;

class MetaboxFactory
{
    private $title;
    private $text_domain;
    private $context;
    private $priority;
    private $screen = array(
        'post',
        'page',
        'dashboard',
        'link',
        'attachment',
        'comment'
    );
    private $description;
    private $post;
    private $meta_fields = array(
        array(
            'label' => 'test',
            'id' => 'test_78175',
            'type' => 'email',
            'default' => 'email',
        ),
    );

    public function __construct($tile, $screen = null, $text_domain = null, $description = '', $context = 'advanced', $priority = 'high')
    {
        $this->title = $tile;
        $this->text_domain = $text_domain;
        $this->context = $context;
        $this->priority = $priority;
        //$this->screen = $screen;
        $this->description = $description;
    }

    public function setScreen($screen)
    {
        $this->screen = $screen;
        return $this;
    }

    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    public function done(){
        add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
        add_action( 'save_post', array( $this, 'save_metabox' ) );
    }

    public function add_metabox() {
        foreach ( $this->screen as $single_screen ) {
            add_meta_box(
                $this->title,
                __( $this->title, $this->text_domain ),
                array( $this, 'meta_box_callback' ),
                $single_screen,
                $this->context,
                'high'
            );
        }
    }

    public function meta_box_callback( $post ) {
        wp_nonce_field( $this->title.'_data', $this->title.'_nonce' );
        echo $this->description;
        $this->field_generator( $post );
    }

    public function field_generator( $post ) {
        $output = '';
        foreach ( $this->meta_fields as $meta_field ) {
            $label = '<label for="' . $meta_field['id'] . '">' . $meta_field['label'] . '</label>';
            $meta_value = get_post_meta( $post->ID, $meta_field['id'], true );
            if ( empty( $meta_value ) ) {
                $meta_value = $meta_field['default']; }
            switch ( $meta_field['type'] ) {
                default:
                    $input = sprintf(
                        '<input %s id="%s" name="%s" type="%s" value="%s">',
                        $meta_field['type'] !== 'color' ? 'style="width: 100%"' : '',
                        $meta_field['id'],
                        $meta_field['id'],
                        $meta_field['type'],
                        $meta_value
                    );
            }
            $output .= $this->format_rows( $label, $input );
        }
        echo '<table class="form-table"><tbody>' . $output . '</tbody></table>';
    }
    public function format_rows( $label, $input ) {
        return '<tr><th>'.$label.'</th><td>'.$input.'</td></tr>';
    }
    public function save_metabox($post_id) {
        // Check if its not a revision
        if ( wp_is_post_revision($post_id))
            return;

        // Check if nonce is correct
        if (!isset( $_POST[$this->title.'_nonce'] ) )
            return $post_id;

        $nonce = $_POST[$this->title.'_nonce'];
        if ( !wp_verify_nonce( $nonce, 'tile_data' ) )
            return $post_id;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;
        foreach ( $this->meta_fields as $meta_field ) {
            if ( isset( $_POST[ $meta_field['id'] ] ) ) {
                switch ( $meta_field['type'] ) {
                    case 'email':
                        $_POST[ $meta_field['id'] ] = sanitize_email( $_POST[ $meta_field['id'] ] );
                        break;
                    case 'text':
                        $_POST[ $meta_field['id'] ] = sanitize_text_field( $_POST[ $meta_field['id'] ] );
                        break;
                }
                update_post_meta( $post_id, $meta_field['id'], $_POST[ $meta_field['id'] ] );
            } else if ( $meta_field['type'] === 'checkbox' ) {
                update_post_meta( $post_id, $meta_field['id'], '0' );
            }
        }
    }
}
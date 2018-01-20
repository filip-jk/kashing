<?php

/**
 * Custom Post Types manager
 */

class Kashing_Post_Type {

    /**
     * Class constructor.
     */

    function __construct() {

        // Register the post type

        add_action( 'init', array( $this, 'register_post_type' ) );

        // Add custom post type columns

        add_filter( 'manage_kashing_posts_columns', array( $this, 'register_post_type_columns' ) ); // manage_{post_type_slug}_posts_columns
        add_action( 'manage_kashing_posts_custom_column', array( $this, 'manage_post_type_columns' ), 10, 2 ); // manage_{post_type_slug}_posts_custom_columns

    }

    /**
     * Custom post type registration action.
     *
     * @return void
     */

    public function register_post_type() { // Must be public so it can be accessed by WordPress add_action()

        $args = array(
            'label' => __( 'Kashing', 'kashing' ),
            'public' => true,
            'show_ui' => true,
            'capability_type' => 'post',
            'hierarchical' => true,
            'has_archive' => false,
            'menu_icon' => 'dashicons-tickets-alt',
            'supports' => array( 'title' ),
            'exclude_from_search' => true,
            'show_in_nav_menus' => false,
            'show_in_menu' => true,
            'show_in_admin_bar' => false,
            'has_archive' => false,
            'public' => false,
            'publicly_queryable' => true,
            'rewrite' => false,
            'labels' => array(
                'add_new' => __( 'Add New Form', 'kashing' ),
                'all_items' => __( 'View Forms', 'kashing' )
            )
        );

        register_post_type( 'kashing' , $args );

    }

    /**
     * Register custom post type columns.
     *
     * @param array
     *
     * @return array
     */

    public function register_post_type_columns( $columns ) {

        $columns['amount'] = __( 'Amount', 'kashing' );
        $columns['desc'] = __( 'Description', 'kashing' );
        unset( $columns['date'] ); // Change the order
        $columns['date'] = __( 'Date', 'kashing' );
        return $columns;

    }

    /**
     * Process custom post type columns content.
     *
     * @param string
     * @param int
     *
     * @return void
     */

    public function manage_post_type_columns( $column, $post_id ) {

        $prefix = Kashing_Payments::$data_prefix;

        switch ( $column ) {
            case 'amount' :
                if ( get_post_meta( $post_id, $prefix . 'amount', true ) ) {
                    echo esc_html( get_post_meta( $post_id, $prefix . 'amount', true ) );
                }
                break;
            case 'desc':
                if ( get_post_meta( $post_id, $prefix . 'desc', true ) ) {
                    echo esc_html( get_post_meta( $post_id, $prefix . 'desc', true ) );
                }
                break;
        }

    }

}

$kashing_post_type = new Kashing_Post_Type();
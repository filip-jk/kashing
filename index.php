<?php
/*
 * Plugin Name:       Kashing
 * Plugin URI:        http://github.com/tommcfarlin/post-meta-manager
 * Description:       Single Post Meta Manager displays the post meta data associated with a given post.
 * Version:           0.2.0
 * Author:            Tom McFarlin
 * Author URI:        http://tommcfarlin.com
 * Text Domain:       single-post-meta-manager-locale
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */
 
if ( ! defined( 'WPINC' ) ) {
    die;
}

add_action('init', 'engage_portfolio_register');  

if ( !function_exists("engage_portfolio_register") ) {
    
	function engage_portfolio_register() {
		
	    $args = array(
	        'label' => esc_html__( 'Kashing Payments', 'crexis'),
	        'public' => true,
	        'show_ui' => true,
	        'capability_type' => 'post',
	        'hierarchical' => true,
	        'has_archive' => false,
	        'menu_icon' => 'dashicons-art',
	        'supports' => array( 'title' )
        );  
	
	    register_post_type( 'kashing-forms' , $args );
	    
	}
    
}

// Metabox

function your_prefix_get_meta_box( $meta_boxes ) {
	$prefix = 'prefix-';

	$meta_boxes[] = array(
		'id' => 'untitled',
		'title' => esc_html__( 'Untitled Metabox', 'metabox-online-generator' ),
		'post_types' => array( 'kashing-forms' ),
		'context' => 'advanced',
		'priority' => 'high',
		'autosave' => false,
		'fields' => array(
			array(
				'id' => $prefix . 'amount',
				'type' => 'text',
				'name' => esc_html__( 'Amount', 'metabox-online-generator' ),
				'desc' => esc_html__( 'Dodatkowy opis', 'metabox-online-generator' ),
			),
			array(
				'id' => $prefix . 'name',
				'name' => esc_html__( 'Name', 'metabox-online-generator' ),
				'type' => 'checkbox',
				'std' => true,
			),
			array(
				'id' => $prefix . 'last_name',
				'name' => esc_html__( 'Last Name', 'metabox-online-generator' ),
				'type' => 'checkbox',
				'std' => true,
			),
			array(
				'id' => $prefix . 'address1',
				'name' => esc_html__( 'Address 1', 'metabox-online-generator' ),
				'type' => 'checkbox',
			),
		),
        'validation' => array(
            'rules'  => array(
                $prefix . 'amount' => array(
                    'required'  => true,
                    'minlength' => 7,
                ),
            ),
            // Optional override of default error messages
            'messages' => array(
                $prefix . 'amount' => array(
                    'required'  => 'API Key is required',
                    'minlength' => 'Password must be at least 7 characters',
                ),
            )
        )
	);

	return $meta_boxes;
}
add_filter( 'rwmb_meta_boxes', 'your_prefix_get_meta_box' );

// Shortcode

function kashing_form_config( $atts, $content = null ) {
    
    extract( shortcode_atts( array(
        "id" => '',
	), $atts ) );
    
    $test = 'podany ID: ' . $id;
    
    $test .= '<br>Amount: ' . get_post_meta( $id, 'prefix-amount', TRUE );
    
    if ( get_post_meta( $id, 'prefix-name', TRUE ) == true ) {
        $test .= 'Wyswietl pole z imieniem';
    } else {
        $test .= 'Nie wyswietlaj';
    }
    
    return $test;
}

add_shortcode( 'kashing_form', 'kashing_form_config' );

// Settings Page

// Register settings page. In this case, it's a theme options page
add_filter( 'mb_settings_pages', 'prefix_options_page' );
function prefix_options_page( $settings_pages ) {
    $settings_pages[] = array(
        'id'          => 'pencil',
        'option_name' => 'pencil',
        'menu_title'  => 'Pencil',
        'icon_url'    => 'dashicons-edit',
        'style'       => 'no-boxes',
        'columns'     => 1,
        'tabs'        => array(
            'general' => 'General Settings',
            'design'  => 'Design Customization',
            'faq'     => 'FAQ & Help',
        ),
        'position'    => 68,
    );
    return $settings_pages;
}

// Register meta boxes and fields for settings page
add_filter( 'rwmb_meta_boxes', 'prefix_options_meta_boxes' );

function prefix_options_meta_boxes( $meta_boxes ) {
    $meta_boxes[] = array(
        'id'             => 'general',
        'title'          => 'General',
        'settings_pages' => 'pencil',
        'tab'            => 'general',

        'fields' => array(
            array(
                'name' => 'API key',
                'id'   => 'api_key',
                'type' => 'text',
            ),
            array(
                'name'    => 'Layout',
                'id'      => 'layout',
                'type'    => 'image_select',
                'options' => array(
                    'sidebar-left'  => 'https://i.imgur.com/Y2sxQ2R.png',
                    'sidebar-right' => 'https://i.imgur.com/h7ONxhz.png',
                    'no-sidebar'    => 'https://i.imgur.com/m7oQKvk.png',
                ),
            ),
        ),
        'validation' => array(
            'rules'  => array(
                'api_key' => array(
                    'required'  => true,
                    'minlength' => 7,
                ),
            ),
            // Optional override of default error messages
            'messages' => array(
                'api_key' => array(
                    'required'  => 'API Key is required',
                    'minlength' => 'Password must be at least 7 characters',
                ),
            )
        )
    );
    $meta_boxes[] = array(
        'id'             => 'colors',
        'title'          => 'Colors',
        'settings_pages' => 'pencil',
        'tab'            => 'design',

        'fields' => array(
            array(
                'name' => 'Heading Color',
                'id'   => 'heading-color',
                'type' => 'color',
            ),
            array(
                'name' => 'Text Color',
                'id'   => 'text-color',
                'type' => 'color',
            ),
        ),
    );

    $meta_boxes[] = array(
        'id'             => 'info',
        'title'          => 'Theme Info',
        'settings_pages' => 'pencil',
        'tab'            => 'faq',
        'fields'         => array(
            array(
                'type' => 'custom_html',
                'std'  => 'Having questions? Check out our documentation',
            ),
        ),
    );
    return $meta_boxes;
}
<?php

class Kashing_Settings {

    public static $opt_name = 'kashing_options'; // ten array będzie wykorzystywany do opcji

    /**
     * Class Constructor.
     */

    function __construct() {

        // Add Plugin Options Array

        add_action( 'admin_init', array( $this, 'plugin_init_options' ) );

        // Plugin Options

        add_action( 'admin_menu', array( $this, 'add_plugin_settings_page' ) );

        // Define Plugin Setting Fields

        add_action( 'admin_init', array( $this, 'add_plugin_setting_fields' ) );

    }

    /**
     * Initialize the plugin options array.
     */

    function plugin_init_options() {

        add_option( 'kashing_options' );

    }

    /**
     * Register plugin settings page menu item.
     */

    public function add_plugin_settings_page() {

        call_user_func(
            'add_submenu_page',
            'edit.php?post_type=kashing',
            esc_html__( 'Settings', 'engage' ),
            esc_html__( 'Settings', 'engage' ),
            'manage_options',
            'kashing-settings',
            array( $this, 'settings_page_view' )
        );

    }

    /**
     * Plugin settings page view.
     */

    public function settings_page_view() { ?>

        <div class="wrap">
            <h1><?php esc_html_e( 'Kashing Payments', 'kashing' ); ?></h1>
            <form method="post" action="options.php">
                <?php

                settings_fields("kashing-core" );

                do_settings_sections("kashing-core-page" );

                // Options Page Submit Button

                submit_button();

                ?>
            </form>
        </div>

        <?php

    }

    /**
     * Define Plugin Setting Fields
     */

    public function add_plugin_setting_fields() {

        // Add settings section

        add_settings_section(
            "kashing-core",
            __( "Core Settings", 'kashing' ),
            null,
            "kashing-core-page"
        );

        // Add setting fields

        // Test Public Key

        add_settings_field(
            "test_public_key",
            __( 'Test Public Key', 'kashing' ),
            array( $this, "display_setting_field" ),
            "kashing-core-page",
            "kashing-core",
            array(
                'option_name' => 'test_public_key',
                'field_type' => 'text',
                'desc' => __( 'Opis', 'kashing' ), // Mozna dodać obsługę description pod inputem
                'validate' => 'jakas_walidacja' // Mozna przekazac w argumencie ze ma byc jakas walidacja danych tutaj wpisanych
            )
        );

        // Test Private Key

        add_settings_field(
            "test_private_key",
            __( 'Test Private Key', 'kashing' ),
            array( $this, "display_setting_field" ),
            "kashing-core-page",
            "kashing-core",
            array(
                'option_name' => 'test_private_key',
                'field_type' => 'text'
            )
        );

        // Test Merchant ID

        add_settings_field(
            "test_merchant_id",
            __( 'Test Merchant ID', 'kashing' ),
            array( $this, "display_setting_field" ),
            "kashing-core-page",
            "kashing-core",
            array(
                'option_name' => 'test_merchant_id',
                'field_type' => 'text'
            )
        );

        register_setting("kashing-core", "kashing_options" );

    }

    /**
     * Callback for a field type of Text
     */

    public function display_setting_field( $args ) {

        // Get field type

        if ( !array_key_exists( 'field_type', $args ) ) return null;
        $field_type = $args[ 'field_type' ];

        // Get options array

        $option_array_name = Kashing_Options::$opt_name; // Name of the general option array
        $options = get_option( $option_array_name );

        // Get field value

        $field_value = '';

        if ( array_key_exists( $args[ 'option_name' ], $options ) ) {
            $field_value = $options[ $args[ 'option_name' ] ];
        }

        // Switch field types

        switch ( $field_type ) {

            case 'text' :

                echo '<input type="text" name="' . esc_attr( $option_array_name ) . '[' . esc_attr( $args[ 'option_name' ] ) . ']" id="' . esc_attr( $args[ 'option_name' ] ) . '" value="' . esc_attr( $field_value ) . '" />';

                break;

            case 'select' :

                echo 'lista rozwijalna... i mozna dodawac inne';

                break;
        }

    }


}

//ZMIANA
$kashing_option = new Kashing_Settings();

function kashing_get_currencies_array() {

    $currencies = array(
        'GBP'   => 'GBP',
        'USD'   => 'USD'
    );

    return $currencies;

}

/**
 * Retrieve an array of all pages.
 *
 * @return key => value array
 */

function kashing_get_pages_array() {

    $pages = array();

    $all_pages = get_pages();

    foreach( $all_pages as $page ) {
        $pages[ $page->ID ] = $page->post_title;
    }

    return $pages;

}
<?php

/**
 * Kashing plugin settings page responsible for plugin settings/options storage and display.
 *
 * Uses the native WordPress Settings API.
 */

class Kashing_Settings {

    /**
     * General prefix.
     *
     * @var array
     */

    private $prefix;

    /**
     * A general name of the plugin settings array
     *
     * @var string
     */

    private $opt_name = 'kashing';

    /**
     * An array of plugin settings fields
     *
     * @var array
     */

    private $sections;

    /**
     * ID of the active settings tab.
     *
     * @var string
     */

    private $active_tab;

    /**
     * Enable or disable the fields dependency globally. Disable = works without JavaScript.
     *
     * @var string
     */

    private $fields_dependency = false;

    /**
     * Class constructor.
     */

    function __construct() {

        $this->prefix = Kashing_Payments::$data_prefix;

        // Add plugin settings page

        add_action( 'admin_menu', array( $this, 'add_plugin_settings_page' ) );

        // Get currencies

        $kashing_currency = new Kashing_Currency();

        // Declare setting fields

        $kashing_api_key_docs = 'http://kashing.com/docs/how-to-get-api-key.html';

        $this->sections = array(
            array(
                'section_id' => 'configuration',
                'title' => __( 'Configuration', 'kashing' ),
                'fields' => array(
                    array(
                        'id' => 'test_mode',
                        'title' => __( 'Test Mode', 'kashing' ),
                        'desc' => __( 'Activate or deactivate the plugin Test Mode. When Test Mode is activated, no credit card payments are processed.', 'kashing' ) . '<span class="kashing-extra-tip"><a href="' . esc_url( $kashing_api_key_docs ) . '" target="_blank">' . __( 'Retrieve your Kashing API Keys', 'kashing' ) . '</a></span>',
                        'type' => 'radio',
                        'options' => array(
                            'yes' => __( 'Yes', 'kashing' ),
                            'no' => __( 'No', 'kashing' )
                        ),
                        'default' => 'yes'
                    ),
                    array(
                        'id' => 'test_merchant_id',
                        'title' => __( 'Test Merchant ID', 'kashing' ),
                        'desc' => __( 'Enter your testing Merchant ID.', 'kashing' ),
                        'type' => 'text',
                        //'dependency' => array( 'test_mode', '!=', 'no' )
                    ),
                    array(
                        'id' => 'test_skey',
                        'title' => __( 'Test Secret Key', 'kashing' ),
                        'desc' => __( 'Enter your testing Kashing Secret Key.', 'kashing' ),
                        'type' => 'text'
                    ),
//                    array(
//                        'id' => 'test_pkey',
//                        'title' => __( 'Test Public Key', 'kashing' ),
//                        'desc' => __( 'Enter your testing Kashing Public Key.', 'kashing' ),
//                        'type' => 'text'
//                    ),
                    array(
                        'id' => 'live_merchant_id',
                        'title' => __( 'Live Merchant ID', 'kashing' ),
                        'desc' => __( 'Enter your live Merchant ID.', 'kashing' ),
                        'type' => 'text'
                    ),
                    array(
                        'id' => 'live_skey',
                        'title' => __( 'Live Secret Key', 'kashing' ),
                        'desc' => __( 'Enter your live Kashing Secret Key.', 'kashing' ),
                        'type' => 'text'
                    ),
//                    array(
//                        'id' => 'live_pkey',
//                        'title' => __( 'Live Public Key', 'kashing' ),
//                        'desc' => __( 'Enter your live Kashing Public Key.', 'kashing' ),
//                        'type' => 'text'
//                    )
                )
            ),
            array(
                'section_id' => 'general',
                'title' => __( 'General', 'kashing' ),
                'fields' => array(
                    array(
                        'id' => 'currency',
                        'title' => __( 'Choose Currency', 'kashing' ),
                        'desc' => __( 'Choose a currency for your payments.', 'kashing' ),
                        'type' => 'select',
                        'options' => $kashing_currency->get_all()
                    ),
                    array(
                        'id' => 'success_page',
                        'title' => __( 'Success Page', 'kashing' ),
                        'desc' => __( 'Choose the page your clients will be redirected to after the payment is successful.', 'kashing' ),
                        'type' => 'select',
                        'options' => kashing_get_pages_array()
                    ),
                    array(
                        'id' => 'failure_page',
                        'title' => __( 'Failure Page', 'kashing' ),
                        'desc' => __( 'Choose the page your clients will be redirected to after the payment failed.', 'kashing' ),
                        'type' => 'select',
                        'options' => kashing_get_pages_array()
                    ),
                )
            )
        );

        // Init Plugin Settings

        add_filter( 'admin_init', array( $this, 'init_plugin_settings' ) );

    }

    /**
     * Add the plugin options page to the dashboard menu.
     */

    public function add_plugin_settings_page() {

        call_user_func(
            'add_submenu_page',
            'edit.php?post_type=kashing',
            esc_html__( 'Kashing Payments Settings', 'engage' ),
            esc_html__( 'Settings', 'engage' ),
            'manage_options',
            'kashing-settings',
            array( $this, 'render_settings_page' )
        );

    }

    /**
     * Plugin settings page view.
     */

    public function render_settings_page() {

        ?>

        <div class="wrap">
            <h1><?php esc_html_e( 'Kashing Payments Settings', 'kashing' ); ?></h1>
            <form method="post" action="options.php" class="kashing-admin-form">

                <?php

                // Get active settings tab

                if ( isset( $_GET[ 'tab' ] ) ) {
                    $this->active_tab = $_GET[ 'tab' ];
                } else { // First tab active
                    $this->active_tab = $this->sections[0]['section_id'];
                }

                // Render settings tabbed nav

                $this->render_settings_nav();

                // Call for all sections

                settings_fields( $this->opt_name );

                foreach ( $this->sections as $section ) {

                    if ( $section['section_id'] == $this->active_tab ) { // Render only fields for the active settings tab
                        do_settings_sections( $this->prefix . $section['section_id'] );
                        break; // No need to continue the loop
                    }
                }

                submit_button();

                ?>
            </form>
        </div>

        <?php

    }

    public function render_settings_nav() {

        ?>
        <h2 class="nav-tab-wrapper">
            <?php

            foreach ( $this->sections as $section ) {

                if ( $this->active_tab == $section['section_id'] ) {
                    $active = ' nav-tab-active';
                } else {
                    $active = '';
                }

                echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=kashing&page=kashing-settings&tab=' . $section['section_id'] ) ) .  '" class="nav-tab' . $active . '">' . esc_html( $section['title'] ) . '</a>';

            }

            ?>
        </h2>
        <?php

    }

    /**
     * Initialize plugin settings.
     */

    public function init_plugin_settings() {

        // WP option check

        if ( false == get_option( $this->opt_name ) ) {
            add_option( $this->opt_name );
        }

        // Register sections and fields

        foreach ( $this->sections as $section ) {

            // Register the section itself

            $section_id = $this->prefix . $section[ 'section_id' ];

            add_settings_section(
                $section_id,
                $section[ 'title' ],
                array( $this, 'render_section_description' ),
                $section_id
            );

            // Register fields

            foreach ( $section['fields'] as $field ) {

                $parameters = array( // Extra parameters to be passed to a render function
                    'type' => $field['type'],
                    'desc' => $field['desc'],
                    'id' => $field['id']
                );

                // Field of type select or radio (multiple selections)

                if ( $field['type'] == 'select' || $field['type'] == 'radio' ) {
                    $parameters = array_merge(
                        $parameters,
                        array(
                            'options' => $field['options']
                        )
                    );
                }

                // Pass default value if provided

                if ( isset( $field['default'] ) ) {
                    $parameters = array_merge(
                        $parameters,
                        array(
                            'default' => $field['default']
                        )
                    );
                }

                // Hidden field based on dependency?

                if ( isset( $field['dependency'] ) && $this->fields_dependency == true ) {

                    $hidden = '';
                    $actual_value = kashing_option( $field['dependency'][0] );
                    $desired_value = $field['dependency'][2];
                    $operator = $field['dependency'][1];

                    switch ( $operator ) {
                        case '=':
                        case 'equals':
                            if ( $actual_value != $desired_value ) {
                                $hidden = 'hidden';
                            }
                            break;
                        case '!=':
                        case 'not':
                            if ( $actual_value == $desired_value ) {
                                $hidden = 'hidden';
                            }
                            break;

                    }

                    // Merge with extra data

                    $parameters = array_merge(
                        $parameters,
                        array(
                            'class' => $hidden,
                            'dependency' => $field['dependency']
                        )
                    );

                }

                // Add the field

                add_settings_field(
                    $this->prefix . $field['id'],
                    $field['title'],
                    array( $this, 'render_option_field' ),
                    $section_id,
                    $section_id, // section_id
                    $parameters
                );

            }

        }

        // Register the entire setting

        register_setting(
            $this->opt_name,
            $this->opt_name,
            array( $this, 'validate_saved_settings' )
        );

    }

    /**
     * Validate and sanitize the plugin settings
     *
     * @param array
     *
     * @return array
     */

    public function validate_saved_settings( $input ) {

        $options = get_option( $this->opt_name ); // Array of all options

        foreach ( $input as $field_name => $new_value ) {
            $options[ $field_name ] = sanitize_text_field( $new_value );
        }

        return $options;

    }

    /**
     * Render the settings section description.
     */

    public function render_section_description( $param ) {
        // Empty
    }

    /**
     * Render the settings field.
     *
     * @param array
     */

    public function render_option_field( $field ) {

        $value = '';

        $options = get_option( $this->opt_name );

        if ( isset( $options[ $field['id'] ] ) ) {
            $value = $options[ $field['id'] ];
        } elseif ( isset( $field['default'] ) ) { // Set a default value if provided
            $options[ $field['id'] ] = $value = $field['default'];
            update_option( $this->opt_name, $options );
        }

        $field_name = 'name="' . $this->opt_name . '[' . $field['id']. ']"';
        $field_id = 'id="' . $field['id'] . '"';

        // Dependency

        if ( isset( $field['dependency'] ) ) {
            $dependency_data = 'data-dep="' . esc_attr( $field['dependency'][0] ) . '" data-dep-op="' . esc_attr( $field['dependency'][1] ) . '" data-dep-val="' . esc_attr( $field['dependency'][2] ) . '"';
        } else {
            $dependency_data = '';
        }

        // Start output

        $output = '<div class="kashing-field-wrap"' . $dependency_data . '>';

        // Switch field type

        switch ( $field['type'] ) {

            case 'text': // Text Field

                $output .= '<input type="text" ' . $field_id . ' ' . $field_name . ' value="' . sanitize_text_field( $value ) . '">';
                break;

            case 'radio': // Radio buttons

                $output .= '<div class="kashing-radio-group">';

                foreach ( $field['options'] as $option_value => $option_label ) {
                    $checked = ( checked( $value, $option_value, false ) == true ) ? ' checked' : '';
                    $output .= '<label><input type="radio" ' . $field_id . ' ' . $field_name . ' value="' . esc_attr( $option_value ) . '"' . $checked . '>' . esc_html( $option_label ) . '</label>';
                }

                $output .= '</div>'; // End .kashing-radio-group

                break;

            case 'select': // Select menu

                $output .= '<select ' . $field_id . ' ' . $field_name . '>';

                foreach ( $field['options'] as $option_value => $option_label ) {
                    $selected = ( checked( $value, $option_value, false ) == true ) ? ' selected="selected"' : '';
                    $output .= '<option value="' . esc_attr( $option_value ) . '"' . $selected . '>' . esc_html( $option_label ) . '</option>';
                }

                $output .= '</select>';

                break;

        }

        $output .= '<p class="description"> '  . $field['desc'] . '</p>';
        $output .= '</div>';

        echo $output;

    }

}

$kashing_settings = new Kashing_Settings();

/**
 * Retrieve an array of all pages.
 *
 * @return array (key => value)
 */

function kashing_get_pages_array() {

    $pages = array();

    $all_pages = get_pages();

    foreach( $all_pages as $page ) {
        $pages[ $page->ID ] = $page->post_title;
    }

    return $pages;

}
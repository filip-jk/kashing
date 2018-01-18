<?php

/**
 * Allows to easily add a metabox window to a desired location along with input fields.
 */

class Kashing_Metabox {

    /**
     * Metabox ID.
     *
     * @var string
     */

    private $id;

    /**
     * Metabox Title.
     *
     * @var string
     */

    private $title;

    /**
     * The screen or screens on which to show the box (such as custom post type).
     *
     * @var string
     */

    private $post_type;

    /**
     * The context within the screen where the boxes should display.
     *
     * @var string
     */

    private $context;

    /**
     * The priority within the context where the boxes should show.
     *
     * @var string
     */

    private $priority;

    /**
     * Metabox fields array.
     *
     * @var array
     */

    private $fields;

    /**
     * Class constructor.
     *
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param array
     */

    function __construct( $id, $title, $post_type, $context, $priority, $fields ) {

        $this->id = $id;
        $this->title = $title;
        $this->post_type = $post_type;
        $this->context = $context;
        $this->priority = $priority;
        $this->fields = $fields;

        add_action( 'add_meta_boxes', array( $this, 'action_add_meta_box') );
        add_action( 'save_post_' . $this->post_type, array( $this, 'save_meta_box' ) );

    }

    /**
     * Register the metabox with the "add_meta_boxes" WordPress action.
     *
     * @param int
     */

    public function action_add_meta_box( $post_id ) {
        add_meta_box(
            $this->id,
            $this->title,
            array( $this, 'render_meta_box' ),
            $this->post_type,
            $this->context,
            $this->priority
        );
    }

    /**
     * Render a meta box.
     *
     * @param int
     */

    public function render_meta_box( $post ) {

        echo '<div class="kashing-metabox kashing-admin-form">';

        // Nonce field

        wp_nonce_field( 'kashing_meta_nonce_action', 'kashing_meta_nonce' );

        // Fields loop

        foreach ( $this->fields as $field ) {

            if ( $field['type'] == 'heading' ) {

                echo '<h3 class="kashing-field-heading">' . esc_html( $field['title'] ) . '</h3>';

            } else { // Not a heading

                $css_classes = $required = $errors = '';

                // Input

                $field_value = get_post_meta( $post->ID, $field['id'], true );

                // Check if required

                if ( isset( $field['validate']['required'] ) ) {

                    $css_classes .= ' required-field';
                    $required = ' required';

                    if ( get_post_status( $post->ID ) == 'publish' && empty( $field_value ) ) { // Do not display the note if post is not created yet
                        $errors .= '<span class="kashing-error required-field">' . __( 'This field is required', 'kashing' ) . '</span>';
                    }

                }

                // Begin output

                echo '<div class="kashing-field kashing-field-' . esc_attr( $field['type'] ) . $css_classes . '">';

                // Label

                echo '<div class="kashing-label"><label for="' . esc_attr( $field['id'] ) . '">' . esc_html( $field['title'] ) . '</label></div>';

                // Additional CSS classes for extra styling

                if ( isset( $field['validate']['class'] ) ) {
                    $css_classes .= ' ' . $field['validate']['class'];
                }

                // Field output

                echo '<div class="kashing-input">';

                switch ( $field['type'] ) {

                    case 'checkbox':
                        ?>
                        <label class="checkbox-extra-label"><input type="checkbox" name="<?php esc_attr_e( $field['id'] ); ?>" value="1" <?php checked( $field_value, 1 ); ?>>
                        <?php
                        break;

                    case 'text':
                        if ( isset( $field['validate']['number'] ) ) {
                            $pattern = ' pattern="[0-9.,]+"';
                        } else {
                            $pattern = '';
                        }
                        echo '<input type="text" id="' . esc_attr( $field['id'] ) . '" ' . $pattern . ' name="' . esc_attr( $field['id'] ) . '" value="' . esc_html( $field_value ). '"' . $required . '>';
                        break;

                    case 'textarea':
                        echo '<textarea rows="3" id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['id'] ) . '"' . $required . '>' . esc_textarea( $field_value ) . '</textarea>';
                        break;

                    default:
                        echo __( 'Unsupported field type', 'kashing' );
                        break;

                }

                // Optional description

                if ( isset( $field['desc'] ) ) {
                    echo '<div class="kashing-desc">' . $field['desc'] . '</div>';
                }

                // If there are any notifications to be displayed

                if ( $errors != '' ) echo $errors;

                // Close the extra label for checkbox and radios

                if ( $field['type'] == 'checkbox' || $field['type'] == 'radio' ) echo '</label>';

                echo '</div></div>';

            }

            // Field end

        }

        echo '</div>';

    }

    /**
     * Save meta box fields data.
     */

    public function save_meta_box( $post_id ) {

        // Check the form nonce

        if ( !isset( $_POST['kashing_meta_nonce'] ) || !wp_verify_nonce( $_POST['kashing_meta_nonce'], 'kashing_meta_nonce_action' ) ){
            return;
        }

        // Return if autosave or if user does not have sufficient permission

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE && !current_user_can( 'edit_post', $post_id ) ){
            return;
        }

        // Save each field

        foreach ( $this->fields as $field ) {

            if ( array_key_exists( 'id', $field ) && isset( $_POST[ $field['id'] ] ) ) {
                update_post_meta( $post_id, $field['id'], sanitize_text_field( $_POST[ $field['id'] ] ) );
            } elseif ( $field['type'] == 'checkbox' ) {
                delete_post_meta( $post_id, $field['id'] );
            }

        }

    }

}
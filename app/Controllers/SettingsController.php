<?php
declare( strict_types = 1 );
namespace DumahX\MeprAV\Controllers;

defined( 'ABSPATH' ) || exit;

final class SettingsController {
    private static $instance = null;

    /**
     * Constructor
     * 
     * Associates methods with the proper hooks.
     * 
     * @access private
     * @return void
     */
    private function __construct() {
        add_action( 'admin_init', \Closure::fromCallable( [ $this, 'register_settings' ] ) );
    }

    /**
     * register_settings()
     * 
     * Registers setting, setting sections, and setting fields for the plugin.
     * 
     * @access public
     * @return void
     */
    private function register_settings(): void {
        register_setting( 'mepr_av_options', 'mepr_av_options', \Closure::fromCallable( [ $this, 'validate_options' ] ) );

        add_settings_section(
            'mepr_av_setting_section',
            esc_html__( 'Settings', 'mepr-age-verification' ),
            \Closure::fromCallable( [ $this, 'render_settings_section' ] ),
            'mepr_av_options'
        );

        // Breaking this up into easier to read bits.
        $this->add_setting_fields();
    }

    /**
     * add_setting_fields()
     * 
     * Adds all of the setting fields used for the plugin.
     * 
     * @access private
     * @return void
     */
    private function add_setting_fields(): void {
        add_settings_field(
            'mepr_av_enable',
            esc_html__( 'Enable Age Verification', 'mepr-age-verification' ),
            \Closure::fromCallable( [ $this, 'render_checkbox' ] ),
            'mepr_av_options',
            'mepr_av_setting_section',
            [
                'id'    => 'mepr_av_enable'
            ]
        );

        add_settings_field(
            'mepr_av_slug_name',
            esc_html__( 'Name Of Slug', 'mepr-age-verification' ),
            \Closure::fromCallable( [ $this, 'render_text' ] ),
            'mepr_av_options',
            'mepr_av_setting_section',
            [
                'id'    => 'mepr_av_slug_name',
                'class' => 'hidden'
            ]
        );

        add_settings_field(
            'mepr_av_min_age',
            esc_html__( 'Minimum Age To Signup (Years)', 'mepr-age-verification' ),
            \Closure::fromCallable( [ $this, 'render_number' ] ),
            'mepr_av_options',
            'mepr_av_setting_section',
            [
                'id'    => 'mepr_av_min_age',
                'class' => 'hidden'
            ]
        );

        add_settings_field(
            'mepr_av_error_message',
            esc_html__( 'Error Message', 'mepr-age-verification' ),
            \Closure::fromCallable( [ $this, 'render_textarea' ] ),
            'mepr_av_options',
            'mepr_av_setting_section',
            [
                'id'    => 'mepr_av_error_message',
                'class' => 'hidden'
            ]
        );

        add_settings_field(
            'mepr_av_verification_error_message',
            esc_html__( 'Verification Error Message', 'mepr-age-verification' ),
            \Closure::fromCallable( [ $this, 'render_textarea' ] ),
            'mepr_av_options',
            'mepr_av_setting_section',
            [
                'id'    => 'mepr_av_verification_error_message',
                'class' => 'hidden'
            ]
        );

        add_settings_field(
            'mepr_av_memberships',
            esc_html__( 'Memberships', 'mepr-age-verification' ),
            \Closure::fromCallable( [ $this, 'render_multiselect' ] ),
            'mepr_av_options',
            'mepr_av_setting_section',
            [
                'id'    => 'mepr_av_memberships',
                'class' => 'hidden'
            ]
        );
    }

    /**
     * validate_options
     * 
     * Validates the options.
     * 
     * @access private
     * @return array
     */
    private function validate_options( $input ): array {
        $option = get_option( 'mepr_av_options', self::get_option_defaults() );
        $custom_field_match = false;
        $mepr_options = \MeprOptions::fetch();
        $custom_fields = $mepr_options->custom_fields;

        if ( ! isset( $input['mepr_av_enable'] ) ) {
            $input['mepr_av_enable'] = null;
        }

        $input['mepr_av_enable'] = ($input['mepr_av_enable'] == 1 ? 1 : 0);

        if ( isset( $input['mepr_av_slug_name'] ) ) {
            $input['mepr_av_slug_name'] = sanitize_text_field( $input['mepr_av_slug_name'] );
        }

        if ( isset( $input['mepr_av_min_age'] ) ) {
            $input['mepr_av_min_age'] = intval( $input['mepr_av_min_age'] );
        }

        if ( isset( $input['mepr_av_error_message'] ) ) {
            $input['mepr_av_error_message'] = wp_kses_post( $input['mepr_av_error_message'] );
        }

        if ( isset( $input['mepr_av_verification_error_message'] ) ) {
            $input['mepr_av_verification_error_message'] = wp_kses_post( $input['mepr_av_verification_error_message'] );
        }

        // Some quick validation.
        if ( empty( $input['mepr_av_memberships'] ) ) {
            add_settings_error( 'mepr_av_options', 'mepr_av_memberships', __( 'Memberships cannot be empty.', 'mepr-age-verification' ) );
        }

        if ( empty( trim( (string) $input['mepr_av_min_age'] ) ) ) {
            add_settings_error( 'mepr_av_options', 'mepr_av_min_age', __( 'Minimum age cannot be empty.', 'mepr-age-verification' ) );
            $input['mepr_av_min_age'] = $option['mepr_av_min_age'];
        }

        if ( empty( trim( $input['mepr_av_error_message'] ) ) ) {
            add_settings_error( 'mepr_av_options', 'mepr_av_error_message', __( 'Error message cannot be empty.', 'mepr-age-verification' ) );
            $input['mepr_av_error_message'] = $option['mepr_av_error_message'];
        }

        if ( empty( trim( $input['mepr_av_verification_error_message'] ) ) ) {
            add_settings_error( 'mepr_av_options', 'mepr_av_verification_error_message', __( 'Verification error message cannot be empty.', 'mepr-age-verification' ) );
            $input['mepr_av_verification_error_message'] = $option['mepr_av_verification_error_message'];
        }

        // Loop through all custom fields and make sure there's a match.
        if ( is_array( $custom_fields ) ) {
            foreach ( $custom_fields as $field ) {
                if ( ! $custom_field_match && $input['mepr_av_slug_name'] == $field->field_key ) {
                    $custom_field_match = true;
                    
                    // Make sure the custom field is actually a date.
                    if ( $field->field_type != 'date' ) {
                        add_settings_error( 'mepr_av_options', 'mepr_av_invalid_slug_type', __( 'Slug name must be a date field.', 'mepr-age-verification' ) );
                        $input['mepr_av_slug_name'] = $option['mepr_av_slug_name'];
                    }

                    // Make sure the custom field will be shown at signup.
                    if ( ! $field->show_on_signup ) {
                        add_settings_error( 'mepr_av_options', 'mepr_av_invalid_slug_format', __( 'Custom field must have "Show at Signup" enabled.', 'mepr-age-verification' ) );
                        $input['mepr_av_slug_name'] = $option['mepr_av_slug_name'];
                    }
                }
            }
        }

        if ( ! $custom_field_match && ! empty( trim( $input['mepr_av_slug_name'] ) ) ) {
            add_settings_error( 'mepr_av_options', 'mepr_av_invalid_slug', __( 'Slug name provided is not a valid field in MemberPress.', 'mepr-age-verification' ) );
            $input['mepr_av_slug_name'] = $option['mepr_av_slug_name'];
        }

        return $input;
    }

    /**
     * render_settings_section()
     * 
     * Renders the HTML for the settings section.
     * 
     * @access private
     * @return void
     */
    private function render_settings_section(): void {
        include MEPR_AV_VIEW . 'settings-section.php';
    }

    /**
     * render_checkbox()
     * 
     * Renders the HTML for the checkbox field.
     * 
     * @access private
     * @return void
     */
    private function render_checkbox( $args ): void {
        $option = get_option( 'mepr_av_options', self::get_option_defaults() );
        $id = isset( $args['id'] ) ? trim( $args['id'] ) : '';
        $tip = isset( $args['tip'] ) ? esc_attr( $args['tip'] ) : '';
        $checked = isset( $option[ $id ] ) ? checked( $option[ $id ], 1, false ) : '';

        include MEPR_AV_VIEW . 'fields/checkbox.php';
    }

    /**
     * render_text()
     * 
     * Renders the HTML for the text field.
     * 
     * @access private
     * @return void
     */
    private function render_text( $args ): void {
        $option = get_option( 'mepr_av_options', self::get_option_defaults() );
        $id = isset( $args['id'] ) ? trim( $args['id'] ) : '';
        $tip = isset( $args['tip'] ) ? esc_attr( $args['tip'] ) : '';
        $value = isset( $option[ $id ] ) ? sanitize_text_field( $option[ $id ] ) : '';
        
        include MEPR_AV_VIEW . 'fields/text.php';
    }

    /**
     * render_textarea()
     * 
     * Renders the HTML for the textarea field.
     * 
     * @access private
     * @return void
     */
    private function render_textarea( $args ): void {
        $option = get_option( 'mepr_av_options', self::get_option_defaults() );
        $id = isset( $args['id'] ) ? trim( $args['id'] ) : '';
        $tip = isset( $args['tip'] ) ? esc_attr( $args['tip'] ) : '';
        $allowed_tags = wp_kses_allowed_html( 'post' );
        $value = isset( $option[ $id ] ) ? wp_kses( stripslashes_deep( $option[ $id ] ), $allowed_tags ) : '';

        include MEPR_AV_VIEW . 'fields/textarea.php';
    }

    /**
     * render_number()
     * 
     * Renders the HTML for the number field.
     * 
     * @access private
     * @return void
     */
    private function render_number( $args ): void {
        $option = get_option( 'mepr_av_options', self::get_option_defaults() );
        $id = isset( $args['id'] ) ? trim( $args['id'] ) : '';
        $tip = isset( $args['tip'] ) ? esc_attr( $args['tip'] ) : '';
        $value = isset( $option[ $id ] ) ? (int) $option[ $id ] : '';

        include MEPR_AV_VIEW . 'fields/number.php';
    }

    /**
     * render_multiselect()
     * 
     * Renders the HTML for the select field with multiple values.
     * 
     * @access private
     * @return void
     */
    private function render_multiselect( $args ): void {
        $option = get_option( 'mepr_av_options', self::get_option_defaults() );
        $id = isset( $args['id'] ) ? trim( $args['id'] ) : '';
        $tip = isset( $args['tip'] ) ? esc_attr( $args['tip'] ) : '';
        $value = isset( $option[ $id ] ) ? $option[ $id ] : '';

        $memberships = self::get_membership_names();
        

        include MEPR_AV_VIEW . 'fields/multiselect.php';
    }

    /**
     * get_membership_names()
     * 
     * Returns an array of all membership names.
     * 
     * @access private
     * @return array
     */
    private static function get_membership_names(): array {
        $memberships = [];

        if ( is_array( \MeprProduct::get_all() ) ) {
            foreach( \MeprProduct::get_all() as $membership ) {
                $memberships[] = $membership->post_title;
            }
        }

        return $memberships;
    }

    /**
     * get_option_defaults()
     * 
     * Returns an array of option defaults for mepr_av_options.
     * 
     * @access public
     * @return array
     */
    public static function get_option_defaults(): array {
        return [
            'mepr_av_enable'                     => false,
            'mepr_av_slug_name'                  => '',
            'mepr_av_min_age'                    => 18,
            'mepr_av_error_message'              => esc_html__( 'Due to your age, you\'re not eligible to register.', 'mepr-age-verification' ),
            'mepr_av_verification_error_message' => esc_html__( 'You must fill out your birthday.', 'mepr-age-verification' ),
            'mepr_av_memberships'                => self::get_membership_names()
        ];
    }

    /**
     * instance()
     * 
     * Allows only one instance of the class to be loaded.
     * 
     * @access public
     * @return object
     */
    public static function instance(): SettingsController {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    }
}
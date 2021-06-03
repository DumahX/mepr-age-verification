<?php
namespace DumahX\MeprAV\Controllers;
use DumahX\MeprAV\Controllers\SettingsController;

defined( 'ABSPATH' ) || exit;

final class SignupController {
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
        add_filter( 'mepr-validate-signup', \Closure::fromCallable( [ $this, 'validate' ] ) );
    }

    /**
     * validate()
     * 
     * The meat and potatoes of the plugin.
     * Validates that the member in question can signup for the membership.
     * 
     * @access private
     * @return mixed
     */
    private function validate( $errors ) {
        $option = get_option( 'mepr_av_options', SettingsController::get_option_defaults() );
        $enable = isset( $option['mepr_av_enable'] ) && ! empty( $option['mepr_av_enable'] ) ? (bool) $option['mepr_av_enable'] : false;
        $slug = isset( $option['mepr_av_slug_name'] ) && ! empty( $option['mepr_av_slug_name'] ) ? sanitize_text_field( $option['mepr_av_slug_name'] ) : '';
        $age = isset( $option['mepr_av_min_age'] ) && ! empty( $option['mepr_av_min_age'] ) ? intval( $option['mepr_av_min_age'] ) : '';
        $error_message = isset( $option['mepr_av_error_message'] ) && ! empty( $option['mepr_av_error_message'] ) ? wp_kses_post( $option['mepr_av_error_message'] ) : '';
        $verification_error_message = isset( $option['mepr_av_verification_error_message'] ) && ! empty( $option['mepr_av_verification_error_message'] ) ? wp_kses_post( $option['mepr_av_verification_error_message'] ) : '';
        $memberships = isset( $option['mepr_av_memberships'] ) && ! empty( $option['mepr_av_memberships'] ) ? $option['mepr_av_memberships'] : [];
        $membership_id = isset( $_POST['mepr_product_id'] ) ? trim( $_POST['mepr_product_id'] ) : '';
        $membership = new \MeprProduct( $membership_id );

        // Age verification is turned off, don't proceed.
        if ( ! $enable ) {
            return $errors;
        }

        // If user is already logged in or there's no membership ID, simply return.
        if ( is_user_logged_in() || empty( $membership_id ) ) {
            return $errors;
        }

        // If the slug name is empty, just return.
        if ( empty( $slug ) ) {
            return $errors;
        }

        // Check if membership needs to be verified.
        if ( ! in_array( $membership->post_title, $memberships ) ) {
            return $errors;
        }

        // If the field wasn't filled out, make sure that it is required.
        if ( ! isset( $_POST[ $slug ] ) || empty( $_POST[ $slug ] ) ) {
            $errors[] = $verification_error_message;
            return $errors;
        }

        $then = strtotime( $_POST[ $slug ] );
        $min = strtotime( '+' . $age . ' years', $then );

        // User doesn't meet criteria.
        if ( time() < $min ) {
            $errors[] = $error_message;
        }

        return $errors;
        
    }

    /**
     * instance()
     * 
     * Allows only one instance of the class to be loaded.
     * 
     * @access public
     * @return object
     */
    public static function instance(): SignupController {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    }
}
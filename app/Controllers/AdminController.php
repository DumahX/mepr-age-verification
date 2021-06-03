<?php
declare( strict_types = 1 );
namespace DumahX\MeprAV\Controllers;

defined( 'ABSPATH' ) || exit;

final class AdminController {
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
        add_action( 'admin_menu', \Closure::fromCallable( [ $this, 'add_options_page' ] ) );
    }

    /**
     * add_options_page()
     * 
     * Adds an option page to the Settings menu.
     * 
     * @access private
     * @return void
     */
    private function add_options_page(): void {
        $hook_suffix = add_options_page(
            __( 'MemberPress Age Verification', 'mepr-age-verification' ),
            __( 'MemberPress Age Verification', 'mepr-age-verification' ),
            'manage_options',
            'mepr-age-verification',
            \Closure::fromCallable( [ $this, 'render_options_page' ] )
        );

        add_action( 'load-' . $hook_suffix, \Closure::fromCallable( [ $this, 'load_scripts_styles' ] ) );
    }

    /**
     * load_scripts_styles()
     * 
     * Hooks into admin_enqueue_scripts to register and enqueue scripts and styles for the mepr-age-verification options page.
     * 
     * @access private
     * @return void
     */
    private function load_scripts_styles(): void {
        add_action( 'admin_enqueue_scripts', \Closure::fromCallable( [ $this, 'enqueue_scripts_styles' ] ) );
    }

    /**
     * enqueue_scripts_styles()
     * 
     * Registers and enqueues scripts and styles for the mepr-age-verification options page.
     * 
     * @access private
     * @return void
     */
    private function enqueue_scripts_styles(): void {
        wp_register_script(
            'mepr-av-options',
            MEPR_AV_JS_URL . 'options.js',
            [ 'jquery' ]
        );

        wp_register_style(
            'mepr-av-options',
            MEPR_AV_CSS_URL . 'options.css'
        );

        wp_enqueue_script( 'mepr-av-options' );
        wp_enqueue_style( 'mepr-av-options' );
    }

    /**
     * render_options_page()
     * 
     * Renders the content of the mepr-age-verification options page.
     * 
     * @access private
     * @return void
     */
    private function render_options_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        require_once MEPR_AV_VIEW . 'options-page.php';
    }

    /**
     * instance()
     * 
     * Allows only one instance of the class to be loaded.
     * 
     * @access public
     * @return object
     */
    public static function instance(): AdminController {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

}
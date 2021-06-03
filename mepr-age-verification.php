<?php
declare( strict_types = 1 );
/*
Plugin Name: MemberPress Age Verification
Description: Restrict member signup based on the member's age.
Version: 1.0.0
Author: Tyler Gilbert
Author URI: https://profiles.wordpress.org/tylerthedude/
Requires: PHP 7.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: mepr-age-verification
Domain Path: /languages

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/
namespace DumahX\MeprAV;
use DumahX\MeprAV\Controllers\AdminController;
use DumahX\MeprAV\Controllers\SettingsController;
use DumahX\MeprAV\Controllers\SignupController;

defined( 'ABSPATH' ) || exit;

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( is_plugin_active( 'memberpress/memberpress.php' ) ) {
    if ( version_compare( phpversion(), '7.2.0', '<' ) ) {
        add_action( 'admin_notices', static function () {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'MemberPress Age Verification requires PHP 7.2.0 or later.', 'mepr-age-verification' ) . '</p></div>';
        } );

        return;
    }

    if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
        require_once __DIR__ . '/vendor/autoload.php';
    }

    define( 'MEPR_AV_VIEW', plugin_dir_path( __FILE__ ) . 'app/Views/' );
    define( 'MEPR_AV_JS_URL', plugins_url( 'js/', __FILE__ ) );
    define( 'MEPR_AV_CSS_URL', plugins_url( 'css/', __FILE__ ) );

    require_once plugin_dir_path( __FILE__ ) . 'app/Lib/functions.php';

    if ( is_admin() ) {
        AdminController::instance();
        SettingsController::instance();
    }

    SignupController::instance();
}
<?php

function mepr_av_activate() {
    load_plugin_textdomain( 'mepr-age-verification', false, plugin_dir_path( __FILE__ ) . 'languages/' );
}
add_action( 'init', 'mepr_av_activate' );
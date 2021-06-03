<?php defined( 'ABSPATH' ) || exit; ?>
<div class="wrap mepr-av-options">
    <h2><?php _e( 'MemberPress Age Verification', 'mepr-age-verification' ); ?></h2>

    <form action="options.php" method="post">
        <?php

        settings_fields( 'mepr_av_options' );
        do_settings_sections( 'mepr_av_options' );
        submit_button();

        ?>
    </form>
</div>
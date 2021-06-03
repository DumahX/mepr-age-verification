<?php defined( 'ABSPATH' ) || exit; ?>

<select id="mepr_av_options_<?php echo $id; ?>" name="mepr_av_options[<?php echo $id; ?>][]" multiple="multiple" required>
    <?php foreach ( $memberships as $membership ) : ?>
        <?php if ( is_array( $option[ $id ] ) ) : ?>
            <?php $selected = in_array( $membership, $option[ $id ] ) ? 'selected="selected"' : ''; ?>
        <?php endif; ?>
        <option value="<?php echo $membership; ?>" <?php echo $selected; ?>><?php echo $membership; ?></option>
    <?php endforeach; ?>
</select>

<i>Hold down the Ctrl (windows) or Command (Mac) key to select multiple options.</i>
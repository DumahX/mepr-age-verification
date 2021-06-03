jQuery( document ).ready( function ( $ ) {
    // Check if age verification is already enabled.
    if ( $( '#mepr_av_options_mepr_av_enable' ).is( ':checked' ) ) {
        $( '#mepr_av_options_mepr_av_enable' ).parent().parent().siblings().show();
    }

    $( '#mepr_av_options_mepr_av_enable' ).change( function () {
        if ( this.checked ) {
            $( '#mepr_av_options_mepr_av_enable' ).parent().parent().siblings().show();
        } else {
            $( '#mepr_av_options_mepr_av_enable' ).parent().parent().siblings().hide();
        }
    } );
} );
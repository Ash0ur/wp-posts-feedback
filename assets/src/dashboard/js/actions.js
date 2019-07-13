jQuery(document).ready( function($){

    wp.codeEditor.initialize($('#feedback-custom-css') );

    $('#feedback-post-type-title').on('change', function() {
        $('#feedback-title-' + $(this).val() ).addClass('active').siblings().removeClass('active');
    });

    $('#feedback-thumbs-up').wpColorPicker();
    $('#feedback-thumbs-down').wpColorPicker();

    jQuery('#feedback-thumbs-up').iris({change: function( event, ui ) {
        jQuery('.dashicons-thumbs-up').css( 'color',  ui.color.toString() );
    } })

    jQuery(' #feedback-thumbs-down').iris({change: function( event, ui ) {
        jQuery('.dashicons-thumbs-down').css( 'color',  ui.color.toString() );
    } })

});
jQuery(function() {
    jQuery('#revisar_sin_stock').on('click', function( e ) {
        e.preventDefault();
        jQuery('#actualizar_sinstock_done').html('Revisando productos sin stock. Puede tardar varios segundos...');
        jQuery.ajax( {
            url: jQuery('#revisar_ajaxurl').val(),
            method: 'POST',
            data: {
                action : 'revisar_sin_stock'
            }
        }).done(function( data ) {
            jQuery('#actualizar_sinstock_done').html(data);
        });

    });

    jQuery('#revisar_sale').on('click', function( e ) {
        e.preventDefault();
        jQuery('#actualizar_sinstock_done').html('Revisando productos con ofertas. Puede tardar varios segundos...');
        jQuery.ajax( {
            url: jQuery('#revisar_ajaxurl').val(),
            method: 'POST',
            data: {
                action : 'revisar_sale'
            }
        }).done(function( data ) {
            jQuery('#actualizar_sinstock_done').html(data);
        }).fail(function(error) {
            console.log('ERROR', error)
        });

    });

    jQuery(document).on('submit', '#actualizar_stock', function( e ) {
        e.preventDefault();
        jQuery('#actualizar_stock_done').html('Obteniendo datos. Puede tardar varios segundos...');
        jQuery.ajax( {
            url: jQuery(this).attr('action'),
            type: 'POST',
            data: new FormData( this ),
            processData: false,
            contentType: false
        }).done(function( data ) {
            console.log('data', data);
            jQuery('#actualizar_stock_done').html(data);
        });

    });
})

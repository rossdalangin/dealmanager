(function( $ ) {
    'use strict';

    $(function() {
        $('.kanban-cards-container').sortable({
            connectWith: '.kanban-cards-container',
            placeholder: 'ui-sortable-placeholder',
            receive: function( event, ui ) {
                var deal_id = ui.item.attr('id').replace('deal-', '');
                var new_stage = $(this).parent().attr('id').replace('stage-', '');

                $.ajax({
                    url: kanban_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'update_deal_stage',
                        deal_id: deal_id,
                        new_stage: new_stage,
                        nonce: kanban_ajax.nonce
                    },
                    success: function( response ) {
                        if ( ! response.success ) {
                            // Handle error, maybe move the card back
                            console.error( 'Failed to update deal stage.' );
                            $(ui.sender).sortable('cancel');
                        }
                    },
                    error: function() {
                        console.error( 'AJAX error.' );
                        $(ui.sender).sortable('cancel');
                    }
                });
            }
        }).disableSelection();
    });

})( jQuery );

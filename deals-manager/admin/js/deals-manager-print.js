(function( $ ) {
    'use strict';

    $(function() {
        $('#print-deals-form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var data = form.serialize();

            // Add nonce and action
            data += '&action=dm_print_deals';
            data += '&nonce=' + print_deals_ajax.nonce;

            $.post(print_deals_ajax.ajax_url, data, function(response) {
                if (response.success) {
                    var printWindow = window.open('', '_blank');
                    printWindow.document.write(response.data.html);
                    printWindow.document.close();
                    printWindow.focus();
                } else {
                    alert('Error: ' + response.data.message);
                }
            });
        });
    });

})( jQuery );

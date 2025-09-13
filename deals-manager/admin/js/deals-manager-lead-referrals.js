(function( $ ) {
    'use strict';

    $(function() {
        var form = $('#lead-referrals-form');
        var container = $('#lead-referrals-report-container');
        var actions = $('#lead-referrals-report-actions');

        form.on('submit', function(e) {
            e.preventDefault();

            var data = form.serialize();

            // Add nonce and action
            data += '&action=dm_generate_lead_referrals_report';
            data += '&nonce=' + lead_referrals_ajax.nonce;

            // Add loading indicator
            container.html('<p>Loading...</p>');
            actions.hide();

            $.post(lead_referrals_ajax.ajax_url, data, function(response) {
                if (response.success) {
                    container.html(response.data.html);
                    actions.show();
                } else {
                    container.html('<p>Error: ' + response.data.message + '</p>');
                }
            }).fail(function() {
                container.html('<p>An unexpected error occurred. Please try again.</p>');
            });
        });

        $('#print-lead-referrals-report').on('click', function(e) {
            e.preventDefault();
            var reportHtml = container.html();
            var printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Print Report</title>');
            printWindow.document.write('<style>body { font-family: sans-serif; } table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid #ccc; padding: 8px; text-align: left; } th { background-color: #f2f2f2; }</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(reportHtml);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        });

        $('#export-lead-referrals-report').on('click', function(e) {
            e.preventDefault();
            var data = form.serialize();
            // Point to admin-post.php for direct file download.
            // The action for our admin-post handler is 'dm_export_lead_referrals'.
            var url = lead_referrals_ajax.admin_post_url + '?action=dm_export_lead_referrals&nonce=' + lead_referrals_ajax.nonce + '&' + data;
            window.location.href = url;
        });
    });

})( jQuery );

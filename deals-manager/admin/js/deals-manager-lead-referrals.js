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
            data += '&action=dm_export_lead_referrals_csv';
            data += '&nonce=' + lead_referrals_ajax.nonce;

            $.post(lead_referrals_ajax.ajax_url, data, function(response) {
                if (response.success) {
                    var blob = new Blob([response.data.csv], { type: 'text/csv;charset=utf-8;' });
                    var link = document.createElement("a");
                    var url = URL.createObjectURL(blob);
                    link.setAttribute("href", url);
                    link.setAttribute("download", "lead-referrals-report.csv");
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    alert('Error: ' + response.data.message);
                }
            }).fail(function() {
                alert('An unexpected error occurred. Please try again.');
            });
        });
    });

})( jQuery );

(function( $ ) {
    'use strict';

    $(function() {
        if ( typeof reports_data === 'undefined' ) {
            return;
        }

        // Deals by Stage Chart
        var ctx = document.getElementById('deals-by-stage-chart');
        if (ctx) {
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: reports_data.deals_by_stage.labels,
                    datasets: [{
                        label: 'Deals by Stage',
                        data: reports_data.deals_by_stage.data,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: false
                        }
                    }
                }
            });
        }

        // Deals by User Table
        var userTable = $('#deals-by-user-table');
        if (userTable.length) {
            if (reports_data.deals_by_user.length) {
                reports_data.deals_by_user.forEach(function(user) {
                    userTable.append('<tr><td>' + user.name + '</td><td>' + user.count + '</td></tr>');
                });
            } else {
                userTable.append('<tr><td colspan="2">No data available</td></tr>');
            }
        }

        // Summary
        $('#summary-total-deals').text(reports_data.summary.total_deals);
        $('#summary-total-won-value').text(reports_data.summary.total_won_value);
        $('#summary-conversion-rate').text(reports_data.summary.conversion_rate);
    });

})( jQuery );

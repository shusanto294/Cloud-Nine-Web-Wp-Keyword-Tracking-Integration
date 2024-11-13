jQuery(document).ready(function($) {
    var ctx = document.getElementById('analyticsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line', // You can change this to 'bar', 'pie', etc.
        data: {
            labels: dates,
            datasets: [{
                label: 'Pageviews',
                data: pageviews,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true,
            }, {
                label: 'Visitors',
                data: visitors,
                borderColor: 'rgba(153, 102, 255, 1)',
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                fill: true,
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
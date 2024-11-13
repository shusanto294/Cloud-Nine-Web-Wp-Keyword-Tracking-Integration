<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Enqueue scripts and styles
function analytics_enqueue_scripts() {
    wp_enqueue_style('analytics-styles', plugin_dir_url(__FILE__) . 'css/analytics.css');
    wp_enqueue_script('chart-js', plugin_dir_url(__FILE__) . 'js/chart.min.js', array('jquery'), null, true);
    wp_enqueue_script('analytics-script', plugin_dir_url(__FILE__) . 'js/analytics.js', array('jquery', 'chart-js'), null, true);
}
add_action('admin_enqueue_scripts', 'analytics_enqueue_scripts');

// Create a dashboard widget
function analytics_dashboard_widget() {
    wp_add_dashboard_widget('analytics_widget', 'Cloud Nine Web Analytics', 'analytics_display_widget');
}
add_action('wp_dashboard_setup', 'analytics_dashboard_widget');

// Settings page for API Key and User ID
function analytics_settings_page() {
    add_management_page(
        'Analytics Settings',
        'Analytics',
        'manage_options',
        'cloudnine-analytics',
        'analytics_render_settings_page'
    );
}
add_action('admin_menu', 'analytics_settings_page');

function analytics_render_settings_page() {
    // Check if the user has submitted the settings
    if (isset($_POST['submit'])) {
        update_option('analytics_user_id', sanitize_text_field($_POST['analytics_user_id']));
        update_option('analytics_web_id', sanitize_text_field($_POST['analytics_web_id']));
        update_option('analytics_api_key', 'Bearer ' . sanitize_text_field(trim($_POST['analytics_api_key'])));
        echo '<div class="updated"><p>Settings saved successfully!</p></div>';
    }

    // Retrieve current values
    $web_id = get_option('analytics_web_id', '');
    $user_id = get_option('analytics_user_id', '');
    $api_key = get_option('analytics_api_key', '');

    // Render the settings form
    ?>
    <div class="wrap">
        <h1>Analytics Settings</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">User ID</th>
                    <td><input type="text" name="analytics_user_id" value="<?php echo esc_attr($user_id); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Website ID</th>
                    <td><input type="text" name="analytics_web_id" value="<?php echo esc_attr($web_id); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td><input type="text" name="analytics_api_key" value="<?php echo esc_attr(trim(str_replace('Bearer ', '', $api_key))); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Callback function to display the widget contents
function analytics_display_widget() {
    // Retrieve API Key and User ID from options
    $api_key = get_option('analytics_api_key', ''); // Get stored API key
    $user_id = get_option('analytics_user_id', ''); // Get stored User ID
    $web_id = get_option('analytics_web_id', '');
    $start_date = date('Y-m-d', strtotime('-30 days'));
    $end_date = date('Y-m-d');
    $url = "https://analytics.cloudnineweb.co/api/statistics/$web_id?start_date=$start_date&end_date=$end_date&type=overview";

    $response = wp_remote_get($url, array(
        'headers' => array(
            'Authorization' => $api_key,
        ),
    ));

    if (is_wp_error($response)) {
        echo 'Unable to retrieve data.';
        return;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($data['data'])) {
        echo 'No data available for the selected period.';
        return;
    }

    // Calculate total pageviews and visitors, and prepare data for the chart
    $total_pageviews = 0;
    $total_visitors = 0;
    $dates = [];
    $pageviews = [];
    $visitors = [];

    foreach ($data['data'] as $day) {
        $total_pageviews += $day['pageviews'];
        $total_visitors += $day['visitors'];
        $dates[] = $day['formatted_date'];
        $pageviews[] = $day['pageviews'];
        $visitors[] = $day['visitors'];
    }

    // Display total pageviews and visitors
    echo '<h3>Analytics Overview - Last 30 Days</h3>';
    echo '<h3>Total Pageviews: <b>' . esc_html($total_pageviews) . '</b></h3>';
    echo '<h3>Total Visitors: <b>' . esc_html($total_visitors) . '</b></h3>';

    // Create a canvas for the chart
    echo '<canvas id="analyticsChart" style="width:100%; max-width:600px;"></canvas>';

    // Pass data to the chart script
    echo '<script type="text/javascript">
        var dates = ' . json_encode($dates) . ';
        var pageviews = ' . json_encode($pageviews) . ';
        var visitors = ' . json_encode($visitors) . ';
    </script>';

    // Initialize the chart
    echo '<script type="text/javascript">
        jQuery(document).ready(function($) {
            var ctx = document.getElementById("analyticsChart").getContext("2d");
            var analyticsChart = new Chart(ctx, {
                type: "line", // You can change this to "bar", "pie", etc.
                data: {
                    labels: dates,
                    datasets: [{
                        label: "Pageviews",
                        data: pageviews,
                        borderColor: "rgba(75, 192, 192, 1)",
                        backgroundColor: "rgba(75, 192, 192, 0.2)",
                        fill: true,
                    }, {
                        label: "Visitors",
                        data: visitors,
                        borderColor: "rgba(153, 102, 255, 1)",
                        backgroundColor: "rgba(153, 102, 255, 0.2)",
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
    </script>';

    // Display the 'Display All Analytics' link styled as a button
    echo '<br><br><a href="' . admin_url('admin-ajax.php?action=redirect_handler') . '" class="button button-primary" target="_blank">Display All Analytics</a>';
}

// AJAX action for redirect handler
add_action('wp_ajax_redirect_handler', function() {
    include plugin_dir_path(__FILE__) . 'redirect-handler.php';
});

// Footer script for AJAX handling
function analytics_ajax_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#displayAllAnalytics').on('click', function(event) {
                event.preventDefault(); // Prevent the default link behavior
                var userId = <?php echo get_current_user_id(); ?>; // Use current user ID
                
                // Generate one-time login code
                $.ajax({
                    url: 'https://analytics.cloudnineweb.co/admin-api/users/' + userId + '/one-time-login-code',
                    type: 'POST',
                    headers: {
                        'Authorization': '<?php echo get_option('analytics_api_key', ''); ?>', // Use stored API Key
                        'Content-Type': 'application/json'
                    },
                    success: function(response) {
                        console.log('Success:', response); // Debugging output
                        if (response.data && response.data.url) {
                            // Open the URL in a new tab
                            window.open(response.data.url, '_blank');
                        } else {
                            alert('URL not found in the response.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', status, error); // Log error
                        alert('Failed to generate one-time login code: ' + error);
                    }
                });
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'analytics_ajax_script');

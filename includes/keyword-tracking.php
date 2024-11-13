<?php


// Add action if the feature is enabled
add_action('admin_init', function() {
    // Get the value of the checkbox field from the options page
    $seo_features = get_field('enable_seo_features', 'option');

    // Check if 'cnw-keyword-tracking' is in the array
    if (is_array($seo_features) && in_array('cnw-keyword-tracking', $seo_features)) {
        add_action('wp_dashboard_setup', 'load_keyword_tracking_widget');
    }
});


function load_keyword_tracking_widget(){
    add_meta_box('dev_keyword_tracking_widget', 'Keyword Tracking', 'cnw_keyword_tracking_widget', 'dashboard', 'side', 'high');
}

function cnw_keyword_tracking_widget() {
    // Define the API URL and the API key
    $domain_name = parse_url(get_bloginfo('url'), PHP_URL_HOST);
    $url = "https://serp.cloudnineweb.co/api/keywords?domain=$domain_name";
    $apiKey = "64e0b422e86703399e0fe028baca0abfd5a84eb7f111e0aa6d505755dd977574";
    

    // Initialize a cURL session
    $ch = curl_init($url);

    // Set the request method to GET
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

    // Set the Authorization header
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey"
    ]);

    // Return the response as a string instead of outputting it
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the request
    $response = curl_exec($ch);

    // Check for cURL errors
    if ($response === false) {
        $error = curl_error($ch);
        echo "cURL Error: $error";
    } else {
        // Convert the JSON response to a PHP object
        $data = json_decode($response);



        // Check if json_decode was successful
        if (json_last_error() === JSON_ERROR_NONE) {
            echo '<style>
                table#keyword-tracking {
                    box-shadow: none !important;
                    width: 100%;
                    border-collapse: collapse;
                }
                table#keyword-tracking th, table#keyword-tracking td {
                    border-bottom: 1px solid #ddd;
                    padding: 10px 0 !Important;
                    text-align: left;
                }
            </style>';
            echo '<table id="keyword-tracking"><tr><th>Keyword</th><th>Position</th></tr>';
            // Successfully converted to PHP object
            foreach($data->keywords as $keyword){
                echo "<tr><td>$keyword->keyword</td><td>$keyword->position</td></tr>";
            }
            echo '</table>';

        } else {
            echo "JSON Decode Error: " . json_last_error_msg();
        }
    }

    // Close the cURL session
    curl_close($ch);
}
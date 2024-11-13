jQuery(document).ready(function($) {
    // Check if cnwAdminData and cnwAdminData.userRoles are defined
    if (typeof cnwAdminData !== 'undefined' && typeof cnwAdminData.userRoles !== 'undefined') {
        // Parse the userRoles JSON string
        var roles = JSON.parse(cnwAdminData.userRoles);
        var $rolesContainer = $('#user_roles');

        // Clear the container
        $rolesContainer.empty();

        // Create and append checkboxes
        $.each(roles, function(value, nameWithCount) {
            var roleName = nameWithCount.replace(/\s*\d+$/, ""); // Remove count from role name
            var userCountMatch = nameWithCount.match(/\d+$/); // Extract count (numeric part only)
            var userCount = userCountMatch ? userCountMatch[0] + ' users' : ""; // Get the count string or empty if not found

            var $checkbox = $('<input>').attr({
                type: 'checkbox',
                id: 'role_' + value,
                name: 'user_roles[]',
                value: value
            });
            var $label = $('<label>').attr('for', 'role_' + value).text(roleName);
            var $span = $('<span>').addClass('user-count').text(userCount);

            // Append the checkbox, label, and count span to the container
            $rolesContainer.append($checkbox).append($label).append($span).append('<br>');
        });
    }

    // Function to move the div to the target container
    var moveDivToTarget = function() {
        var div = $('.postbox.cnw-email-send'); // Select the div
        var targetContainer = $('#wpbody #wpbody-content .wrap.acf-settings-wrap #post #poststuff #post-body #postbox-container-2 #normal-sortables');

        if (div.length && targetContainer.length) {
            targetContainer.prepend(div); // Move the div
        }
    };

    // Call the function to move the div
    moveDivToTarget();
});

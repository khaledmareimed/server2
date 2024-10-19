<?php
/*
Plugin Name: WooCommerce WhatsApp Integration
Description: Sends WhatsApp notifications to customers and admins for new orders and password reset requests.
Version: 1.2
Author: Your Name
*/

// Function to send WhatsApp messages using the Meta WhatsApp API
function send_whatsapp_message($phone_number, $message) {
    $whatsapp_number_id = get_option('whatsapp_phone_number_id');
    $access_token = get_option('whatsapp_access_token');

    if (!$whatsapp_number_id || !$access_token) {
        return;  // Exit if API credentials are not set
    }

    $url = 'https://graph.facebook.com/v17.0/' . $whatsapp_number_id . '/messages';
    
    $payload = json_encode([
        'messaging_product' => 'whatsapp',
        'to' => $phone_number,
        'type' => 'text',
        'text' => [
            'body' => $message
        ]
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

// Send WhatsApp message on new order
add_action('woocommerce_thankyou', 'send_order_whatsapp_notification', 10, 1);
function send_order_whatsapp_notification($order_id) {
    if (!$order_id) return;

    $order = wc_get_order($order_id);
    $customer_phone = $order->get_billing_phone();
    $order_details = "Order #{$order_id} - Total: " . $order->get_total();

    // Get messages and admin phone number from options
    $customer_message = get_option('whatsapp_customer_message', 'Thank you for your order! Your order ID is ' . $order_id);
    $admin_phone_number = get_option('whatsapp_admin_phone_number', '');
    $admin_message = "New order received: " . $order_details;

    // Send message to customer
    if ($customer_phone) {
        send_whatsapp_message($customer_phone, $customer_message);
    }

    // Send message to admin
    if ($admin_phone_number) {
        send_whatsapp_message($admin_phone_number, $admin_message);
    }
}

// Add settings field for API credentials, custom messages, and admin phone number in WordPress admin
add_action('admin_menu', 'whatsapp_settings_menu');
function whatsapp_settings_menu() {
    add_menu_page('WhatsApp Settings', 'WhatsApp Settings', 'manage_options', 'whatsapp-settings', 'whatsapp_settings_page');
}

function whatsapp_settings_page() {
    if ($_POST) {
        // Save settings
        update_option('whatsapp_access_token', sanitize_text_field($_POST['whatsapp_access_token']));
        update_option('whatsapp_phone_number_id', sanitize_text_field($_POST['whatsapp_phone_number_id']));
        update_option('whatsapp_customer_message', sanitize_textarea_field($_POST['whatsapp_customer_message']));
        update_option('whatsapp_admin_phone_number', sanitize_text_field($_POST['whatsapp_admin_phone_number']));

        echo '<div class="updated"><p>Settings updated successfully!</p></div>';
    }

    // Get saved values
    $access_token = get_option('whatsapp_access_token', '');
    $phone_number_id = get_option('whatsapp_phone_number_id', '');
    $customer_message = get_option('whatsapp_customer_message', 'Thank you for your order!');
    $admin_phone_number = get_option('whatsapp_admin_phone_number', '');

    ?>
    <div class="wrap">
        <h1>WhatsApp Settings</h1>
        <form method="POST">
            <h2>WhatsApp API Settings</h2>
            <label for="whatsapp_access_token">Access Token:</label><br>
            <input type="text" name="whatsapp_access_token" value="<?php echo esc_attr($access_token); ?>" size="50"><br><br>
            
            <label for="whatsapp_phone_number_id">WhatsApp Phone Number ID:</label><br>
            <input type="text" name="whatsapp_phone_number_id" value="<?php echo esc_attr($phone_number_id); ?>" size="50"><br><br>

            <h2>Customer Order Message</h2>
            <textarea name="whatsapp_customer_message" rows="5" cols="50"><?php echo esc_textarea($customer_message); ?></textarea><br><br>

            <h2>Admin Phone Number</h2>
            <input type="text" name="whatsapp_admin_phone_number" value="<?php echo esc_attr($admin_phone_number); ?>" size="50" placeholder="Enter admin WhatsApp number"><br><br>

            <input type="submit" value="Save Settings" class="button button-primary">
        </form>
    </div>
    <?php
}

// Send WhatsApp message for password reset
add_filter('retrieve_password_message', 'send_whatsapp_reset_password', 10, 4);
function send_whatsapp_reset_password($message, $key, $user_login, $user_data) {
    $user_phone = get_user_meta($user_data->ID, 'billing_phone', true);

    if ($user_phone) {
        $reset_link = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
        $whatsapp_message = "Reset your password using this link: " . $reset_link;
        send_whatsapp_message($user_phone, $whatsapp_message);

        return '';  // Prevents sending the email
    }

    return $message;  // Fallback to email if phone number is missing
}
?>

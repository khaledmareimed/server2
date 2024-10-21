<?php
/*
Plugin Name: WooCommerce Order WhatsApp Notification with Password Reset
Description: Sends a WhatsApp notification using CallMeBot API when a new WooCommerce order is placed, and when the admin requests a password reset.
Version: 1.1
Author: Your Name
*/

// Hook into WooCommerce's order creation action
add_action('woocommerce_thankyou', 'send_whatsapp_notification_on_new_order', 10, 1);

function send_whatsapp_notification_on_new_order($order_id) {
    // Get the order details
    $order = wc_get_order($order_id);
    $order_number = $order->get_order_number();
    $order_date = date('Y-m-d H:i:s', strtotime($order->get_date_created()));
    $order_total = $order->get_total();

    // Prepare WhatsApp message
    $phone_number = '972597758060'; // The phone number you want to send the notification to
    $apikey = '1269039'; // Your CallMeBot API key
    $message = 'You have a new order. Order Number: ' . $order_number . '. Order Time: ' . $order_date . '. Order Total: ' . $order_total . ' ILS.';

    // Create the URL for the CallMeBot API request
    $api_url = 'https://api.callmebot.com/whatsapp.php?phone=' . $phone_number . '&text=' . urlencode($message) . '&apikey=' . $apikey;

    // Send the API request
    wp_remote_get($api_url);
}

// Hook into the password reset request
add_action('retrieve_password_message', 'send_whatsapp_forgot_password_link', 10, 4);

function send_whatsapp_forgot_password_link($message, $key, $user_login, $user_data) {
    // Check if the user is an admin
    if (in_array('administrator', $user_data->roles)) {
        // Generate the password reset link
        $reset_link = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');

        // Prepare WhatsApp message
        $phone_number = '972597758060'; // Admin's phone number
        $apikey = '1269039'; // Your CallMeBot API key
        $whatsapp_message = 'Admin password reset request. Click the link to reset: ' . $reset_link;

        // Create the URL for the CallMeBot API request
        $api_url = 'https://api.callmebot.com/whatsapp.php?phone=' . $phone_number . '&text=' . urlencode($whatsapp_message) . '&apikey=' . $apikey;

        // Send the API request
        wp_remote_get($api_url);
    }

    // Return the original email message (unchanged)
    return $message;
}

<?php
/*
Plugin Name: WooCommerce Order WhatsApp Notification with Editable Settings
Description: Sends a WhatsApp notification using CallMeBot API when a new WooCommerce order is placed, with editable settings for API key, phone number, and message. Default WordPress behavior for password reset.
Version: 1.2
Author: Your Name
*/

// Hook into WooCommerce's order creation action
add_action('woocommerce_thankyou', 'send_whatsapp_notification_on_new_order', 10, 1);

function send_whatsapp_notification_on_new_order($order_id) {
    // Get the admin settings
    $phone_number = get_option('woo_whatsapp_phone_number', '');
    $apikey = get_option('woo_whatsapp_api_key', '');
    $message_template = get_option('woo_whatsapp_order_message', 'You have a new order. Order Number: {order_number}, Order Time: {order_date}, Total: {order_total} ILS.');

    // Get the order details
    $order = wc_get_order($order_id);
    $order_number = $order->get_order_number();
    $order_date = date('Y-m-d H:i:s', strtotime($order->get_date_created()));
    $order_total = $order->get_total();

    // Replace placeholders with actual order data
    $message = str_replace(
        ['{order_number}', '{order_date}', '{order_total}'],
        [$order_number, $order_date, $order_total],
        $message_template
    );

    // Create the URL for the CallMeBot API request
    $api_url = 'https://api.callmebot.com/whatsapp.php?phone=' . $phone_number . '&text=' . urlencode($message) . '&apikey=' . $apikey;

    // Send the API request
    wp_remote_get($api_url);
}

// Add settings menu to the WordPress admin
add_action('admin_menu', 'woo_whatsapp_settings_menu');

function woo_whatsapp_settings_menu() {
    add_menu_page(
        'WhatsApp Notification Settings', 
        'WhatsApp Notifications', 
        'manage_options', 
        'woo-whatsapp-settings', 
        'woo_whatsapp_settings_page', 
        'dashicons-email-alt', 
        80
    );
}

// Settings page content
function woo_whatsapp_settings_page() {
    ?>
    <div class="wrap">
        <h1>WhatsApp Notification Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('woo_whatsapp_settings_group');
            do_settings_sections('woo-whatsapp-settings');
            submit_button();
            ?>
        </form>
        <h3>Placeholders for Message:</h3>
        <p>
            <strong>{order_number}</strong> - Inserts the order number.<br>
            <strong>{order_date}</strong> - Inserts the order date and time.<br>
            <strong>{order_total}</strong> - Inserts the total price in ILS.<br>
        </p>
    </div>
    <?php
}

// Register settings
add_action('admin_init', 'woo_whatsapp_register_settings');

function woo_whatsapp_register_settings() {
    // Register settings
    register_setting('woo_whatsapp_settings_group', 'woo_whatsapp_phone_number');
    register_setting('woo_whatsapp_settings_group', 'woo_whatsapp_api_key');
    register_setting('woo_whatsapp_settings_group', 'woo_whatsapp_order_message');

    // Add settings fields
    add_settings_section('woo_whatsapp_main_settings', 'Main Settings', null, 'woo-whatsapp-settings');

    add_settings_field(
        'woo_whatsapp_phone_number',
        'WhatsApp Phone Number',
        'woo_whatsapp_phone_number_callback',
        'woo-whatsapp-settings',
        'woo_whatsapp_main_settings'
    );

    add_settings_field(
        'woo_whatsapp_api_key',
        'CallMeBot API Key',
        'woo_whatsapp_api_key_callback',
        'woo-whatsapp-settings',
        'woo_whatsapp_main_settings'
    );

    add_settings_field(
        'woo_whatsapp_order_message',
        'Order Notification Message',
        'woo_whatsapp_order_message_callback',
        'woo-whatsapp-settings',
        'woo_whatsapp_main_settings'
    );
}

// Callback functions for each setting
function woo_whatsapp_phone_number_callback() {
    $phone_number = get_option('woo_whatsapp_phone_number', '');
    echo '<input type="text" name="woo_whatsapp_phone_number" value="' . esc_attr($phone_number) . '" />';
}

function woo_whatsapp_api_key_callback() {
    $apikey = get_option('woo_whatsapp_api_key', '');
    echo '<input type="text" name="woo_whatsapp_api_key" value="' . esc_attr($apikey) . '" />';
}

function woo_whatsapp_order_message_callback() {
    $message = get_option('woo_whatsapp_order_message', 'You have a new order. Order Number: {order_number}, Order Time: {order_date}, Total: {order_total} ILS.');
    echo '<textarea name="woo_whatsapp_order_message" rows="5" cols="50">' . esc_textarea($message) . '</textarea>';
}

<?php
/*
Plugin Name: WooCommerce Order WhatsApp Notification with Editable Settings and Admin Password Reset
Description: Sends a WhatsApp notification using CallMeBot API when a new WooCommerce order is placed, and sends password reset link via WhatsApp for admin users.
Version: 1.3
Author: Your Name
*/

// Hook into WooCommerce's order creation action
add_action('woocommerce_thankyou', 'send_whatsapp_notification_on_new_order', 10, 1);

function send_whatsapp_notification_on_new_order($order_id) {
    // Get the admin settings
    $phone_number = get_option('woo_whatsapp_phone_number', '');
    $apikey = get_option('woo_whatsapp_api_key', '');
    $message_template = get_option('woo_whatsapp_order_message', 'تم استلام طلب جديد من متجر {store_name}. رقم الطلب: {order_number}. تاريخ الطلب: {order_date}. المبلغ الإجمالي: {order_total} شيكل.');

    // Get the store name
    $store_name = get_bloginfo('name');

    // Get the order details
    $order = wc_get_order($order_id);
    $order_number = $order->get_order_number();
    $order_date = date('Y-m-d H:i:s', strtotime($order->get_date_created()));
    $order_total = $order->get_total();

    // Replace placeholders with actual order data
    $message = str_replace(
        ['{order_number}', '{order_date}', '{order_total}', '{store_name}'],
        [$order_number, $order_date, $order_total, $store_name],
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
        <h1>إعدادات إشعارات واتساب</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('woo_whatsapp_settings_group');
            do_settings_sections('woo-whatsapp-settings');
            submit_button();
            ?>
        </form>
        <h3>الرموز المتاحة في الرسالة:</h3>
        <p>
            <strong>{order_number}</strong> - رقم الطلب.<br>
            <strong>{order_date}</strong> - تاريخ ووقت الطلب.<br>
            <strong>{order_total}</strong> - المبلغ الإجمالي بالشيكل.<br>
            <strong>{store_name}</strong> - اسم المتجر.<br>
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
        'رقم واتساب',
        'woo_whatsapp_phone_number_callback',
        'woo-whatsapp-settings',
        'woo_whatsapp_main_settings'
    );

    add_settings_field(
        'woo_whatsapp_api_key',
        'API Key',
        'woo_whatsapp_api_key_callback',
        'woo-whatsapp-settings',
        'woo_whatsapp_main_settings'
    );

    add_settings_field(
        'woo_whatsapp_order_message',
        'رسالة إشعار الطلب',
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
    $message = get_option('woo_whatsapp_order_message', 'تم استلام طلب جديد من متجر {store_name}. رقم الطلب: {order_number}. تاريخ الطلب: {order_date}. المبلغ الإجمالي: {order_total} شيكل.');
    echo '<textarea name="woo_whatsapp_order_message" rows="5" cols="50">' . esc_textarea($message) . '</textarea>';
}

// Hook into the password reset request
add_action('retrieve_password_message', 'send_whatsapp_forgot_password_link', 10, 4);

function send_whatsapp_forgot_password_link($message, $key, $user_login, $user_data) {
    // Check if the user is an admin
    if (in_array('administrator', $user_data->roles)) {
        // Generate the password reset link
        $reset_link = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');

        // Prepare WhatsApp message
        $phone_number = get_option('woo_whatsapp_phone_number', ''); // Admin's phone number from settings
        $apikey = get_option('woo_whatsapp_api_key', ''); // Your CallMeBot API key
        $whatsapp_message = 'طلب إعادة تعيين كلمة المرور للمسؤول. رابط إعادة التعيين: ' . $reset_link;

        // Create the URL for the CallMeBot API request
        $api_url = 'https://api.callmebot.com/whatsapp.php?phone=' . $phone_number . '&text=' . urlencode($whatsapp_message) . '&apikey=' . $apikey;

        // Send the API request
        wp_remote_get($api_url);
    }

    // Return the original email message (unchanged)
    return $message;
}

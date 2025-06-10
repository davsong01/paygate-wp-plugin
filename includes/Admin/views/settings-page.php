<?php
if (!current_user_can('manage_options')) {
    return;
}

global $wpdb;

$initiation_endpoint = $wpdb->get_var("SELECT option_value FROM {$settings_table} WHERE option_key = 'initiation_endpoint'");
$webhook_secret      = $wpdb->get_var("SELECT option_value FROM {$settings_table} WHERE option_key = 'webhook_secret'");
$modal_title         = $wpdb->get_var("SELECT option_value FROM {$settings_table} WHERE option_key = 'modal_title'");
$modal_text         = $wpdb->get_var("SELECT option_value FROM {$settings_table} WHERE option_key = 'modal_text'");
$button_text         = $wpdb->get_var("SELECT option_value FROM {$settings_table} WHERE option_key = 'button_text'");

$settings_table = $wpdb->prefix . 'paygate_settings';

// Load current settings
$api_key  = $wpdb->get_var("SELECT option_value FROM {$settings_table} WHERE option_key = 'api_key'");
$base_url = $wpdb->get_var("SELECT option_value FROM {$settings_table} WHERE option_key = 'base_url'");

// Save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('paygate_settings_save')) {
    $new_api_key  = sanitize_text_field($_POST['paygate_api_key']);
    $new_base_url = esc_url_raw($_POST['paygate_base_url']);

    $wpdb->replace($settings_table, ['option_key' => 'api_key', 'option_value' => $new_api_key]);
    $wpdb->replace($settings_table, ['option_key' => 'base_url', 'option_value' => $new_base_url]);

    echo '<div class="updated"><p>Settings saved.</p></div>';
    $api_key  = $new_api_key;
    $base_url = $new_base_url;
}
?>

<div class="wrap">
    <h1>Settings</h1>
    <form method="post">
        <?php
        wp_nonce_field('paygate_settings_save');
        $new_endpoint      = sanitize_text_field($_POST['paygate_initiation_endpoint']);
        $new_webhook       = sanitize_text_field($_POST['paygate_webhook_secret']);
        $new_modal_title   = sanitize_text_field($_POST['paygate_modal_title']);
        $new_modal_text   = sanitize_text_field($_POST['paygate_modal_text']);
        $new_button_text   = sanitize_text_field($_POST['paygate_button_text']);

        $wpdb->replace($settings_table, ['option_key' => 'initiation_endpoint', 'option_value' => $new_endpoint]);
        $wpdb->replace($settings_table, ['option_key' => 'webhook_secret', 'option_value' => $new_webhook]);
        $wpdb->replace($settings_table, ['option_key' => 'modal_title', 'option_value' => $new_modal_title]);
        $wpdb->replace($settings_table, ['option_key' => 'modal_text', 'option_value' => $new_modal_text]);
        $wpdb->replace($settings_table, ['option_key' => 'button_text', 'option_value' => $new_button_text]);

        // Refresh values
        $initiation_endpoint = $new_endpoint;
        $webhook_secret      = $new_webhook;
        $modal_title         = $new_modal_title;
        $modal_text             = $new_modal_text;
        $button_text         = $new_button_text;
        ?>

        <table class="form-table">
            <tr>
                <th scope="row"><label for="paygate_api_key">API Key</label></th>
                <td><input name="paygate_api_key" type="text" id="paygate_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="paygate_base_url">Base URL</label></th>
                <td><input name="paygate_base_url" type="url" id="paygate_base_url" value="<?php echo esc_attr($base_url); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="paygate_initiation_endpoint">Initiation Endpoint</label></th>
                <td><input name="paygate_initiation_endpoint" type="text" id="paygate_initiation_endpoint" value="<?php echo esc_attr($initiation_endpoint); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="paygate_webhook_secret">Webhook Secret</label></th>
                <td><input name="paygate_webhook_secret" type="text" id="paygate_webhook_secret" value="<?php echo esc_attr($webhook_secret); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="paygate_modal_title">Modal Title</label></th>
                <td><input name="paygate_modal_title" type="text" id="paygate_modal_title" value="<?php echo esc_attr($modal_title); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="paygate_modal_text">Modal Text</label></th>
                <td><input name="paygate_modal_text" type="text" id="paygate_modal_text" value="<?php echo esc_attr($modal_text); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="paygate_button_text">Default Button Text</label></th>
                <td><input name="paygate_button_text" type="text" id="paygate_button_text" value="<?php echo esc_attr($button_text); ?>" class="regular-text"></td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
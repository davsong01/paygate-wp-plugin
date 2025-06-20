<?php 
namespace Controllers;

class SettingsController
{
    public function render()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'paygate_settings';

        $settings = $wpdb->get_results("SELECT * FROM $table ORDER BY option_key ASC");
        include plugin_dir_path(__FILE__) . '/../templates/settings/index.php';
    }

    public function save()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'paygate_settings';

        if (!isset($_POST['paygate_settings_form']) || !isset($_POST['settings']) || !is_array($_POST['settings'])) {
            wp_redirect(admin_url('admin.php?page=paygate_settings&status=error'));
            exit;
        }
        
        foreach ($_POST['settings'] as $key => $value) {
            $wpdb->update(
                $table,
                ['option_value' => sanitize_text_field($value)],
                ['option_key' => sanitize_key($key)]
            );
        }

        wp_redirect(admin_url('admin.php?page=paygate_settings&status=success'));
        exit;
    }
}

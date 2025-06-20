<?php
class Settings {
    public static function get($key, $default = null) {
        global $wpdb;
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT option_value FROM {$wpdb->prefix}paygate_settings WHERE option_key = %s", $key
        ));
        return $value !== null ? maybe_unserialize($value) : $default;
    }

    public static function set($key, $value) {
        global $wpdb;
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}paygate_settings WHERE option_key = %s", $key
        ));
        if ($exists) {
            $wpdb->update(
                "{$wpdb->prefix}paygate_settings",
                ['option_value' => maybe_serialize($value)],
                ['option_key' => $key]
            );
        } else {
            $wpdb->insert("{$wpdb->prefix}paygate_settings", [
                'option_key'   => $key,
                'option_value' => maybe_serialize($value),
            ]);
        }
    }
}

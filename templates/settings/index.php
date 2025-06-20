<div class="wrap">
    <h1 class="wp-heading-inline">PayGate Settings</h1>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div class="notice notice-success is-dismissible">
            <p>Settings saved successfully.</p>
        </div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'error'): ?>
        <div class="notice notice-error is-dismissible">
            <p>Invalid submission.</p>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= esc_url(admin_url('admin-post.php')) ?>">
        <input type="hidden" name="action" value="paygate_save_settings">
        <input type="hidden" name="paygate_settings_form" value="1">

        <table class="form-table" role="presentation">
            <tbody>
                <?php foreach ($settings as $setting): ?>
                    <tr>
                        <th scope="row">
                            <label for="<?= esc_attr($setting->option_key) ?>">
                                <?= esc_html(ucwords(str_replace('_', ' ', $setting->option_key))) ?>
                            </label>
                        </th>
                        <td>
                            <input type="text"
                                name="settings[<?= esc_attr($setting->option_key) ?>]"
                                id="<?= esc_attr($setting->option_key) ?>"
                                class="regular-text"
                                value="<?= esc_attr($setting->option_value) ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php submit_button('Save Settings'); ?>
    </form>
</div>
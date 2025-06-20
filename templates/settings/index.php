<div class="wrap">
    <h1>PayGate Settings</h1>

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

        <div class="row">
            <?php foreach ($settings as $setting): ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?= esc_html(ucwords(str_replace('_', ' ', $setting->option_key))) ?></label>
                    <input type="text" name="settings[<?= esc_attr($setting->option_key) ?>]" class="form-control" value="<?= esc_attr($setting->option_value) ?>">
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
</div>
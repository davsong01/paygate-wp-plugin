<?php
global $wpdb;
$table = $wpdb->prefix . 'paygate_shortcodes';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('paygate_save_shortcode')) {
    $slug = sanitize_title($_POST['slug']);
    $button_text = sanitize_text_field($_POST['button_text']);
    $product_name = sanitize_text_field($_POST['product_name']);

    if ($slug && $button_text && $product_name) {
        $wpdb->replace($table, [
            'slug' => $slug,
            'button_text' => $button_text,
            'product_name' => $product_name
        ]);
    }
}

if (isset($_GET['delete'])) {
    $wpdb->delete($table, ['id' => intval($_GET['delete'])]);
}

$shortcodes = $wpdb->get_results("SELECT * FROM {$table} ORDER BY id DESC");
?>

<div class="wrap">
    <h1>Manage Paygate Shortcodes</h1>

    <h2>Add New Shortcode</h2>
    <form method="POST">
        <?php wp_nonce_field('paygate_save_shortcode'); ?>
        <table class="form-table">
            <tr>
                <th><label for="slug">Shortcode Slug</label></th>
                <td><input type="text" name="slug" id="slug" required class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="button_text">Button Text</label></th>
                <td><input type="text" name="button_text" id="button_text" required class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="product_name">Product Name</label></th>
                <td><input type="text" name="product_name" id="product_name" required class="regular-text"></td>
            </tr>
        </table>
        <?php submit_button('Save Shortcode'); ?>
    </form>

    <hr>

    <h2>Saved Shortcodes</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Slug</th>
                <th>Button Text</th>
                <th>Product Name</th>
                <th>Shortcode</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($shortcodes as $shortcode): ?>
                <tr>
                    <td><?php echo esc_html($shortcode->id); ?></td>
                    <td><?php echo esc_html($shortcode->slug); ?></td>
                    <td><?php echo esc_html($shortcode->button_text); ?></td>
                    <td><?php echo esc_html($shortcode->product_name); ?></td>
                    <td><code>[paygate slug="<?php echo esc_attr($shortcode->slug); ?>"]</code></td>
                    <td><a href="?page=paygate-shortcodes&delete=<?php echo esc_attr($shortcode->id); ?>" onclick="return confirm('Delete this shortcode?');">Delete</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
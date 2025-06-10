<form id="paygate-payment-form">
    <label for="amount"><?php esc_html_e('Amount', 'paygate-wordpress-plugin'); ?></label>
    <input type="number" name="amount" id="amount" required />

    <button type="submit"><?php esc_html_e('Pay Now', 'paygate-wordpress-plugin'); ?></button>
</form>
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="customerForm" class="modal-content">
            <div class="modal-header">
                <h5>Add Customer</h5>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="paygate_save_customer">
                <?php wp_nonce_field('paygate_customers', 'nonce'); ?>
                <input name="name" class="form-control mb-2" placeholder="Full Name" required>
                <input name="email" class="form-control mb-2" placeholder="Email" required>
                <input name="phone" class="form-control mb-2" placeholder="Phone">
                <select name="gender" class="form-control mb-2">
                    <option value="">Select Gender</option>
                    <option>Male</option>
                    <option>Female</option>
                </select>
                <input name="address" class="form-control mb-2" placeholder="Address">
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">Save</button>
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>
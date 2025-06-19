<div class="container mt-4">
    <h4>Customers</h4>
    <button id="addCustomerBtn" class="btn btn-primary mb-3">+ Add Customer</button>
    <?php include_once PAYGATE_PATH . 'templates/partials/admin-alert.php'; ?>

    <div class="table-responsive">
        <table id="customersTable" class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Gender</th>
                    <th>Bank Details</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<?php include plugin_dir_path(__FILE__) . '/modal-form.php'; ?>
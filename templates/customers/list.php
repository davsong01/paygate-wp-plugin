<div class="wrap">
    <h1 class="wp-heading-inline">Customers</h1>
    <a href="#" id="addCustomerBtn" class="page-title-action">+ Add Customer</a>

    <?php include_once PAYGATE_PATH . 'templates/partials/admin-alert.php'; ?>

    <hr class="wp-header-end">

    <table id="customersTable" class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Phone</th>
                <th scope="col">Gender</th>
                <th scope="col">Bank Details</th>
                <th scope="col">Created</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<?php include plugin_dir_path(__FILE__) . '/modal-form.php'; ?>
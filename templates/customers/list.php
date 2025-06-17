<button id="addCustomerBtn" class="btn btn-primary mb-3">+ Add Customer</button>
<?php include_once PAYGATE_PATH . 'partials/admin-alert.php'; ?>

<table id="customersTable" class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Account Details</th>
            <th>Gender</th>
            <th>Address</th>
            <th>Actions</th>
        </tr>
    </thead>
</table>

<?php include PAYGATE_PATH . 'partials/customers/modal.php'; ?>
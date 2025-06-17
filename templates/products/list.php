<div class="wrap">
    <h1 class="wp-heading-inline">Products</h1>
    <button id="addProductBtn" class="btn btn-primary mb-3">+ Add Product</button>
    <?php include_once PAYGATE_PATH . 'templates/partials/admin-alert.php'; ?>
    <div class="table-responsive">
        <table id="productsTable" class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<?php include plugin_dir_path(__FILE__) . '/modal-form.php'; ?>
<!-- Modal -->
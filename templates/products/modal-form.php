<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="productForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Add / Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <!-- Hidden Fields -->
                    <input type="hidden" name="id" id="product_id">
                    <input type="hidden" name="slug" id="product_slug">

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="product_name" class="form-label">Name</label>
                        <input type="text" name="name" id="product_name" class="form-control" required>
                    </div>

                    <!-- SKU -->
                    <div class="mb-3">
                        <label for="product_sku" class="form-label">SKU</label>
                        <input type="text" name="sku" id="product_sku" class="form-control" required>
                    </div>

                    <!-- Price -->
                    <div class="mb-3">
                        <label for="product_price" class="form-label">Price</label>
                        <input type="number" step="0.01" name="price" id="product_price" class="form-control" required>
                    </div>

                    <!-- In Stock -->
                    <div class="mb-3">
                        <label for="product_stock" class="form-label">In Stock</label>
                        <input type="number" name="in_stock" id="product_stock" class="form-control" required>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label for="product_status" class="form-label">Status</label>
                        <select name="status" id="product_status" class="form-select" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Product</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
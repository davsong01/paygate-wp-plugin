<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="productForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Add/Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="product_id">
                <div class="mb-3">
                    <label for="product_name" class="form-label">Name</label>
                    <input type="text" name="name" id="product_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="product_slug" class="form-label">Slug</label>
                    <input type="text" name="slug" id="product_slug" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="product_price" class="form-label">Price</label>
                    <input type="number" name="price" id="product_price" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="product_stock" class="form-label">In Stock</label>
                    <input type="number" name="in_stock" id="product_stock" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="product_status" class="form-label">Status</label>
                    <select name="status" id="product_status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Save Product</button>
            </div>
        </form>
    </div>
</div>
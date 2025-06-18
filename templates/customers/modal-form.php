<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="customerForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-3">
                <input type="hidden" name="id" id="customer_id">
                <div class="col-md-6">
                    <label for="customer_name" class="form-label">Full Name</label>
                    <input type="text" name="name" id="customer_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="customer_email" class="form-label">Email</label>
                    <input type="email" name="email" id="customer_email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="customer_phone" class="form-label">Phone</label>
                    <input type="text" name="phone" id="customer_phone" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="customer_gender" class="form-label">Gender</label>
                    <select name="gender" id="customer_gender" class="form-select">
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="col-12">
                    <label for="customer_address" class="form-label">Address</label>
                    <textarea name="address" id="customer_address" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" id="customerSubmitBtn" class="btn btn-success">Save Customer</button>
                <!-- <button type="submit" class="btn btn-success">Save Customer</button> -->
            </div>
        </form>
    </div>
</div>
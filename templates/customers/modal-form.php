<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="customerForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-3">
                <input type="hidden" name="id" id="customer_id">
                <div class="col-md-4">
                    <label for="title" class="form-label">Title <span class="required">*</span></label>
                    <select name="title" id="customer_title" class="form-select" required>
                        <option value="">Select Title</option>
                        <option value="mr">Mr</option>
                        <option value="mrs">Mrs</option>
                        <option value="miss">Miss</option>
                        <option value="ms">Ms</option>
                        <option value="dr">Dr</option>
                        <option value="prof">Prof</option>
                        <option value="chief">Chief</option>
                        <option value="eng">Engr</option>
                        <option value="rev">Rev</option>
                        <option value="hon">Hon</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="customer_name" class="form-label">First Name <span class="required">*</span></label>
                    <input type="text" name="firstname" id="customer_firstname" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="customer_name" class="form-label">Surname Name <span class="required">*</span></label>
                    <input type="text" name="surname" id="customer_surname" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="customer_name" class="form-label">Middle Name</label>
                    <input type="text" name="middlename" id="customer_middlename" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="customer_email" class="form-label">Email <span class="required">*</span></label>
                    <input type="email" name="email" id="customer_email" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="customer_phone" class="form-label">Phone <span class="required">*</span></label>
                    <input type="text" name="phone" id="customer_phone" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="customer_gender" class="form-label">Gender <span class="required">*</span></label>
                    <select name="gender" id="customer_gender" class="form-select">
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="customer_dob" class="form-label">Date of Birth</label>
                    <input type="date" name="dob" id="customer_dob" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="customer_address_line_1" class="form-label">Address Line 1 <span class="required">*</span></label>
                    <input type="text" name="address_line_1" id="customer_address_line_1" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label for="customer_address_line_2" class="form-label">Address Line 2</label>
                    <input type="text" name="address_line_2" id="customer_address_line_2" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="customer_country" class="form-label">Country <span class="required">*</span></label>
                    <select name="country_id" id="customer_country" class="form-select" required>
                        <option value="">Select Country</option>
                        <!-- Options populated dynamically -->
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="customer_state" class="form-label">State <span class="required">*</span></label>
                    <select name="state_id" id="customer_state" class="form-select" required>
                        <option value="">Select State</option>
                        <!-- Options populated dynamically -->
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="city" class="form-label">City</label>
                    <input type="text" name="city" id="city" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" id="customerSubmitBtn" class="btn btn-success">Save Customer</button>
                <!-- <button type="submit" class="btn btn-success">Save Customer</button> -->
            </div>
        </form>
    </div>
</div>
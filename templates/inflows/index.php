<div class="container mt-4">
    <h3>Inflow Transactions</h3>

    <!-- Filter Form -->
    <form id="inflowFilterForm" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="text" name="name" class="form-control" placeholder="Customer Name">
        </div>
        <div class="col-md-3">
            <input type="email" name="email" class="form-control" placeholder="Customer Email">
        </div>
        <div class="col-md-2">
            <input type="text" name="account_number" class="form-control" placeholder="Account Number">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">Status</option>
                <option value="Successful">Successful</option>
                <option value="Failed">Failed</option>
                <option value="Pending">Pending</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="date_from" class="form-control">
        </div>
        <div class="col-md-2">
            <input type="date" name="date_to" class="form-control">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Inflow Table -->
    <table id="inflowTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Transaction</th>
                <th>Account Number</th>
                <th>Amount</th>
                <th>Originator</th>
                <th>Date</th>
                <th>Status</th>
                <th>View</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="inflowJsonModal" tabindex="-1" aria-labelledby="inflowJsonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Transaction Details (JSON)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre><code id="inflowJsonOutput" class="json-output"></code></pre>
            </div>
        </div>
    </div>
</div>
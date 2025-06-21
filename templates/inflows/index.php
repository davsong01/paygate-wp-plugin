<div class="wrap">
    <h1 class="wp-heading-inline">Inflow Transactions</h1>

    <form id="inflowFilterForm" method="get">
        <div class="tablenav top">
            <div class="wp-filter" style="padding: 15px 10px;">
                <div class="filter-items" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">

                    <div style="flex: 1 1 30%;">
                        <label for="filter-name"><strong>Customer Name</strong></label><br>
                        <input id="filter-name" type="text" name="name" placeholder="Customer Name" style="width: 100%;">
                    </div>

                    <div style="flex: 1 1 30%;">
                        <label for="filter-email"><strong>Customer Email</strong></label><br>
                        <input id="filter-email" type="email" name="email" placeholder="Customer Email" style="width: 100%;">
                    </div>

                    <div style="flex: 1 1 30%;">
                        <label for="filter-account"><strong>Account Number</strong></label><br>
                        <input id="filter-account" type="text" name="account_number" placeholder="Account Number" style="width: 100%;">
                    </div>

                    <div style="flex: 1 1 20%;">
                        <label for="filter-status"><strong>Status</strong></label><br>
                        <select id="filter-status" name="status" style="width: 100%;">
                            <option value="">All</option>
                            <option value="Successful">Successful</option>
                            <option value="Failed">Failed</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>

                    <div style="flex: 1 1 20%;">
                        <label for="filter-date-from"><strong>Date From</strong></label><br>
                        <input id="filter-date-from" type="date" name="date_from" style="width: 100%;">
                    </div>

                    <div style="flex: 1 1 20%;">
                        <label for="filter-date-to"><strong>Date To</strong></label><br>
                        <input id="filter-date-to" type="date" name="date_to" style="width: 100%;">
                    </div>

                    <div style="flex: 1 1 100%;">
                        <input type="submit" class="button button-primary" value="Filter">
                    </div>

                </div>
            </div>
        </div>
    </form>


    <div id="space" style="margin-top:200px"></div>

    <table class="wp-list-table widefat fixed striped" id="inflowTable">
        <thead>
            <tr>
                <th scope=" col">Customer</th>
                <th scope="col">Transaction</th>
                <th scope="col">Account Number</th>
                <th scope="col">Amount</th>
                <th scope="col">Originator</th>
                <th scope="col">Date</th>
                <th scope="col">Status</th>
                <th scope="col">View</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Modal -->
<div id="inflowJsonModal" class="modal" style="display:none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6>Transaction Details (JSON)</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <pre><code id="inflowJsonOutput" class="json-output" style="max-height:500px; overflow:auto;"></code></pre>
            </div>
        </div>
    </div>
</div>
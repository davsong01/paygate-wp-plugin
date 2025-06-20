<div class="wrap">
    <h1 class="wp-heading-inline">Inflow Transactions</h1>

    <form id="inflowFilterForm" method="get">
        <div class="tablenav top">
            <div class="alignleft actions">
                <input type="text" name="name" class="regular-text" placeholder="Customer Name">
                <input type="email" name="email" class="regular-text" placeholder="Customer Email">
                <input type="text" name="account_number" class="regular-text" placeholder="Account Number">

                <select name="status">
                    <option value="">All Statuses</option>
                    <option value="Successful">Successful</option>
                    <option value="Failed">Failed</option>
                    <option value="Pending">Pending</option>
                </select>

                <input type="date" name="date_from">
                <input type="date" name="date_to">

                <input type="submit" class="button action" value="Filter">
            </div>
        </div>
    </form>

    <table class="wp-list-table widefat fixed striped" id="inflowTable">
        <thead>
            <tr>
                <th scope="col">Customer</th>
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
    <div class="modal-dialog modal-lg" style="max-width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Transaction Details (JSON)</h2>
                <button type="button" class="close" onclick="jQuery('#inflowJsonModal').hide();">&times;</button>
            </div>
            <div class="modal-body">
                <pre><code id="inflowJsonOutput" class="json-output" style="max-height:500px; overflow:auto;"></code></pre>
            </div>
        </div>
    </div>
</div>
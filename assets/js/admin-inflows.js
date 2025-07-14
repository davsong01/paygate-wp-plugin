jQuery(function ($) {
    const inflowModal = new bootstrap.Modal('#inflowJsonModal');

    const inflowTable = $('#inflowTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: PayGateVars.ajax_url,
            type: 'POST',
            data: function (d) {
                const filters = $('#inflowFilterForm').serializeArray();
                filters.forEach(item => {
                    d[item.name] = item.value;
                });
                d.action = 'paygate_get_inflows';
                d.nonce = PayGateVars.nonce;
            },
            dataSrc: res => res.data || []
        },
        columns: [
            {
                data: null,
                render: function (data, type, row) {
                    const name = row.full_name || '';
                    const email = row.customer_email || '';
                    const badge = row.mock_mode === 'Live'
                    ? '<span class="badge bg-success" style="margin-left: 6px;">Live</span>'
                    : '<span class="badge bg-dark" style="margin-left: 6px;">Mock</span>';
                    return `
                        <strong>${name}</strong><br>
                        <span style="color: #555;">${email}</span><br>${badge} 
                    `;
                }
            },
            {
                data: 'transaction_details',
                render: function (data) {
                    return data || '';
                }
            },
            { data: 'account_number' },
            { data: 'total_amount' },
            { data: 'originator_details' },
            { data: 'created_at' },
            { data: 'status' },
            {
                data: 'raw_dump',
                render: function (data, type, row) {
                    const encodedJson = encodeURIComponent(JSON.stringify(data));
                    return `<button class="button view-json" data-json="${encodedJson}">View</button>`;
                }
            }
        ]
    });

    $('#inflowFilterForm').on('submit', function (e) {
        e.preventDefault();
        inflowTable.ajax.reload();
    });

    $(document).on('click', '.view-json', function () {
        const encoded = $(this).data('json');
        const decoded = decodeURIComponent(encoded);
        const parsed = JSON.parse(decoded);
        const formatted = JSON.stringify(parsed, null, 2);
        $('#inflowJsonOutput').text(formatted);
        // $('#inflowJsonOutput').text(JSON.stringify(parsed, null, 2));
        inflowModal.show();

        inflowModal.show();
    });
});

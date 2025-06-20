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
            { data: 'full_name' },
            { data: 'transaction_details' },
            { data: 'account_number' },
            { data: 'total_amount' },
            { data: 'originator_details' },
            { data: 'created_at' },
            { data: 'status' },
            {
                data: 'raw_dump',
                render: function (data, type, row) {
                    return `<button class="btn btn-sm btn-secondary view-json" data-json='${JSON.stringify(data)}'>View</button>`;
                }
            }
        ]
    });

    $('#inflowFilterForm').on('submit', function (e) {
        e.preventDefault();
        inflowTable.ajax.reload();
    });

    $(document).on('click', '.view-json', function () {
        const json = $(this).data('json');
        $('#inflowJsonOutput').text(JSON.stringify(json, null, 2));
        inflowModal.show();
    });
});

jQuery(function($) {
    if (!$('#customersTable').length) return;

    const modalEl = document.getElementById('customerModal');
    const modal = new bootstrap.Modal(modalEl);
    const $form = $('#customerForm');

    const table = $('#customersTable').DataTable({
        ajax: {
            url: PayGateVars.ajax_url,
            type: 'POST',
            data: { action: 'paygate_get_customers', nonce: PayGateVars.nonce },
            dataSrc: res => res.data || []
        },
        columns: [
            { data: 'name' }, { data: 'email' }, { data: 'phone' },
            { data: 'gender' }, { data: 'address' },
            {
                data: null,
                render: row => row.account_number
                    ? `Bank: ${row.bank_name}<br>Acct No: ${row.account_number}`
                    : 'N/A'
            },
            {
                data: null,
                orderable: false,
                render: row => `
                    <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${row.id}">Delete</button>
                `
            }
        ]
    });

    $('#addCustomerBtn').on('click', () => {
        $form[0].reset();
        modal.show();
    });

    $(document).on('click', '.delete-btn', function () {
        if (!confirm('Are you sure?')) return;
        $.post(PayGateVars.ajax_url, {
            action: 'paygate_delete_customer',
            nonce: PayGateVars.nonce,
            id: $(this).data('id')
        }, () => table.ajax.reload());
    });

    $form.on('submit', function(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this).entries());

        $.post(PayGateVars.ajax_url, data)
            .done(res => {
                if (res.success) {
                    showAlert('success', res.data.message);
                    modal.hide();
                    table.ajax.reload();
                } else {
                    showAlert('danger', res.data.message || 'Error saving customer.');
                }
            })
            .fail(() => showAlert('danger', 'Request failed'));
    });
});

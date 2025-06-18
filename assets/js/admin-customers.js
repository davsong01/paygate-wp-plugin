jQuery(function ($) {
    const alertEl = $('#paygate-alert');
    const alertMsg = $('#paygate-alert-message');

    function showAlert(type, message) {
        alertEl.removeClass('d-none alert-success alert-danger alert-warning alert-info show')
            .addClass(`alert-${type}`)
            .find('#paygate-alert-message').html(message);

        setTimeout(() => alertEl.addClass('show'), 10);
        $('html, body').animate({ scrollTop: alertEl.offset().top - 100 }, 400);

        setTimeout(() => {
            alertEl.removeClass('show');
            setTimeout(() => alertEl.addClass('d-none'), 300);
        }, 5000);
    }

    function slugify(text) {
        return text
            .toString()
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '-')         
            .replace(/[^\w\-]+/g, '')      
            .replace(/\-\-+/g, '-');
    }

    // ===================== PRODUCTS =====================
    
    if ($('#customersTable').length) {
        const customerModalEl = document.getElementById('customerModal');
        const customerModal = new bootstrap.Modal(customerModalEl);
        const $customerForm = $('#customerForm');
    
        const customerTable = $('#customersTable').DataTable({
            ajax: {
                url: PayGateVars.ajax_url,
                type: 'POST',
                data: { action: 'paygate_get_customers', nonce: PayGateVars.nonce },
                dataSrc: res => res.data || []
            },
            columns: [
                { data: 'name' },
                { data: 'email' },
                { data: 'phone' },
                { data: 'gender' },
                { data: 'address' },
                {
                    data: null,
                    render: function (data, type, row) {
                        if (!row.bank_name || !row.account_number) {
                            return '<em>No account assigned</em>';
                        }
                        return `
                            <strong>Bank:</strong> ${row.bank_name}<br>
                            <strong>Account No:</strong> ${row.account_number}<br>
                            <strong>Account Name:</strong> ${row.account_name}
                        `;
                    }
                },
                { data: 'created_at' },
                {
                    data: null,
                    orderable: false,
                    render: function (data, type, row) {
                        return `
                            <button type="button" class="btn btn-sm btn-outline-primary edit-customer" data-id="${row.id}">Edit</button>
                            <button type="button" class="btn btn-sm btn-outline-danger delete-customer" data-id="${row.id}">Delete</button>
                        `;
                    }
                }
            ]
            
            
        });
    
        // $('#addCustomerBtn').on('click', () => {
        //     $('#customerForm')[0].reset();
        //     $('#customer_id').val('');
        //     customerModal.show();
        // });
    
        // $(document).on('click', '.edit-customer', function () {
        //     const row = customerTable.row($(this).closest('tr')).data();
        //     if (!row) return;
    
        //     $('#customer_id').val(row.id);
        //     $('#customer_name').val(row.name);
        //     $('#customer_email').val(row.email);
        //     $('#customer_phone').val(row.phone);
        //     $('#customer_gender').val(row.gender);
        //     $('#customer_address').val(row.address);
    
        //     customerModal.show();
        // });
        $('#addCustomerBtn').on('click', () => {
            $('#customerForm')[0].reset();
            $('#customer_id').val('');
            $('#customerModalLabel').text('Add Customer');
            $('#customerSubmitBtn').text('Save Customer');
            customerModal.show();
        });
        
        $(document).on('click', '.edit-customer', function () {
            const row = customerTable.row($(this).closest('tr')).data();
            if (!row) return;
        
            $('#customer_id').val(row.id);
            $('#customer_name').val(row.name);
            $('#customer_email').val(row.email);
            $('#customer_phone').val(row.phone);
            $('#customer_gender').val(row.gender);
            $('#customer_address').val(row.address);
        
            $('#customerModalLabel').text('Update Customer');
            $('#customerSubmitBtn').text('Update Customer');
        
            customerModal.show();
        });
        
    
        $(document).on('click', '.delete-customer', function () {
            const id = $(this).data('id');
            if (!confirm('Are you sure you want to delete this customer?')) return;
    
            $.post(PayGateVars.ajax_url, {
                action: 'paygate_delete_customer',
                nonce: PayGateVars.nonce,
                id
            }).done(() => {
                showAlert('success', 'Customer deleted successfully.');
                customerTable.ajax.reload();
            }).fail(err => {
                showAlert('danger', 'Error deleting customer.');
                console.error(err);
            });
        });
    
        $customerForm.on('submit', function (e) {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(this).entries());
            const isUpdate = !!data.id;
        
            data.action = isUpdate ? 'paygate_update_customer' : 'paygate_save_customer';
            data.nonce = PayGateVars.nonce;
        
            $.post(PayGateVars.ajax_url, data)
                .done((res) => {
                    const { message } = res.data;
                    showAlert('success', message);
                    customerModal.hide();
                    customerTable.ajax.reload();
                })
                .fail(err => {
                    const msg = err?.responseJSON?.data?.message || 'Error saving customer.';
                    showAlert('danger', msg);
                    console.error(err);
                });
        });
        
    }
    
});

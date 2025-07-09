jQuery(function ($) {
    const alertEl = $('#paygate-alert');
    const alertMsg = $('#paygate-alert-message');
    const customerModalEl = document.getElementById('customerModal');
    const customerModal = new bootstrap.Modal(customerModalEl);
    const $customerForm = $('#customerForm');

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

    function populateCountries(callback = null) {
        $.post(PayGateVars.ajax_url, {
            action: 'paygate_get_countries',
            nonce: PayGateVars.nonce
        }, function (res) {
            const select = $('#customer_country');
            select.empty().append('<option value="">Select Country</option>');

            if (res.success) {
                res.data.forEach(c => {
                    select.append(`<option value="${c.id}">${c.name}</option>`);
                });
            }

            if (typeof callback === 'function') callback();
        });
    }

    function populateStates(countryId, callback = () => {}) {
        $.post(PayGateVars.ajax_url, {
            action: 'paygate_get_states_by_country',
            nonce: PayGateVars.nonce,
            country_id: countryId
        }, function (res) {
            const select = $('#customer_state');
            select.empty().append('<option value="">Select State</option>');

            if (res.success) {
                res.data.forEach(s => {
                    select.append(`<option value="${s.id}">${s.name}</option>`);
                });
            }

            callback();
        });
    }

    // Trigger when country changes
    $('#customer_country').on('change', function () {
        const countryId = $(this).val();
        if (countryId) {
            populateStates(countryId);
        } else {
            $('#customer_state').empty().append('<option value="">Select State</option>');
        }
    });

    if ($('#customersTable').length) {
        const customerTable = $('#customersTable').DataTable({
            ajax: {
                url: PayGateVars.ajax_url,
                type: 'POST',
                data: { action: 'paygate_get_customers', nonce: PayGateVars.nonce },
                dataSrc: res => res.data || []
            },
            pagingType: 'simple_numbers', // modern look: Previous 1 2 3 Next
            dom: '<"top"f>rt<"bottom"ip><"clear">',
            language: {
                paginate: {
                    previous: '« Prev',
                    next: 'Next »'
                },
            },
    
            columns: [
                {
                    data: null,
                    render: function (data, type, row) {
                        const first = row.firstname || '';
                        const middle = row.middlename || '';
                        const surname = row.surname || '';
                        return `${first} ${middle} ${surname}`.trim().replace(/\s+/g, ' ');
                    }
                },
                { data: 'email' },
                { data: 'phone' },
                {
                    data: 'gender',
                    render: function (data, type, row) {
                        if (!data) return '';
                        return data.charAt(0).toUpperCase() + data.slice(1);
                    }
                },
                {
                    data: null,
                    render: function (data, type, row) {
                        if (!row.bank_name || !row.account_number) {
                            return `
                                <em>No account assigned</em><br>
                                <button class="btn btn-sm btn-info btn-sm generate-account-btn" data-id="${row.id}">
                                    + Generate
                                </button>
                            `;
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

        // Add Customer
        $('#addCustomerBtn').on('click', () => {
            $customerForm[0].reset();
            $('#customer_id').val('');
            $('#customerModalLabel').text('Add Customer');
            $('#customerSubmitBtn').text('Save Customer');
            populateCountries();
            $('#customer_state').empty().append('<option value="">Select State</option>');
            customerModal.show();
        });

        // Edit Customer
        $(document).on('click', '.edit-customer', function () {
            const row = customerTable.row($(this).closest('tr')).data();
            if (!row) return;

            $('#customer_id').val(row.id);
            $('#customer_firstname').val(row.firstname);
            $('#customer_surname').val(row.surname);
            $('#customer_middlename').val(row.middlename);
            $('#customer_email').val(row.email);
            $('#customer_phone').val(row.phone);
            $('#customer_gender').val(row.gender);
            $('#customer_title').val(row.title);
            $('#customer_address_line_1').val(row.address_line_1);
            $('#customer_address_line_2').val(row.address_line_2);
            $('#customer_dob').val(row.dob);
            $('#city').val(row.city);

            // Load countries → set → then load states
            populateCountries(() => {
                $('#customer_country').val(row.country_id);
                populateStates(row.country_id, () => {
                    $('#customer_state').val(row.state_id);
                });
            });

            $('#customerModalLabel').text('Update Customer');
            $('#customerSubmitBtn').text('Update Customer');
            customerModal.show();
        });

        // Delete Customer
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

        // Submit Customer Form
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

        // $(document).on('click', '.generate-account-btn', function () {
        //     const customerId = $(this).data('id');
        
        //     if (!customerId) return;
        
        //     $.post(PayGateVars.ajax_url, {
        //         action: 'paygate_generate_account_number',
        //         nonce: PayGateVars.nonce,
        //         customer_id: customerId
        //     })
        //     .done(res => {
        //         if (res.success) {
        //             showAlert('success', res.data.message || 'Account generated successfully.');
        //             $('#customersTable').DataTable().ajax.reload(null, false);
        //         } else {
        //             showAlert('danger', res.data.message || 'Failed to generate account.');
        //         }
        //     })
        //     .fail(err => {
        //         console.error(err);
        //         showAlert('danger', 'An unexpected error occurred.');
        //     });
        // });
        $(document).on('click', '.generate-account-btn', function () {
            const $btn = $(this);
            const customerId = $btn.data('id');
        
            if (!customerId) return;
        
            const originalHTML = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Generating...');
        
            $.post(PayGateVars.ajax_url, {
                action: 'paygate_generate_account_number',
                nonce: PayGateVars.nonce,
                customer_id: customerId
            })
            .done(res => {
                if (res.success) {
                    showAlert('success', res.data.message || 'Account generated successfully.');
                    $('#customersTable').DataTable().ajax.reload(null, false);
                } else {
                    showAlert('danger', res.data.message || 'Failed to generate account.');
                }
            })
            .fail(err => {
                console.error(err);
                showAlert('danger', 'An unexpected error occurred.');
            })
            .always(() => {
                $btn.prop('disabled', false).html(originalHTML);
            });
        });
        
        
    }
});


jQuery(function ($) {
    if (!$('#productsTable').length) return;

    const modalEl = document.getElementById('productModal');
    const modal = new bootstrap.Modal(modalEl);
    const $form = $('#productForm');
    const alertEl = $('#paygate-alert');
    const alertMsg = $('#paygate-alert-message');

    // DataTable setup
    const table = $('#productsTable').DataTable({
        ajax: {
            url: PayGateVars.ajax_url,
            type: 'POST',
            data: { action: 'paygate_get_products', nonce: PayGateVars.nonce },
            dataSrc: res => res.data || []
        },
        columns: [
            { data: 'name' },
            { data: 'slug' },
            { data: 'price' },
            { data: 'in_stock' },
            { data: 'status' },
            {
                data: null,
                orderable: false,
                render: row => `
                    <button type="button" class="btn btn-sm btn-outline-primary edit-btn" data-id="${row.id}">Edit</button>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="${row.id}">Delete</button>
                `
            }
        ]
    });

    // Show modal to add new
    $('#addProductBtn').on('click', () => {
        $form[0].reset();
        $('#product_id').val('');
        modal.show();
    });

    // Edit product
    $(document).on('click', '.edit-btn', function () {
        const row = table.row($(this).closest('tr')).data();
        if (!row) return;

        $('#product_id').val(row.id);
        $('#product_name').val(row.name);
        $('#product_sku').val(row.sku);
        $('#product_price').val(row.price);
        $('#product_stock').val(row.in_stock);
        $('#product_status').val(row.status);

        modal.show();
    });

    // Delete product
    $(document).on('click', '.delete-btn', function () {
        const id = $(this).data('id');
        if (!confirm('Are you sure you want to delete this product?')) return;

        $.post(PayGateVars.ajax_url, {
            action: 'paygate_delete_product',
            nonce: PayGateVars.nonce,
            id
        }).done(() => {
            showAlert('success', 'Product deleted successfully.');
            table.ajax.reload();
        }).fail(err => {
            showAlert('danger', 'Error deleting product.');
            console.error(err);
        });
    });

    // Form submit
    $form.on('submit', function (e) {
        e.preventDefault();

        const data = Object.fromEntries(new FormData(this).entries());
        data.action = 'paygate_save_product';
        data.nonce = PayGateVars.nonce;

        // Optional: Auto-generate slug client-side if you want
        // data.slug = slugify(data.name);

        $.post(PayGateVars.ajax_url, data)
            .done((res) => {
                const { message, slug_modified, final_slug } = res.data;

                showAlert('success', message);

                if (slug_modified) {
                    showAlert('warning', `Note: The product slug was adjusted to "<strong>${final_slug}</strong>" because the original was already taken.`);
                }

                modal.hide();
                table.ajax.reload();
            })
            .fail(err => {
                const msg = err?.responseJSON?.data?.message || 'Error saving product.';
                showAlert('danger', msg);
                console.error(err);
            });
    });

    // Show alert
    function showAlert(type, message) {
        alertEl
            .removeClass('d-none alert-success alert-danger alert-warning alert-info show')
            .addClass(`alert alert-${type} alert-dismissible fade show`)
            .removeAttr('style');

        alertMsg.html(message);

        setTimeout(() => {
            if (alertEl.hasClass('show')) {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl[0]);
                bsAlert.close();
            }
        }, 5000);
    }

    // Optional slugify function (if needed)
    function slugify(text) {
        return text
            .toString()
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '-')         
            .replace(/[^\w\-]+/g, '')      
            .replace(/\-\-+/g, '-');        
    }
});

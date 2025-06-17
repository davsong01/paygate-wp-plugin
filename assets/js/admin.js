jQuery(function ($) {
    if (!$('#productsTable').length) return;

    const modalEl = document.getElementById('productModal');
    const modal = new bootstrap.Modal(modalEl);
    const $form = $('#productForm');

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

    $('#addProductBtn').on('click', () => {
        $form[0].reset();
        $('#product_id').val('');
        modal.show();
    });

    $(document).on('click', '.edit-btn', function () {
        const row = table.row($(this).closest('tr')).data();
        if (!row) return;

        $('#product_id').val(row.id);
        $('#product_name').val(row.name);
        $('#product_slug').val(row.slug);
        $('#product_price').val(row.price);
        $('#product_stock').val(row.in_stock);
        $('#product_status').val(row.status);

        modal.show();
    });

    $(document).on('click', '.delete-btn', function () {
        const id = $(this).data('id');
        if (!confirm('Are you sure you want to delete this product?')) return;

        $.post(PayGateVars.ajax_url, {
            action: 'paygate_delete_product',
            nonce: PayGateVars.nonce,
            id
        }).done(() => {
            table.ajax.reload();
        }).fail(err => {
            alert('Error deleting product.');
            console.error(err);
        });
    });

    $form.on('submit', function (e) {
        e.preventDefault();

        const data = Object.fromEntries(new FormData(this).entries());
        data.action = 'paygate_save_product';
        data.nonce = PayGateVars.nonce;

        $.post(PayGateVars.ajax_url, data)
            .done(() => {
                modal.hide();
                table.ajax.reload();
            })
            .fail(err => {
                alert('Error saving product.');
                console.error(err);
            });
    });
});

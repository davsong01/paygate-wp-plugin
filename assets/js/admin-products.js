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
    
    if ($('#productsTable').length) {
        const productModalEl = document.getElementById('productModal');
        const productModal = new bootstrap.Modal(productModalEl);
        const $productForm = $('#productForm');

        const productTable = $('#productsTable').DataTable({
            ajax: {
                url: PayGateVars.ajax_url,
                type: 'POST',
                data: { action: 'paygate_get_products', nonce: PayGateVars.nonce },
                dataSrc: res => res.data || []
            },
            columns: [
                { data: 'name' },
                { data: 'sku' },
                { data: 'price' },
                { data: 'in_stock' },
                { data: 'status' },
                {
                    data: null,
                    orderable: false,
                    render: row => `
                        <button type="button" class="btn btn-sm btn-outline-primary edit-product" data-id="${row.id}">Edit</button>
                        <button type="button" class="btn btn-sm btn-outline-danger delete-product" data-id="${row.id}">Delete</button>
                    `
                }
            ]
        });

        $('#addProductBtn').on('click', () => {
            $productForm[0].reset();
            $('#product_id').val('');
            productModal.show();
        });

        $(document).on('click', '.edit-product', function () {
            const row = productTable.row($(this).closest('tr')).data();
            if (!row) return;

            $('#product_id').val(row.id);
            $('#product_name').val(row.name);
            $('#product_sku').val(row.sku);
            $('#product_price').val(row.price);
            $('#product_stock').val(row.in_stock);
            $('#product_status').val(row.status);

            productModal.show();
        });

        $(document).on('click', '.delete-product', function () {
            const id = $(this).data('id');
            if (!confirm('Are you sure you want to delete this product?')) return;

            $.post(PayGateVars.ajax_url, {
                action: 'paygate_delete_product',
                nonce: PayGateVars.nonce,
                id
            }).done(() => {
                showAlert('success', 'Product deleted successfully.');
                productTable.ajax.reload();
            }).fail(err => {
                showAlert('danger', 'Error deleting product.');
                console.error(err);
            });
        });

        $productForm.on('submit', function (e) {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(this).entries());
            data.action = 'paygate_save_product';
            data.nonce = PayGateVars.nonce;

            $.post(PayGateVars.ajax_url, data)
                .done((res) => {
                    const { message, slug_modified, final_slug } = res.data;
                    showAlert('success', message);

                    if (slug_modified) {
                        showAlert('warning', `Note: The product slug was adjusted to "<strong>${final_slug}</strong>" because the original was already taken.`);
                    }

                    productModal.hide();
                    productTable.ajax.reload();
                })
                .fail(err => {
                    const msg = err?.responseJSON?.data?.message || 'Error saving product.';
                    showAlert('danger', msg);
                    console.error(err);
                });
        });
    }
});

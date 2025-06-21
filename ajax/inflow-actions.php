<?php
    add_action('wp_ajax_paygate_get_inflows', function () {
    global $wpdb;

    check_ajax_referer('paygate_nonce', 'nonce');

    $limit  = intval($_POST['length']);
    $offset = intval($_POST['start']);
    $search = esc_sql($_POST['search']['value']);

    $conditions = "1=1";
    if (!empty($_POST['name'])) {
        $conditions .= $wpdb->prepare(" AND CONCAT_WS(' ', c.firstname, c.middlename, c.surname) LIKE %s", '%' . $_POST['name'] . '%');
    }
    if (!empty($_POST['email'])) {
        $conditions .= $wpdb->prepare(" AND c.email LIKE %s", '%' . $_POST['email'] . '%');
    }
    if (!empty($_POST['account_number'])) {
        $conditions .= $wpdb->prepare(" AND t.account_number LIKE %s", '%' . $_POST['account_number'] . '%');
    }
    if (!empty($_POST['status'])) {
        $conditions .= $wpdb->prepare(" AND t.status = %s", $_POST['status']);
    }
    if (!empty($_POST['date_from'])) {
        $conditions .= $wpdb->prepare(" AND t.created_at >= %s", $_POST['date_from']);
    }
    if (!empty($_POST['date_to'])) {
        $conditions .= $wpdb->prepare(" AND t.created_at <= %s", $_POST['date_to']);
    }

    $table  = $wpdb->prefix . 'paygate_transactions';
    $cust   = $wpdb->prefix . 'paygate_customers';

    $query = "
        SELECT 
            t.*, 
            CONCAT_WS(' ', c.firstname, c.middlename, c.surname) AS full_name,
            CONCAT('<strong>Transaction ID:</strong> ', t.transaction_ref, '<br>','<strong>Ref:</strong> ', t.request_ref) AS transaction_details,
            CONCAT(
                '<strong>Name:</strong> ', JSON_UNQUOTE(JSON_EXTRACT(t.originator_details, '$.originator_account_name')), '<br>',
                '<strong>Account:</strong> ', JSON_UNQUOTE(JSON_EXTRACT(t.originator_details, '$.originator_account_number')), '<br>',
                '<strong>Bank:</strong> ', JSON_UNQUOTE(JSON_EXTRACT(t.originator_details, '$.originator_bank_name'))
            ) AS originator_details,
            t.mock_mode,
            c.email AS customer_email
        FROM {$table} t
        LEFT JOIN {$cust} c ON c.id = t.customer_id
        WHERE {$conditions}
        ORDER BY t.created_at DESC
        LIMIT %d OFFSET %d
    ";


    $results = $wpdb->get_results($wpdb->prepare($query, $limit, $offset), ARRAY_A);

    $totalRecords = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");

    wp_send_json([
        'draw' => intval($_POST['draw']),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => count($results),
        'data' => $results
    ]);
});


<?php
session_start();

require_once __DIR__ . '/../../includes/db.php';

function orderEscape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Allowed status values
$allowedStatuses = [
    'pending',
    'processing',
    'shipped',
    'delivered',
    'cancelled'
];

// Read filters
$search = trim($_GET['search'] ?? '');
$statusFilter = strtolower(trim($_GET['status'] ?? ''));
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');

// Validate status
if (
    $statusFilter !== '' &&
    !in_array($statusFilter, $allowedStatuses, true)
) {
    $statusFilter = '';
}

// Validate dates
function validOrderDate($date) {
    if ($date === '') {
        return true;
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    $parts = explode('-', $date);

    return checkdate(
        (int)$parts[1],
        (int)$parts[2],
        (int)$parts[0]
    );
}

if (!validOrderDate($dateFrom)) {
    $dateFrom = '';
}

if (!validOrderDate($dateTo)) {
    $dateTo = '';
}

if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
    $temp = $dateFrom;
    $dateFrom = $dateTo;
    $dateTo = $temp;
}

// Dashboard statistics across all orders
$statsQuery = "
    SELECT
        COUNT(*) AS total_orders,
        COALESCE(SUM(CASE WHEN LOWER(status) = 'pending'
            THEN 1 ELSE 0 END), 0) AS pending_orders,
        COALESCE(SUM(CASE WHEN LOWER(status) = 'processing'
            THEN 1 ELSE 0 END), 0) AS processing_orders,
        COALESCE(SUM(CASE WHEN LOWER(status) = 'shipped'
            THEN 1 ELSE 0 END), 0) AS shipped_orders,
        COALESCE(SUM(CASE WHEN LOWER(status) = 'delivered'
            THEN 1 ELSE 0 END), 0) AS delivered_orders,
        COALESCE(SUM(CASE WHEN LOWER(status) = 'cancelled'
            THEN 1 ELSE 0 END), 0) AS cancelled_orders
    FROM orders
";

$statsResult = mysqli_query($conn, $statsQuery);

if (!$statsResult) {
    exit("Unable to load order statistics.");
}

$stats = mysqli_fetch_assoc($statsResult);

// Build search and filter conditions
$where = [];

if ($search !== '') {
    $safeSearch = mysqli_real_escape_string($conn, $search);
    $searchLike = "'%" . $safeSearch . "%'";

    $where[] = "(
        order_number LIKE $searchLike
        OR customer_name LIKE $searchLike
        OR customer_email LIKE $searchLike
    )";
}

if ($statusFilter !== '') {
    $safeStatus = mysqli_real_escape_string($conn, $statusFilter);
    $where[] = "LOWER(status) = '$safeStatus'";
}

if ($dateFrom !== '') {
    $safeDateFrom = mysqli_real_escape_string($conn, $dateFrom);
    $where[] = "created_at >= '$safeDateFrom 00:00:00'";
}

if ($dateTo !== '') {
    $safeDateTo = mysqli_real_escape_string($conn, $dateTo);
    $where[] = "created_at < DATE_ADD('$safeDateTo', INTERVAL 1 DAY)";
}

$whereSql = '';

if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

// Pagination
$perPage = 10;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);

if (!$page || $page < 1) {
    $page = 1;
}

// Count filtered orders
$countQuery = "
    SELECT COUNT(*) AS filtered_count
    FROM orders
    $whereSql
";

$countResult = mysqli_query($conn, $countQuery);

if (!$countResult) {
    exit("Unable to count orders.");
}

$countRow = mysqli_fetch_assoc($countResult);
$filteredCount = (int)$countRow['filtered_count'];

$totalPages = max(1, (int)ceil($filteredCount / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

// Fetch current page of orders
$ordersQuery = "
    SELECT
        id,
        order_number,
        customer_name,
        customer_email,
        total_amount,
        status,
        payment_method,
        created_at
    FROM orders
    $whereSql
    ORDER BY created_at DESC, id DESC
    LIMIT $perPage OFFSET $offset
";

$ordersResult = mysqli_query($conn, $ordersQuery);

if (!$ordersResult) {
    exit("Unable to load orders.");
}

// Preserve current filters when changing pages
$paginationParams = [
    'search' => $search,
    'status' => $statusFilter,
    'date_from' => $dateFrom,
    'date_to' => $dateTo
];

function orderStatusClass($status) {
    $status = strtolower(trim((string)$status));

    $classes = [
        'pending' => 'pending',
        'processing' => 'processing',
        'shipped' => 'shipped',
        'delivered' => 'delivered',
        'cancelled' => 'cancelled'
    ];

    return $classes[$status] ?? 'unknown';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Order Management | Mora Blog</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 25px;
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            color: #222;
        }

        .container {
            max-width: 1400px;
            margin: auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        h1 {
            margin: 0 0 8px;
        }

        .muted {
            color: #6b7280;
            font-size: 14px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border-radius: 9px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
            border-left: 4px solid #64748b;
        }

        .stat-card h3 {
            font-size: 14px;
            color: #6b7280;
            margin: 0 0 12px;
        }

        .stat-number {
            font-size: 28px;
            font-weight: bold;
        }

        .stat-pending { border-left-color: #d97706; }
        .stat-processing { border-left-color: #2563eb; }
        .stat-shipped { border-left-color: #6366f1; }
        .stat-delivered { border-left-color: #16a34a; }
        .stat-cancelled { border-left-color: #dc2626; }

        .panel {
            background: white;
            border-radius: 9px;
            padding: 22px;
            margin-bottom: 22px;
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
        }

        .panel h2 {
            margin: 0 0 18px;
            font-size: 20px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 14px;
            align-items: end;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 7px;
        }

        input, select {
            width: 100%;
            padding: 11px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            background: white;
            font-size: 14px;
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            border: 0;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            text-align: center;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #111827;
        }

        .btn-view {
            background: #2563eb;
            color: white;
            padding: 8px 11px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 1000px;
            border-collapse: collapse;
        }

        th, td {
            padding: 13px 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            font-size: 14px;
        }

        th {
            background: #f9fafb;
            color: #374151;
            white-space: nowrap;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .pending {
            background: #fef3c7;
            color: #92400e;
        }

        .processing {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .shipped {
            background: #e0e7ff;
            color: #3730a3;
        }

        .delivered {
            background: #dcfce7;
            color: #166534;
        }

        .cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .unknown {
            background: #e5e7eb;
            color: #374151;
        }

        .empty {
            text-align: center;
            padding: 35px;
            color: #6b7280;
        }

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .pagination {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 9px 12px;
            border-radius: 5px;
            background: #e5e7eb;
            color: #111827;
            text-decoration: none;
            font-size: 14px;
        }

        .pagination .current {
            background: #2563eb;
            color: white;
        }

        .pagination .disabled {
            opacity: .45;
            pointer-events: none;
        }

        @media (max-width: 900px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 550px) {
            body {
                padding: 14px;
            }

            .stats-grid,
            .filter-grid {
                grid-template-columns: 1fr;
            }

            .panel {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1>Order Management</h1>
            <div class="muted">
                Manage, search and track customer orders.
            </div>
        </div>

        <a href="index.php" class="btn btn-secondary">
            Reset / Refresh
        </a>
    </div>

    <!-- Dashboard Statistics -->
    <div class="stats-grid">

        <div class="stat-card">
            <h3>Total Orders</h3>
            <div class="stat-number">
                <?= (int)$stats['total_orders'] ?>
            </div>
        </div>

        <div class="stat-card stat-pending">
            <h3>Pending</h3>
            <div class="stat-number">
                <?= (int)$stats['pending_orders'] ?>
            </div>
        </div>

        <div class="stat-card stat-processing">
            <h3>Processing</h3>
            <div class="stat-number">
                <?= (int)$stats['processing_orders'] ?>
            </div>
        </div>

        <div class="stat-card stat-shipped">
            <h3>Shipped</h3>
            <div class="stat-number">
                <?= (int)$stats['shipped_orders'] ?>
            </div>
        </div>

        <div class="stat-card stat-delivered">
            <h3>Delivered</h3>
            <div class="stat-number">
                <?= (int)$stats['delivered_orders'] ?>
            </div>
        </div>

        <div class="stat-card stat-cancelled">
            <h3>Cancelled</h3>
            <div class="stat-number">
                <?= (int)$stats['cancelled_orders'] ?>
            </div>
        </div>

    </div>

    <!-- Search and Filters -->
    <div class="panel">
        <h2>Search & Filter Orders</h2>

        <form method="GET" action="index.php">

            <div class="filter-grid">

                <div>
                    <label for="search">
                        Search Order / Customer
                    </label>

                    <input
                        type="text"
                        name="search"
                        id="search"
                        placeholder="Order number, name or email"
                        value="<?= orderEscape($search) ?>"
                    >
                </div>

                <div>
                    <label for="status">Order Status</label>

                    <select name="status" id="status">
                        <option value="">All Statuses</option>

                        <?php foreach ($allowedStatuses as $statusOption): ?>
                            <option
                                value="<?= orderEscape($statusOption) ?>"
                                <?= $statusFilter === $statusOption
                                    ? 'selected' : '' ?>
                            >
                                <?= orderEscape(ucfirst($statusOption)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="date_from">From Date</label>

                    <input
                        type="date"
                        name="date_from"
                        id="date_from"
                        value="<?= orderEscape($dateFrom) ?>"
                    >
                </div>

                <div>
                    <label for="date_to">To Date</label>

                    <input
                        type="date"
                        name="date_to"
                        id="date_to"
                        value="<?= orderEscape($dateTo) ?>"
                    >
                </div>

            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    Apply Filters
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Clear Filters
                </a>
            </div>

        </form>
    </div>

    <!-- Orders Table -->
    <div class="panel">
        <h2>Orders List</h2>

        <p class="muted">
            Showing
            <?= $filteredCount > 0 ? $offset + 1 : 0 ?>
            -
            <?= min($offset + $perPage, $filteredCount) ?>
            of <?= $filteredCount ?> matching orders.
        </p>

        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Order Date</th>
                    <th>Action</th>
                </tr>
                </thead>

                <tbody>

                <?php if (mysqli_num_rows($ordersResult) > 0): ?>

                    <?php while ($order = mysqli_fetch_assoc($ordersResult)): ?>
                        <tr>
                            <td>
                                <strong>
                                    <?= orderEscape($order['order_number']) ?>
                                </strong>
                            </td>

                            <td>
                                <?= orderEscape($order['customer_name']) ?>
                            </td>

                            <td>
                                <?= orderEscape($order['customer_email']) ?>
                            </td>

                            <td>
                                ₹<?= number_format(
                                    (float)$order['total_amount'], 2
                                ) ?>
                            </td>

                            <td>
                                <span class="status <?= orderStatusClass($order['status']) ?>">
                                    <?= orderEscape($order['status']) ?>
                                </span>
                            </td>

                            <td>
                                <?= orderEscape(
                                    ucwords(str_replace(
                                        '_',
                                        ' ',
                                        $order['payment_method']
                                    ))
                                ) ?>
                            </td>

                            <td>
                                <?= orderEscape($order['created_at']) ?>
                            </td>

                            <td>
                                <a
                                    href="view.php?id=<?= (int)$order['id'] ?>"
                                    class="btn btn-view"
                                >
                                    View Order
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="8" class="empty">
                            No orders match your search or filters.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="table-footer">

                <div class="muted">
                    Page <?= $page ?> of <?= $totalPages ?>
                </div>

                <div class="pagination">

                    <?php
                    $previousParams = $paginationParams;
                    $previousParams['page'] = max(1, $page - 1);

                    $nextParams = $paginationParams;
                    $nextParams['page'] = min($totalPages, $page + 1);
                    ?>

                    <a
                        class="<?= $page <= 1 ? 'disabled' : '' ?>"
                        href="?<?= orderEscape(http_build_query($previousParams)) ?>"
                    >
                        Previous
                    </a>

                    <?php for (
                        $pageNumber = max(1, $page - 2);
                        $pageNumber <= min($totalPages, $page + 2);
                        $pageNumber++
                    ): ?>

                        <?php
                        $pageParams = $paginationParams;
                        $pageParams['page'] = $pageNumber;
                        ?>

                        <a
                            class="<?= $pageNumber === $page ? 'current' : '' ?>"
                            href="?<?= orderEscape(http_build_query($pageParams)) ?>"
                        >
                            <?= $pageNumber ?>
                        </a>

                    <?php endfor; ?>

                    <a
                        class="<?= $page >= $totalPages ? 'disabled' : '' ?>"
                        href="?<?= orderEscape(http_build_query($nextParams)) ?>"
                    >
                        Next
                    </a>

                </div>
            </div>
        <?php endif; ?>

    </div>

</div>
</body>
</html>
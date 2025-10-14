<?php
require_once 'config.php';
$title = "Sales Returns";
include 'header.php';

// --- Handle search/filter ---
$where = [];
$params = [];

if (!empty($_GET['return_no'])) {
    $where[] = "sr.return_no LIKE ?";
    $params[] = "%" . $_GET['return_no'] . "%";
}
if (!empty($_GET['customer_id'])) {
    $where[] = "sr.customer_id = ?";
    $params[] = $_GET['customer_id'];
}
if (!empty($_GET['return_date'])) {
    $where[] = "DATE(sr.invoice_date) = ?"; // filter by invoice_date
    $params[] = $_GET['return_date']; // format: yyyy-mm-dd
}

// --- Fetch sales returns with item count and total ---
$sql = "
    SELECT 
        sr.return_id, 
        sr.return_no, 
        sr.invoice_date AS bill_date, 
        c.customer_name,
        COALESCE(items.item_count, 0) AS item_count,
        COALESCE(items.total_amount, 0) AS total_amount
    FROM sales_returns sr
    LEFT JOIN customers c ON sr.customer_id = c.customer_id
    LEFT JOIN (
        SELECT sr_id, COUNT(*) AS item_count, SUM(line_total) AS total_amount
        FROM sales_return_items
        GROUP BY sr_id
    ) items ON sr.return_id = items.sr_id
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY sr.return_no DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$returns = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Fetch customers for filter ---
$customers = $pdo->query("SELECT customer_id, customer_name FROM customers ORDER BY customer_name")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2>Sales Returns</h2>

    <div class="mb-3">
        <a href="sales_return.php" class="btn btn-outline-success">➕ New Sales Return</a>
    </div>

    <!-- Search / Filter Form -->
    <form method="get" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="text" name="return_no" value="<?= htmlspecialchars($_GET['return_no'] ?? '') ?>" class="form-control" placeholder="Search Return No">
        </div>
        <div class="col-md-3">
            <select name="customer_id" class="form-select">
                <option value="">-- Filter by Customer --</option>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['customer_id'] ?>" <?= (($_GET['customer_id'] ?? '') == $c['customer_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['customer_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" name="return_date" value="<?= htmlspecialchars($_GET['return_date'] ?? '') ?>" class="form-control" placeholder="Bill Date">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-outline-primary">🔍 Search</button>
            <a href="sales_return_list.php" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <!-- Results Table -->
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Return No</th>
                <th>Bill Date</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($returns): ?>
                <?php foreach ($returns as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['return_no']) ?></td>
                        <td><?= date('d-m-Y', strtotime($r['bill_date'])) ?></td>
                        <td><?= htmlspecialchars($r['customer_name']) ?></td>
                        <td><?= $r['item_count'] ?></td>
                        <td><?= number_format($r['total_amount'], 2) ?></td>
                        <td>
                            <a href="view_sales_return.php?id=<?= $r['return_id'] ?>" class="btn btn-outline-primary btn-sm">👁 </a>
                            <a href="delete_sales_return.php?id=<?= $r['return_id'] ?>" onclick="return confirm('Delete this sales return?')" class="btn btn-outline-danger btn-sm">🗑 </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No sales returns found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once 'config.php';
$title = "Sales Invoices";
include 'header.php';

// --- Handle search/filter ---
$where = [];
$params = [];

if (!empty($_GET['si_number'])) {
    $where[] = "si.si_number LIKE ?";
    $params[] = "%" . $_GET['si_number'] . "%";
}
if (!empty($_GET['customer_id'])) {
    $where[] = "si.customer_id = ?";
    $params[] = $_GET['customer_id'];
}

// Fetch sales invoices with item count + total amount
$sql = "
    SELECT 
        si.si_id, 
        si.si_number, 
        si.si_date, 
        si.due_date,
        c.customer_name,
        COALESCE(items.item_count, 0) AS item_count,
        COALESCE(items.total_amount, 0) AS total_amount
    FROM sales_invoices si
    JOIN customers c ON si.customer_id = c.customer_id
    LEFT JOIN (
        SELECT 
            si_id, 
            COUNT(*) AS item_count,
            SUM(line_total) AS total_amount
        FROM sales_invoice_items
        GROUP BY si_id
    ) items ON si.si_id = items.si_id
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY si.si_number ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch customers for filter
$customers = $pdo->query("SELECT customer_id, customer_name FROM customers ORDER BY customer_name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Sales Invoices</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">

<h2>Sales Invoices</h2>

<div class="mb-3">
    <a href="insert_sales_invoice.php" class="btn btn-outline-primary">➕ New Invoice</a>
</div>

<!-- Search / Filter Form -->
<form method="get" class="row g-3 mb-4">
    <div class="col-md-4">
        <input type="text" name="si_number" value="<?= htmlspecialchars($_GET['si_number'] ?? '') ?>" class="form-control" placeholder="Search Invoice Number">
    </div>
    <div class="col-md-4">
        <select name="customer_id" class="form-select">
            <option value="">-- Filter by Customer --</option>
            <?php foreach ($customers as $c): ?>
                <option value="<?= $c['customer_id'] ?>" <?= (($_GET['customer_id'] ?? '')==$c['customer_id'])?'selected':'' ?>>
                    <?= htmlspecialchars($c['customer_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <button type="submit" class="btn btn-outline-primary">🔍 Search</button>
        <a href="sales_invoice_list.php" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>

<!-- Results -->
<table class="table table-bordered table-striped">
    <thead class="table-light">
        <tr>
            <th>SI Number</th>
            <th>Invoice Date</th>
            <th>Customer</th>
            <th>Due Date</th>
            <th>Items</th>
            <th>Total Amount (₹)</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($invoices): ?>
            <?php foreach ($invoices as $inv): ?>
                <tr>
                    <td><?= htmlspecialchars($inv['si_number']) ?></td>
                    <td><?= date('d-m-Y', strtotime($inv['si_date'])) ?></td>
                    <td><?= htmlspecialchars($inv['customer_name']) ?></td>
                    <td><?= $inv['due_date'] ? date('d-m-Y', strtotime($inv['due_date'])) : '-' ?></td>
                    <td><?= $inv['item_count'] ?></td>
                    <td><?= number_format($inv['total_amount'], 2) ?></td>
                    <td>
                        <button class="btn btn-outline-success btn-sm" onclick="showPrint(<?= $inv['si_id'] ?>)">🖨 </button>
                        <a href="edit_sales_invoice.php?id=<?= $inv['si_id'] ?>" class="btn btn-outline-warning btn-sm">✏ </a>
                        <a href="delete_sales_invoice.php?id=<?= $inv['si_id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this invoice?')">🗑 </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">No invoices found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Hidden Print Area -->
<div id="printArea" style="display:none; margin-top:30px;"></div>

<script>
function showPrint(si_id){
    fetch('sales_invoice_print.php?id=' + si_id)
    .then(response => response.text())
    .then(html => {
        let printArea = document.getElementById('printArea');
        printArea.innerHTML = html;
        printArea.style.display = 'block';
        printDiv('printArea');
    })
    .catch(err => console.error(err));
}

function printDiv(divId) {
    var printContents = document.getElementById(divId).innerHTML;
    var originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
}
</script>

</body>
</html>
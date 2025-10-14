<?php
require_once 'config.php';
$title = "Purchase Invoices";
include 'header.php';

// --- Handle search/filter ---
$where = [];
$params = [];

if (!empty($_GET['pi_number'])) {
    $where[] = "pi.pi_number LIKE ?";
    $params[] = "%" . $_GET['pi_number'] . "%";
}
if (!empty($_GET['supplier_id'])) {
    $where[] = "pi.supplier_id = ?";
    $params[] = $_GET['supplier_id'];
}

// Fetch invoices with item count
$sql = "
    SELECT 
        pi.pi_id, 
        pi.pi_number, 
        pi.pi_date, 
        pi.due_date,
        s.supplier_name,
        COALESCE(items.item_count, 0) AS item_count,
        COALESCE(items.total_amount, 0) AS total_amount
    FROM purchase_invoices pi
    JOIN suppliers s ON pi.supplier_id = s.supplier_id
    LEFT JOIN (
        SELECT 
            pi_id, 
            COUNT(*) AS item_count, 
            SUM(line_total) AS total_amount
        FROM purchase_invoice_items
        GROUP BY pi_id
    ) items ON pi.pi_id = items.pi_id
";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY pi.pi_number ASC";


$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch suppliers for filter
$suppliers = $pdo->query("SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Purchase Invoices</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">

<h2>Purchase Invoices</h2>

<div class="mb-3">
    <a href="insert_invoice.php" class="btn btn-outline-primary">➕ New Invoice</a>
   
</div>

<!-- Search / Filter Form -->
<form method="get" class="row g-3 mb-4">
    <div class="col-md-4">
        <input type="text" name="pi_number" value="<?= htmlspecialchars($_GET['pi_number'] ?? '') ?>" class="form-control" placeholder="Search Invoice Number">
    </div>
    <div class="col-md-4">
        <select name="supplier_id" class="form-select">
            <option value="">-- Filter by Supplier --</option>
            <?php foreach ($suppliers as $s): ?>
                <option value="<?= $s['supplier_id'] ?>" <?= (($_GET['supplier_id'] ?? '')==$s['supplier_id'])?'selected':'' ?>>
                    <?= htmlspecialchars($s['supplier_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <button type="submit" class="btn btn-outline-primary">🔍 Search</button>
        <a href="purchase_invoice_list.php" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>

<!-- Results -->
<table class="table table-bordered table-striped">
    <thead class="table-light">
    <tr>
        <th>PI Number</th>
        <th>Invoice Date</th>
        <th>Supplier</th>
        <th>Due Date</th>
        <th>Items</th>
        <th>Total Amount (₹)</th> <!-- ✅ New column -->
        <th>Actions</th>
    </tr>
</thead>

   <tbody>
<?php if ($invoices): ?>
    <?php foreach ($invoices as $inv): ?>
        <tr>
            <td><?= htmlspecialchars($inv['pi_number']) ?></td>
            <td><?= date('d-m-Y', strtotime($inv['pi_date'])) ?></td>
            <td><?= htmlspecialchars($inv['supplier_name']) ?></td>
            <td><?= $inv['due_date'] ? date('d-m-Y', strtotime($inv['due_date'])) : '-' ?></td>
            <td><?= $inv['item_count'] ?></td>
            <td>₹ <?= number_format($inv['total_amount'], 2) ?></td> <!-- ✅ Show formatted total -->
            <td>
                <button class="btn btn-outline-success btn-sm" onclick="showPrint(<?= $inv['pi_id'] ?>)">🖨</button>
                <a href="edit_invoice.php?id=<?= $inv['pi_id'] ?>" class="btn btn-outline-warning btn-sm">✏</a>
                <a href="delete_invoice.php?id=<?= $inv['pi_id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this invoice?')">🗑</a>
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
function showPrint(pi_id){
    fetch('purchase_invoice_print.php?id=' + pi_id)
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
<?php
require_once 'config.php';
$title = "Purchase Orders";
include 'header.php';

// --- Handle search/filter ---
$where = [];
$params = [];

if (!empty($_GET['po_number'])) {
    $where[] = "po.po_number LIKE ?";
    $params[] = "%" . $_GET['po_number'] . "%";
}
if (!empty($_GET['supplier_id'])) {
    $where[] = "po.supplier_id = ?";
    $params[] = $_GET['supplier_id'];
}

// Fetch purchase orders with item count
$sql = "
    SELECT 
        po.po_id, 
        po.po_number, 
        po.po_date, 
        po.expected_date,
        s.supplier_name,
        COALESCE(items.item_count, 0) AS item_count
    FROM purchase_orders po
    JOIN suppliers s ON po.supplier_id = s.supplier_id
    LEFT JOIN (
        SELECT po_id, COUNT(*) AS item_count
        FROM purchase_order_items
        GROUP BY po_id
    ) items ON po.po_id = items.po_id
";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY po.po_number ASC";


$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch suppliers for filter
$suppliers = $pdo->query("SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Purchase Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
  
  <h2>Purchase Orders</h2>

  <div class="mb-3">
    <a href="insert_po.php" class="btn btn-outline-primary">➕ New Purchase Order</a>
  
  </div>

  <!-- Search / Filter Form -->
  <form method="get" class="row g-3 mb-4">
    <div class="col-md-4">
      <input type="text" name="po_number" value="<?= htmlspecialchars($_GET['po_number'] ?? '') ?>" class="form-control" placeholder="Search PO Number">
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
      <a href="purchase_list.php" class="btn btn-outline-primary">Reset</a>
    </div>
  </form>

  <!-- Results -->
  <table class="table table-bordered table-striped">
    <thead class="table-light">
      <tr>
        
        <th>PO Number</th>
        <th>PO Date</th>
        <th>Supplier</th>
        <th>Expected Delivery</th>
        <th>Items</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($orders): ?>
        <?php foreach ($orders as $o): ?>
          <tr>
            
            <td><?= htmlspecialchars($o['po_number']) ?></td>
            <td><?= date('d-m-Y', strtotime($o['po_date'])) ?></td>
            <td><?= htmlspecialchars($o['supplier_name']) ?></td>
            <td><?= $o['expected_date'] ? date('d-m-Y', strtotime($o['expected_date'])) : '-' ?></td>
            <td><?= $o['item_count'] ?></td>
           <td>
    <button class="btn btn-outline-success btn-sm" onclick="showPrint(<?= $o['po_id'] ?>)">🖨 </button>
    
    <a href="edit_po.php?id=<?= $o['po_id'] ?>" class="btn btn-outline-warning btn-sm">✏ </a>
    <a href="delete_po.php?id=<?= $o['po_id'] ?>" class="btn btn-outlindanger btn-sm" onclick="return confirm('Delete this PO?')">🗑 </a>
</td>

          </tr>
          
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="7" class="text-center">No purchase orders found.</td>
        </tr>
      <?php endif; ?>
      
    </tbody>
  </table>
<!-- Hidden Print Area -->
<div id="printArea" style="display:none; margin-top:30px;"></div>
<script>
function showPrint(po_id){
    fetch('purchase_order_print.php?id=' + po_id) // ✅ make sure filename matches
    .then(response => response.text())
    .then(html => {
        let printArea = document.getElementById('printArea');
        printArea.innerHTML = html;
        printArea.style.display = 'block';
        printDiv('printArea'); // ✅ call print only for this PO
    })
    .catch(err => console.error(err));
}

function printDiv(divId) {
    var printContents = document.getElementById(divId).innerHTML;
    var originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload(); // refresh to restore full page
}
</script>



</body>
</html>

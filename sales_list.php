<?php
require_once 'config.php';
$title = "Sales Orders";
include 'header.php';

// --- Handle search/filter ---
$where = [];
$params = [];

if (!empty($_GET['so_number'])) {
    $where[] = "so.so_number LIKE ?";
    $params[] = "%" . $_GET['so_number'] . "%";
}
if (!empty($_GET['customer_id'])) {
    $where[] = "so.customer_id = ?";
    $params[] = $_GET['customer_id'];
}

// Fetch sales orders with item count
$sql = "
    SELECT 
        so.so_id, 
        so.so_number, 
        so.so_date, 
        so.delivery_date,
        c.customer_name,
        COALESCE(items.item_count, 0) AS item_count
    FROM sales_orders so
    JOIN customers c ON so.customer_id = c.customer_id
    LEFT JOIN (
        SELECT so_id, COUNT(*) AS item_count
        FROM sales_order_items
        GROUP BY so_id
    ) items ON so.so_id = items.so_id
";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY so.so_number ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch customers for filter
$customers = $pdo->query("SELECT customer_id, customer_name FROM customers ORDER BY customer_name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Sales Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
  
  <h2>Sales Orders</h2>

  <div class="mb-3">
    <a href="insert_sales.php" class="btn btn-outline-primary">➕ New Sales Order</a>
  </div>

  <!-- Search / Filter Form -->
  <form method="get" class="row g-3 mb-4">
    <div class="col-md-4">
      <input type="text" name="so_number" value="<?= htmlspecialchars($_GET['so_number'] ?? '') ?>" class="form-control" placeholder="Search SO Number">
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
      <a href="sales_list.php" class="btn btn-outline-primary">Reset</a>
    </div>
  </form>

  <!-- Results -->
  <table class="table table-bordered table-striped">
    <thead class="table-light">
      <tr>
        <th>SO Number</th>
        <th>SO Date</th>
        <th>Customer</th>
        <th>Delivery Date</th>
        <th>Items</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($orders): ?>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><?= htmlspecialchars($o['so_number']) ?></td>
            <td><?= date('d-m-Y', strtotime($o['so_date'])) ?></td>
            <td><?= htmlspecialchars($o['customer_name']) ?></td>
            <td><?= $o['delivery_date'] ? date('d-m-Y', strtotime($o['delivery_date'])) : '-' ?></td>
            <td><?= $o['item_count'] ?></td>
            <td>
              <button class="btn btn-outline-success btn-sm" onclick="showPrint(<?= $o['so_id'] ?>)">🖨 </button>
              <a href="edit_sales.php?id=<?= $o['so_id'] ?>" class="btn btn-outline-warning btn-sm">✏ </a>
              <a href="delete_sales.php?id=<?= $o['so_id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this SO?')">🗑 </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6" class="text-center">No sales orders found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Hidden Print Area -->
  <div id="printArea" style="display:none; margin-top:30px;"></div>
  <script>
  function showPrint(so_id){
      fetch('sales_order_print.php?id=' + so_id)
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

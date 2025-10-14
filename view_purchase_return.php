<?php
require_once 'config.php';
include 'header.php';
if (empty($_GET['id'])) {
    die("❌ Invalid Request");
}

$id = (int)$_GET['id'];

// Fetch return header
$sql = "
    SELECT 
        pr.return_id,
        pr.return_no,
        pr.bill_date,
        s.supplier_name
    FROM purchase_returns pr
    JOIN suppliers s ON pr.supplier_id = s.supplier_id
    WHERE pr.return_id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$return = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$return) {
    die("❌ Purchase return not found");
}

// ✅ Fetch return items directly (no JOIN, since `items` table doesn’t exist)
$sql = "
    SELECT 
        p.product_name AS item_name,
        ri.quantity,
        ri.rate AS price,   -- use rate instead of price
        ri.line_total AS total
    FROM purchase_return_items ri
    JOIN products p ON ri.product_id = p.product_id
    WHERE ri.pr_id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>View Purchase Return</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
  
  <h2>Purchase Return Details</h2>

  <div class="mb-3">
    <a href="purchase_return_list.php" class="btn btn-outline-secondary">⬅ Back</a>
  </div>

  <table class="table table-bordered">
    <tr><th>Return No</th><td><?= htmlspecialchars($return['return_no']) ?></td></tr>
    <tr><th>Bill Date</th><td><?= date('d-m-Y', strtotime($return['bill_date'])) ?></td></tr>
    <tr><th>Supplier</th><td><?= htmlspecialchars($return['supplier_name']) ?></td></tr>
  </table>

  <h4>Returned Items</h4>
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Item</th>
        <th>Quantity</th>
        <th>Price</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($items): ?>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><?= htmlspecialchars($it['item_name']) ?></td>
            <td><?= $it['quantity'] ?></td>
            <td><?= number_format($it['price'], 2) ?></td>
            <td><?= number_format($it['total'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="4" class="text-center">No items found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

</body>
</html>

<?php
require_once 'config.php';
$title = "Purchase Returns";
include 'header.php';

// --- Handle search/filter ---
$where = [];
$params = [];

if (!empty($_GET['return_no'])) {
    $where[] = "pr.return_no LIKE ?";
    $params[] = "%" . $_GET['return_no'] . "%";
}
if (!empty($_GET['supplier_id'])) {
    $where[] = "pr.supplier_id = ?";
    $params[] = $_GET['supplier_id'];
}

// Fetch purchase returns with item count
$sql = "
    SELECT 
        pr.return_id, 
        pr.return_no, 
        pr.bill_date, 
        s.supplier_name,
        COALESCE(items.item_count, 0) AS item_count
    FROM purchase_returns pr
    JOIN suppliers s ON pr.supplier_id = s.supplier_id
    LEFT JOIN (
        SELECT pr_id, COUNT(*) AS item_count
        FROM purchase_return_items
        GROUP BY pr_id
    ) items ON pr.return_id = items.pr_id
";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY pr.return_no DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$returns = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch suppliers for filter
$suppliers = $pdo->query("SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Purchase Returns</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
  
  <h2>Purchase Returns</h2>

  <div class="mb-3">
    <a href="purchase_return.php" class="btn btn-outline-success">➕ New Purchase Return</a>
  </div>

  <!-- Search / Filter Form -->
  <form method="get" class="row g-3 mb-4">
    <div class="col-md-4">
      <input type="text" name="return_no" value="<?= htmlspecialchars($_GET['return_no'] ?? '') ?>" class="form-control" placeholder="Search Return No">
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
      <a href="purchase_return_list.php" class="btn btn-outline-secondary">Reset</a>
    </div>
  </form>

  <!-- Results -->
  <table class="table table-bordered table-striped">
    <thead class="table-light">
      <tr>
        <th>Return No</th>
        <th>Bill Date</th>
        <th>Supplier</th>
        <th>Items</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($returns): ?>
        <?php foreach ($returns as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['return_no']) ?></td>
            <td><?= date('d-m-Y', strtotime($r['bill_date'])) ?></td>
            <td><?= htmlspecialchars($r['supplier_name']) ?></td>
            <td><?= $r['item_count'] ?></td>
            <td>
              <!-- View -->
              <a href="view_purchase_return.php?id=<?= $r['return_id'] ?>" class="btn btn-outline-primary btn-sm">👁 </a>
              
              <!-- Delete -->
              <a href="delete_purchase_return.php?id=<?= $r['return_id'] ?>" 
                 onclick="return confirm('Delete this purchase return?')" 
                 class="btn btn-outline-danger btn-sm">🗑 </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" class="text-center">No purchase returns found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

</body>
</html>

<?php
require_once 'config.php';
$title = "Payment Out";
include 'header.php';

// --- Handle search/filter ---
$where = [];
$params = [];

if (!empty($_GET['pm_number'])) {
    $where[] = "po.pm_number LIKE ?";
    $params[] = "%" . $_GET['pm_number'] . "%";
}
if (!empty($_GET['supplier_id'])) {
    $where[] = "po.party_id = ?";
    $params[] = $_GET['supplier_id'];
}

// --- Fetch payments out ---
$sql = "
    SELECT 
        po.id, 
        po.pm_number, 
        po.date, 
        COALESCE(s.supplier_name, l.ledger_name) AS supplier_name,
        po.amount,
        po.payment_mode,
        po.notes
    FROM payments_out po
    LEFT JOIN suppliers s ON po.party_id = s.supplier_id
    LEFT JOIN ledgers l ON po.party_id = l.id
";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY po.pm_number ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Fetch suppliers for filter dropdown ---
$suppliers = $pdo->query("SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Payment Out List</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">

  <h2>Payment Out</h2>

  <div class="mb-3">
    <a href="payment_out.php" class="btn btn-outline-primary">➕ New Payment Out</a>
  </div>

  <!-- Search / Filter Form -->
  <form method="get" class="row g-3 mb-4">
    <div class="col-md-4">
      <input type="text" name="pm_number" value="<?= htmlspecialchars($_GET['pm_number'] ?? '') ?>" class="form-control" placeholder="Search PM Number">
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
      <a href="payment_out_list.php" class="btn btn-outline-secondary">Reset</a>
    </div>
  </form>

  <!-- Results Table -->
  <table class="table table-bordered table-striped">
    <thead class="table-light">
      <tr>
        <th>PM Number</th>
        <th>Date</th>
        <th>Supplier</th>
        <th>Amount</th>
        <th>Mode</th>
        <th>Notes</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($payments): ?>
        <?php foreach ($payments as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['pm_number']) ?></td>
            <td><?= date('d-m-Y', strtotime($p['date'])) ?></td>
            <td><?= htmlspecialchars($p['supplier_name']) ?></td>
            <td>₹<?= number_format($p['amount'], 2) ?></td>
            <td><?= htmlspecialchars($p['payment_mode']) ?></td>
            <td><?= htmlspecialchars($p['notes']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6" class="text-center">No payments found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

</body>
</html>

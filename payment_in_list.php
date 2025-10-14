<?php
require_once 'config.php';
$title = "Payment In";
include 'header.php';

// --- Handle search/filter ---
$where = [];
$params = [];

if (!empty($_GET['pi_number'])) {
    $where[] = "pi.pi_number LIKE ?";
    $params[] = "%" . $_GET['pi_number'] . "%";
}
if (!empty($_GET['customer_id'])) {
    $where[] = "pi.party_id = ?";
    $params[] = $_GET['customer_id'];
}

// --- Fetch payments in ---
$sql = "
    SELECT 
        pi.id, 
        pi.pi_number, 
        pi.date, 
        COALESCE(c.customer_name, l.ledger_name) AS customer_name,
        pi.amount,
        pi.payment_mode,
        pi.notes
    FROM payments_in pi
    LEFT JOIN customers c ON pi.party_id = c.customer_id
    LEFT JOIN ledgers l ON pi.party_id = l.id
";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY pi.pi_number ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Fetch customers for filter dropdown (FIXED) ---
$customers = $pdo->query("SELECT customer_id, customer_name FROM customers ORDER BY customer_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Payment In List</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">

  <h2>Payment In</h2>

  <div class="mb-3">
    <a href="payment_in.php" class="btn btn-outline-primary">➕ New Payment In</a>
  </div>

  <!-- Search / Filter Form -->
  <form method="get" class="row g-3 mb-4">
    <div class="col-md-4">
      <input type="text" name="pi_number" value="<?= htmlspecialchars($_GET['pi_number'] ?? '') ?>" class="form-control" placeholder="Search PI Number">
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
      <a href="payment_in_list.php" class="btn btn-outline-secondary">Reset</a>
    </div>
  </form>

  <!-- Results -->
  <table class="table table-bordered table-striped">
    <thead class="table-light">
      <tr>
        <th>PI Number</th>
        <th>Date</th>
        <th>Customer</th>
        <th>Amount</th>
        <th>Mode</th>
        <th>Notes</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($payments): ?>
        <?php foreach ($payments as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['pi_number']) ?></td>
            <td><?= date('d-m-Y', strtotime($p['date'])) ?></td>
            <td><?= htmlspecialchars($p['customer_name']) ?></td>
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

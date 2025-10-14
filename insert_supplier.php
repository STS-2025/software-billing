<?php
require_once 'config.php'; // $pdo connection
$title = "Purchase Orders";
include 'header.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_name = trim($_POST['supplier_name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    // ✅ Backend Validation
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $message = "❌ Phone number must be exactly 10 digits.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO suppliers (supplier_name, address, phone, email)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$supplier_name, $address, $phone, $email]);
            $message = "✅ Supplier Added Successfully!";
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// ✅ Fetch suppliers in ascending order
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY supplier_id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Add Supplier</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
  <h2>Supplier Entry</h2>

  <?php if ($message): ?>
    <div class="alert alert-info"><?= $message ?></div>
  <?php endif; ?>

  <form method="post" class="mb-4" onsubmit="return validatePhone()">
    <div class="row mb-3">
      <div class="col-md-6">
        <label>Supplier Name</label>
        <input type="text" name="supplier_name" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label>Phone</label>
        <input type="text" name="phone" id="phone" class="form-control"
               maxlength="10" pattern="[0-9]{10}" required
               placeholder="Enter 10-digit phone number">
      </div>
    </div>
    <div class="mb-3">
      <label>Address</label>
      <textarea name="address" class="form-control"></textarea>
    </div>
    <div class="mb-3">
      <label>Email</label>
      <input type="email" name="email" class="form-control">
    </div>
    <button type="submit" class="btn btn-outline-primary">Save Supplier</button>
    <a href="dashboard.php" class="btn btn-outline-secondary">Back</a>
  </form>

  <h4>Existing Suppliers</h4>
  <table class="table table-bordered table-striped">
    <thead class="table-light">
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Address</th>
        <th>Phone</th>
        <th>Email</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($suppliers as $s): ?>
        <tr>
          <td><?= $s['supplier_id'] ?></td>
          <td><?= htmlspecialchars($s['supplier_name']) ?></td>
          <td><?= htmlspecialchars($s['address']) ?></td>
          <td><?= htmlspecialchars($s['phone']) ?></td>
          <td><?= htmlspecialchars($s['email']) ?></td>
          <td>
            <a href="edit_supplier.php?id=<?= $s['supplier_id'] ?>" class="btn btn-sm btn-outline-primary">
              <i class="fa fa-edit"></i>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php if (isset($_GET['msg']) && $_GET['msg'] == 'linked'): ?>
    <div class="alert alert-warning">
        Cannot delete supplier because it is linked with purchase orders.
    </div>
  <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
    <div class="alert alert-success">
        Supplier deleted successfully!
    </div>
  <?php endif; ?>

  <script>
  // ✅ Frontend JS validation
  function validatePhone() {
      const phone = document.getElementById("phone").value;
      if (!/^[0-9]{10}$/.test(phone)) {
          alert("Phone number must be exactly 10 digits.");
          return false;
      }
      return true;
  }
  </script>
</body>
</html>
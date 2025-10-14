<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$id = (int) $_GET['id'];

// Fetch supplier details
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE supplier_id = ?");
$stmt->execute([$id]);
$supplier = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$supplier) {
    die("Supplier not found!");
}

$message = "";

// Update supplier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE suppliers 
                           SET supplier_name=?, address=?, phone=?, email=? 
                           WHERE supplier_id=?");
    $stmt->execute([
        $_POST['supplier_name'],
        $_POST['address'],
        $_POST['phone'],
        $_POST['email'],
        $id
    ]);
    $message = "Supplier updated successfully!";
    // Refresh supplier data
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE supplier_id = ?");
    $stmt->execute([$id]);
    $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Edit Supplier</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
  <h2>Edit Supplier</h2>

  <?php if ($message): ?>
    <div class="alert alert-success"><?= $message ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label>Supplier Name</label>
      <input type="text" name="supplier_name" class="form-control" value="<?= htmlspecialchars($supplier['supplier_name']) ?>" required>
    </div>
    <div class="mb-3">
      <label>Address</label>
      <textarea name="address" class="form-control"><?= htmlspecialchars($supplier['address']) ?></textarea>
    </div>
    <div class="mb-3">
      <label>Phone</label>
      <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($supplier['phone']) ?>">
    </div>
    <div class="mb-3">
      <label>Email</label>
      <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($supplier['email']) ?>">
    </div>
    <button type="submit" class="btn btn-outline-primary">Update</button>
    <a href="insert_supplier.php" class="btn btn-outline-secondary">Back</a>
  </form>
</body>
</html>

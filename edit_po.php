<?php
require_once 'config.php';
include 'header.php';

$po_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch PO
$stmt = $pdo->prepare("SELECT * FROM purchase_orders WHERE po_id=?");
$stmt->execute([$po_id]);
$po = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$po) {
    die("Purchase order not found!");
}

// Fetch items
$itemStmt = $pdo->prepare("SELECT * FROM purchase_order_items WHERE po_id=?");
$itemStmt->execute([$po_id]);
$items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch suppliers
$suppliers = $pdo->query("SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name")->fetchAll(PDO::FETCH_ASSOC);

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Update PO with tax_mode
        $stmt = $pdo->prepare("UPDATE purchase_orders 
            SET po_number=?, po_date=?, expected_date=?, supplier_id=?, tax_mode=?, notes=?, terms=? 
            WHERE po_id=?");
        $stmt->execute([
            $_POST['po_number'],
            $_POST['po_date'],
            $_POST['expected_date'],
            $_POST['supplier_id'],
            $_POST['tax_mode'],
            $_POST['notes'],
            $_POST['terms'],
            $po_id
        ]);

        // Delete old items
        $pdo->prepare("DELETE FROM purchase_order_items WHERE po_id=?")->execute([$po_id]);

        // Insert new items
        if (!empty($_POST['product_name']) && is_array($_POST['product_name'])) {
            foreach ($_POST['product_name'] as $i => $product) {
                $product = trim($product);
                if ($product === '') continue;

                $qty  = (float)($_POST['quantity'][$i] ?? 0);
                $rate = (float)($_POST['rate'][$i] ?? 0);

                $stmt = $pdo->prepare("
                    INSERT INTO purchase_order_items 
                    (po_id, product_name, quantity, rate)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$po_id, $product, $qty, $rate]);
            }
        }

        $pdo->commit();
        header("Location: edit_po.php?id=$po_id&msg=success");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'success') {
    $message = "Purchase Order updated successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Edit Purchase Order</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
  <h2>Edit Purchase Order</h2>
  <?php if ($message): ?>
    <div class="alert alert-info"><?= $message ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="row mb-3">
      <div class="col-md-4">
        <label>PO Number</label>
        <input type="text" name="po_number" class="form-control" value="<?= htmlspecialchars($po['po_number']) ?>" required>
      </div>
      <div class="col-md-4">
        <label>PO Date</label>
        <input type="date" name="po_date" class="form-control" value="<?= $po['po_date'] ?>" required>
      </div>
      <div class="col-md-4">
        <label>Expected Delivery</label>
        <input type="date" name="expected_date" class="form-control" value="<?= $po['expected_date'] ?>">
      </div>
    </div>

    <div class="mb-3">
      <label>Supplier</label>
      <select name="supplier_id" class="form-select" required>
        <?php foreach ($suppliers as $s): ?>
          <option value="<?= $s['supplier_id'] ?>" <?= $s['supplier_id']==$po['supplier_id']?'selected':'' ?>>
            <?= htmlspecialchars($s['supplier_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Tax Mode -->
    <div class="mb-3">
      <label class="form-label">Tax Mode</label><br>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="tax_mode" value="Inclusive" 
          <?= ($po['tax_mode'] === 'Inclusive') ? 'checked' : '' ?> required>
        <label class="form-check-label">Inclusive</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="tax_mode" value="Exclusive" 
          <?= ($po['tax_mode'] === 'Exclusive') ? 'checked' : '' ?>>
        <label class="form-check-label">Exclusive</label>
      </div>
    </div>

    <h5>Items</h5>
    <table class="table table-bordered" id="itemsTable">
      <thead>
        <tr>
          <th>Product</th>
          <th>Quantity</th>
          <th>Rate</th>
          <th><button type="button" class="btn btn-outline-info btn-sm" onclick="addRow()">+</button></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
          <td><input type="text" name="product_name[]" class="form-control" value="<?= htmlspecialchars($item['product_name']) ?>"></td>
          <td><input type="number" step="0.01" name="quantity[]" class="form-control" value="<?= $item['quantity'] ?>"></td>
          <td><input type="number" step="0.01" name="rate[]" class="form-control" value="<?= $item['rate'] ?>"></td>
          <td><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)">x</button></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="mb-3">
      <label>Notes</label>
      <textarea name="notes" class="form-control"><?= htmlspecialchars($po['notes']) ?></textarea>
    </div>
    <div class="mb-3">
      <label>Terms & Conditions</label>
      <textarea name="terms" class="form-control"><?= htmlspecialchars($po['terms']) ?></textarea>
    </div>

    <button type="submit" class="btn btn-outline-primary">Update PO</button>
    <a href="purchase_list.php" class="btn btn-outline-secondary">Back</a>
  </form>

<script>
function addRow() {
    let table = document.getElementById("itemsTable").getElementsByTagName("tbody")[0];
    let newRow = table.rows[0].cloneNode(true);
    newRow.querySelectorAll("input").forEach(input => input.value = "");
    table.appendChild(newRow);
}
function removeRow(btn) {
    let row = btn.closest("tr");
    let table = document.getElementById("itemsTable").getElementsByTagName("tbody")[0];
    if (table.rows.length > 1) row.remove();
}
</script>
</body>
</html>

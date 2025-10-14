<?php
require_once 'config.php';
$title = "Edit Purchase Invoice";
include 'header.php';

$pi_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($pi_id <= 0) {
    die("Invalid Invoice ID");
}

// Fetch invoice
$stmt = $pdo->prepare("SELECT * FROM purchase_invoices WHERE pi_id = ?");
$stmt->execute([$pi_id]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$invoice) {
    die("Invoice not found");
}

// Fetch invoice items
$stmt_items = $pdo->prepare("SELECT * FROM purchase_invoice_items WHERE pi_id = ?");
$stmt_items->execute([$pi_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// Fetch suppliers
$suppliers = $pdo->query("SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name")->fetchAll(PDO::FETCH_ASSOC);

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $tax_mode = $_POST['tax_mode'] ?? ($invoice['tax_mode'] ?? 'Inclusive');

        // Update invoice header with totals
        $stmt = $pdo->prepare("UPDATE purchase_invoices 
            SET pi_date=?, due_date=?, supplier_id=?, supplier_bill_no=?, notes=?, terms=?, tax_mode=?, subtotal=?, discount_total=?, gst_total=?, grand_total=? 
            WHERE pi_id=?");
        $stmt->execute([
            $_POST['pi_date'],
            $_POST['due_date'],
            $_POST['supplier_id'],
            $_POST['supplier_bill_no'],
            $_POST['notes'],
            $_POST['terms'],
            $tax_mode,
            $_POST['subtotal'],
            $_POST['discount_total'],
            $_POST['gst_total'],
            $_POST['grand_total'],
            $pi_id
        ]);

        // Delete old items
        $pdo->prepare("DELETE FROM purchase_invoice_items WHERE pi_id=?")->execute([$pi_id]);

        // Insert items
        if (!empty($_POST['product_name'])) {
            foreach ($_POST['product_name'] as $i => $prod) {
                if (trim($prod) === '') continue;

                $stmt_item = $pdo->prepare("INSERT INTO purchase_invoice_items 
                    (pi_id, category, product_name, quantity, rate, discount, gst, line_total, tax_mode) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_item->execute([
                    $pi_id,
                    $_POST['category'][$i],
                    $prod,
                    $_POST['quantity'][$i],
                    $_POST['rate'][$i],
                    $_POST['discount'][$i],
                    $_POST['gst'][$i],
                    $_POST['line_total'][$i],
                    $tax_mode
                ]);
            }
        }

        $pdo->commit();
        $message = "Invoice updated successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <h2>Edit Purchase Invoice</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Invoice Number</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($invoice['pi_number']) ?>" readonly>
            </div>
            <div class="col-md-3">
                <label>Invoice Date</label>
                <input type="date" name="pi_date" value="<?= $invoice['pi_date'] ?>" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Due Date</label>
                <input type="date" name="due_date" value="<?= $invoice['due_date'] ?>" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Supplier Bill No</label>
                <input type="text" name="supplier_bill_no" value="<?= htmlspecialchars($invoice['supplier_bill_no']) ?>" class="form-control">
            </div>
        </div>

        <div class="mb-3 row">
            <div class="col-md-6">
                <label>Supplier</label>
                <select name="supplier_id" class="form-select" required>
                    <option value="">-- Select Supplier --</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= $s['supplier_id'] ?>" <?= $s['supplier_id'] == $invoice['supplier_id'] ? "selected" : "" ?>>
                            <?= htmlspecialchars($s['supplier_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Tax Mode -->
        <div class="mb-3">
            <label>Tax Mode:</label><br>
            <input type="radio" name="tax_mode" value="Inclusive" <?= ($invoice['tax_mode'] ?? 'Inclusive') == 'Inclusive' ? 'checked' : '' ?>> Inclusive
            <input type="radio" name="tax_mode" value="Exclusive" <?= ($invoice['tax_mode'] ?? '') == 'Exclusive' ? 'checked' : '' ?>> Exclusive
        </div>

        <!-- Items Table -->
        <h5>Invoice Items</h5>
        <table class="table table-bordered" id="itemsTable">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Rate</th>
                    <th>Discount %</th>
                    <th>GST %</th>
                    <th>Line Total</th>
                    <th><button type="button" class="btn btn-success btn-sm" onclick="addRow()">➕ Add</button></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><input type="text" name="category[]" value="<?= htmlspecialchars($item['category']) ?>" class="form-control"></td>
                    <td><input type="text" name="product_name[]" value="<?= htmlspecialchars($item['product_name']) ?>" class="form-control"></td>
                    <td><input type="number" name="quantity[]" value="<?= $item['quantity'] ?>" class="form-control"></td>
                    <td><input type="number" name="rate[]" value="<?= $item['rate'] ?>" class="form-control"></td>
                    <td><input type="number" name="discount[]" value="<?= $item['discount'] ?>" class="form-control"></td>
                    <td><input type="number" name="gst[]" value="<?= $item['gst'] ?>" class="form-control"></td>
                    <td><input type="number" name="line_total[]" value="<?= $item['line_total'] ?>" class="form-control" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">❌</button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals -->
       

        <div class="mb-3">
            <label>Notes</label>
            <textarea name="notes" class="form-control"><?= htmlspecialchars($invoice['notes']) ?></textarea>
        </div>
        <div class="mb-3">
            <label>Terms & Conditions</label>
            <textarea name="terms" class="form-control"><?= htmlspecialchars($invoice['terms']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Invoice</button>
        <a href="purchase_invoice_list.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<script>
function addRow() {
    let table = document.querySelector("#itemsTable tbody");
    let firstRow = table.rows[0];
    let newRow = firstRow.cloneNode(true);

    newRow.querySelectorAll("input").forEach(input => input.value = "");
    table.appendChild(newRow);
    recalcTotals();
}

function removeRow(btn) {
    let row = btn.closest("tr");
    let table = document.querySelector("#itemsTable tbody");
    if (table.rows.length > 1) {
        row.remove();
        recalcTotals();
    }
}

function recalcTotals() {
    let rows = document.querySelectorAll("#itemsTable tbody tr");
    let subtotal = 0, discount_total = 0, gst_total = 0;

    let taxMode = document.querySelector("input[name='tax_mode']:checked")?.value || "Inclusive";

    rows.forEach(row => {
        let qty = parseFloat(row.querySelector("input[name='quantity[]']").value) || 0;
        let rate = parseFloat(row.querySelector("input[name='rate[]']").value) || 0;
        let disc = parseFloat(row.querySelector("input[name='discount[]']").value) || 0;
        let gst = parseFloat(row.querySelector("input[name='gst[]']").value) || 0;

        let line = qty * rate;
        let discAmt = line * (disc / 100);
        let afterDisc = line - discAmt;
        let gstAmt = (taxMode === "Exclusive") ? (afterDisc * (gst / 100)) : 0;
        let finalLine = afterDisc + gstAmt;

        row.querySelector("input[name='line_total[]']").value = finalLine.toFixed(2);

        subtotal += line;
        discount_total += discAmt;
        gst_total += gstAmt;
    });

    
}

document.addEventListener("input", function(e) {
    if (e.target.closest("#itemsTable")) {
        recalcTotals();
    }
});
document.addEventListener("change", function(e) {
    if (e.target.name === "tax_mode") {
        recalcTotals();
    }
});
document.addEventListener("DOMContentLoaded", recalcTotals);
</script>
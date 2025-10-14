<?php
require_once 'config.php';
$title = "Edit Sales Invoice";
include 'header.php';

$si_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($si_id <= 0) {
    die("Invalid Invoice ID");
}

// Fetch invoice header
$stmt = $pdo->prepare("SELECT * FROM sales_invoices WHERE si_id = ?");
$stmt->execute([$si_id]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$invoice) {
    die("Invoice not found");
}

// Fetch invoice items
$stmt_items = $pdo->prepare("SELECT * FROM sales_invoice_items WHERE si_id = ?");
$stmt_items->execute([$si_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// Fetch customers
$customers = $pdo->query("SELECT customer_id, customer_name FROM customers ORDER BY customer_name")->fetchAll(PDO::FETCH_ASSOC);

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $tax_mode = $_POST['tax_mode'] ?? ($invoice['tax_mode'] ?? 'Inclusive');

        // Update invoice header
        $stmt = $pdo->prepare("UPDATE sales_invoices 
            SET si_date=?, due_date=?, customer_id=?, customer_bill_no=?, notes=?, terms=?, tax_mode=?, subtotal=?, discount_total=?, gst_total=?, grand_total=? 
            WHERE si_id=?");
        $stmt->execute([
            $_POST['si_date'],
            $_POST['due_date'],
            $_POST['customer_id'],
            $_POST['customer_bill_no'],
            $_POST['notes'],
            $_POST['terms'],
            $tax_mode,
            $_POST['subtotal'],
            $_POST['discount_total'],
            $_POST['gst_total'],
            $_POST['grand_total'],
            $si_id
        ]);

        // Delete old items
        $pdo->prepare("DELETE FROM sales_invoice_items WHERE si_id=?")->execute([$si_id]);

        // Insert items
        if (!empty($_POST['product_name'])) {
            foreach ($_POST['product_name'] as $i => $prod) {
                if (trim($prod) === '') continue;

                $stmt_item = $pdo->prepare("INSERT INTO sales_invoice_items 
                    (si_id, category, product_name, quantity, rate, discount, gst, line_total, tax_mode) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_item->execute([
                    $si_id,
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
        $message = "Sales Invoice updated successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <h2>Edit Sales Invoice</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Invoice Number</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($invoice['si_number']) ?>" readonly>
            </div>
            <div class="col-md-3">
                <label>Invoice Date</label>
                <input type="date" name="si_date" value="<?= $invoice['si_date'] ?>" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Due Date</label>
                <input type="date" name="due_date" value="<?= $invoice['due_date'] ?>" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Customer Bill No</label>
                <input type="text" name="customer_bill_no" value="<?= htmlspecialchars($invoice['customer_bill_no']) ?>" class="form-control">
            </div>
        </div>

        <div class="mb-3 row">
            <div class="col-md-6">
                <label>Customer</label>
                <select name="customer_id" class="form-select" required>
                    <option value="">-- Select Customer --</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['customer_id'] ?>" <?= $c['customer_id'] == $invoice['customer_id'] ? "selected" : "" ?>>
                            <?= htmlspecialchars($c['customer_name']) ?>
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

        <div class="mb-3">
            <label>Notes</label>
            <textarea name="notes" class="form-control"><?= htmlspecialchars($invoice['notes']) ?></textarea>
        </div>
        <div class="mb-3">
            <label>Terms & Conditions</label>
            <textarea name="terms" class="form-control"><?= htmlspecialchars($invoice['terms']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Invoice</button>
        <a href="sales_invoice_list.php" class="btn btn-secondary">Back</a>
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

document.addEventListener("input", recalcTotals);
document.addEventListener("change", e => {
    if (e.target.name === "tax_mode") recalcTotals();
});
document.addEventListener("DOMContentLoaded", recalcTotals);
</script>

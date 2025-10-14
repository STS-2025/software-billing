<?php
require_once "config.php";
$title = "Sales Return";

// --- Handle form submit BEFORE any HTML output ---
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Insert Sales Return
        $stmt = $pdo->prepare("INSERT INTO sales_returns 
            (return_no, invoice_no, invoice_date, customer_id, state, tax_mode, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['return_no'],
            $_POST['bill_no'],
            $_POST['bill_date'],
            $_POST['customer_id'],
            $_POST['state'],
            $_POST['tax_mode'],
            $_POST['description']
        ]);

        $sr_id = $pdo->lastInsertId();

        // Check if stock column exists
        $columns = $pdo->query("SHOW COLUMNS FROM products LIKE 'stock'")->fetch();
        $has_stock = $columns ? true : false;

        // Insert Items + update stock
        if (!empty($_POST['product_id'])) {
            foreach ($_POST['product_id'] as $i => $pid) {
                if (!$pid) continue;
                $qty = $_POST['quantity'][$i];
                $rate = $_POST['rate'][$i];
                $disc = $_POST['discount'][$i];
                $gst = $_POST['gst'][$i];
                $line_total = $_POST['line_total'][$i];

                $stmt_item = $pdo->prepare("INSERT INTO sales_return_items 
                    (sr_id, product_id, quantity, rate, discount, gst, line_total) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt_item->execute([
                    $sr_id, $pid, $qty, $rate, $disc, $gst, $line_total
                ]);

                if ($has_stock) {
                    $pdo->prepare("UPDATE products SET stock = stock + ? WHERE product_id = ?")
                        ->execute([$qty, $pid]);
                }
            }
        }

        $pdo->commit();

        // Redirect to same page to avoid duplicate submission and refresh SR number
        header("Location: sales_return.php?success=1");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
    }
}

// --- Now include header and rest of HTML ---
include "header.php";

// Auto-generate SR Number
$last_sr = $pdo->query("SELECT return_no FROM sales_returns ORDER BY return_id DESC LIMIT 1")->fetchColumn();
if ($last_sr) {
    $num = (int)substr($last_sr, strrpos($last_sr, "/") + 1);
    $return_no = "SR/25-26/" . str_pad($num + 1, 4, "0", STR_PAD_LEFT);
} else {
    $return_no = "SR/25-26/0001";
}

// Check stock
$columns = $pdo->query("SHOW COLUMNS FROM products LIKE 'stock'")->fetch();
$has_stock = $columns ? true : false;

// Fetch products and customers
$product_query = "SELECT product_id, product_name, rate, gst, discount";
if ($has_stock) $product_query .= ", stock";
$product_query .= " FROM products ORDER BY product_name";
$products = $pdo->query($product_query)->fetchAll(PDO::FETCH_ASSOC);

$customers = $pdo->query("SELECT customer_id, customer_name FROM customers ORDER BY customer_name")->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['success'])) {
    $message = "✅ Sales Return Saved Successfully!";
}
?>


<div class="container mt-4">
    <h2>Sales Return</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="row mb-3">
            <div class="col-md-3">
                <label>SR Number</label>
                <input type="text" name="return_no" class="form-control" value="<?= $return_no ?>" readonly>
            </div>
            <div class="col-md-3">
                <label>Bill Number</label>
                <input type="text" name="bill_no" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Bill Date</label>
                <input type="date" name="bill_date" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>State / Place of Supply</label>
                <input type="text" name="state" class="form-control" value="Tamil Nadu">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Customer</label>
                <select name="customer_id" class="form-select" required>
                    <option value="">-- Select Customer --</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['customer_id'] ?>"><?= htmlspecialchars($c['customer_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label>Tax Mode</label><br>
                <input type="radio" name="tax_mode" value="Inclusive" checked onclick="updateAllLineTotals()"> Inclusive
                <input type="radio" name="tax_mode" value="Exclusive" onclick="updateAllLineTotals()"> Exclusive
            </div>
        </div>

        <h5 class="mt-4">Return Items</h5>
        <table class="table table-bordered" id="itemsTable">
            <thead class="table-dark">
                <tr>
                    <th>Product</th>
                    <?php if ($has_stock) echo "<th>Stock</th>"; ?>
                    <th>Unit</th>
                    <th>Rate</th>
                    <th>Qty</th>
                    <th>Discount %</th>
                    <th>GST %</th>
                    <th>Line Total</th>
                    <th><button type="button" class="btn btn-success btn-sm" onclick="addRow()">➕</button></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="product_id[]" class="form-select" onchange="fillProductDetails(this)">
                            <option value="">-- Select Product --</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['product_id'] ?>" 
                                    data-rate="<?= $p['rate'] ?>" 
                                    data-gst="<?= $p['gst'] ?>" 
                                    data-discount="<?= $p['discount'] ?>" 
                                    data-stock="<?= $has_stock ? $p['stock'] : 0 ?>">
                                    <?= htmlspecialchars($p['product_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <?php if ($has_stock): ?>
                    <td><input type="text" name="stock[]" class="form-control" readonly></td>
                    <?php endif; ?>
                    <td><input type="text" name="unit[]" value="Nos" class="form-control"></td>
                    <td><input type="number" name="rate[]" class="form-control" readonly></td>
                    <td><input type="number" name="quantity[]" value="1" class="form-control" oninput="updateLineTotal(this)"></td>
                    <td><input type="number" name="discount[]" class="form-control" readonly></td>
                    <td><input type="number" name="gst[]" class="form-control" readonly></td>
                    <td><input type="number" name="line_total[]" class="form-control" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">x</button></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="<?= $has_stock ? '8' : '6' ?>" class="text-end">Net Amount</th>
                    <th><input type="number" id="grandTotal" class="form-control" readonly></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
 <div class="text-end">
          <button type="submit" class="btn btn-outline-success">
            <i class="fa fa-save"></i> Save
          </button>
          <a href="sales_return_list.php" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Back
          </a>
        </div>
    </form>
</div>

<script>
let products = <?php echo json_encode($products); ?>;

function addRow() {
    let tbody = document.querySelector("#itemsTable tbody");
    let newRow = tbody.rows[0].cloneNode(true);
    newRow.querySelectorAll("input").forEach(inp => inp.value = "");
    newRow.querySelectorAll("select").forEach(sel => sel.selectedIndex = 0);
    tbody.appendChild(newRow);
}

function removeRow(btn) {
    let row = btn.closest("tr");
    let tbody = document.querySelector("#itemsTable tbody");
    if (tbody.rows.length > 1) row.remove();
    calculateGrandTotal();
}

function fillProductDetails(el) {
    let row = el.closest("tr");
    let opt = el.selectedOptions[0];
    if (!opt) return;

    let stockField = row.querySelector("input[name='stock[]']");
    if (stockField) stockField.value = opt.dataset.stock || 0;

    row.querySelector("input[name='rate[]']").value = opt.dataset.rate || 0;
    row.querySelector("input[name='discount[]']").value = opt.dataset.discount || 0;
    row.querySelector("input[name='gst[]']").value = opt.dataset.gst || 0;
    updateLineTotal(row.querySelector("input[name='quantity[]']"));
}

function getTaxMode() {
    let mode = document.querySelector("input[name='tax_mode']:checked");
    return mode ? mode.value : "Exclusive";
}

function updateLineTotal(el) {
    let row = el.closest("tr");
    let qty = parseFloat(row.querySelector("input[name='quantity[]']").value) || 0;
    let rate = parseFloat(row.querySelector("input[name='rate[]']").value) || 0;
    let disc = parseFloat(row.querySelector("input[name='discount[]']").value) || 0;
    let gst = parseFloat(row.querySelector("input[name='gst[]']").value) || 0;

    let total = qty * rate;
    total -= total * (disc / 100);
    if (getTaxMode() === "Exclusive") {
        total += total * (gst / 100);
    }
    row.querySelector("input[name='line_total[]']").value = total.toFixed(2);
    calculateGrandTotal();
}

function updateAllLineTotals() {
    document.querySelectorAll("input[name='quantity[]']").forEach(inp => updateLineTotal(inp));
}

function calculateGrandTotal() {
    let total = 0;
    document.querySelectorAll("input[name='line_total[]']").forEach(inp => {
        total += parseFloat(inp.value) || 0;
    });
    document.getElementById("grandTotal").value = total.toFixed(2);
}
</script>
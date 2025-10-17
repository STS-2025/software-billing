<?php
require_once 'config.php';
$title = "New Purchase Invoice";
include 'header.php';

function generatePINumber($pdo) {
    $last_pi = $pdo->query("SELECT pi_number FROM purchase_invoices ORDER BY pi_id DESC LIMIT 1")->fetchColumn();
    if ($last_pi) {
        $num = (int)substr($last_pi, strrpos($last_pi, "/")+1);
        return "PI/25-26/" . str_pad($num + 1, 4, "0", STR_PAD_LEFT);
    } else {
        return "PI/25-26/0001";
    }
}

$pi_number = generatePINumber($pdo);
$selected_po_id = isset($_GET['po_id']) ? intval($_GET['po_id']) : 0;
$po = null;
$items = [];

if ($selected_po_id) {
    $stmt = $pdo->prepare("SELECT * FROM purchase_orders WHERE po_id = ?");
    $stmt->execute([$selected_po_id]);
    $po = $stmt->fetch(PDO::FETCH_ASSOC);

    $itemStmt = $pdo->prepare("SELECT * FROM purchase_order_items WHERE po_id = ?");
    $itemStmt->execute([$selected_po_id]);
    $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

    if ($po) {
        $selected_supplier_id = $po['supplier_id'];
    }
}

$selected_supplier_id = isset($_GET['supplier_id']) ? intval($_GET['supplier_id']) : 0;
$viewMode = isset($_GET['view']) && $_GET['view'] == 1;
$pi_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$categories = $pdo->query("SELECT category_name FROM categories ORDER BY category_name")->fetchAll(PDO::FETCH_COLUMN);

if ($viewMode && $pi_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM purchase_invoices WHERE pi_id = ?");
    $stmt->execute([$pi_id]);
    $pi = $stmt->fetch(PDO::FETCH_ASSOC);

    $itemStmt = $pdo->prepare("SELECT * FROM purchase_invoice_items WHERE pi_id = ?");
    $itemStmt->execute([$pi_id]);
    $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $pi = [];
    $items = [];
}

$suppliers = $pdo->query("SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name")->fetchAll(PDO::FETCH_ASSOC);
$products = $pdo->query("SELECT product_name, category, rate, discount, gst, stock_quantity FROM products")->fetchAll(PDO::FETCH_ASSOC);

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO purchase_invoices 
            (pi_number, pi_date, due_date, supplier_id, supplier_bill_no, notes, tax_mode, terms) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['pi_number'],
            $_POST['pi_date'],
            $_POST['due_date'],
            $_POST['supplier_id'],
            $_POST['supplier_bill_no'],
            $_POST['notes'],
            $_POST['tax_mode'],
            $_POST['terms']
        ]);

        $pi_id = $pdo->lastInsertId();
         $grandTotal = 0; // initialize

        if (!empty($_POST['product_name'])) {
            foreach ($_POST['product_name'] as $i => $prod) {
                if(trim($prod) === '') continue;

                $qty = $_POST['quantity'][$i];
                $rate = $_POST['rate'][$i];
                $disc = $_POST['discount'][$i];
                $gst = $_POST['gst'][$i];
                $tax_mode = $_POST['tax_mode'];

                $line_total = $qty * $rate;
                $line_total -= $line_total * ($disc/100);
                if($tax_mode === "Exclusive") $line_total += $line_total * ($gst/100);

                $stmt_item = $pdo->prepare("INSERT INTO purchase_invoice_items 
                    (pi_id, category, product_name, quantity, rate, discount, gst, line_total, tax_mode) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_item->execute([
                    $pi_id,
                    $_POST['category'][$i],
                    $prod,
                    $qty,
                    $rate,
                    $disc,
                    $gst,
                    $line_total,
                    $tax_mode
                ]);

               /* $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_name = ?")
                    ->execute([$qty, $prod]);*/
                    // Add to grand total
                $grandTotal += $line_total;
            }
        }
        // Step 3: Update invoice grand total
        $updateTotal = $pdo->prepare("UPDATE purchase_invoices SET grand_total = ? WHERE pi_id = ?");
        $updateTotal->execute([$grandTotal, $pi_id]);


        $pdo->commit();
        $pi_number = generatePINumber($pdo);
$message = "Purchase Invoice saved successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <h2>Create Purchase Invoice</h2>
    <?php if($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="post" id="invoiceForm">
        <div class="row mb-3">
            <div class="col-md-3">
                <label>PI Number</label>
                <input type="text" name="pi_number" class="form-control" value="<?= $pi_number ?>" readonly>
            </div>
            <div class="col-md-3">
                <label>Invoice Date</label>
                <input type="date" name="pi_date" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Due Date</label>
                <input type="date" name="due_date" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Supplier Bill No</label>
                <input type="text" name="supplier_bill_no" id="supplier_bill_no" class="form-control">
            </div>
        </div>

        <div class="mb-3 row">
            <?php
            $purchase_orders = $pdo->query("
                SELECT po.po_id, po.po_number, s.supplier_name 
                FROM purchase_orders po
                JOIN suppliers s ON po.supplier_id = s.supplier_id
                ORDER BY po.po_id DESC
            ")->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="col-md-3">
                <label>PO Number</label>
                <select name="po_id" id="po_id" class="form-select">
                    <option value="">-- Select PO --</option>
                    <?php foreach($purchase_orders as $po): ?>
                        <option value="<?= $po['po_id'] ?>">
                            <?= htmlspecialchars($po['po_number']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label>Supplier</label>
                <select name="supplier_id" class="form-select" required>
                    <option value="">-- Select Supplier --</option>
                    <?php foreach($suppliers as $s): ?>
                        <option value="<?= $s['supplier_id'] ?>" <?= (isset($selected_supplier_id) && $s['supplier_id'] == $selected_supplier_id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['supplier_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <h5 class="mt-4">Invoice Items</h5>
        <div class="mb-3">
            <label>Tax Mode:</label><br>
            <input type="radio" name="tax_mode" value="Inclusive" checked onchange="updateAllLineTotals()"> Inclusive
            <input type="radio" name="tax_mode" value="Exclusive" onchange="updateAllLineTotals()"> Exclusive
        </div>

        <table class="table table-bordered table-striped" id="itemsTable">
            <thead class="table-dark">
                <tr>
                    <th>Category</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Rate</th>
                    <th>Discount %</th>
                    <th>GST %</th>
                    <th>Line Total</th>
                    <th>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="addRow()" >➕ </button>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="category[]" class="form-select" onchange="updateProductOptions(this)">
                            <option value="">-- Select Category --</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td style="vertical-align: top;">
                        <div style="display: flex; flex-direction: column;">
                            <select name="product_name[]" class="form-select" onchange="fillProductDetails(this)">
                                <option value="">-- Select Product --</option>
                            </select>
                            <small class="text-muted stock-info" style="font-size: 12px; margin-top: 2px;"></small>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column;">
                            <input type="number" name="quantity[]" value="1" class="form-control" oninput="validateQuantity(this)">
                            <small class="text-danger qty-error" style="font-size: 12px; display:none;">Quantity exceeds stock!</small>
                        </div>
                    </td>
                    <td><input type="number" name="rate[]" class="form-control" readonly></td>
                    <td><input type="number" name="discount[]" class="form-control" readonly></td>
                    <td><input type="number" name="gst[]" class="form-control" readonly></td>
                    <td><input type="number" name="line_total[]" class="form-control" readonly></td>
                    <td><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)" >x</button></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="6" class="text-end">Grand Total:</th>
                    <th><input type="number" id="grandTotal" class="form-control" readonly></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>

        <div class="mb-3">
            <label>Notes</label>
            <textarea name="notes" class="form-control" rows="3"></textarea>
        </div>
        <div class="mb-3">
            <label>Terms & Conditions</label>
            <textarea name="terms" class="form-control" rows="3"></textarea>
        </div>
        <div class="text-end">
          <button type="submit" class="btn btn-outline-success">
            <i class="fa fa-save"></i> Save
          </button>
          <a href="purchase_invoice_list.php" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Back
          </a>
        </div>
    </form>
</div>
<script>
    function selectPO(po_id) {
    if (po_id) {
        window.location.href = window.location.pathname + "?po_id=" + po_id;
    } else {
        window.location.href = window.location.pathname;
    }
}
let products = <?php echo json_encode($products); ?>;

// PO change event
document.getElementById("po_id").addEventListener("change", function(e){
    e.preventDefault();
    if (this.value) loadPOData(this.value);
    else window.location.href = window.location.pathname;
});

// Clone template row
document.addEventListener("DOMContentLoaded", function() {
    const templateRow = document.querySelector("#itemsTable tbody tr").cloneNode(true);
    templateRow.id = "templateRow";
    templateRow.style.display = "none";
    document.querySelector("#itemsTable tbody").prepend(templateRow);
});

function addRow() {
    const table = document.querySelector('#itemsTable tbody');
    const template = document.getElementById("templateRow");
    const newRow = template.cloneNode(true);
    newRow.id = "";
    newRow.style.display = "";
    table.appendChild(newRow);
}

function removeRow(btn) {
    const row = btn.closest('tr');
    const table = document.querySelector('#itemsTable tbody');
    if (table.rows.length > 2) row.remove();
    calculateGrandTotal();
}

function updateProductOptions(el) {
    const row = el.closest('tr');
    const productSelect = row.querySelector('select[name="product_name[]"]');
    productSelect.innerHTML = '<option value="">-- Select Product --</option>';
    
    products.forEach(p => {
        if (p.category === el.value) {
            const opt = document.createElement('option');
            opt.value = p.product_name;
            opt.text = p.product_name;
            opt.dataset.rate = p.rate;
            opt.dataset.gst = p.gst;
            opt.dataset.discount = p.discount;
            opt.dataset.stock = p.stock_quantity;
            productSelect.appendChild(opt);
        }
    });
}

function fillProductDetails(el) {
    const row = el.closest('tr');
    const product = products.find(p => p.product_name === el.value);
    const stockInfo = row.querySelector('.stock-info');

    if (product) {
        row.querySelector('input[name="rate[]"]').value = product.rate || 0;
        row.querySelector('input[name="discount[]"]').value = product.discount || 0;
        row.querySelector('input[name="gst[]"]').value = product.gst || 0;
        stockInfo.textContent = "Stock: " + (product.stock_quantity || 0);
        stockInfo.style.color = (product.stock_quantity <= 0) ? "red" : "#6c757d";
        row.querySelector('input[name="quantity[]"]').setAttribute('data-stock', product.stock_quantity);
    } else {
        stockInfo.textContent = "";
    }

    updateLineTotal(row.querySelector('input[name="quantity[]"]'));
}

function getTaxMode() {
    const mode = document.querySelector('input[name="tax_mode"]:checked');
    return mode ? mode.value : 'Exclusive';
}

function updateLineTotal(el) {
    const row = el.closest('tr');
    const qty = parseFloat(row.querySelector('input[name="quantity[]"]').value) || 0;
    const rate = parseFloat(row.querySelector('input[name="rate[]"]').value) || 0;
    const disc = parseFloat(row.querySelector('input[name="discount[]"]').value) || 0;
    const gst = parseFloat(row.querySelector('input[name="gst[]"]').value) || 0;

    let total = qty * rate;
    total -= total * (disc / 100);

    if (getTaxMode() === 'Exclusive') total += total * (gst / 100);

    row.querySelector('input[name="line_total[]"]').value = total.toFixed(2);
    calculateGrandTotal();
}

function updateAllLineTotals() {
    document.querySelectorAll('input[name="quantity[]"]').forEach(input => updateLineTotal(input));
}

function calculateGrandTotal() {
    let total = 0;
    document.querySelectorAll('input[name="line_total[]"]').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    document.getElementById('grandTotal').value = total.toFixed(2);
}

function validateQuantity(el) {
    const stock = parseFloat(el.getAttribute('data-stock')) || 0;
    const qty = parseFloat(el.value) || 0;
    const errorMsg = el.parentNode.querySelector('.qty-error');

    if (qty > stock && stock > 0) {
        errorMsg.style.display = 'block';
    } else {
        errorMsg.style.display = 'none';
    }

    updateLineTotal(el);
}

function loadPOData(po_id) {
    if (!po_id) return;

    fetch("load_po.php?po_id=" + po_id)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.querySelector("select[name='supplier_id']").value = data.po.supplier_id;
                document.getElementById("supplier_bill_no").value = data.po.supplier_bill_no || "";

                const tbody = document.querySelector("#itemsTable tbody");
                const template = document.getElementById("templateRow");
                tbody.innerHTML = "";
                tbody.appendChild(template);

                data.items.forEach(item => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td><input type="text" name="category[]" value="${item.category}" class="form-control" readonly></td>
                        <td style="vertical-align: top;">
                            <div style="display: flex; flex-direction: column;">
                                <input type="text" name="product_name[]" value="${item.product_name}" class="form-control" readonly>
                                <small class="text-muted stock-info" style="font-size: 12px; margin-top: 2px;"></small>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column;">
                                <input type="number" name="quantity[]" value="${item.quantity}" class="form-control" oninput="validateQuantity(this)">
                                <small class="text-danger qty-error" style="font-size: 12px; display:none;">Quantity exceeds stock!</small>
                            </div>
                        </td>
                        <td><input type="number" name="rate[]" value="${item.rate}" class="form-control"></td>
                        <td><input type="number" name="discount[]" value="${item.discount}" class="form-control"></td>
                        <td><input type="number" name="gst[]" value="${item.gst}" class="form-control"></td>
                        <td><input type="number" name="line_total[]" value="${item.line_total}" class="form-control" readonly></td>
                        <td><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)">x</button></td>
                    `;
                    tbody.appendChild(row);
                });

                calculateGrandTotal();
            }
        });
}

// 🟢 AJAX form submit to update stock immediately
document.getElementById("invoiceForm").addEventListener("submit", function(e){
    e.preventDefault(); // prevent default submit

    const form = this;
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        alert("Invoice saved successfully!");

        // Update stock-info for each product row
        document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
            const productName = row.querySelector('select[name="product_name[]"]').value;
            const stockInfo = row.querySelector('.stock-info');
            const product = products.find(p => p.product_name === productName);

            if(product){
                const qty = parseFloat(row.querySelector('input[name="quantity[]"]').value) || 0;
                product.stock_quantity = (parseFloat(product.stock_quantity) || 0) + qty;
                stockInfo.textContent = "Stock: " + product.stock_quantity;
                stockInfo.style.color = (product.stock_quantity <= 0) ? "red" : "#6c757d";
                row.querySelector('input[name="quantity[]"]').setAttribute('data-stock', product.stock_quantity);
            }
        });

        form.reset(); // reset for new invoice
        
    })
    .catch(err => console.error(err));
});
</script>



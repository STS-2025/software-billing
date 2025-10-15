<?php
require_once 'config.php';
$title = "New Sales Invoice";
include 'header.php';

// --- Function to generate SI number ---
function generateSINumber($pdo) {
    $year1 = date("y");
    $month = date("n");
    if ($month >= 4) $year2 = $year1 + 1;
    else { $year2 = $year1; $year1 = $year1 - 1; }
    $fy = $year1 . "-" . $year2;

    $stmt = $pdo->prepare("SELECT si_number FROM sales_invoices WHERE si_number LIKE ? ORDER BY si_id DESC LIMIT 1");
    $stmt->execute(["SI/$fy/%"]);
    $last_si = $stmt->fetchColumn();

    if ($last_si && preg_match("/(\d{4})$/", $last_si, $m)) $next = (int)$m[1] + 1;
    else $next = 1;

    return "SI/$fy/" . str_pad($next, 4, "0", STR_PAD_LEFT);
}

// Fetch data for form
$soNumbers = $pdo->query("SELECT so_id, so_number FROM sales_orders ORDER BY so_id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT category_name FROM categories ORDER BY category_name")->fetchAll(PDO::FETCH_COLUMN);
$customers = $pdo->query("SELECT customer_id, customer_name FROM customers ORDER BY customer_name")->fetchAll(PDO::FETCH_ASSOC);
$products = $pdo->query("SELECT product_name, category, rate, discount, gst, stock_quantity FROM products")->fetchAll(PDO::FETCH_ASSOC);

$si_number = generateSINumber($pdo);
$message = "";

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        $so_id = !empty($_POST['so_id']) ? intval($_POST['so_id']) : NULL;

        // Insert invoice
        $stmt = $pdo->prepare("INSERT INTO sales_invoices 
            (si_number, si_date, due_date, customer_id, customer_bill_no, notes, tax_mode, terms, so_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['si_number'], $_POST['si_date'], $_POST['due_date'],
            $_POST['customer_id'], $_POST['customer_bill_no'], $_POST['notes'],
            $_POST['tax_mode'], $_POST['terms'], $so_id
        ]);
        $si_id = $pdo->lastInsertId();

        // Insert invoice items and update stock
        if (!empty($_POST['product_name'])) {
            foreach ($_POST['product_name'] as $i => $prod) {
                if (trim($prod) === '') continue;
                $qty = floatval($_POST['quantity'][$i]);
                $rate = floatval($_POST['rate'][$i]);
                $disc = floatval($_POST['discount'][$i]);
                $gst = floatval($_POST['gst'][$i]);
                $tax_mode = $_POST['tax_mode'];

                // Check stock
                $stockCheck = $pdo->prepare("SELECT stock_quantity FROM products WHERE product_name = ?");
                $stockCheck->execute([$prod]);
                $currentStock = $stockCheck->fetchColumn();
                if ($currentStock < $qty) throw new Exception("Not enough stock for product: $prod (Available: $currentStock)");

                // Line total calculation
                $line_total = $qty * $rate;
                $line_total -= $line_total * ($disc / 100);
                if ($tax_mode === "Exclusive") $line_total += $line_total * ($gst / 100);

                // Insert item
                $stmt_item = $pdo->prepare("INSERT INTO sales_invoice_items 
                    (si_id, category, product_name, quantity, rate, discount, gst, line_total, tax_mode) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_item->execute([
                    $si_id, $_POST['category'][$i], $prod, $qty, $rate, $disc, $gst, $line_total, $tax_mode
                ]);

                // Update stock
                $updateStock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_name = ?");
                $updateStock->execute([$qty, $prod]);
                
                // Add to grand total
                $grandTotal += $line_total;
            }
        }
        
        // Update sales_invoices with grand total
        $updateTotal = $pdo->prepare("UPDATE sales_invoices SET grand_total = ? WHERE si_id = ?");
        $updateTotal->execute([$grandTotal, $si_id]);


        $pdo->commit();
        $message = "Sales Saved Successfully!";
        $si_number = generateSINumber($pdo); // refresh SI number
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <h2>Create Sales Invoice</h2>

    <!-- Inline message div -->
    <?php if($message): ?>
        <div id="saveMessage" class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php else: ?>
        <div id="saveMessage" class="alert alert-success" style="display:none;"></div>
    <?php endif; ?>

    <form method="post" id="invoiceForm">
        <div class="row mb-3">
            <div class="col-md-3">
                <label>SI Number</label>
                <input type="text" name="si_number" class="form-control" value="<?= htmlspecialchars($si_number) ?>" readonly>
            </div>
            <div class="col-md-3">
                <label>Invoice Date</label>
                <input type="date" name="si_date" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Due Date</label>
                <input type="date" name="due_date" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Customer Bill No</label>
                <input type="text" name="customer_bill_no" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label>SO Number</label>
                <select name="so_id" class="form-select">
                    <option value="">-- Select SO Number --</option>
                    <?php foreach($soNumbers as $so): ?>
                        <option value="<?= $so['so_id'] ?>"><?= htmlspecialchars($so['so_number']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label>Customer</label>
                <select name="customer_id" class="form-select" required>
                    <option value="">-- Select Customer --</option>
                    <?php foreach($customers as $c): ?>
                        <option value="<?= $c['customer_id'] ?>"><?= htmlspecialchars($c['customer_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
<div id="deletedWarning" class="alert alert-warning" style="display:none; margin-bottom:10px;"></div>
        <h5 class="mt-4">Invoice Items</h5>
        <div class="mb-3">
            <label>Tax Mode:</label><br>
            <input type="radio" name="tax_mode" value="Inclusive" checked onchange="updateAllLineTotals()"> Inclusive
            <input type="radio" name="tax_mode" value="Exclusive" onchange="updateAllLineTotals()"> Exclusive
        </div>

        <table class="table table-bordered" id="itemsTable">
            <thead class="table-dark">
                <tr>
                    <th>Category</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Rate</th>
                    <th>Discount %</th>
                    <th>GST %</th>
                    <th>Line Total</th>
                    <th><button type="button" class="btn btn-outline-success btn-sm" onclick="addRow()">➕ Add</button></th>
                </tr>
            </thead>
            <tbody>
                <tr id="templateRow" style="display:none;">
                    <td>
                        <select name="category[]" class="form-select" onchange="updateProductOptions(this)">
                            <option value="">-- Select Category --</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <div>
                            <select name="product_name[]" class="form-select" onchange="fillProductDetails(this)">
                                <option value="">-- Select Product --</option>
                            </select>
                            <small class="text-muted stock-info"></small>
                        </div>
                    </td>
                    <td><input type="number" name="quantity[]" value="1" class="form-control" oninput="checkStock(this); updateLineTotal(this)"></td>
                    <td><input type="number" name="rate[]" class="form-control" readonly></td>
                    <td><input type="number" name="discount[]" class="form-control" readonly></td>
                    <td><input type="number" name="gst[]" class="form-control" readonly></td>
                    <td><input type="number" name="line_total[]" class="form-control" readonly></td>
                    <td><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)">x</button></td>
                </tr>
                <!-- First row -->
                <tr>
                    <td>
                        <select name="category[]" class="form-select" onchange="updateProductOptions(this)">
                            <option value="">-- Select Category --</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <div>
                            <select name="product_name[]" class="form-select" onchange="fillProductDetails(this)">
                                <option value="">-- Select Product --</option>
                            </select>
                            <small class="text-muted stock-info"></small>
                        </div>
                    </td>
                    <td><input type="number" name="quantity[]" value="1" class="form-control" oninput="checkStock(this); updateLineTotal(this)"></td>
                    <td><input type="number" name="rate[]" class="form-control" readonly></td>
                    <td><input type="number" name="discount[]" class="form-control" readonly></td>
                    <td><input type="number" name="gst[]" class="form-control" readonly></td>
                    <td><input type="number" name="line_total[]" class="form-control" readonly></td>
                    <td><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)">x</button></td>
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
            <button type="submit" class="btn btn-outline-success"><i class="fa fa-save"></i> Save</button>
            <a href="sales_invoice_list.php" class="btn btn-outline-secondary"><i class="fa fa-arrow-left"></i> Back</a>
        </div>
    </form>
</div>

<script>
let products = <?php echo json_encode($products); ?>;


function addRow(){
    let tbody = document.querySelector('#itemsTable tbody');
    let t = document.getElementById('templateRow');
    let n = t.cloneNode(true);
    n.id = '';
    n.style.display = '';
    n.querySelectorAll('input').forEach(i => i.value = 1);
    n.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
    tbody.appendChild(n);
}

function removeRow(btn){
    let row = btn.closest('tr');
    let table = document.querySelector('#itemsTable tbody');
    if(table.rows.length>1) row.remove();
    calculateGrandTotal();
}

function updateProductOptions(el){
    let row = el.closest('tr');
    let productSelect = row.querySelector('select[name="product_name[]"]');
    productSelect.innerHTML = '<option value="">-- Select Product --</option>';
    products.forEach(p => {
        if(p.category === el.value){
            let opt = document.createElement('option');
            opt.value = p.product_name;
            opt.text = p.product_name;
            productSelect.appendChild(opt);
        }
    });
}

function fillProductDetails(el){
    let row = el.closest('tr');
    let p = products.find(pr => pr.product_name === el.value);
    let s = row.querySelector('.stock-info');
    if(p){
        row.querySelector('input[name="rate[]"]').value = p.rate || 0;
        row.querySelector('input[name="discount[]"]').value = p.discount || 0;
        row.querySelector('input[name="gst[]"]').value = p.gst || 0;
        s.textContent = "Stock: " + (p.stock_quantity || 0);
        row.dataset.stock = p.stock_quantity || 0;
    } else { s.textContent = ""; row.dataset.stock = 0; }
    updateLineTotal(row.querySelector('input[name="quantity[]"]'));
}

function checkStock(el){
    let row = el.closest('tr');
    let stock = parseFloat(row.dataset.stock || 0);
    let qty = parseFloat(el.value) || 0;
    let msg = row.querySelector('.text-danger');
    if(qty > stock){
        if(!msg){
            msg = document.createElement('small');
            msg.className = 'text-danger d-block';
            msg.textContent = "⚠ Quantity exceeds stock!";
            el.insertAdjacentElement('afterend', msg);
        }
    } else if(msg) msg.remove();
}

function getTaxMode(){ 
    let m = document.querySelector('input[name="tax_mode"]:checked'); 
    return m?m.value:'Exclusive'; 
}

function updateLineTotal(el){
    let r = el.closest('tr');
    let q = parseFloat(r.querySelector('input[name="quantity[]"]').value) || 0;
    let rt = parseFloat(r.querySelector('input[name="rate[]"]').value) || 0;
    let d = parseFloat(r.querySelector('input[name="discount[]"]').value) || 0;
    let g = parseFloat(r.querySelector('input[name="gst[]"]').value) || 0;
    let t = q * rt;
    t -= t*(d/100);
    if(getTaxMode()==='Exclusive') t += t*(g/100);
    r.querySelector('input[name="line_total[]"]').value = t.toFixed(2);
    calculateGrandTotal();
}

function updateAllLineTotals(){ 
    document.querySelectorAll('input[name="quantity[]"]').forEach(i=>updateLineTotal(i)); 
}

function calculateGrandTotal(){
    let total=0;
    document.querySelectorAll('input[name="line_total[]"]').forEach(i=>total+=parseFloat(i.value)||0);
    document.getElementById('grandTotal').value=total.toFixed(2);
}

// AJAX form submit to update stock immediately
document.getElementById("invoiceForm").addEventListener("submit", function(e){
    e.preventDefault(); // prevent normal submit

    const form = this;
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        // Show success message inline instead of alert
    const msgBox = document.getElementById('saveMessage');
    msgBox.style.display = 'block';
    msgBox.textContent = 'Sales Saved Successfully!';

        // Update stock-info for each product row
        document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
            const productName = row.querySelector('select[name="product_name[]"]').value;
            const stockInfo = row.querySelector('.stock-info');
            const product = products.find(p => p.product_name === productName);

            if(product){
                const qty = parseFloat(row.querySelector('input[name="quantity[]"]').value) || 0;
                // Subtract sold quantity from stock
                product.stock_quantity = (parseFloat(product.stock_quantity) || 0) - qty;
                stockInfo.textContent = "Stock: " + product.stock_quantity;
                stockInfo.style.color = (product.stock_quantity <= 0) ? "red" : "#6c757d";
                row.querySelector('input[name="quantity[]"]').setAttribute('data-stock', product.stock_quantity);
            }
        });

        // Recalculate totals
        updateAllLineTotals();

        // Generate new SI number for next invoice
        fetch(window.location.href)
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newSINumber = doc.querySelector('input[name="si_number"]').value;
                document.querySelector('input[name="si_number"]').value = newSINumber;
            });

        form.reset(); // reset form for next invoice
    })
    .catch(err => console.error(err));
});
document.querySelector("select[name='so_id']").addEventListener("change", function(){
    const so_id = this.value;
    if(!so_id){
        // If no SO selected, refresh page or reset table
        window.location.href = window.location.pathname;
        return;
    }

    fetch("load_so.php?so_id=" + so_id)
        .then(res => res.json())
        .then(data => {
            if(!data.success) return;

            // Set customer
            document.querySelector("select[name='customer_id']").value = data.so.customer_id;

            // Clear existing table rows except template
            const tbody = document.querySelector("#itemsTable tbody");
            tbody.querySelectorAll("tr:not(#templateRow)").forEach(r => r.remove());

            const template = document.getElementById("templateRow");

            data.items.forEach(item => {
                const row = template.cloneNode(true);
                row.id = "";
                row.style.display = "";

                // Set category first
                const categorySelect = row.querySelector("select[name='category[]']");
                categorySelect.value = item.category;

                // Trigger updateProductOptions to populate products
                updateProductOptions(categorySelect);

                // Select the saved product
                const productSelect = row.querySelector("select[name='product_name[]']");
                productSelect.value = item.product_name;

                // Fill other details
                row.querySelector("input[name='quantity[]']").value = item.quantity;
                row.querySelector("input[name='rate[]']").value = item.rate;
                row.querySelector("input[name='discount[]']").value = item.discount;
                row.querySelector("input[name='gst[]']").value = item.gst;

                // Stock info
                const stockInfo = row.querySelector(".stock-info");
                const product = products.find(p => p.product_name === item.product_name);
                if(product){
                    stockInfo.textContent = "Stock: " + product.stock_quantity;
                    row.querySelector("input[name='quantity[]']").setAttribute('data-stock', product.stock_quantity);
                }

                tbody.appendChild(row);
            });

            // Recalculate totals
            updateAllLineTotals();
        });
});


</script>
</body>
</html>

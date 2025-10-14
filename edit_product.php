<?php
require_once 'config.php';
$title = "Edit Product";
include 'header.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id=?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found!");
}

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        UPDATE products SET 
        category=?, product_name=?, hsn_code=?, rate=?, gst=?, stock_quantity=?, mrp=?, discount=?, reorder_level=?
        WHERE product_id=?
    ");
    $stmt->execute([
        $_POST['category'],
        $_POST['product_name'],
        $_POST['hsn_code'],
        $_POST['rate'],
        $_POST['gst'],
        $_POST['stock_quantity'],
        $_POST['mrp'],
        $_POST['discount'],
        $_POST['reorder_level'],
        $product_id
    ]);

    $message = "Product updated successfully!";
}
?>

<style>
body {
    background: #f5f7fa;
}

/* Heading glow */
.glow-heading {
    font-size: 2.2rem;
    font-weight: bold;
    color: #140d77;
    animation: glow-title 2s ease-in-out infinite alternate;
}
@keyframes glow-title {
    from { text-shadow: 0 0 5px white; }
    to { text-shadow: 0 0 20px white; }
}

/* Page container */
.form-container {
    width: 100%;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding-top: 40px;
}

/* Form card */
.form-card {
    width: 70%;
    background: #fff;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(20, 13, 119, 0.3);
    overflow-y: auto;
}

/* Fixed buttons on right side above form */
.fixed-buttons {
    position: absolute;
    top: 140px; /* Adjust based on your header height */
    right: 40px; /* 👈 Positions buttons towards the right side */
    display: flex;
    flex-direction: row;
    gap: 15px;
    z-index: 9999;
}

.fixed-buttons .btn {
    border-radius: 5px;
    font-weight: bold;
    box-shadow: 0 0 10px rgba(20, 13, 119, 0.5);
}

/* Input focus glow */
.form-control:focus {
    border-color: #140d77;
    box-shadow: 0 0 8px rgba(20, 13, 119, 0.6);
    transition: all 0.3s ease-in-out;
}

/* Buttons glow */
.btn-glow {
    border-radius: 5px;
    font-weight: bold;
    transition: all 0.3s ease;
}
.btn-glow:hover {
    box-shadow: 0 0 15px rgba(20, 13, 119, 0.8);
    transform: translateY(-2px);
}

/* Label style */
.form-label {
    font-weight: 500;
}
</style>

<!-- Fixed Buttons -->
<div class="fixed-buttons">
    <button type="submit" form="editProductForm" class="btn btn-success btn-glow">Update Product</button>
    <a href="products.php" class="btn btn-secondary btn-glow">Back</a>
</div>

<div class="form-container">
    <div class="form-card">
        <h2 class="glow-heading mb-4 text-center">Edit Product</h2>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form id="editProductForm" method="post">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['category_name']) ?>" <?= $cat['category_name'] == $product['category'] ? "selected" : "" ?>>
                                <?= htmlspecialchars($cat['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="product_name" class="form-control" value="<?= htmlspecialchars($product['product_name']) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">HSN Code</label>
                    <input type="text" name="hsn_code" class="form-control" value="<?= htmlspecialchars($product['hsn_code']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Rate</label>
                    <input type="number" step="0.01" name="rate" class="form-control" value="<?= htmlspecialchars($product['rate']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">GST (%)</label>
                    <input type="number" step="0.01" name="gst" class="form-control" value="<?= htmlspecialchars($product['gst']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Stock Quantity</label>
                    <input type="number" step="0.01" name="stock_quantity" class="form-control" value="<?= htmlspecialchars($product['stock_quantity']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">MRP</label>
                    <input type="number" step="0.01" name="mrp" class="form-control" value="<?= htmlspecialchars($product['mrp']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Discount (%)</label>
                    <input type="number" step="0.01" name="discount" class="form-control" value="<?= htmlspecialchars($product['discount']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Reorder Level</label>
                    <input type="number" step="0.01" name="reorder_level" class="form-control" value="<?= htmlspecialchars($product['reorder_level']) ?>">
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
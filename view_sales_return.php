<?php
require_once 'config.php';
include 'header.php';

if (empty($_GET['id'])) {
    die("❌ Invalid Request");
}

$id = (int)$_GET['id'];

// Fetch return header
$sql = "
    SELECT 
        sr.return_id,
        sr.return_no,
        sr.invoice_no,
        sr.invoice_date AS bill_date,
        c.customer_name,
        sr.state,
        sr.tax_mode,
        sr.description
    FROM sales_returns sr
    LEFT JOIN customers c ON sr.customer_id = c.customer_id
    WHERE sr.return_id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$return = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$return) {
    die("❌ Sales return not found");
}

// Fetch return items
$sql = "
    SELECT 
        p.product_name AS item_name,
        sri.quantity,
        sri.rate,
        sri.discount,
        sri.gst,
        sri.line_total
    FROM sales_return_items sri
    LEFT JOIN products p ON sri.product_id = p.product_id
    WHERE sri.sr_id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2>Sales Return Details</h2>

    <div class="mb-3">
        <a href="sales_return_list.php" class="btn btn-outline-secondary">⬅ Back</a>
    </div>

    <table class="table table-bordered">
        <tr><th>Return No</th><td><?= htmlspecialchars($return['return_no']) ?></td></tr>
        <tr><th>Bill No</th><td><?= htmlspecialchars($return['invoice_no']) ?></td></tr>
        <tr><th>Bill Date</th><td><?= date('d-m-Y', strtotime($return['bill_date'])) ?></td></tr>
        <tr><th>Customer</th><td><?= htmlspecialchars($return['customer_name']) ?></td></tr>
        <tr><th>State</th><td><?= htmlspecialchars($return['state']) ?></td></tr>
        <tr><th>Tax Mode</th><td><?= htmlspecialchars($return['tax_mode']) ?></td></tr>
        <tr><th>Description</th><td><?= htmlspecialchars($return['description']) ?></td></tr>
    </table>

    <h4>Returned Items</h4>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Rate</th>
                <th>Discount %</th>
                <th>GST %</th>
                <th>Line Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($items): ?>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?= htmlspecialchars($it['item_name']) ?></td>
                        <td><?= $it['quantity'] ?></td>
                        <td><?= number_format($it['rate'], 2) ?></td>
                        <td><?= $it['discount'] ?></td>
                        <td><?= $it['gst'] ?></td>
                        <td><?= number_format($it['line_total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">No items found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

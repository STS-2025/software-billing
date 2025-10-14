<?php
ob_start(); // prevent blank page due to header redirects
session_start();
require_once 'config.php';
$title = "Edit Customer";
include 'header.php';

$customer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($customer_id <= 0) {
    die("Invalid customer ID");
}

$message = "";
$cleared = false; // flag to clear textbox after update

// ✅ Handle Update Button
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['customer_name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    if ($name === '' || $phone === '') {
        $message = "⚠️ Name and phone cannot be empty!";
    } else {
        $stmt = $pdo->prepare("
            UPDATE customers SET 
            customer_name=?, address=?, phone=?, email=? 
            WHERE customer_id=?
        ");
        $stmt->execute([$name, $address, $phone, $email, $customer_id]);

        // ✅ Store message + flag for clearing input fields
        $_SESSION['update_msg'] = "✅ Customer updated successfully!";
        $_SESSION['clear_input'] = true;

        // ✅ Refresh the same page (avoids resubmission)
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $customer_id);
        exit;
    }
}

// ✅ Fetch latest customer data
$stmt = $pdo->prepare("SELECT * FROM customers WHERE customer_id=?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$customer) {
    die("Customer not found!");
}

// ✅ Handle message + input clear flag
if (isset($_SESSION['update_msg'])) {
    $message = $_SESSION['update_msg'];
    unset($_SESSION['update_msg']);
}
if (isset($_SESSION['clear_input'])) {
    $cleared = true;
    unset($_SESSION['clear_input']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa; }
        .product-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(20, 13, 119, 0.25);
            padding: 40px;
            max-width: 600px;
            margin: 60px auto;
            transition: all 0.3s ease;
        }
        .product-card:hover { box-shadow: 0 12px 35px rgba(20, 13, 119, 0.35); transform: translateY(-4px); }
        .glow-heading {
            font-size: 2rem; font-weight: bold; text-align: center; color: #140d77;
            animation: glow-title 2s ease-in-out infinite alternate; margin-bottom: 25px;
        }
        @keyframes glow-title { from { text-shadow: 0 0 5px white; } to { text-shadow: 0 0 20px white; } }
        .form-control:focus { border-color: #140d77; box-shadow: 0 0 8px rgba(20, 13, 119, 0.6); }
        .btn-glow { border-radius: 5px; font-weight: bold; transition: all 0.3s ease; }
        .btn-glow:hover { box-shadow: 0 0 15px rgba(20, 13, 119, 0.8); transform: translateY(-2px); }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    </style>
</head>

<body>
    <div class="product-card">
        <h3 class="glow-heading">Edit Customer</h3>

        <!-- ✅ Success Message -->
        <?php if ($message): ?>
            <div class="alert alert-success text-center" id="msgBox" style="animation: fadeIn 1s;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- ✅ Customer Edit Form -->
        <form method="post">
            <div class="mb-3">
                <label class="form-label fw-bold">Customer Name</label>
                <input type="text" name="customer_name" class="form-control"
                       value="<?= $cleared ? '' : htmlspecialchars($customer['customer_name']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Address</label>
                <textarea name="address" class="form-control"><?= $cleared ? '' : htmlspecialchars($customer['address']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Phone</label>
                <input type="text" name="phone" class="form-control" maxlength="10" pattern="[0-9]{10}"
                       value="<?= $cleared ? '' : htmlspecialchars($customer['phone']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Email</label>
                <input type="email" name="email" class="form-control"
                       value="<?= $cleared ? '' : htmlspecialchars($customer['email']) ?>">
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-success btn-glow px-4">Update</button>
                <a href="customer.php" class="btn btn-secondary btn-glow px-4">Back</a>
            </div>
        </form>
    </div>

    <script>
        // ✅ Hide success message after 3 seconds
        setTimeout(() => {
            const msg = document.getElementById('msgBox');
            if (msg) msg.style.display = 'none';
        }, 3000);
    </script>
</body>
</html>

<?php 
include 'footer.php'; 
ob_end_flush();
?>

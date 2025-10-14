<?php
require_once 'config.php';
$title = "Customers";
include 'header.php';

$message = "";

// Insert customer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $message = "❌ Phone number must be exactly 10 digits.";
    } else {
        try {
            // Check if name already exists
            $check = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE LOWER(customer_name) = LOWER(?)");
            $check->execute([$customer_name]);
            if ($check->fetchColumn() > 0) {
                $message = "⚠️ This customer name already exists!";
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO customers (customer_name, address, phone, email)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$customer_name, $address, $phone, $email]);
                $message = "✅ Customer Added Successfully!";
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

$customers = $pdo->query("SELECT * FROM customers ORDER BY customer_id ASC")
                 ->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
body {
    background: #f5f7fa;
}

/* Glow heading */
.glow-heading {
    font-size: 1.8rem;
    font-weight: bold;
    color: #140d77;
    animation: glow-title 2s ease-in-out infinite alternate;
}
@keyframes glow-title {
    from { text-shadow: 0 0 5px white; }
    to { text-shadow: 0 0 20px white; }
}

/* Layout */
.split-container {
    display: flex;
    gap: 20px;
    align-items: stretch;
    flex-wrap: nowrap;
}

.table-card {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(20, 13, 119, 0.2);
    flex: 2;
    min-width: 600px;
    overflow: hidden;
}

.form-card {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(20, 13, 119, 0.3);
    flex: 0.6;
    min-width: 250px;
    max-width: 320px;
}

.table {
    width: 100%;
    border-collapse: collapse;
}
.table thead {
    background: #140d77;
    color: #fff;
}
.table th, .table td {
    padding: 10px;
    text-align: left;
    vertical-align: middle;
}
.table-hover tbody tr:hover {
    background-color: rgba(20, 13, 119, 0.1);
}

.form-control:focus {
    border-color: #140d77;
    box-shadow: 0 0 8px rgba(20, 13, 119, 0.6);
}

.btn-glow {
    border-radius: 5px;
    font-weight: bold;
    transition: all 0.3s ease;
}
.btn-glow:hover {
    box-shadow: 0 0 15px rgba(20, 13, 119, 0.8);
    transform: translateY(-2px);
}

.alert {
    font-weight: bold;
}

.product-heading {
    font-size: 2.5rem;
    font-weight: 700;
    text-transform: uppercase;
    background: linear-gradient(90deg, #007bff, #00c3ff, #007bff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-size: 200%;
    letter-spacing: 1.5px;
    animation: textShine 4s ease-in-out infinite, fadeSlideIn 1s ease forwards;
    display: inline-block;
    margin: 0 auto;
}
@keyframes textShine {
    0% { background-position: 200% center; }
    100% { background-position: -200% center; }
}
@keyframes fadeSlideIn {
    0% { opacity: 0; transform: translateY(25px); }
    100% { opacity: 1; transform: translateY(0); }
}

@media (max-width: 992px) {
    .split-container {
        flex-wrap: wrap;
    }
    .table-card, .form-card {
        min-width: 100%;
        flex: 1 1 100%;
    }
}

/* Duplicate name message */
#name_error {
    color: red;
    font-size: 14px;
    margin-top: 5px;
    display: block;
}
</style>


<div class="text-center my-4">
    <h2 class="product-heading fw-bold">
        Customer Management
    </h2>
</div>


<?php if ($message): ?>
    <div class="alert alert-info"><?= $message ?></div>
<?php endif; ?>

<div class="split-container">
    <!-- Existing Customers Table -->
    <div class="table-card">
        <h4 class="glow-heading mb-3">Existing Customers</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($customers)): ?>
                    <?php foreach ($customers as $c): ?>
                    <tr>
                        <td><?= $c['customer_id'] ?></td>
                        <td><?= htmlspecialchars($c['customer_name']) ?></td>
                        <td><?= htmlspecialchars($c['address']) ?></td>
                        <td><?= htmlspecialchars($c['phone']) ?></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <td>
                            <a href="edit_customer.php?id=<?= $c['customer_id'] ?>" class="btn btn-outline-primary btn-sm btn-glow">
                                <i class="fa fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted">No customers found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Customer Entry Form -->
    <div class="form-card">
        <h4 class="glow-heading mb-3">Add Customer</h4>
        <form method="post" onsubmit="return validatePhone()" id="customerForm">
            <div class="mb-3">
                <label class="form-label">Customer Name</label>
                <input type="text" name="customer_name" id="customer_name" class="form-control" required autocomplete="off">
                <span id="name_error"></span>
            </div>
            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" id="phone" class="form-control"
                       maxlength="10" pattern="[0-9]{10}" required
                       placeholder="Enter 10-digit phone no">
            </div>
            <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control">
            </div>
            <button type="submit" class="btn btn-success btn-glow" id="saveBtn">Save Customer</button>
            <a href="dashboard.php" class="btn btn-secondary btn-glow">Back</a>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// Validate phone number
function validatePhone() {
    const phone = document.getElementById("phone").value;
    if (!/^[0-9]{10}$/.test(phone)) {
        alert("Phone number must be exactly 10 digits.");
        return false;
    }
    return true;
}

// Duplicate name check
$(document).ready(function(){
    $('#customer_name').on('keyup', function(){
        var name = $(this).val().trim();

        if(name !== ''){
            $.ajax({
                url: 'check_customer.php',
                type: 'POST',
                data: { customer_name: name },
                success: function(response){
                    if(response === 'exists'){
                        $('#name_error').text('⚠️ This customer name already exists');
                        $('#saveBtn').prop('disabled', true);
                    } else {
                        $('#name_error').text('');
                        $('#saveBtn').prop('disabled', false);
                    }
                }
            });
        } else {
            $('#name_error').text('');
            $('#saveBtn').prop('disabled', false);
        }
    });
});
</script>

<?php include 'footer.php'; ?>

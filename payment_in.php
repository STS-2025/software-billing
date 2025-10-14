<?php
require_once 'config.php';
include 'header.php';

// --- Auto-generate Payment-In Number (robust: try pi_number, fallback to pm_number) ---
$last_num = false;
$prefix = 'PI';

try {
    // First try the expected column name
    $last_num = $pdo->query("SELECT pi_number FROM payments_in ORDER BY id DESC LIMIT 1")->fetchColumn();
    $prefix = 'PI';
} catch (PDOException $e) {
    // If that column doesn't exist, try the older/alternate column
    try {
        $last_num = $pdo->query("SELECT pm_number FROM payments_in ORDER BY id DESC LIMIT 1")->fetchColumn();
        $prefix = 'PM';
    } catch (PDOException $e2) {
        // both attempts failed (no such columns) — keep $last_num = false
        $last_num = false;
    }
}

if ($last_num) {
    // take the trailing number after last "/" and increment
    $num = (int) substr($last_num, strrpos($last_num, "/") + 1);
    $pi_number = $prefix . "/25-26/" . str_pad($num + 1, 4, "0", STR_PAD_LEFT);
} else {
    $pi_number = $prefix . "/25-26/0001";
}

// Get customers from customers table
$customers = $pdo->query("SELECT customer_id AS id, customer_name AS name FROM customers ORDER BY customer_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Get customers from ledgers table
$ledgerCustomers = $pdo->query("SELECT id, ledger_name AS name FROM ledgers WHERE type='Customer' ORDER BY ledger_name ASC")->fetchAll(PDO::FETCH_ASSOC);

$parties = array_merge($customers, $ledgerCustomers);
?>
<div class="container mt-4">
  <div class="card shadow">
   <div class="card-header" style="background-color: #140d77; color: white;">

      <h5><i class="fa fa-hand-holding-usd"></i> Payment In</h5>
    </div>
    <div class="card-body">
      <form action="save_payment_in.php" method="POST">
        <div class="row mb-3">
          <div class="col-md-4">
            <label>PI Number</label>
            <input type="text" name="pi_number" class="form-control" value="<?= htmlspecialchars($pi_number) ?>" readonly>
          </div>
          <div class="col-md-4">
            <label>Date</label>
            <input type="date" name="date" class="form-control" required value="<?= date('Y-m-d') ?>">
          </div>
          <div class="col-md-4">
            <label>Party (Customer)</label>
           <select name="party_id" id="partySelect" class="form-select" required onchange="fetchCustomerBalance(this.value)">
    <option value="">-- Select Customer --</option>
    <?php foreach ($parties as $party): ?>
        <option value="<?= htmlspecialchars($party['id']) ?>"><?= htmlspecialchars($party['name']) ?></option>
    <?php endforeach; ?>
</select>
          </div>
        </div>

        <!-- TO RECEIVE DISPLAY -->
        <div class="row mb-3" id="toReceiveContainer" style="display:none;">
          <div class="col-md-4">
            <label><strong>To Receive:</strong></label>
            <span id="toReceiveAmount" style="font-weight:bold; color:green;">₹ 0.00</span>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-4">
            <label>Amount</label>
            <input type="number" step="0.01" name="amount" class="form-control" required id="paymentAmount" oninput="checkAmount()">
            <span id="amountError" style="color:red; display:none;">Payment exceeds balance!</span>
          </div>
          <div class="col-md-4">
            <label>Payment Mode</label>
            <select name="payment_mode" class="form-select" required>
              <option value="">-- Select Mode --</option>
              <option value="Cash A/c">Cash A/c</option>
              <option value="Paytm">Paytm</option>
              <option value="Google Pay">Google Pay</option>
              <option value="UPI">UPI</option>
            </select>
          </div>
          <div class="col-md-4">
            <label>Notes</label>
            <input type="text" name="notes" class="form-control" placeholder="Optional notes">
          </div>
        </div>

        <div class="text-end">
          <button type="submit" class="btn btn-outline-success">Save</button>
          <a href="payment_in_list.php" class="btn btn-outline-secondary">Back</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.getElementById("partySelect").addEventListener("change", function(){
    var selectedOption = this.options[this.selectedIndex];
    document.getElementById("partyName").value = selectedOption.text;
});

function fetchCustomerBalance(partyId) {
    const toReceiveContainer = document.getElementById("toReceiveContainer");
    const toReceiveAmount = document.getElementById("toReceiveAmount");

    if (!partyId) {
        toReceiveContainer.style.display = "none";
        toReceiveAmount.textContent = "₹ 0.00";
        return;
    }

    fetch("get_customer_balance.php?party_id=" + partyId)
        .then(response => response.json())
        .then(data => {
            toReceiveContainer.style.display = "block";
            toReceiveAmount.textContent = "₹ " + data.balance;
        })
        .catch(err => {
            console.error(err);
            toReceiveContainer.style.display = "block";
            toReceiveAmount.textContent = "Error";
        });
}

function validateForm() {
    const balance = parseFloat(document.getElementById("toReceiveAmount").textContent.replace(/[₹,]/g, '').trim()) || 0;
    const amount = parseFloat(document.getElementById("paymentAmount").value) || 0;

    if (amount > balance) {
        alert("Payment amount cannot exceed To Receive balance!");
        return false; // stop form submission
    }
    return true;
}

function checkAmount() {
    const balanceText = document.getElementById("toReceiveAmount").textContent.replace(/[₹,]/g, '').trim();
    const balance = parseFloat(balanceText) || 0;
    const amountField = document.getElementById("paymentAmount");
    const errorSpan = document.getElementById("amountError");
    const amount = parseFloat(amountField.value) || 0;

    // If amount exceeds balance, show error and clear invalid input
    if (amount > balance) {
        errorSpan.style.display = "block";
        amountField.value = "";
        amountField.focus();
    } else {
        errorSpan.style.display = "none";
    }
}
</script>

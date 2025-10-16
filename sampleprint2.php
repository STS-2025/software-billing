<?php

require_once 'config.php'; // $pdo connection
$terms = "Net 30 Days";// 1. Get the Invoice ID



/*$host = "localhost";
$user = "root";       // default in XAMPP
$pass = "";           // default is empty
$db   = "web";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}*/

$invoice_id = $_GET['id'] ?? die("Error: Invoice ID not specified.");
// --- FETCH COMPANY DETAILS ---
$stmt_company = $pdo->prepare("SELECT company_name, gstin, phone_number, address_line_1 ,bank_name,account_number,ifsc_code FROM company_settings WHERE id = 1");
$stmt_company->execute();


$company_data = $stmt_company->fetch();

if ($company_data) {
    // Data successfully fetched into the $company_data array

    // Assign data to individual variables for cleaner use in HTML:
    $your_company_name = $company_data['company_name'];
    $your_gstin = $company_data['gstin'];
    $your_phone = $company_data['phone_number'];
    $your_address = $company_data['address_line_1'];
    $bank_name = $company_data['bank_name'];
    $account_number = $company_data['account_number'];
    $ifsc_code = $company_data['ifsc_code'];

} else {
    // Handle the case where the data is not found
    $your_company_name = "Company Not Found";
    $your_gstin = "";
    $your_phone = "";
    $your_address = "";
    $bank_name = "";
    $account_number = "";
    $ifsc_code = "";
}

//$stmt_company->close();


// --- Query 1: Fetch Main Invoice & Customer Data ---
$stmt_main = $pdo->prepare("
    SELECT 
        i.si_date,i.si_number as si_number,i.terms as terms,
        c.customer_name AS customer_name, c.address, c.gstin AS customer_gstin,c.ship_address
    FROM sales_invoices i
    JOIN customers c ON i.customer_id = c.customer_id
    WHERE i.si_id = ?
");   //i.sub_total, i.tax_amount, i.grand_total, i.payment_terms, 

$stmt_main->execute([$invoice_id]); 

// Fetch the single result row
$invoice_header = $stmt_main->fetch(); 

if (!$invoice_header) {
    die("Invoice not found.");
}


// --- Query 2: Fetch ALL Line Items ---
$stmt_items = $pdo->prepare("
   SELECT 
    si.product_name, si.quantity,si.rate, si.discount, si.gst, si.line_total,si.tax_mode, p.hsn_code 
FROM sales_invoice_items si JOIN products p on si.product_name=p.product_name
WHERE si.si_id = ?
");
// Pass the parameter as a simple indexed array.
$stmt_items->execute([$invoice_id]);

// 3. Fetch all result rows into an associative array
// PDO::FETCH_ASSOC is the default, matching MYSQLI_ASSOC behavior.
$items_array = $stmt_items->fetchAll();// Fetch all items into an array
//calculations
$sub_total = 0;
$tax_total = 0;

foreach ($items_array as $item) {
    $line_total = ($item['quantity'] * $item['rate']);
    $line_total -= $line_total * ($item['discount'] / 100);

    $gst_amount = $line_total * $item['gst'] / 100;
    $tax_total += $gst_amount;

    if ($item['tax_mode'] === 'Exclusive') {
        $line_total += $gst_amount;
    }

    $sub_total += $line_total;
}
$shipping = 50.00;
$grand_total = $sub_total+$shipping;
// --- Final Calculations ---
require 'convertor.php';
$amount_in_words = numberToWords($grand_total);

 

?>
<!DOCTYPE html>
<html>
<head>
    <title>Invoice #<?php echo $invoice_header['si_number']; ?></title>
    <!-- <link rel="stylesheet" href="screen.css" media="screen"> -->
    <?php
   
    $css_relative_path = '/print.css'; 
   
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['PHP_SELF']) . '/' . $css_relative_path;
    
    // Clean up the path (removes /../, cleans multiple slashes)
    $clean_path = str_replace(['//', ':/'], ['/', '://'], $path);
    
    $full_css_url = $protocol . "://" . $host . $clean_path;
    
    // Output the link tag using the full, absolute URL
    echo '<link rel="stylesheet" href="' . $full_css_url . '">';
    ?>
  
    
</head>
<body>

<div class="invoice-wrapper">
   <header class="header">
    <div class="logo-box">
        <?php if (!empty($your_logo_path)): ?>
            <img src="<?php echo $your_logo_path; ?>" alt="<?php echo $your_company_name; ?> Logo">
        <?php else: ?>
            <h2><?php echo $your_company_name; ?></h2>
        <?php endif; ?>
    </div>
    <address class="company-info">
        <p><?php echo $your_address; ?></p>
        <p>GSTIN:<strong> <?php echo $your_gstin; ?></strong><p>PHONE NUMBER : <strong><?php echo $your_phone; ?></strong></p>
    </address>
</header>
  

    <h1 class="invoice-title">TAX INVOICE</h1>

    <section class="details-section">
        <div class="bill-to">
            <h3>Bill To:</h3>
            <p><strong><?php echo $invoice_header['customer_name']; ?></strong></p>
            <p><?php echo $invoice_header['address']; ?></p>
            <p>Customer GSTIN: <?php echo $invoice_header['customer_gstin']; ?></p>
        </div>
        <div class="address-box ship-to">
        <h3>Ship To:</h3>
        
        <?php if (!empty($ship_to_name)): ?>
            <p><strong> <?php echo $invoice_header['ship_address']; ?></strong></p>                 
            <p>address</p>                                       <?php// echo $shipping_address_full; ?>
            <?php else: ?>
            <p><?php echo $invoice_header['address']; ?></p>
        <?php endif; ?>
    </div>
        <div class="invoice-info">
            <p><strong>Invoice No:</strong> <?php echo $invoice_header['si_number']; ?></p>
            <p><strong>Date:</strong> <?php echo $invoice_header['si_date']; ?></p>
            <p><strong>Payment Terms:</strong> <?php echo $terms; ?></p>
        </div>
    </section>

    <section class="item-section">
        <table class="item-table">
            <thead>
                <tr>
                    <th class="col-sno">S.No.</th>
                    <th class="col-item">Description</th>
                    <th class="col-hsn">HSN/SAC</th>
                    <th class="col-qty">Qty</th>
                    <th class="col-unit">Unit</th> 
                    <th class="col-price">Rate</th>
                    <th class="col-amount">Discount</th>
                    <th class="col-gst-amount">GST Amount</th>
                    <th class="col-linetotal">LineTotal</th>
                </tr>
            </thead>
           <tbody>
            
            <?php 
            $sno = 1;
            foreach ($items_array as $item): 
            ?>
            <tr class="invoice-item-row">
                <td><?php echo $sno++; ?></td>
                <td><?php echo $item['product_name']; ?></td>
                <td><?php echo $item['hsn_code'] ?? 'N/A'; ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo $item['unit'] ?? 'No.'; ?></td>
                <td class="text-right"><?php echo number_format($item['rate'], 2); ?></td>
                <td class="text-right"><?php echo number_format($item['discount'], 2); ?></td>
                <td class="text-right"><?php echo $item['gst']; ?></td>
                <td class="text-right"><?php echo number_format($item['line_total'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
                
                <tr><td colspan="7" class="empty-row"></td></tr>
            </tbody>
        </table>
    </section>

    <section class="summary-section">
        <div class="summary-totals">
            <table class="total-table">
                <tr><td>Sub Total:</td><td class="text-right"><?php echo number_format($sub_total, 2); ?></td></tr>
                <tr><td>Tax (<?php echo  $item['gst'] * 100; ?>%):</td><td class="text-right"><?php echo number_format($gst_amount, 2); ?></td></tr>
                <tr><td>Shipping:</td><td class="text-right"><?php echo number_format($shipping, 2); ?></td></tr>
                <tr class="grand-total-row"><td><strong>GRAND TOTAL:</strong></td><td class="text-right"><strong><?php echo number_format($grand_total, 2); ?></Strong></td></tr>
            </table>
        </div>
        
        <p class="amount-in-words"><strong>Amount Chargeable (in words): </strong><?php echo $amount_in_words; ?>**</p>
    </section>

    <footer>
        <div class="terms-bank">
            <h3>Terms & Conditions:</h3>
            <p><?php echo $invoice_header['terms']; ?> </p>
           
           <div class="bankdetails"> <p><strong>Bank Transfer Details:</strong><br>
            <strong>Bank Name:<strong> <?php echo $bank_name; ?><br>
            <strong>A/C Number:<strong> <?php echo $account_number; ?><br>
            <strong>IFSC Code:<strong> <?php echo $ifsc_code; ?>
        </p></div>
        </div>

        <div class="signature">
            <p>For <strong><?php echo $your_company_name; ?><strong></p>
            <br><br>
            <p>(Authorized Signatory)</p>
        </div>
        
        <p class="powered-by no-print">Powered by Your Awesome Billing Software</p>
    </footer>
</div>

<!-- <button onclick="window.print()" class="print-button no-print">Print Invoice</button> 
<script>
        // Auto-print script
        window.onload = function() {
            window.print();
        }
    </script>-->

</body>

</html>
<?php
// Include the database connection file (using $pdo object)
require_once 'config.php';

require_once 'header.php'; // Include the HTML head and navigation

// 1. Initialize Date Variables and Check for Filter Submission
$start_date = date('Y-m-d', strtotime('-30 days'));
$end_date = date('Y-m-d');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['filter'])) {
    // Sanitize and validate dates from user input (filter_var is good practice)
    $start_date = filter_var($_POST['start_date'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $end_date = filter_var($_POST['end_date'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
}
// Function to calculate the Opening Stock (Closing Stock of the day BEFORE the start date)
function get_opening_stock($pdo, $product_name, $start_date) {
    // 1. Get the fixed initial stock from the master products table
    $sql_initial = "SELECT stock_quantity FROM products WHERE product_name = ?";
    $stmt_initial = $pdo->prepare($sql_initial);
    $stmt_initial->execute([$product_name]);
    $initial_master_stock = $stmt_initial->fetchColumn() ?: 0.00; 
    
    // We will use the original $start_date directly in the query, 
    // but change the SQL operator from '<=' to '<'
    $report_start_date = $start_date; // Use this variable for binding

    // The complex SQL to calculate Historical Movement 
    // (transactions BEFORE the report date starts)
    $sql_opening = "
        SELECT
            (
                IFNULL(pur.total_purchased, 0) 
                - IFNULL(prr.total_pr, 0) 
                - IFNULL(sal.total_sold, 0) 
                + IFNULL(srr.total_sr, 0)
            ) AS historical_movement
        FROM
            (SELECT DISTINCT product_name FROM purchase_invoice_items UNION SELECT DISTINCT product_name FROM sales_invoice_items) pr

        LEFT JOIN
            (SELECT pii.product_name, SUM(pii.quantity) AS total_purchased 
             FROM purchase_invoice_items pii
             JOIN purchase_invoices pi ON pii.pi_id = pi.pi_id  
             WHERE pi.pi_date < ? AND pii.product_name = ?  /* CHANGED: <= to < */
             GROUP BY pii.product_name) pur ON pr.product_name = pur.product_name

        LEFT JOIN
            (SELECT p.product_name, SUM(pri.quantity) AS total_pr
             FROM purchase_return_items pri
             JOIN purchase_returns prt ON pri.pr_id = prt.return_id 
             JOIN products p ON pri.product_id = p.product_id
             WHERE prt.bill_date < ? AND p.product_name = ?  /* CHANGED: <= to < */
             GROUP BY p.product_name) prr ON pr.product_name = prr.product_name

        LEFT JOIN
            (SELECT sii.product_name, SUM(sii.quantity) AS total_sold 
             FROM sales_invoice_items sii
             JOIN sales_invoices si ON sii.si_id = si.si_id  
             WHERE si.si_date < ? AND sii.product_name = ?  /* CHANGED: <= to < */
             GROUP BY sii.product_name) sal ON pr.product_name = sal.product_name

        LEFT JOIN
            (SELECT p.product_name, SUM(sri.quantity) AS total_sr
             FROM sales_return_items sri
             JOIN sales_returns srt ON sri.sr_id = srt.return_id 
             JOIN products p ON sri.product_id = p.product_id
             WHERE srt.return_date < ? AND p.product_name = ?  /* CHANGED: <= to < */
             GROUP BY p.product_name) srr ON pr.product_name = srr.product_name
        
        WHERE pr.product_name = ?
    ";

    try {
        $stmt_open = $pdo->prepare($sql_opening);
        
        // Bind 4 dates (now all are $start_date) and 5 product names
        $bind_array_open = [
            $report_start_date, $product_name, // Purchase
            $report_start_date, $product_name, // P. Return
            $report_start_date, $product_name, // Sales
            $report_start_date, $product_name, // S. Return
            $product_name // Main WHERE clause
        ];
        
        $stmt_open->execute($bind_array_open);
        $result = $stmt_open->fetch(PDO::FETCH_ASSOC);

        // Extract the historical movement.
        $historical_movement = $result ? $result['historical_movement'] : 0.00;

        // SUM the two parts: Initial Master Stock (100) + Historical Movement (transactions before today)
        $final_opening_stock = $initial_master_stock + $historical_movement;
        
        return $final_opening_stock;

    } catch (PDOException $e) {
        error_log("Opening Stock Query Error: " . $e->getMessage());
        return 0.00; // Return 0 on error
    }
}
// 2. The Complex SQL Query with Joins and Date Filters
// We use named placeholders (:start_date, :end_date) for better readability with PDO.
$sql = "
     SELECT
        pr.product_name,
        IFNULL(pur.total_purchased, 0) AS total_purchased,
        IFNULL(prr.total_pr, 0) AS total_purchase_return,
        IFNULL(sal.total_sold, 0) AS total_sold,
        IFNULL(srr.total_sr, 0) AS total_sales_return,
        (IFNULL(pur.total_purchased, 0) - IFNULL(prr.total_pr, 0) - IFNULL(sal.total_sold, 0) + IFNULL(srr.total_sr, 0)) AS net_movement
    FROM
        ( -- Get a unique list of all products involved
          SELECT DISTINCT product_name FROM purchase_invoice_items
          UNION
          SELECT DISTINCT product_name FROM sales_invoice_items
        ) pr

    LEFT JOIN
        ( -- Total Purchases (INWARD)
          SELECT pii.product_name, SUM(pii.quantity) AS total_purchased 
          FROM purchase_invoice_items pii
          JOIN purchase_invoices pi ON pii.pi_id = pi.pi_id  
          WHERE pi.pi_date BETWEEN ? AND ?
          GROUP BY pii.product_name
        ) pur ON pr.product_name = pur.product_name

    LEFT JOIN
        ( -- Total Purchase Returns (OUTWARD)
          SELECT p.product_name, SUM(pri.quantity) AS total_pr
          FROM purchase_return_items pri
          JOIN purchase_returns prt ON pri.pr_id = prt.return_id 
          JOIN products p ON pri.product_id = p.product_id
          WHERE prt.bill_date BETWEEN ? AND ?
          GROUP BY p.product_name
        ) prr ON pr.product_name = prr.product_name

    LEFT JOIN
        ( -- Total Sales (OUTWARD)
          SELECT sii.product_name, SUM(sii.quantity) AS total_sold 
          FROM sales_invoice_items sii
          JOIN sales_invoices si ON sii.si_id = si.si_id  
          WHERE si.si_date BETWEEN  ? AND ? 
          GROUP BY sii.product_name
        ) sal ON pr.product_name = sal.product_name

    LEFT JOIN
        ( -- Total Sales Returns (INWARD)
          SELECT p.product_name, SUM(sri.quantity) AS total_sr
          FROM sales_return_items sri
          JOIN sales_returns srt ON sri.sr_id = srt.return_id 
          JOIN products p ON sri.product_id = p.product_id
          WHERE srt.return_date >= ? AND srt.return_date < DATE_ADD(?, INTERVAL 1 DAY)
          GROUP BY p.product_name
        ) srr ON pr.product_name = srr.product_name

    ORDER BY pr.product_name
";

// 3. Execute PDO Prepared Statement
$stmt = $pdo->prepare($sql);

// The query now has 8 positional placeholders ('?'). 
// This array matches the 8 placeholders:
$bind_array = [
    $start_date, $end_date, // 1 & 2: for Purchases (The one we just fixed!)
    $start_date, $end_date, // 3 & 4: for Purchase Returns
    $start_date, $end_date, // 5 & 6: for Sales
    $start_date, $end_date  // 7 & 8: for Sales Returns
];

try {
    // This will now match: 8 placeholders = 8 parameters
    $stmt->execute($bind_array); 
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // If you still get an error, it will likely be a new, more specific SQL error.
    die("Query Error: " . $e->getMessage());
}
?>

<h1 class="mb-4">Datewise Stock Movement Summary</h1>

<form method="POST" class="row g-3 align-items-end mb-4 bg-light p-3 rounded shadow-sm">
    <div class="col-md-4">
        <label for="start_date" class="form-label">Start Date:</label>
        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" required>
    </div>
    <div class="col-md-4">
        <label for="end_date" class="form-label">End Date:</label>
        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" required>
    </div>
    <div class="col-md-4">
        <button type="submit" name="filter" class="btn btn-primary w-100">Apply Filter</button>
    </div>
</form>

<?php
if (count($results) > 0) {
    echo '<div class="alert alert-info">Showing <strong>Net Movement</strong> between <strong>' . date('d-M-Y', strtotime($start_date)) . '</strong> and <strong>' . date('d-M-Y', strtotime($end_date)) . '</strong>.</div>';
    
    // Start of the Bootstrap Table
    echo '<div class="table-responsive">';
    echo '<table class="table table-striped table-hover table-bordered">';
    echo '<thead class="table-dark">';
    echo '<tr>';
    echo '<th style="background-color: #140d77;">Product Name</th>';
    echo '<th class="text-end text-white"style="background-color: #140d77;">Purchased (In)</th>';
    echo '<th class="text-end text-white"style="background-color: #140d77;">P. Return (Out)</th>';
    echo '<th class="text-end text-white"style="background-color: #140d77;">Sold (Out)</th>';
    echo '<th class="text-end text-white"style="background-color: #140d77;">S. Return (In)</th>';
    echo '<th class="text-end  text-white"style="background-color: #140d77;">Net Movement</th>';
    echo '<th class="text-end  text-white"style="background-color: #140d77;">Calculated Current Stock</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Loop through the results
    foreach($results as $row) {
        
        $net_movement = $row['net_movement'];
        $product_name = $row['product_name'];
        
        // DYNAMIC CALCULATION: Get the Closing Stock as of the day before $start_date
        $opening_stock_at_start = get_opening_stock($pdo, $product_name, $start_date);

        $current_stock_actual = $opening_stock_at_start + $net_movement;
        // CLAMP THE DISPLAY VALUE AT ZERO
     $current_stock = max(0, $current_stock_actual);
      // Check if stock is negative. This determines the overall row color.
    $is_negative = ($current_stock <= 0);
    $row_class = $is_negative ? 'table-danger text-black' : '';

    // 2. Define conditional CSS classes for the last two <td>s
// If stock is negative, we use an empty string so the row's red background shows.
    $net_movement_cell_style = $is_negative ? '' : ''; //style="background-color:#F5FFF5;"
    
    // Elegant Light Green Background for Calculated Current Stock: #F5FFF5
    $final_stock_cell_style  = $is_negative ? '' : '';//style="background-color:#F5FFF5; font-weight: bolder;"
   
       echo '<tr class="' . $row_class . '">';
        echo '<td>' . htmlspecialchars($row['product_name']) . '</td>';
        echo '<td class="text-end text-success">' . number_format($row['total_purchased'], 2) . '</td>';
        echo '<td class="text-end text-danger">' . number_format($row['total_purchase_return'], 2) . '</td>';
        echo '<td class="text-end text-success">' . number_format($row['total_sold'], 2) . '</td>';
        echo '<td class="text-end text-danger">' . number_format($row['total_sales_return'], 2) . '</td>';
        // Column 6: Net Movement (Conditional Background)
    echo '<td class="text-end fw-bold"  ' . $net_movement_cell_style . '">' . number_format($net_movement, 2) . '</td>';

    // Column 7: Calculated Current Stock (Conditional Background)
    echo '<td class="text-end fw-bold"  ' . $final_stock_cell_style . '">' . number_format($current_stock, 2) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    
} else {
    echo '<div class="alert alert-warning" role="alert">No products found with movements in the selected date range.</div>';
}

// 4. Close the main container and remove the database connection object
?>
</main> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</body>
</html>

<?php
// Set the PDO object to null to close the connection (good practice)
$pdo = null;
?>
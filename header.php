<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}
$name     = htmlspecialchars($_SESSION['user'] ?? 'User');
$role     = htmlspecialchars($_SESSION['role'] ?? 'User');
$company  = htmlspecialchars($_SESSION['company'] ?? 'N/A');
$gender   = strtolower($_SESSION['gender'] ?? 'female');

$profilePic = ($gender === 'female' || $gender === 'girl' || $gender === 'f') ?
    "https://cdn-icons-png.flaticon.com/512/4140/4140051.png" :
    "https://cdn-icons-png.flaticon.com/512/3135/3135715.png";
$title = isset($title) ? $title : "Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
  body {
  background-color: #E9EEFF;
}

      font-family: "Poppins", sans-serif;
      margin: 0;
      padding: 0;
    }

    /* Sidebar */
    .app-sidebar {
      width: 260px;
      height: 100vh;
      background: #140d77;
      color: white;
      position: fixed;
      top: 0;
      left: 0;
      overflow-y: auto;
      transition: all 0.4s ease;
      box-shadow: 0 0 20px rgba(13, 71, 161, 0.7);
      z-index: 1000;
    }

    .app-sidebar .logo {
      font-size: 22px;
      font-weight: bold;
      text-align: center;
      margin: 20px 0;
      color: white;
      text-shadow: 0px 0px 6px rgba(255,255,255,0.7);
    }

    .app-sidebar ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .app-sidebar .nav-link {
      color: #e3f2fd;
      padding: 12px 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 15px;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s ease;
      border-radius: 8px;
      margin: 4px 10px;
    }

    .app-sidebar .nav-link:hover,
    .app-sidebar .nav-link.active {
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
      text-shadow: 0px 0px 10px rgba(255,255,255,1);
    }

    .submenu {
      padding-left: 20px;
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.4s ease;
    }

    .submenu.show {
      max-height: 500px;
    }

    .rotate-icon {
      margin-left: auto;
      transition: transform 0.3s ease;
    }

    [aria-expanded="true"] .rotate-icon {
      transform: rotate(90deg);
    }

    .main-content {
      margin-left: 260px;
      padding: 25px;
      transition: all 0.4s ease;
    }

    /* Scrollbar */
    .app-sidebar::-webkit-scrollbar { width: 6px; }
    .app-sidebar::-webkit-scrollbar-thumb {
      background-color: rgba(255,255,255,0.3);
      border-radius: 3px;
    }

    /* Responsive */
    @media (max-width: 992px) {
      .app-sidebar {
        left: -260px;
      }
      .app-sidebar.show {
        left: 0;
      }
      .main-content {
        margin-left: 0;
      }
    }

    .toggle-btn {
      background: transparent;
      border: none;
      color: #0d47a1;
      font-size: 22px;
      position: fixed;
      top: 15px;
      left: 15px;
      z-index: 1100;
      cursor: pointer;
    }
    /* ==============================
   🖨️ PRINT TABLE STYLING
   ============================== */
@media print {
  body {
    background: white !important;
    color: black !important;
     /* keep table header color */
    font-size: 13px;
  }

  .app-sidebar,
  .mobile-header,
  .btn,
  .nav,
  .alert,
  .offcanvas,
  .no-print {
    display: none !important;
  }

  .main-content {
    margin: 0 !important;
    padding: 0 !important;
    width: 100%;
  }

  table {
    border-collapse: collapse !important;
    width: 100% !important;
    font-size: 13px;
  }

  table th,
  table td {
    border: 1px solid #140d77 !important;
    padding: 6px !important;
    text-align: center;
  }

  table thead th {
    background: White !important;
    color: Black !important;
  }

  /* Optional: Page break between large tables */
  .page-break {
    page-break-before: always;
  }
  .topbar h5 {
  font-size: 1.1rem;
}
}
  </style>
</head>

<body>
<!-- Toggle Button for Mobile -->
<button class="toggle-btn" id="menuToggle"><i class="fa fa-bars"></i></button>

<div class="app-sidebar" id="sidebar">
  <div class="logo"><i class="fa-solid fa-layer-group me-2"></i> Menu</div>
  <ul class="nav flex-column">

    <li><a href="dashboard.php" class="nav-link"><i class="fa fa-home"></i> Dashboard</a></li>

    <!-- Master -->
    <li>
      <a href="#masterMenu" class="nav-link d-flex align-items-center" data-bs-toggle="collapse">
        <i class="fa fa-database"></i> Master <i class="fa fa-chevron-right rotate-icon"></i>
      </a>
      <ul class="collapse submenu" id="masterMenu">
          <li><a href="category.php" class="nav-link"><i class="fa fa-tags me-2"></i> Category</a></li>
    <li><a href="products.php" class="nav-link"><i class="fa fa-box-open me-2"></i> Products</a></li>
    <li><a href="customer.php" class="nav-link"><i class="fa fa-user-friends me-2"></i> Customer</a></li>
    <li><a href="insert_supplier.php" class="nav-link"><i class="fa fa-truck me-2"></i> Supplier</a></li>
    <li><a href="ledger.php" class="nav-link"><i class="fa fa-book me-2"></i> Ledger</a></li>
      </ul>
    </li>

    <!-- Transaction -->
    <li>
      <a href="#transactionMenu" class="nav-link d-flex align-items-center" data-bs-toggle="collapse">
        <i class="fa fa-random"></i> Transaction <i class="fa fa-chevron-right rotate-icon"></i>
      </a>
      <ul class="collapse submenu" id="transactionMenu">

        <!-- Purchase -->
        <li>
          <a href="#purchaseMenu" class="nav-link d-flex align-items-center" data-bs-toggle="collapse">
            <i class="fa fa-shopping-cart"></i> Purchase <i class="fa fa-chevron-right rotate-icon"></i>
          </a>
          <ul class="collapse submenu" id="purchaseMenu">
            <li><a href="purchase_list.php" class="nav-link"><i class="fa fa-shopping-cart me-2"></i> Purchase Order</a></li>
<li><a href="purchase_invoice_list.php" class="nav-link"><i class="fa fa-file-invoice me-2"></i> Purchase Invoice</a></li>
<li><a href="purchase_return_list.php" class="nav-link"><i class="fa fa-undo me-2"></i> Purchase Return</a></li>
          </ul>
        </li>

        <!-- Sales -->
        <li>
          <a href="#salesMenu" class="nav-link d-flex align-items-center" data-bs-toggle="collapse">
            <i class="fa fa-cash-register"></i> Sales <i class="fa fa-chevron-right rotate-icon"></i>
          </a>
          <ul class="collapse submenu" id="salesMenu">
           <li><a href="sales_list.php" class="nav-link"><i class="fa fa-shopping-bag me-2"></i> Sales Order</a></li>
<li><a href="sales_invoice_list.php" class="nav-link"><i class="fa fa-file-invoice me-2"></i> Sales Invoice</a></li>
<li><a href="sales_return_list.php" class="nav-link"><i class="fa fa-undo me-2"></i> Sales Return</a></li>
          </ul>
        </li>

        <li><a href="payment_out_list.php" class="nav-link"><i class="fa fa-wallet"></i> Payment Out</a></li>
        <li><a href="payment_in_list.php" class="nav-link"><i class="fa fa-money-bill"></i> Payment In</a></li>
      </ul>
    </li>

    <!-- Reports -->
    <li>
      <a href="#reportsMenu" class="nav-link d-flex align-items-center" data-bs-toggle="collapse">
        <i class="fa fa-chart-line"></i> Reports <i class="fa fa-chevron-right rotate-icon"></i>
      </a>
      <ul class="collapse submenu" id="reportsMenu">
      <li><a href="stock_summary.php" class="nav-link"><i class="fa fa-warehouse me-2"></i> Stock Summary</a></li>
<li><a href="sales_summary.php" class="nav-link"><i class="fa fa-chart-line me-2"></i> Sales Summary</a></li>
      </ul>
    </li>

    <li><a href="profile.php" class="nav-link"><i class="fa fa-user"></i> Profile</a></li>
    
      <!-- Only show Users menu for Admin -->
<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
  <li><a href="admin_list.php" class="nav-link"><i class="fa fa-users"></i> Users</a></li>
<?php endif; ?>

    <li><a href="logout.php" class="nav-link "><i class="fa fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<div class="main-content">
  <!-- Topbar / Header -->
<div class="topbar shadow-sm bg-white d-flex justify-content-between align-items-center px-4 py-3 mb-4" style="border-bottom: 1px solid #eee;">
    <!-- Left: Welcome -->
    <div>
        <h5 class="mb-0 fw-bold text-primary">Welcome, <?= $name ?></h5>
        <small class="text-muted">Here’s a summary of today’s operational status</small>
    </div>

    <!-- Right: Profile -->
    <div class="d-flex align-items-center">
        <div class="text-end me-3">
            <strong><?= $name ?></strong><br>
            <small class="text-secondary">Role: <?= $role ?></small><br>
            <small class="text-secondary">Company: <?= $company ?></small>
        </div>
        <img src="<?= $profilePic ?>" alt="Profile" class="rounded-circle border border-2 border-primary" width="50" height="50">
    </div>
</div>
  <!-- Your page content goes here -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Sidebar toggle for mobile
  document.getElementById("menuToggle").addEventListener("click", function () {
    document.getElementById("sidebar").classList.toggle("show");
  });

  // Keep submenu open if active link is inside
  document.querySelectorAll('.submenu a').forEach(link => {
    if (window.location.href.includes(link.getAttribute('href'))) {
      let parentCollapse = link.closest('.collapse');
      let bsCollapse = new bootstrap.Collapse(parentCollapse, { toggle: true });
      link.classList.add('active');
    }
    
  });
</script>
</body>
</html>
<?php
// ============================================================
// dashboard.php — Main Dashboard
// Shows stats, subscription status, and recent orders
// ============================================================
include 'includes/config.php';
require_login(); // redirect to login.php if not logged in

// Get fresh user data from database (not just from session)
$user = get_user($conn, $_SESSION['user_id']);

// ---- Check if subscription has expired (auto-downgrade) ----
if ($user['plan'] === 'premium' && !empty($user['sub_end'])) {
    if (strtotime($user['sub_end']) < strtotime(date('Y-m-d'))) {
        // Subscription expired: reset to free plan
        $conn->query("UPDATE users SET plan='free', sub_start=NULL, sub_end=NULL WHERE id={$user['id']}");
        $user['plan']      = 'free';
        $user['sub_end']   = null;
        $user['sub_start'] = null;
    }
}

// ---- Fetch order statistics for this user ----
$uid = $user['id']; // shortcut for user ID

// Count total orders
$r = $conn->query("SELECT COUNT(*) as c FROM orders WHERE user_id=$uid");
$total_orders = $r->fetch_assoc()['c']; // get the count value

// Count paid orders
$r = $conn->query("SELECT COUNT(*) as c FROM orders WHERE user_id=$uid AND status='paid'");
$paid_orders = $r->fetch_assoc()['c'];

// Count pending orders
$r = $conn->query("SELECT COUNT(*) as c FROM orders WHERE user_id=$uid AND status='pending'");
$pending_orders = $r->fetch_assoc()['c'];

// Sum of today's order totals (all statuses)
$today = date('Y-m-d'); // today's date in YYYY-MM-DD format
$r = $conn->query("SELECT COALESCE(SUM(total_price),0) as s FROM orders WHERE user_id=$uid AND order_date='$today'");
// COALESCE returns 0 if SUM is NULL (no orders today)
$today_sales = $r->fetch_assoc()['s'];

// Fetch 5 most recent orders to display in the table
$recent = $conn->query(
    "SELECT * FROM orders WHERE user_id=$uid ORDER BY created_at DESC LIMIT 5"
);

// ---- Check if subscription is expiring soon (within 3 days) ----
$expiring_soon = false;
if (is_premium($user) && !empty($user['sub_end'])) {
    $days_left = (strtotime($user['sub_end']) - strtotime(date('Y-m-d'))) / 86400;
    // 86400 = number of seconds in a day
    if ($days_left <= 3) {
        $expiring_soon = true; // show warning banner
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard – EasyBiz</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Include the sidebar navigation -->
<?php include 'includes/sidebar.php'; ?>

<!-- Main content area (pushed right of sidebar) -->
<div class="main-content">

  <!-- ---- Top Navbar ---- -->
  <div class="top-navbar">
    <!-- Mobile hamburger button — shows sidebar on small screens -->
    <button class="btn btn-light btn-sm d-lg-none" onclick="openSidebar()">
      <i class="bi bi-list fs-5"></i>
    </button>

    <!-- Page title -->
    <h5 class="mb-0 fw-bold">Dashboard</h5>

    <!-- Right side: welcome message + date -->
    <div class="text-end">
      <span class="text-muted small">
        Welcome, <?php echo e($user['name']); ?> |
        <?php echo date('d M Y'); ?> <!-- today's date -->
      </span>
    </div>
  </div>

  <!-- ---- Page Content ---- -->
  <div class="page-content">

    <!-- ============================================================
         SUBSCRIPTION STATUS BANNERS
         ============================================================ -->

    <?php if ($expiring_soon): ?>
      <!-- Warning banner: expiring within 3 days -->
      <div class="sub-banner expiring mb-3">
        <div>
          <i class="bi bi-clock-history me-2"></i>
          <strong>Your subscription expires in <?php echo (int)$days_left; ?> day(s)!</strong>
          Renew now to keep using EasyBiz.
        </div>
        <a href="upgrade.php" class="btn btn-warning btn-sm">Renew Now</a>
      </div>

    <?php elseif (is_premium($user)): ?>
      <!-- Green banner: active premium -->
      <div class="sub-banner premium mb-3">
        <div>
          <i class="bi bi-star-fill me-2"></i>
          <strong>Premium Active</strong> — Expires <?php echo date('d M Y', strtotime($user['sub_end'])); ?>
        </div>
        <a href="upgrade.php" class="btn btn-success btn-sm">Manage</a>
      </div>

    <?php else: ?>
      <!-- Red/orange banner: free plan -->
      <div class="sub-banner free mb-3">
        <div>
          <i class="bi bi-lightning me-2"></i>
          <strong>Free Plan</strong> —
          <?php echo FREE_LIMIT - $user['trial_used']; ?> order(s) remaining.
          Upgrade for unlimited access.
        </div>
        <a href="upgrade.php" class="btn btn-danger btn-sm">Upgrade – 3,000 FCFA</a>
      </div>
    <?php endif; ?>

    <!-- ============================================================
         STAT CARDS — summary numbers at the top
         ============================================================ -->
    <div class="row g-3 mb-4">

      <!-- Total Orders card -->
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="bi bi-cart"></i></div>
          <div class="stat-info">
            <h3><?php echo $total_orders; ?></h3>
            <p>Total Orders</p>
          </div>
        </div>
      </div>

      <!-- Paid Orders card -->
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
          <div class="stat-info">
            <h3><?php echo $paid_orders; ?></h3>
            <p>Paid Orders</p>
          </div>
        </div>
      </div>

      <!-- Pending Orders card -->
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-icon yellow"><i class="bi bi-hourglass"></i></div>
          <div class="stat-info">
            <h3><?php echo $pending_orders; ?></h3>
            <p>Pending</p>
          </div>
        </div>
      </div>

      <!-- Today's Sales card -->
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-icon red"><i class="bi bi-cash"></i></div>
          <div class="stat-info">
            <!-- number_format adds thousand separators: 45000 → 45,000 -->
            <h3 style="font-size:1.1rem;"><?php echo number_format($today_sales, 0); ?></h3>
            <p>Today (FCFA)</p>
          </div>
        </div>
      </div>

    </div>

    <!-- ============================================================
         RECENT ORDERS TABLE
         ============================================================ -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2"></i>Recent Orders</span>
        <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
      </div>
      <div class="card-body p-0">

        <?php if ($recent->num_rows === 0): ?>
          <!-- Empty state: no orders yet -->
          <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size:3rem; color:var(--border);"></i>
            <p class="text-muted mt-2">No orders yet. <a href="add_order.php">Add your first order!</a></p>
          </div>

        <?php else: ?>
          <!-- Orders table -->
          <div class="table-responsive"> <!-- horizontal scroll on small screens -->
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>Customer</th>
                  <th>Product</th>
                  <th>Qty</th>
                  <th>Total</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($o = $recent->fetch_assoc()): ?>
                  <!-- Loop through each recent order -->
                  <tr>
                    <td><?php echo e($o['customer_name']); ?></td>
                    <td><?php echo e($o['product_name']); ?></td>
                    <td><?php echo $o['quantity']; ?></td>
                    <td><strong><?php echo number_format($o['total_price'], 0); ?> FCFA</strong></td>
                    <td><?php echo date('d M', strtotime($o['order_date'])); ?></td>
                    <td>
                      <!-- Show colored badge based on status -->
                      <?php if ($o['status'] === 'paid'): ?>
                        <span class="badge-paid">Paid</span>
                      <?php else: ?>
                        <span class="badge-pending">Pending</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <!-- Link to message generation for this specific order -->
                      <a href="messages.php?order_id=<?php echo $o['id']; ?>"
                         class="btn btn-sm btn-outline-primary"
                         title="Generate WhatsApp message">
                        <i class="bi bi-chat-dots"></i>
                      </a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

      </div>
    </div>

  </div><!-- end page-content -->
</div><!-- end main-content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/easybiz/js/app.js"></script> <!-- our custom JS file -->
</body>
</html>

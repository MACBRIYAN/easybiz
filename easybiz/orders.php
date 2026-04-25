<?php
// ============================================================
// orders.php — View All Orders
// Displays all orders for the logged-in user with filters
// ============================================================
include 'includes/config.php';
require_login();

$user = get_user($conn, $_SESSION['user_id']);
$uid  = $user['id'];

// ---- Handle status filter from URL (e.g. ?filter=paid) ----
$filter = $_GET['filter'] ?? 'all'; // default: show all orders
$where  = "user_id = $uid"; // base SQL condition

// Add status filter if not "all"
if ($filter === 'paid')    $where .= " AND status = 'paid'";
if ($filter === 'pending') $where .= " AND status = 'pending'";

// ---- Handle marking order as paid ----
// When user clicks "Mark as Paid" button, this processes it
if (isset($_GET['mark_paid'])) {
    $oid = (int)$_GET['mark_paid']; // cast to int to prevent SQL injection
    // Make sure this order belongs to the current user before updating
    $conn->query("UPDATE orders SET status='paid' WHERE id=$oid AND user_id=$uid");
    header("Location: orders.php?filter=$filter"); // refresh page
    exit();
}

// ---- Handle deleting an order ----
if (isset($_GET['delete'])) {
    $oid = (int)$_GET['delete'];
    $conn->query("DELETE FROM orders WHERE id=$oid AND user_id=$uid");
    header("Location: orders.php");
    exit();
}

// ---- Fetch all orders matching the filter ----
$orders = $conn->query("SELECT * FROM orders WHERE $where ORDER BY order_date DESC, created_at DESC");

// ---- Calculate totals for filtered results ----
$r = $conn->query("SELECT COALESCE(SUM(total_price),0) as total FROM orders WHERE $where");
$grand_total = $r->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orders – EasyBiz</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

  <div class="top-navbar">
    <button class="btn btn-light btn-sm d-lg-none" onclick="openSidebar()">
      <i class="bi bi-list fs-5"></i>
    </button>
    <h5 class="mb-0 fw-bold"><i class="bi bi-cart me-2"></i>All Orders</h5>
    <a href="add_order.php" class="btn btn-sm btn-primary">
      <i class="bi bi-plus me-1"></i> Add Order
    </a>
  </div>

  <div class="page-content">

    <!-- Filter Tabs -->
    <div class="d-flex gap-2 mb-4 flex-wrap">
      <!-- Each link filters by status — active class highlights current tab -->
      <a href="orders.php?filter=all"
         class="btn btn-sm <?php echo $filter==='all' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
        All Orders
      </a>
      <a href="orders.php?filter=pending"
         class="btn btn-sm <?php echo $filter==='pending' ? 'btn-warning text-dark' : 'btn-outline-secondary'; ?>">
        Pending
      </a>
      <a href="orders.php?filter=paid"
         class="btn btn-sm <?php echo $filter==='paid' ? 'btn-success' : 'btn-outline-secondary'; ?>">
        Paid
      </a>
    </div>

    <!-- Grand total for current filter -->
    <div class="alert alert-light border mb-3">
      <i class="bi bi-cash me-2"></i>
      Total for <strong><?php echo ucfirst($filter); ?></strong> orders:
      <strong class="text-success"><?php echo number_format($grand_total, 0); ?> FCFA</strong>
    </div>

    <!-- Orders Table -->
    <div class="card">
      <div class="card-body p-0">

        <?php if ($orders->num_rows === 0): ?>
          <!-- Empty state -->
          <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size:3rem; color:var(--border);"></i>
            <p class="text-muted mt-3">No orders found.
              <a href="add_order.php">Add your first order</a>
            </p>
          </div>

        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Customer</th>
                  <th>Product</th>
                  <th>Qty</th>
                  <th>Unit Price</th>
                  <th>Total</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $num = 1; // row number counter
                while ($o = $orders->fetch_assoc()):
                ?>
                <tr>
                  <td class="text-muted"><?php echo $num++; ?></td>
                  <td><strong><?php echo e($o['customer_name']); ?></strong></td>
                  <td><?php echo e($o['product_name']); ?></td>
                  <td><?php echo $o['quantity']; ?></td>
                  <td><?php echo number_format($o['unit_price'], 0); ?> FCFA</td>
                  <td><strong class="text-success"><?php echo number_format($o['total_price'], 0); ?> FCFA</strong></td>
                  <td><?php echo date('d M Y', strtotime($o['order_date'])); ?></td>
                  <td>
                    <?php if ($o['status'] === 'paid'): ?>
                      <span class="badge-paid">Paid</span>
                    <?php else: ?>
                      <span class="badge-pending">Pending</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="d-flex gap-1">

                      <!-- Generate Message button -->
                      <a href="messages.php?order_id=<?php echo $o['id']; ?>"
                         class="btn btn-xs btn-outline-primary"
                         style="font-size:0.75rem; padding:3px 8px;"
                         title="Generate Message">
                        <i class="bi bi-chat-dots"></i>
                      </a>

                      <!-- Mark as Paid (only show if pending) -->
                      <?php if ($o['status'] === 'pending'): ?>
                        <a href="orders.php?mark_paid=<?php echo $o['id']; ?>&filter=<?php echo $filter; ?>"
                           class="btn btn-xs btn-outline-success"
                           style="font-size:0.75rem; padding:3px 8px;"
                           title="Mark as Paid"
                           onclick="return confirm('Mark this order as paid?')">
                          <i class="bi bi-check"></i>
                        </a>
                      <?php endif; ?>

                      <!-- Delete button -->
                      <a href="orders.php?delete=<?php echo $o['id']; ?>"
                         class="btn btn-xs btn-outline-danger"
                         style="font-size:0.75rem; padding:3px 8px;"
                         title="Delete Order"
                         onclick="return confirm('Delete this order? This cannot be undone.')">
                        <!-- confirm() shows browser dialog before deleting -->
                        <i class="bi bi-trash"></i>
                      </a>

                    </div>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/app.js"></script>
</body>
</html>

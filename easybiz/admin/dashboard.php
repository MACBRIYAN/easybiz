<?php
// ============================================================
// admin/dashboard.php — Admin Control Panel
// View users, payments, confirm upgrades
// ============================================================
require_once '../includes/config.php';
require_admin(); // redirect to admin login if not authenticated

// ---- Summary stats ----
$total_users    = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$premium_users  = $conn->query("SELECT COUNT(*) as c FROM users WHERE plan='premium'")->fetch_assoc()['c'];
$pending_pays   = $conn->query("SELECT COUNT(*) as c FROM payments WHERE status='pending'")->fetch_assoc()['c'];
$confirmed_pays = $conn->query("SELECT COUNT(*) as c FROM payments WHERE status='confirmed'")->fetch_assoc()['c'];

// ---- Handle admin actions (confirm or reject payment) ----
if (isset($_GET['confirm'])) {
    $pid = (int)$_GET['confirm']; // payment ID to confirm

    // Get payment + user info
    $r   = $conn->query("SELECT * FROM payments WHERE id=$pid");
    $pay = $r->fetch_assoc();

    if ($pay && $pay['status'] === 'pending') {
        // Calculate subscription dates
        $start = date('Y-m-d');         // today
        $end   = date('Y-m-d', strtotime('+' . SUB_DAYS . ' days')); // 30 days from today

        // Update the user to premium plan
        $conn->query("UPDATE users SET plan='premium', sub_start='$start', sub_end='$end' WHERE id={$pay['user_id']}");

        // Mark the payment as confirmed and record the time
        $conn->query("UPDATE payments SET status='confirmed', confirmed_at=NOW() WHERE id=$pid");

        $msg = "Payment confirmed! User upgraded to Premium until $end.";
    }
}

if (isset($_GET['reject'])) {
    $pid = (int)$_GET['reject'];
    $conn->query("UPDATE payments SET status='rejected', confirmed_at=NOW() WHERE id=$pid");
    $msg = "Payment rejected.";
}

// ---- Fetch pending payments ----
$pending_list = $conn->query(
    "SELECT p.*, u.name, u.phone FROM payments p
     JOIN users u ON p.user_id = u.id
     WHERE p.status = 'pending'
     ORDER BY p.submitted_at DESC"
);

// ---- Fetch all users ----
$users_list = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard – EasyBiz</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background:var(--bg);">

<!-- Admin Top Navigation Bar -->
<nav class="navbar navbar-dark admin-topbar px-4 sticky-top" style="background:var(--dark);">
  <span class="navbar-brand fw-bold">
    Easy<span style="color:var(--accent)">Biz</span>
    <small class="fs-6 text-white-50 ms-2">Admin Panel</small>
  </span>
  <div class="d-flex gap-2 align-items-center">
    <span class="text-white-50 small">
      Logged in as: <?php echo e($_SESSION['admin_username']); ?>
    </span>
    <a href="logout.php" class="btn btn-sm btn-outline-light">Logout</a>
  </div>
</nav>

<div class="container-fluid py-4">

  <!-- Success/action message -->
  <?php if (isset($msg)): ?>
    <div class="alert alert-success"><?php echo e($msg); ?></div>
  <?php endif; ?>

  <!-- ============================================================
       STAT CARDS
       ============================================================ -->
  <div class="row g-3 mb-4">

    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-people"></i></div>
        <div class="stat-info">
          <h3><?php echo $total_users; ?></h3>
          <p>Total Users</p>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-star"></i></div>
        <div class="stat-info">
          <h3><?php echo $premium_users; ?></h3>
          <p>Premium Users</p>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-icon yellow"><i class="bi bi-clock"></i></div>
        <div class="stat-info">
          <h3><?php echo $pending_pays; ?></h3>
          <p>Pending Payments</p>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-cash-coin"></i></div>
        <div class="stat-info">
          <h3><?php echo $confirmed_pays; ?></h3>
          <p>Confirmed Payments</p>
        </div>
      </div>
    </div>

  </div>

  <!-- ============================================================
       PENDING PAYMENTS — need admin action
       ============================================================ -->
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between">
      <span><i class="bi bi-clock-history me-2 text-warning"></i>Pending Payments</span>
      <span class="badge bg-warning text-dark"><?php echo $pending_pays; ?> pending</span>
    </div>
    <div class="card-body p-0">

      <?php if ($pending_list->num_rows === 0): ?>
        <div class="text-center py-4 text-muted">
          <i class="bi bi-check-all me-2"></i>No pending payments. All clear!
        </div>

      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>User</th>
                <th>Phone</th>
                <th>Method</th>
                <th>Amount</th>
                <th>Submitted</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($p = $pending_list->fetch_assoc()): ?>
                <tr>
                  <td><strong><?php echo e($p['name']); ?></strong></td>
                  <td><?php echo e($p['phone']); ?></td>
                  <td>
                    <!-- Show MTN or Orange badge -->
                    <?php if ($p['method'] === 'mtn'): ?>
                      <span class="badge" style="background:#fbbf24; color:#000;">MTN MoMo</span>
                    <?php else: ?>
                      <span class="badge" style="background:#f97316; color:#fff;">Orange Money</span>
                    <?php endif; ?>
                  </td>
                  <td><strong><?php echo number_format($p['amount'], 0); ?> FCFA</strong></td>
                  <td><?php echo date('d M Y H:i', strtotime($p['submitted_at'])); ?></td>
                  <td>
                    <div class="d-flex gap-2">
                      <!-- Confirm button (activates premium) -->
                      <a href="dashboard.php?confirm=<?php echo $p['id']; ?>"
                         class="btn btn-sm btn-success"
                         onclick="return confirm('Confirm this payment and activate Premium for <?php echo e($p['name']); ?>?')">
                        <i class="bi bi-check-circle me-1"></i>Confirm
                      </a>
                      <!-- Reject button -->
                      <a href="dashboard.php?reject=<?php echo $p['id']; ?>"
                         class="btn btn-sm btn-outline-danger"
                         onclick="return confirm('Reject this payment?')">
                        <i class="bi bi-x-circle me-1"></i>Reject
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

  <!-- ============================================================
       ALL USERS TABLE
       ============================================================ -->
  <div class="card">
    <div class="card-header">
      <i class="bi bi-people me-2"></i>All Users
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Phone</th>
              <th>Plan</th>
              <th>Orders Used (trial)</th>
              <th>Subscription End</th>
              <th>Joined</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $n = 1;
            while ($u = $users_list->fetch_assoc()):
            ?>
            <tr>
              <td><?php echo $n++; ?></td>
              <td><strong><?php echo e($u['name']); ?></strong></td>
              <td><?php echo e($u['phone']); ?></td>
              <td>
                <?php if ($u['plan'] === 'premium'): ?>
                  <span class="badge bg-success">Premium</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Free</span>
                <?php endif; ?>
              </td>
              <td><?php echo $u['trial_used']; ?> / <?php echo FREE_LIMIT; ?></td>
              <td>
                <?php echo $u['sub_end'] ? date('d M Y', strtotime($u['sub_end'])) : '—'; ?>
              </td>
              <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

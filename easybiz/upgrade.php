<?php
// ============================================================
// upgrade.php — Subscription / Upgrade Page
// User submits payment proof to upgrade to Premium
// ============================================================
include 'includes/config.php';
require_login();

$user    = get_user($conn, $_SESSION['user_id']);
$success = '';
$error   = '';

// ============================================================
// Handle "I Have Paid" form submission
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $method = $_POST['method'] ?? ''; // 'mtn' or 'orange'

    if (empty($method)) {
        $error = "Please select a payment method.";
    } else {
        // Check if there's already a pending payment for this user
        // (prevent duplicate submissions)
        $r = $conn->query("SELECT id FROM payments WHERE user_id={$user['id']} AND status='pending'");

        if ($r->num_rows > 0) {
            // Already submitted — waiting for admin
            $error = "Your payment is already submitted and being reviewed. Please wait for admin confirmation.";
        } else {
            // Insert a new pending payment record
            $stmt = $conn->prepare(
                "INSERT INTO payments (user_id, method, amount, status) VALUES (?, ?, ?, 'pending')"
            );
            $amount = SUB_PRICE; // 3000 FCFA from config
            $stmt->bind_param("isd", $user['id'], $method, $amount);

            if ($stmt->execute()) {
                $success = "Payment submitted! Our team will confirm within 24 hours. You'll be upgraded automatically once confirmed.";
            } else {
                $error = "Failed to submit payment. Please try again.";
            }
        }
    }
}

// ---- Check if user has a pending payment right now ----
$pending_payment = null;
$r = $conn->query("SELECT * FROM payments WHERE user_id={$user['id']} AND status='pending' LIMIT 1");
if ($r->num_rows > 0) {
    $pending_payment = $r->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Upgrade – EasyBiz</title>
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
    <h5 class="mb-0 fw-bold"><i class="bi bi-star me-2"></i>Upgrade to Premium</h5>
    <span></span>
  </div>

  <div class="page-content">

    <!-- Already premium: show current status -->
    <?php if (is_premium($user)): ?>
      <div class="alert alert-success">
        <i class="bi bi-star-fill me-2"></i>
        <strong>You are already on Premium!</strong>
        Your plan is active until <strong><?php echo date('d M Y', strtotime($user['sub_end'])); ?></strong>.
      </div>
    <?php endif; ?>

    <!-- Success message after payment submission -->
    <?php if ($success): ?>
      <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i><?php echo e($success); ?>
      </div>
    <?php endif; ?>

    <!-- Error message -->
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo e($error); ?></div>
    <?php endif; ?>

    <!-- Pending payment notice -->
    <?php if ($pending_payment): ?>
      <div class="alert alert-warning">
        <i class="bi bi-clock me-2"></i>
        <strong>Payment Under Review</strong> —
        Submitted on <?php echo date('d M Y H:i', strtotime($pending_payment['submitted_at'])); ?>.
        Our admin will confirm within 24 hours.
      </div>
    <?php endif; ?>

    <div class="row g-4 justify-content-center">

      <!-- Pricing card -->
      <div class="col-lg-5">
        <div class="pricing-card text-center">
          <span class="badge bg-warning text-dark mb-3">⭐ Premium Plan</span>
          <h3 class="fw-bold">3,000 FCFA</h3>
          <p class="opacity-75 mb-4">per 30 days</p>

          <!-- Feature list -->
          <ul class="list-unstyled text-start mb-4">
            <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i>Unlimited orders</li>
            <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i>WhatsApp message generator</li>
            <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i>Business profile branding</li>
            <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i>Full dashboard access</li>
            <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i>30-day access</li>
          </ul>

        </div>
      </div>

      <!-- Payment instructions -->
      <div class="col-lg-6">
        <div class="card">
          <div class="card-header fw-bold">
            <i class="bi bi-cash me-2"></i>How to Pay
          </div>
          <div class="card-body">

            <!-- Step 1 -->
            <div class="d-flex gap-3 mb-4">
              <!-- Numbered step circle -->
              <div style="width:35px; height:35px; border-radius:50%; background:var(--primary);
                          color:white; display:flex; align-items:center; justify-content:center;
                          flex-shrink:0; font-weight:700;">1</div>
              <div>
                <strong>Send 3,000 FCFA to one of these numbers:</strong>
                <div class="mt-2">
                  <!-- MTN MoMo payment box -->
                  <div class="p-3 mb-2 rounded" style="background:#fff3cd; border:1px solid #fbbf24;">
                    <i class="bi bi-phone me-2"></i>
                    <strong>MTN MoMo:</strong> 6XX XXX XXX
                    <!-- IMPORTANT: Replace with your actual MTN MoMo number above -->
                  </div>
                  <!-- Orange Money payment box -->
                  <div class="p-3 rounded" style="background:#ffe4d1; border:1px solid #f97316;">
                    <i class="bi bi-phone me-2"></i>
                    <strong>Orange Money:</strong> 6XX XXX XXX
                    <!-- IMPORTANT: Replace with your actual Orange Money number above -->
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 2 -->
            <div class="d-flex gap-3 mb-4">
              <div style="width:35px; height:35px; border-radius:50%; background:var(--primary);
                          color:white; display:flex; align-items:center; justify-content:center;
                          flex-shrink:0; font-weight:700;">2</div>
              <div>
                <strong>After paying, select your method and click "I Have Paid"</strong>
                <p class="text-muted small mt-1">Our admin will verify your payment and activate your account.</p>
              </div>
            </div>

            <!-- ============================================================
                 Payment Confirmation Form
                 ============================================================ -->
            <?php if (!$pending_payment && !is_premium($user)): ?>
              <form method="POST" action="upgrade.php">
                <!-- Select payment method used -->
                <div class="mb-3">
                  <label class="form-label">Payment Method Used</label>
                  <select name="method" class="form-select" required>
                    <option value="">-- Select method --</option>
                    <option value="mtn">MTN MoMo</option>
                    <option value="orange">Orange Money</option>
                  </select>
                </div>

                <!-- Submit button -->
                <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                  <i class="bi bi-check-circle me-2"></i>I Have Paid – Notify Admin
                </button>
              </form>

            <?php elseif ($pending_payment): ?>
              <!-- Already waiting for admin -->
              <div class="text-center py-3">
                <i class="bi bi-hourglass-split" style="font-size:2rem; color:var(--accent);"></i>
                <p class="mt-2 text-muted">Waiting for admin confirmation...</p>
              </div>

            <?php elseif (is_premium($user)): ?>
              <!-- Already premium -->
              <div class="text-center py-3">
                <i class="bi bi-patch-check-fill" style="font-size:2rem; color:var(--success);"></i>
                <p class="mt-2 text-success fw-bold">You're on Premium! 🎉</p>
              </div>
            <?php endif; ?>

          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/app.js"></script>
</body>
</html>

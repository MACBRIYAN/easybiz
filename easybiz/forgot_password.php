<?php
// ============================================================
// forgot_password.php — Password Recovery via OTP
// 3-step process: Enter phone → Enter OTP → New password
// ============================================================
include 'includes/config.php';

$step    = $_POST['step'] ?? 'phone'; // current step: 'phone', 'otp', or 'reset'
$error   = '';
$success = '';
$phone   = ''; // phone number carried through steps

// ============================================================
// STEP 1: User entered phone number — send OTP
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'phone') {

    $phone = trim($_POST['phone'] ?? '');

    if (empty($phone)) {
        $error = "Please enter your phone number.";
        $step  = 'phone'; // stay on step 1
    } else {
        // Check if phone exists in the database
        $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $error = "No account found with that phone number.";
            $step  = 'phone';
        } else {
            // ---- Generate a 4-digit OTP code ----
            $otp = rand(1000, 9999); // random 4-digit number

            // Set expiry to 10 minutes from now
            $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            // Delete any old OTP codes for this phone (clean up)
            $conn->query("DELETE FROM otp_codes WHERE phone = '$phone'");

            // Save new OTP in database
            $ins = $conn->prepare("INSERT INTO otp_codes (phone, code, expires_at) VALUES (?, ?, ?)");
            $ins->bind_param("sss", $phone, $otp, $expires);
            $ins->execute();

            // ---- In a real app, send OTP via SMS here ----
            // Example with Africa's Talking SMS API:
            // sendSMS($phone, "Your EasyBiz reset code is: $otp");
            //
            // For now, we display it on screen for testing
            // IMPORTANT: Remove this in production and use real SMS

            $success = "OTP code sent! For testing, your code is: <strong>$otp</strong>";
            // ☝️ REMOVE THIS LINE in production — just say "code sent"

            $step = 'otp'; // move to step 2
        }
    }
}

// ============================================================
// STEP 2: User entered OTP — verify it
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'otp') {

    $phone    = trim($_POST['phone'] ?? '');
    $otp_input = trim($_POST['otp']  ?? '');

    if (empty($otp_input)) {
        $error = "Please enter the OTP code.";
    } else {
        // Look for a valid, unused, non-expired OTP for this phone
        $now  = date('Y-m-d H:i:s'); // current datetime
        $stmt = $conn->prepare(
            "SELECT id FROM otp_codes
             WHERE phone = ? AND code = ? AND used = 0 AND expires_at > ?"
        );
        $stmt->bind_param("sss", $phone, $otp_input, $now);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $error = "Invalid or expired code. Please try again.";
            $step  = 'otp'; // stay on step 2
        } else {
            // OTP is valid — move to step 3 (set new password)
            $step = 'reset';
        }
    }
}

// ============================================================
// STEP 3: User entered new password — update it
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'reset') {

    $phone = trim($_POST['phone'] ?? '');
    $otp_input = trim($_POST['otp'] ?? '');
    $new_pass  = trim($_POST['new_password']     ?? '');
    $conf_pass = trim($_POST['confirm_password'] ?? '');

    if (empty($new_pass)) {
        $error = "Please enter a new password.";
        $step  = 'reset';
    } elseif (strlen($new_pass) < 6) {
        $error = "Password must be at least 6 characters.";
        $step  = 'reset';
    } elseif ($new_pass !== $conf_pass) {
        $error = "Passwords do not match.";
        $step  = 'reset';
    } else {
        // Hash the new password securely
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);

        // Update user's password in database
        $upd = $conn->prepare("UPDATE users SET password = ? WHERE phone = ?");
        $upd->bind_param("ss", $hashed, $phone);
        $upd->execute();

        // Mark OTP as used so it cannot be reused
        $conn->query("UPDATE otp_codes SET used = 1 WHERE phone = '$phone'");

        // Done — redirect to login with success message
        $_SESSION['flash_success'] = "Password changed successfully. Please login.";
        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password – EasyBiz</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-wrapper">
  <div class="auth-card">

    <!-- Logo -->
    <div class="auth-logo">
      <h2>Easy<span style="color:var(--accent)">Biz</span></h2>
      <p class="text-muted small">Password Recovery</p>
    </div>

    <!-- Progress indicator: shows which step user is on -->
    <div class="d-flex gap-2 mb-4 justify-content-center">
      <!-- Step 1 bubble -->
      <div style="width:32px; height:32px; border-radius:50%;
                  background:<?php echo in_array($step,['phone','otp','reset']) ? 'var(--primary)' : '#e0e0e0'; ?>;
                  color:white; display:flex; align-items:center; justify-content:center; font-size:0.8rem; font-weight:700;">
        1
      </div>
      <!-- Connector line -->
      <div style="flex:1; height:2px; background:#e0e0e0; align-self:center;
                  background:<?php echo in_array($step,['otp','reset']) ? 'var(--primary)' : '#e0e0e0'; ?>;"></div>
      <!-- Step 2 bubble -->
      <div style="width:32px; height:32px; border-radius:50%;
                  background:<?php echo in_array($step,['otp','reset']) ? 'var(--primary)' : '#e0e0e0'; ?>;
                  color:white; display:flex; align-items:center; justify-content:center; font-size:0.8rem; font-weight:700;">
        2
      </div>
      <!-- Connector line -->
      <div style="flex:1; height:2px; align-self:center;
                  background:<?php echo $step==='reset' ? 'var(--primary)' : '#e0e0e0'; ?>;"></div>
      <!-- Step 3 bubble -->
      <div style="width:32px; height:32px; border-radius:50%;
                  background:<?php echo $step==='reset' ? 'var(--primary)' : '#e0e0e0'; ?>;
                  color:white; display:flex; align-items:center; justify-content:center; font-size:0.8rem; font-weight:700;">
        3
      </div>
    </div>

    <!-- Error and Success messages -->
    <?php if ($error): ?>
      <div class="alert alert-danger small"><?php echo e($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success small"><?php echo $success; /* not escaped — contains HTML bold tag */ ?></div>
    <?php endif; ?>

    <!-- ============================================================
         STEP 1: Enter Phone Number
         ============================================================ -->
    <?php if ($step === 'phone'): ?>
      <h6 class="fw-bold mb-3">Step 1: Enter your phone number</h6>
      <form method="POST" action="forgot_password.php">
        <input type="hidden" name="step" value="phone"><!-- tells PHP which step we're on -->
        <div class="mb-3">
          <label class="form-label">Phone Number</label>
          <input type="tel" name="phone" class="form-control"
                 placeholder="e.g. 690123456" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary w-100">
          Send OTP Code
        </button>
      </form>

    <!-- ============================================================
         STEP 2: Enter OTP Code
         ============================================================ -->
    <?php elseif ($step === 'otp'): ?>
      <h6 class="fw-bold mb-3">Step 2: Enter the OTP code</h6>
      <form method="POST" action="forgot_password.php">
        <!-- Hidden fields to carry phone through steps -->
        <input type="hidden" name="step"  value="otp">
        <input type="hidden" name="phone" value="<?php echo e($phone); ?>">
        <div class="mb-3">
          <label class="form-label">OTP Code</label>
          <input type="text" name="otp" class="form-control text-center"
                 placeholder="Enter 4-digit code"
                 maxlength="4" style="font-size:1.5rem; letter-spacing:8px;"
                 required autofocus>
          <small class="text-muted">Code expires in 10 minutes</small>
        </div>
        <button type="submit" class="btn btn-primary w-100">Verify Code</button>
      </form>

    <!-- ============================================================
         STEP 3: Set New Password
         ============================================================ -->
    <?php elseif ($step === 'reset'): ?>
      <h6 class="fw-bold mb-3">Step 3: Set a new password</h6>
      <form method="POST" action="forgot_password.php">
        <!-- Carry phone and OTP through to final step -->
        <input type="hidden" name="step"  value="reset">
        <input type="hidden" name="phone" value="<?php echo e($phone); ?>">
        <input type="hidden" name="otp"   value="<?php echo e($otp_input ?? ''); ?>">

        <div class="mb-3">
          <label class="form-label">New Password</label>
          <input type="password" name="new_password" class="form-control"
                 placeholder="Minimum 6 characters" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm New Password</label>
          <input type="password" name="confirm_password" class="form-control"
                 placeholder="Repeat new password" required>
        </div>
        <button type="submit" class="btn btn-success w-100">
          <i class="bi bi-check-circle me-2"></i>Set New Password
        </button>
      </form>
    <?php endif; ?>

    <!-- Back to login link -->
    <p class="text-center mt-3 small">
      <a href="login.php" class="text-muted">
        <i class="bi bi-arrow-left me-1"></i>Back to Login
      </a>
    </p>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// ============================================================
// login.php — User Login Page
// ============================================================
include 'includes/config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = ''; // holds error message to display

// ============================================================
// Handle login form submission
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $phone = trim($_POST['phone']    ?? '');
    $pass  = trim($_POST['password'] ?? '');

    // Basic check: ensure fields are not empty
    if (empty($phone) || empty($pass)) {
        $error = "Please enter your phone number and password.";
    } else {
        // Look up user by phone number
        $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc(); // get user row as associative array

        if ($user && password_verify($pass, $user['password'])) {
            // password_verify compares plain password to stored hash safely

            // ---- Subscription expiry check ----
            // If user was premium but their end date has passed, reset to free
            if ($user['plan'] === 'premium' && !empty($user['sub_end'])) {
                if (strtotime($user['sub_end']) < strtotime(date('Y-m-d'))) {
                    // Subscription expired — update plan to free in DB
                    $conn->query("UPDATE users SET plan='free', sub_start=NULL, sub_end=NULL WHERE id={$user['id']}");
                    $user['plan'] = 'free'; // update local copy too
                }
            }

            // Set session variables to keep user logged in
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            // Redirect to dashboard after successful login
            header("Location: dashboard.php");
            exit();

        } else {
            // Wrong phone or password
            $error = "Incorrect phone number or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login – EasyBiz</title>
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
      <p class="text-muted small">Welcome back! Login to your account</p>
    </div>

    <!-- Error message if login failed -->
    <?php if ($error): ?>
      <div class="alert alert-danger">
        <i class="bi bi-exclamation-circle me-2"></i><?php echo e($error); ?>
      </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST" action="login.php">

      <!-- Phone Number (username) -->
      <div class="mb-3">
        <label class="form-label">Phone Number</label>
        <input type="tel" name="phone" class="form-control"
               placeholder="e.g. 690123456"
               value="<?php echo e($_POST['phone'] ?? ''); ?>"
               required autofocus>
        <!-- autofocus = cursor goes here automatically when page loads -->
      </div>

      <!-- Password -->
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control"
               placeholder="Your password"
               required>
      </div>

      <!-- Forgot password link -->
      <div class="text-end mb-3">
        <a href="forgot_password.php" class="small text-primary">Forgot Password?</a>
      </div>

      <!-- Submit button -->
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-box-arrow-in-right me-2"></i>Login
      </button>
    </form>

    <!-- Register link -->
    <p class="text-center mt-3 small text-muted">
      New to EasyBiz?
      <a href="register.php" class="text-primary">Create free account</a>
    </p>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

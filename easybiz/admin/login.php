<?php
// ============================================================
// admin/login.php — Admin Login Page
// Separate login for admin (not regular users)
// ============================================================
require_once '../includes/config.php'; // go up one folder to find config

$error = '';

// Handle admin login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Please enter username and password.";
    } else {
        // Look up admin by username
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin  = $result->fetch_assoc();

        if ($admin && password_verify($password, $admin['password'])) {
            // Valid admin credentials — set admin session
            $_SESSION[ADMIN_SESSION_NAME] = $admin['id']; // mark as logged in
            $_SESSION['admin_username']   = $admin['username'];

            // Redirect to admin dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid admin credentials.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login – EasyBiz</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css"> <!-- one level up -->
</head>
<body>

<div class="auth-wrapper" style="background:linear-gradient(135deg, #1a1a2e, #16213e);">
  <div class="auth-card">

    <div class="auth-logo">
      <h2>Easy<span style="color:var(--accent)">Biz</span></h2>
      <p class="text-muted small">Admin Panel</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control"
               placeholder="Admin username" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control"
               placeholder="Admin password" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">
        Login as Admin
      </button>
    </form>

    <p class="text-center mt-3 small">
      <a href="../index.php" class="text-muted">← Back to main site</a>
    </p>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

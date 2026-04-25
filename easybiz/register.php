<?php
// ============================================================
// register.php — New User Registration Page
// ============================================================
include 'includes/config.php'; // load DB, session helpers

// If already logged in, go to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error   = ''; // variable to store error message shown to user
$success = ''; // variable to store success message

// ============================================================
// Handle form submission (when user clicks "Create Account")
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect and trim form inputs (trim removes leading/trailing spaces)
    $name  = trim($_POST['name']  ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $pass  = trim($_POST['password'] ?? '');
    $pass2 = trim($_POST['password2'] ?? ''); // password confirmation

    // --- Validation: ensure all fields are filled ---
    if (empty($name) || empty($phone) || empty($pass)) {
        $error = "Please fill in all fields.";

    // --- Validation: passwords must match ---
    } elseif ($pass !== $pass2) {
        $error = "Passwords do not match.";

    // --- Validation: password must be at least 6 characters ---
    } elseif (strlen($pass) < 6) {
        $error = "Password must be at least 6 characters.";

    } else {
        // Check if this phone number is already registered
        $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->bind_param("s", $phone); // "s" = string type
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Phone number already exists in the database
            $error = "This phone number is already registered. Please login.";
        } else {
            // Hash the password before storing (NEVER store plain text passwords)
            // password_hash uses bcrypt algorithm by default
            $hashed = password_hash($pass, PASSWORD_DEFAULT);

            // Insert new user into the database
            $insert = $conn->prepare(
                "INSERT INTO users (name, phone, password, plan, trial_used) VALUES (?, ?, ?, 'free', 0)"
            );
            $insert->bind_param("sss", $name, $phone, $hashed);

            if ($insert->execute()) {
                // Registration successful — get the new user's ID
                $new_id = $conn->insert_id;

                // Log the user in immediately by setting session variables
                $_SESSION['user_id']   = $new_id;   // store user ID in session
                $_SESSION['user_name'] = $name;      // store name for welcome message

                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                // Database insert failed (rare server error)
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account – EasyBiz</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Centered wrapper for the auth card -->
<div class="auth-wrapper">
  <div class="auth-card">

    <!-- Logo / App Name -->
    <div class="auth-logo">
      <h2>Easy<span style="color:var(--accent)">Biz</span></h2>
      <p class="text-muted small">Create your free account</p>
    </div>

    <!-- Show error message if there is one -->
    <?php if ($error): ?>
      <div class="alert alert-danger">
        <i class="bi bi-exclamation-circle me-2"></i><?php echo e($error); ?>
      </div>
    <?php endif; ?>

    <!-- Registration Form -->
    <form method="POST" action="register.php">

      <!-- Full Name field -->
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control"
               placeholder="e.g. Briyan Store"
               value="<?php echo e($_POST['name'] ?? ''); ?>" <!-- keep value after error -->
               required>
      </div>

      <!-- Phone Number field (used as username) -->
      <div class="mb-3">
        <label class="form-label">Phone Number</label>
        <input type="tel" name="phone" class="form-control"
               placeholder="e.g. 690123456"
               value="<?php echo e($_POST['phone'] ?? ''); ?>"
               required>
        <small class="text-muted">This will be your login username</small>
      </div>

      <!-- Password field -->
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control"
               placeholder="Minimum 6 characters"
               required>
      </div>

      <!-- Confirm Password field -->
      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password2" class="form-control"
               placeholder="Repeat your password"
               required>
      </div>

      <!-- Terms notice -->
      <p class="small text-muted mb-3">
        By registering you get <strong>5 free orders</strong>. Upgrade anytime for 3,000 FCFA/month.
      </p>

      <!-- Submit button -->
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-person-plus me-2"></i>Create Account
      </button>
    </form>

    <!-- Link to login page -->
    <p class="text-center mt-3 small text-muted">
      Already have an account?
      <a href="login.php" class="text-primary">Login here</a>
    </p>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

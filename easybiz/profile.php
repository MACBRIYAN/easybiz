<?php
// ============================================================
// profile.php — Business Profile Setup / Edit
// User sets their business info used in order messages
// ============================================================
include 'includes/config.php';
require_login();

$user    = get_user($conn, $_SESSION['user_id']);
$profile = get_profile($conn, $user['id']); // may be null if not set yet

$success = '';
$error   = '';

// ============================================================
// Handle form submission
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $bname   = trim($_POST['business_name'] ?? '');
    $address = trim($_POST['address']       ?? '');
    $country = trim($_POST['country']       ?? '');
    $momo    = trim($_POST['momo_number']   ?? '');
    $om      = trim($_POST['om_number']     ?? '');

    if (empty($bname)) {
        $error = "Business name is required.";
    } else {
        if ($profile) {
            // Profile already exists — UPDATE it
            $stmt = $conn->prepare(
                "UPDATE business_profiles
                 SET business_name=?, address=?, country=?, momo_number=?, om_number=?
                 WHERE user_id=?"
            );
            $stmt->bind_param("sssssi", $bname, $address, $country, $momo, $om, $user['id']);
        } else {
            // No profile yet — INSERT new one
            $stmt = $conn->prepare(
                "INSERT INTO business_profiles (user_id, business_name, address, country, momo_number, om_number)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("isssss", $user['id'], $bname, $address, $country, $momo, $om);
        }

        if ($stmt->execute()) {
            $success = "Business profile saved successfully!";
            $profile = get_profile($conn, $user['id']); // reload updated profile
        } else {
            $error = "Failed to save. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Business – EasyBiz</title>
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
    <h5 class="mb-0 fw-bold"><i class="bi bi-shop me-2"></i>My Business Profile</h5>
    <span></span>
  </div>

  <div class="page-content">

    <!-- Success message -->
    <?php if ($success): ?>
      <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i><?php echo e($success); ?>
      </div>
    <?php endif; ?>

    <!-- Error message -->
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo e($error); ?></div>
    <?php endif; ?>

    <!-- Info box if first time -->
    <?php if (!$profile): ?>
      <div class="alert alert-info mb-3">
        <i class="bi bi-info-circle me-2"></i>
        Set up your business profile. This info will appear on all your order messages.
      </div>
    <?php endif; ?>

    <div class="row g-4">

      <!-- Profile form -->
      <div class="col-lg-7">
        <div class="card">
          <div class="card-header">Business Details</div>
          <div class="card-body">

            <form method="POST" action="profile.php">

              <!-- Business Name -->
              <div class="mb-3">
                <label class="form-label">Business Name <span class="text-danger">*</span></label>
                <input type="text" name="business_name" class="form-control"
                       placeholder="e.g. Briyan Store, Sandra Fashion..."
                       value="<?php echo e($profile['business_name'] ?? $_POST['business_name'] ?? ''); ?>"
                       required>
                <small class="text-muted">This appears at the top of every order message</small>
              </div>

              <!-- Physical Address -->
              <div class="mb-3">
                <label class="form-label">Address / Location</label>
                <input type="text" name="address" class="form-control"
                       placeholder="e.g. Bonamoussadi, Douala"
                       value="<?php echo e($profile['address'] ?? ''); ?>">
              </div>

              <!-- Country -->
              <div class="mb-3">
                <label class="form-label">Country</label>
                <input type="text" name="country" class="form-control"
                       placeholder="e.g. Cameroon"
                       value="<?php echo e($profile['country'] ?? 'Cameroon'); ?>">
              </div>

              <!-- Divider for payment section -->
              <hr>
              <h6 class="mb-3 text-muted">Payment Numbers (shown in order messages)</h6>

              <!-- MTN MoMo Number -->
              <div class="mb-3">
                <label class="form-label">MTN MoMo Number</label>
                <div class="input-group">
                  <!-- Input group: prefix + input field side by side -->
                  <span class="input-group-text" style="background:#fbbf24;">📱</span>
                  <input type="tel" name="momo_number" class="form-control"
                         placeholder="e.g. 677123456"
                         value="<?php echo e($profile['momo_number'] ?? ''); ?>">
                </div>
              </div>

              <!-- Orange Money Number -->
              <div class="mb-3">
                <label class="form-label">Orange Money Number</label>
                <div class="input-group">
                  <span class="input-group-text" style="background:#f97316; color:white;">📱</span>
                  <input type="tel" name="om_number" class="form-control"
                         placeholder="e.g. 695123456"
                         value="<?php echo e($profile['om_number'] ?? ''); ?>">
                </div>
              </div>

              <!-- Save button -->
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-2"></i>Save Business Profile
              </button>

            </form>
          </div>
        </div>
      </div>

      <!-- Right: Preview of how message will look -->
      <div class="col-lg-5">
        <div class="card">
          <div class="card-header">
            <i class="bi bi-eye me-2"></i>Message Preview
          </div>
          <div class="card-body">
            <p class="text-muted small mb-2">This is how your messages will look:</p>
            <div class="message-box" style="font-size:0.8rem;">
Hello Sandra,

Your order from <strong style="color:#fbbf24;"><?php echo e($profile['business_name'] ?? 'Your Business Name'); ?></strong>:

Product:    Shoes
Quantity:   3
Unit Price: 15,000 FCFA
Total:      45,000 FCFA

Date: 24 April 2026

Pickup Address:
<?php echo e($profile['address'] ?? 'Your Address'); ?>, <?php echo e($profile['country'] ?? 'Cameroon'); ?>

Please send payment to:
<?php echo $profile['momo_number'] ? "MTN MoMo: " . e($profile['momo_number']) : "MTN MoMo: 6XXXXXXXX"; ?>

Thank you for your purchase! 🙏
            </div>
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

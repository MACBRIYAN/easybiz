<?php
// ============================================================
// add_order.php — Add New Order Form
// Only accessible to logged-in users
// ============================================================
include 'includes/config.php';
require_login(); // redirect if not logged in

$user = get_user($conn, $_SESSION['user_id']);

// ---- Check subscription / free trial access ----
// Users on free plan can only create FREE_LIMIT (5) orders
$can_add = true; // assume they can add by default
$limit_msg = '';

if (!is_premium($user)) {
    // User is on free plan — check how many orders they've used
    if ($user['trial_used'] >= FREE_LIMIT) {
        $can_add  = false; // they've hit the limit
        $limit_msg = "You have reached your free limit of " . FREE_LIMIT . " orders. Please upgrade to continue.";
    }
}

$error   = '';
$success = '';

// ============================================================
// Handle form submission
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $can_add) {

    $customer  = trim($_POST['customer_name']  ?? '');
    $product   = trim($_POST['product_name']   ?? '');
    $quantity  = (int)($_POST['quantity']      ?? 0); // cast to integer
    $unit_price= (float)($_POST['unit_price']  ?? 0); // cast to decimal
    $status    = $_POST['status']              ?? 'pending';
    $order_date= $_POST['order_date']          ?? date('Y-m-d'); // default today

    // Validate required fields
    if (empty($customer) || empty($product)) {
        $error = "Customer name and product name are required.";
    } elseif ($quantity < 1) {
        $error = "Quantity must be at least 1.";
    } elseif ($unit_price <= 0) {
        $error = "Unit price must be greater than 0.";
    } else {
        // Calculate total price automatically
        $total = $quantity * $unit_price; // CORE: auto-calculation

        // Insert order into database
        $stmt = $conn->prepare(
            "INSERT INTO orders (user_id, customer_name, product_name, quantity, unit_price, total_price, status, order_date)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        // "i" = int, "s" = string, "d" = double/decimal
        $stmt->bind_param("issiddss",
            $user['id'], $customer, $product, $quantity, $unit_price, $total, $status, $order_date
        );

        if ($stmt->execute()) {
            // If user is on free plan, increment their trial_used counter
            if (!is_premium($user)) {
                $conn->query("UPDATE users SET trial_used = trial_used + 1 WHERE id = {$user['id']}");
            }

            // Redirect to messages page so user can generate WhatsApp message
            $new_order_id = $conn->insert_id; // get the ID of the newly inserted order
            header("Location: messages.php?order_id=$new_order_id&new=1");
            exit();
        } else {
            $error = "Failed to save order. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Order – EasyBiz</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

  <!-- Top Navbar -->
  <div class="top-navbar">
    <button class="btn btn-light btn-sm d-lg-none" onclick="openSidebar()">
      <i class="bi bi-list fs-5"></i>
    </button>
    <h5 class="mb-0 fw-bold"><i class="bi bi-plus-circle me-2"></i>Add New Order</h5>
    <a href="orders.php" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i> Back to Orders
    </a>
  </div>

  <div class="page-content">

    <!-- Show limit warning if free trial is used up -->
    <?php if (!$can_add): ?>
      <div class="alert alert-danger">
        <i class="bi bi-lock me-2"></i>
        <?php echo e($limit_msg); ?>
        <a href="upgrade.php" class="btn btn-danger btn-sm ms-3">Upgrade Now – 3,000 FCFA</a>
      </div>

    <?php else: ?>

      <!-- Free plan orders remaining notice -->
      <?php if (!is_premium($user)): ?>
        <div class="alert alert-warning">
          <i class="bi bi-info-circle me-2"></i>
          Free Plan: You have used
          <strong><?php echo $user['trial_used']; ?></strong> of
          <strong><?php echo FREE_LIMIT; ?></strong> free orders.
        </div>
      <?php endif; ?>

      <!-- Error message -->
      <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo e($error); ?></div>
      <?php endif; ?>

      <!-- ============================================================
           ADD ORDER FORM
           ============================================================ -->
      <div class="card">
        <div class="card-header">
          <i class="bi bi-cart-plus me-2"></i>Order Details
        </div>
        <div class="card-body">
          <form method="POST" action="add_order.php">

            <div class="row g-3">

              <!-- Customer Name -->
              <div class="col-md-6">
                <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                <input type="text" name="customer_name" class="form-control"
                       placeholder="e.g. Sandra Nkomo"
                       value="<?php echo e($_POST['customer_name'] ?? ''); ?>"
                       required>
              </div>

              <!-- Product Name -->
              <div class="col-md-6">
                <label class="form-label">Product Name <span class="text-danger">*</span></label>
                <input type="text" name="product_name" class="form-control"
                       placeholder="e.g. Shoes, Bag, Phone..."
                       value="<?php echo e($_POST['product_name'] ?? ''); ?>"
                       required>
              </div>

              <!-- Quantity -->
              <div class="col-md-4">
                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                <input type="number" name="quantity" id="quantity" class="form-control"
                       placeholder="e.g. 3" min="1"
                       value="<?php echo e($_POST['quantity'] ?? ''); ?>"
                       oninput="calculateTotal()" <!-- call JS function as user types -->
                       required>
              </div>

              <!-- Unit Price -->
              <div class="col-md-4">
                <label class="form-label">Unit Price (FCFA) <span class="text-danger">*</span></label>
                <input type="number" name="unit_price" id="unit_price" class="form-control"
                       placeholder="e.g. 15000" min="1" step="any"
                       value="<?php echo e($_POST['unit_price'] ?? ''); ?>"
                       oninput="calculateTotal()" <!-- recalculate on change -->
                       required>
              </div>

              <!-- Total (read-only, auto-calculated by JavaScript) -->
              <div class="col-md-4">
                <label class="form-label">Total Price (Auto)</label>
                <input type="text" id="total_display" class="form-control"
                       placeholder="Auto-calculated"
                       readonly  <!-- user cannot edit this field -->
                       style="background:#f8f9fa; font-weight:bold; color:var(--success);">
              </div>

              <!-- Order Date -->
              <div class="col-md-4">
                <label class="form-label">Order Date</label>
                <input type="date" name="order_date" class="form-control"
                       value="<?php echo $_POST['order_date'] ?? date('Y-m-d'); ?>">
                <!-- Defaults to today but user can change it -->
              </div>

              <!-- Payment Status -->
              <div class="col-md-4">
                <label class="form-label">Payment Status</label>
                <select name="status" class="form-select">
                  <!-- Keep selected value after form error -->
                  <option value="pending" <?php echo (($_POST['status'] ?? '') === 'pending') ? 'selected' : ''; ?>>
                    Pending
                  </option>
                  <option value="paid" <?php echo (($_POST['status'] ?? '') === 'paid') ? 'selected' : ''; ?>>
                    Paid
                  </option>
                </select>
              </div>

            </div><!-- end row -->

            <!-- Action Buttons -->
            <div class="d-flex gap-2 mt-4">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-2"></i>Save Order
              </button>
              <a href="orders.php" class="btn btn-outline-secondary">Cancel</a>
            </div>

          </form>
        </div>
      </div>

    <?php endif; ?>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/app.js"></script>
<script>
// ============================================================
// calculateTotal() — runs in the browser as user types
// Gets quantity and unit price, multiplies them, shows result
// ============================================================
function calculateTotal() {
    // Read values from the quantity and price input fields
    var qty   = parseFloat(document.getElementById('quantity').value)   || 0;
    var price = parseFloat(document.getElementById('unit_price').value) || 0;

    // Calculate: Total = Quantity × Unit Price
    var total = qty * price;

    // Show formatted result in the read-only total field
    // toLocaleString adds thousand separators (e.g. 45000 → 45,000)
    if (total > 0) {
        document.getElementById('total_display').value = total.toLocaleString() + ' FCFA';
    } else {
        document.getElementById('total_display').value = ''; // clear if no values yet
    }
}
</script>
</body>
</html>

<?php
// ============================================================
// messages.php — WhatsApp Message Generator
// Generates a professional order message for any order
// ============================================================
include 'includes/config.php';
require_login();

$user    = get_user($conn, $_SESSION['user_id']);
$profile = get_profile($conn, $user['id']); // get business profile

// Get the order ID from the URL: messages.php?order_id=5
$order_id = (int)($_GET['order_id'] ?? 0);

// Variable to hold the fetched order
$order = null;

// Fetch order from DB (must belong to current user)
if ($order_id) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $order  = $result->fetch_assoc();
}

// If order not found or doesn't belong to user, show all orders
if (!$order) {
    // Fetch the user's most recent order as default
    $r = $conn->query("SELECT * FROM orders WHERE user_id={$user['id']} ORDER BY created_at DESC LIMIT 1");
    $order = $r->fetch_assoc();
}

// ============================================================
// BUILD THE WhatsApp MESSAGE
// This is the core message generation logic
// ============================================================
$message = ''; // default empty

if ($order && $profile) {
    // All info is available — generate the full message
    $message = "Hello {$order['customer_name']},\n\n";
    $message .= "Your order from {$profile['business_name']}:\n\n";
    $message .= "Product:    {$order['product_name']}\n";
    $message .= "Quantity:   {$order['quantity']}\n";
    $message .= "Unit Price: " . number_format($order['unit_price'], 0) . " FCFA\n";
    $message .= "Total:      " . number_format($order['total_price'], 0) . " FCFA\n\n";
    $message .= "Date: " . date('d F Y', strtotime($order['order_date'])) . "\n\n";

    // Add address if set in business profile
    if (!empty($profile['address'])) {
        $message .= "Pickup Address:\n";
        $message .= $profile['address'];
        if (!empty($profile['country'])) {
            $message .= ", " . $profile['country'];
        }
        $message .= "\n\n";
    }

    // Add payment number(s) if set
    if (!empty($profile['momo_number'])) {
        $message .= "Please send payment to:\n";
        $message .= "MTN MoMo: " . $profile['momo_number'] . "\n";
    }
    if (!empty($profile['om_number'])) {
        if (empty($profile['momo_number'])) {
            $message .= "Please send payment to:\n";
        }
        $message .= "Orange Money: " . $profile['om_number'] . "\n";
    }

    $message .= "\nThank you for your purchase! 🙏";

} elseif ($order && !$profile) {
    // Order exists but no business profile — partial message
    $message = "Hello {$order['customer_name']},\n\n";
    $message .= "Your order:\n\n";
    $message .= "Product:    {$order['product_name']}\n";
    $message .= "Quantity:   {$order['quantity']}\n";
    $message .= "Unit Price: " . number_format($order['unit_price'], 0) . " FCFA\n";
    $message .= "Total:      " . number_format($order['total_price'], 0) . " FCFA\n\n";
    $message .= "⚠️ Complete your Business Profile to add address & payment details to this message.";
}

// ---- Fetch all orders for the select dropdown ----
$all_orders = $conn->query(
    "SELECT id, customer_name, product_name, order_date FROM orders WHERE user_id={$user['id']} ORDER BY created_at DESC"
);

// Show success notice if redirected here after adding new order
$new_order = isset($_GET['new']) && $_GET['new'] == '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Generate Message – EasyBiz</title>
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
    <h5 class="mb-0 fw-bold"><i class="bi bi-chat-dots me-2"></i>Message Generator</h5>
    <a href="add_order.php" class="btn btn-sm btn-primary">
      <i class="bi bi-plus me-1"></i>New Order
    </a>
  </div>

  <div class="page-content">

    <!-- Success: order just added -->
    <?php if ($new_order): ?>
      <div class="alert alert-success mb-3">
        <i class="bi bi-check-circle me-2"></i>
        Order saved! Here is your WhatsApp message. Copy and send it to your customer.
      </div>
    <?php endif; ?>

    <!-- Warning: no business profile set up yet -->
    <?php if (!$profile): ?>
      <div class="alert alert-warning mb-3">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Your Business Profile is incomplete. Messages won't have your address and payment number.
        <a href="profile.php" class="btn btn-sm btn-warning ms-2">Setup Profile</a>
      </div>
    <?php endif; ?>

    <div class="row g-4">

      <!-- Left: Order selector + message box -->
      <div class="col-lg-7">
        <div class="card">
          <div class="card-header">
            <i class="bi bi-whatsapp me-2 text-success"></i>
            Generated WhatsApp Message
          </div>
          <div class="card-body">

            <!-- Dropdown to choose which order to generate message for -->
            <div class="mb-3">
              <label class="form-label">Select Order</label>
              <select class="form-select" onchange="window.location='messages.php?order_id='+this.value">
                <!-- Reload page with new order_id when user selects different order -->
                <option value="">-- Select an order --</option>
                <?php if ($all_orders->num_rows > 0): ?>
                  <?php while ($ao = $all_orders->fetch_assoc()): ?>
                    <option value="<?php echo $ao['id']; ?>"
                      <?php echo ($order && $ao['id'] == $order['id']) ? 'selected' : ''; ?>>
                      <?php echo e($ao['customer_name']); ?> –
                      <?php echo e($ao['product_name']); ?> –
                      <?php echo date('d M', strtotime($ao['order_date'])); ?>
                    </option>
                  <?php endwhile; ?>
                <?php endif; ?>
              </select>
            </div>

            <!-- The generated message display box -->
            <?php if ($message): ?>
              <div class="message-box" id="messageBox">
                <?php echo e($message); ?>
                <!-- e() escapes HTML but keeps line breaks via pre-wrap CSS -->
              </div>

              <!-- Copy to clipboard button -->
              <div class="d-flex gap-2 mt-3">
                <button class="btn-copy" onclick="copyMessage()">
                  <i class="bi bi-clipboard me-2"></i>Copy Message
                </button>
                <!-- WhatsApp link (opens WhatsApp with message pre-filled) -->
                <a href="https://wa.me/?text=<?php echo rawurlencode($message); ?>"
                   target="_blank"
                   class="btn btn-success">
                  <i class="bi bi-whatsapp me-2"></i>Open in WhatsApp
                </a>
              </div>

              <!-- Copy confirmation (hidden until button clicked) -->
              <div id="copyConfirm" style="display:none;" class="alert alert-success mt-2 py-2">
                ✅ Message copied! Paste it into WhatsApp.
              </div>

            <?php else: ?>
              <!-- No orders yet -->
              <div class="text-center py-4">
                <i class="bi bi-chat-x" style="font-size:3rem; color:var(--border);"></i>
                <p class="text-muted mt-2">
                  No orders found. <a href="add_order.php">Add an order first</a>.
                </p>
              </div>
            <?php endif; ?>

          </div>
        </div>
      </div>

      <!-- Right: Order details summary -->
      <div class="col-lg-5">
        <?php if ($order): ?>
          <div class="card">
            <div class="card-header">
              <i class="bi bi-receipt me-2"></i>Order Summary
            </div>
            <div class="card-body">
              <!-- Order details summary -->
              <table class="table table-borderless mb-0">
                <tr>
                  <td class="text-muted">Customer</td>
                  <td><strong><?php echo e($order['customer_name']); ?></strong></td>
                </tr>
                <tr>
                  <td class="text-muted">Product</td>
                  <td><?php echo e($order['product_name']); ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Quantity</td>
                  <td><?php echo $order['quantity']; ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Unit Price</td>
                  <td><?php echo number_format($order['unit_price'], 0); ?> FCFA</td>
                </tr>
                <tr class="border-top">
                  <td class="text-muted">Total</td>
                  <td><strong class="text-success fs-5"><?php echo number_format($order['total_price'], 0); ?> FCFA</strong></td>
                </tr>
                <tr>
                  <td class="text-muted">Date</td>
                  <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Status</td>
                  <td>
                    <?php if ($order['status'] === 'paid'): ?>
                      <span class="badge-paid">Paid</span>
                    <?php else: ?>
                      <span class="badge-pending">Pending</span>
                    <?php endif; ?>
                  </td>
                </tr>
              </table>
            </div>
          </div>
        <?php endif; ?>

        <!-- Business profile quick view -->
        <?php if ($profile): ?>
          <div class="card mt-3">
            <div class="card-header">
              <i class="bi bi-shop me-2"></i>Your Business Info
            </div>
            <div class="card-body">
              <p class="mb-1"><strong><?php echo e($profile['business_name']); ?></strong></p>
              <p class="mb-1 text-muted small"><?php echo e($profile['address'] ?? '—'); ?></p>
              <p class="mb-1 text-muted small"><?php echo e($profile['country'] ?? '—'); ?></p>
              <?php if ($profile['momo_number']): ?>
                <p class="mb-1 small"><i class="bi bi-phone me-1"></i>MTN: <?php echo e($profile['momo_number']); ?></p>
              <?php endif; ?>
              <a href="profile.php" class="btn btn-sm btn-outline-secondary mt-2">Edit Profile</a>
            </div>
          </div>
        <?php endif; ?>

      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/app.js"></script>
<script>
// ============================================================
// copyMessage() — copies the message text to clipboard
// ============================================================
function copyMessage() {
    var box = document.getElementById('messageBox');

    // Get the text content (innerText preserves line breaks)
    var text = box.innerText;

    // Use the modern Clipboard API to copy
    navigator.clipboard.writeText(text).then(function() {
        // Show confirmation message for 3 seconds
        var confirm = document.getElementById('copyConfirm');
        confirm.style.display = 'block';
        setTimeout(function() {
            confirm.style.display = 'none'; // hide after 3 seconds
        }, 3000);
    }).catch(function() {
        // Fallback for older browsers: select all text and copy
        var range = document.createRange();
        range.selectNode(box);
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(range);
        document.execCommand('copy'); // old way to copy
        window.getSelection().removeAllRanges();
        alert('Message copied!'); // simple alert as fallback
    });
}
</script>
</body>
</html>

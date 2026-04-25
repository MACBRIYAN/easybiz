<?php
// ============================================================
// includes/sidebar.php
// Reusable sidebar navigation — include this on every dashboard page
// Requires: $user array must be set in the calling page
// ============================================================
?>

<!-- Dark overlay shown on mobile when sidebar is open -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- ============================================================
     SIDEBAR
     ============================================================ -->
<nav class="sidebar" id="sidebar">

  <!-- Logo at top of sidebar -->
  <div class="sidebar-logo">
    <h4>Easy<span>Biz</span></h4>
    <!-- Display business name if profile set, otherwise user name -->
    <small style="color:rgba(255,255,255,0.5); font-size:0.75rem;">
      <?php
      // Get the business profile for the current user
      $bp = get_profile($conn, $_SESSION['user_id']);
      echo e($bp ? $bp['business_name'] : $_SESSION['user_name']);
      // If business profile exists, show business name; else show user name
      ?>
    </small>
  </div>

  <!-- Navigation links -->
  <div class="sidebar-nav">

    <!-- Get current page filename to highlight active link -->
    <?php $current = basename($_SERVER['PHP_SELF']); // e.g. "dashboard.php" ?>

    <!-- Dashboard link -->
    <a href="dashboard.php" class="<?php echo $current === 'dashboard.php' ? 'active' : ''; ?>">
      <i class="bi bi-house"></i> Dashboard
    </a>

    <!-- Orders link -->
    <a href="orders.php" class="<?php echo $current === 'orders.php' ? 'active' : ''; ?>">
      <i class="bi bi-cart"></i> Orders
    </a>

    <!-- Add Order link -->
    <a href="add_order.php" class="<?php echo $current === 'add_order.php' ? 'active' : ''; ?>">
      <i class="bi bi-plus-circle"></i> Add Order
    </a>

    <!-- Messages link -->
    <a href="messages.php" class="<?php echo $current === 'messages.php' ? 'active' : ''; ?>">
      <i class="bi bi-chat-dots"></i> Messages
    </a>

    <!-- Business Profile link -->
    <a href="profile.php" class="<?php echo $current === 'profile.php' ? 'active' : ''; ?>">
      <i class="bi bi-shop"></i> My Business
    </a>

    <!-- Subscription / Upgrade link -->
    <a href="upgrade.php" class="<?php echo $current === 'upgrade.php' ? 'active' : ''; ?>">
      <i class="bi bi-star"></i> Upgrade
    </a>
    <a href="Reviews.php" class="<?php echo $current === 'Reviews.php' ? 'active' : ''; ?>">
      <i class="bi bi-pencil-square"></i> Reviews
    </a>

    <!-- Divider line -->
    <hr style="border-color:rgba(255,255,255,0.1); margin:10px 20px;">

    <!-- Logout link -->
    <a href="logout.php">
      <i class="bi bi-box-arrow-left"></i> Logout
    </a>

  </div>

  <!-- Subscription status badge at bottom of sidebar -->
  <div style="padding:15px 20px; margin-top:auto;">
    <?php if (is_premium($user)): ?>
      <!-- Show green badge if premium and not expired -->
      <div style="background:rgba(52,168,83,0.2); color:#4ade80; padding:8px 12px; border-radius:8px; font-size:0.75rem;">
        <i class="bi bi-star-fill me-1"></i>
        Premium · Expires <?php echo date('d M Y', strtotime($user['sub_end'])); ?>
      </div>
    <?php else: ?>
      <!-- Show upgrade prompt if on free plan -->
      <div style="background:rgba(251,188,4,0.2); color:#fbbf24; padding:8px 12px; border-radius:8px; font-size:0.75rem;">
        <i class="bi bi-lightning me-1"></i>
        Free Plan · <a href="upgrade.php" style="color:#fbbf24; text-decoration:underline;">Upgrade</a>
      </div>
    <?php endif; ?>
  </div>

</nav>

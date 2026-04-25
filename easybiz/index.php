<?php
// ============================================================
// index.php — Landing Page
// This is the first page visitors see before logging in
// ============================================================
include 'includes/config.php'; // load DB connection, session, helpers

// If user is already logged in, redirect them to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <!-- Makes site mobile-friendly (responsive viewport) -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EasyBiz – Track Sales, Grow Your Business</title>

  <!-- Bootstrap 5 CSS (from CDN — requires internet) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

  <!-- Bootstrap Icons (for icons throughout the site) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

  <!-- Our custom stylesheet -->
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- ============================================================
     NAVBAR — top navigation bar
     ============================================================ -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background:var(--dark); position:sticky; top:0; z-index:100;">
  <div class="container">
    <!-- Brand / Logo -->
    <a class="navbar-brand fw-bold fs-4" href="index.php">
      Easy<span style="color:var(--accent)">Biz</span>
    </a>

    <!-- Mobile toggle button (hamburger) — shows/hides nav links on mobile -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Collapsible nav links -->
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto gap-2"> <!-- ms-auto pushes to right -->
        <li class="nav-item">
          <!-- Login button -->
          <a class="btn btn-outline-light btn-sm" href="login.php">Login</a>
        </li>
        <li class="nav-item">
          <!-- Sign up button — accent color to stand out -->
          <a class="btn btn-warning btn-sm fw-bold" href="register.php">Start Free</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- ============================================================
     HERO SECTION — main headline and call to action
     ============================================================ -->
<section class="hero-section">
  <div class="container">
    <div class="row align-items-center g-5">

      <!-- Left column: text content -->
      <div class="col-lg-6">
        <!-- Small label above headline -->
        <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill">
          🚀 Free to Start
        </span>

        <!-- Main headline -->
        <h1>Run Your Business <br><span style="color:var(--accent)">Smarter</span></h1>

        <!-- Subtitle -->
        <p class="lead mt-3">
          Track sales, calculate totals automatically, and send professional
          order messages to customers via WhatsApp — all in one place.
        </p>

        <!-- Key benefit bullet points -->
        <ul class="list-unstyled mt-4 mb-4">
          <li class="mb-2">
            <i class="bi bi-check-circle-fill text-warning me-2"></i>
            Track every order & payment
          </li>
          <li class="mb-2">
            <i class="bi bi-check-circle-fill text-warning me-2"></i>
            Auto-calculate totals (no more mistakes)
          </li>
          <li class="mb-2">
            <i class="bi bi-check-circle-fill text-warning me-2"></i>
            Generate WhatsApp messages instantly
          </li>
          <li class="mb-2">
            <i class="bi bi-check-circle-fill text-warning me-2"></i>
            Manage your business from your phone
          </li>
        </ul>

        <!-- CTA buttons -->
        <div class="d-flex gap-3 flex-wrap">
          <a href="register.php" class="btn btn-warning btn-lg fw-bold px-5">
            Get Started Free
          </a>
          <a href="login.php" class="btn btn-outline-light btn-lg px-4">
            Login
          </a>
        </div>

        <!-- Small reassurance text below buttons -->
        <p class="mt-3 text-white-50 small">
          ✅ 5 free orders • No credit card needed
        </p>
      </div>

      <!-- Right column: visual mock of the dashboard -->
      <div class="col-lg-6">
        <div class="card p-4" style="border-radius:16px; background:rgba(255,255,255,0.95);">
          <h6 class="text-muted mb-3">📦 Order Summary</h6>
          <!-- Mock order row 1 -->
          <div class="d-flex justify-content-between align-items-center p-3 mb-2"
               style="background:#f8f9fa; border-radius:10px;">
            <div>
              <strong>Sandra – Shoes</strong><br>
              <small class="text-muted">Qty: 3 × 15,000 FCFA</small>
            </div>
            <div class="text-end">
              <strong class="text-success">45,000 FCFA</strong><br>
              <span class="badge-paid">Paid</span>
            </div>
          </div>
          <!-- Mock order row 2 -->
          <div class="d-flex justify-content-between align-items-center p-3 mb-2"
               style="background:#f8f9fa; border-radius:10px;">
            <div>
              <strong>Kevin – Bag</strong><br>
              <small class="text-muted">Qty: 1 × 8,500 FCFA</small>
            </div>
            <div class="text-end">
              <strong style="color:var(--danger)">8,500 FCFA</strong><br>
              <span class="badge-pending">Pending</span>
            </div>
          </div>
          <!-- Total row -->
          <div class="d-flex justify-content-between mt-3 pt-3 border-top">
            <strong>Total Today</strong>
            <strong class="text-primary">53,500 FCFA</strong>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ============================================================
     FEATURES SECTION
     ============================================================ -->
<section class="py-5" style="background:var(--bg);">
  <div class="container">
    <!-- Section header -->
    <div class="text-center mb-5">
      <h2 class="fw-bold">Everything you need to grow</h2>
      <p class="text-muted">Built for WhatsApp sellers, shop owners and market traders</p>
    </div>

    <!-- 3-column feature cards -->
    <div class="row g-4">

      <!-- Feature 1 -->
      <div class="col-md-4">
        <div class="feature-card">
          <div class="icon"><i class="bi bi-cart-check"></i></div>
          <h5 class="fw-bold">Order Tracking</h5>
          <p class="text-muted">Log every sale with customer name, product, quantity and price. Never lose track of an order again.</p>
        </div>
      </div>

      <!-- Feature 2 -->
      <div class="col-md-4">
        <div class="feature-card">
          <div class="icon"><i class="bi bi-calculator"></i></div>
          <h5 class="fw-bold">Auto Calculation</h5>
          <p class="text-muted">Enter quantity and unit price — the system calculates your total automatically. Zero math errors.</p>
        </div>
      </div>

      <!-- Feature 3 -->
      <div class="col-md-4">
        <div class="feature-card">
          <div class="icon"><i class="bi bi-chat-dots"></i></div>
          <h5 class="fw-bold">WhatsApp Messages</h5>
          <p class="text-muted">Generate professional order confirmation messages with one click. Copy and send to customers instantly.</p>
        </div>
      </div>

      <!-- Feature 4 -->
      <div class="col-md-4">
        <div class="feature-card">
          <div class="icon"><i class="bi bi-shop"></i></div>
          <h5 class="fw-bold">Business Profile</h5>
          <p class="text-muted">Add your business name, address and payment details. They appear on all your order messages.</p>
        </div>
      </div>

      <!-- Feature 5 -->
      <div class="col-md-4">
        <div class="feature-card">
          <div class="icon"><i class="bi bi-graph-up"></i></div>
          <h5 class="fw-bold">Sales Dashboard</h5>
          <p class="text-muted">See your total, paid and pending orders at a glance. Know exactly how your business is doing.</p>
        </div>
      </div>

      <!-- Feature 6 -->
      <div class="col-md-4">
        <div class="feature-card">
          <div class="icon"><i class="bi bi-shield-lock"></i></div>
          <h5 class="fw-bold">Secure & Private</h5>
          <p class="text-muted">Your data is yours only. Password protection and account recovery via OTP code.</p>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ============================================================
     PRICING SECTION
     ============================================================ -->
<section class="py-5" style="background:white;">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Simple Pricing</h2>
      <p class="text-muted">Start free, upgrade when you're ready</p>
    </div>

    <div class="row justify-content-center g-4">

      <!-- Free Plan -->
      <div class="col-md-5">
        <div class="card h-100 p-4">
          <h5 class="fw-bold">Free Trial</h5>
          <div class="display-4 fw-bold my-3">0 <span class="fs-5 text-muted">FCFA</span></div>
          <ul class="list-unstyled">
            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>5 free orders</li>
            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Order tracking</li>
            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Auto calculation</li>
            <li class="mb-2 text-muted"><i class="bi bi-x me-2"></i>Message generation</li>
            <li class="mb-2 text-muted"><i class="bi bi-x me-2"></i>Unlimited orders</li>
          </ul>
          <a href="register.php" class="btn btn-outline-primary mt-auto">Start Free</a>
        </div>
      </div>

      <!-- Premium Plan — highlighted -->
      <div class="col-md-5">
        <div class="pricing-card h-100">
          <!-- "Most Popular" badge -->
          <span class="badge bg-warning text-dark mb-3">⭐ Most Popular</span>
          <h5 class="fw-bold">Premium</h5>
          <div class="display-4 fw-bold my-3">
            3,000 <span class="fs-5 opacity-75">FCFA/month</span>
          </div>
          <ul class="list-unstyled text-start">
            <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i>Unlimited orders</li>
            <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i>WhatsApp messages</li>
            <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i>Business profile</li>
            <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i>Sales dashboard</li>
            <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i>30-day access</li>
          </ul>
          <a href="register.php" class="btn btn-warning fw-bold mt-3 px-4">Get Premium</a>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer style="background:var(--dark); color:rgba(255,255,255,0.6);" class="py-4 text-center">
  <p class="mb-1">
    &copy; <?php echo date('Y'); ?> <!-- auto year from PHP -->
    Easy<strong class="text-warning">Biz</strong> — Built for African small businesses
  </p>
  <p class="small">Need help? Contact: +237 6XX XXX XXX</p>
</footer>

<!-- Bootstrap 5 JS Bundle (includes Popper for dropdowns, modals) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

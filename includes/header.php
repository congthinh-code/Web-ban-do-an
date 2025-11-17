<?php
// header.php - include this at top of pages where header needed
if (session_status() === PHP_SESSION_NONE) session_start();
//include_once "webbandoan.php"; // phải trả về $conn (MySQLi)

// ----- Lấy thông tin user -----
$userInfo = null;
if (!empty($_SESSION['user_id']) && isset($conn)) {
  $uid = intval($_SESSION['user_id']);
  $sql = "SELECT id, username, email, avatar FROM users WHERE id = ?";
  if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
      $userInfo = $res->fetch_assoc();
    }
    $stmt->close();
  }
}

// ----- Đếm giỏ hàng (session) -----
$cartCount = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $it) {
    $qty = isset($it['qty']) ? intval($it['qty']) : 1;
    $cartCount += max(0, $qty);
  }
}

// ----- Lấy thông báo (nếu có bảng notifications) -----
$notifCount = 0;
$notifs = [];
if (isset($conn) && !empty($userInfo)) {
  $uid = intval($userInfo['id']);
  $sqlCount = "SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = ? AND is_read = 0";
  if ($stmt = $conn->prepare($sqlCount)) {
    $stmt->bind_param("i", $uid);
    if ($stmt->execute()) {
      $r = $stmt->get_result();
      if ($r && $row = $r->fetch_assoc()) $notifCount = intval($row['cnt']);
    }
    $stmt->close();
  }
  $sqlList = "SELECT id, title, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
  if ($stmt = $conn->prepare($sqlList)) {
    $stmt->bind_param("i", $uid);
    if ($stmt->execute()) {
      $r = $stmt->get_result();
      if ($r) {
        while ($row = $r->fetch_assoc()) $notifs[] = $row;
      }
    }
    $stmt->close();
  }
}

// đường dẫn resources
$cssPath = "/assets/css/header.css";
$jsPath  = "/assets/js/header.js";
$logo    = "/images/logo.png";
$avatarDefault = "images/default-avatar.png";
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Ăn Húp Hội</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars($cssPath); ?>">
</head>
<body>

<header class="site-header" role="banner">
  <div class="header-inner">

    <!-- LEFT: logo -->
    <div class="header-left">
      <a href="../index.php" class="brand-link">
        <?php if (file_exists($logo)): ?>
          <img src="<?php echo htmlspecialchars($logo); ?>" alt="Logo" class="brand-logo">
        <?php else: ?>
          <div class="brand-icon">🍲</div>
        <?php endif; ?>
        <div class="brand-text">
          <span class="brand-name">Ăn Húp Hội</span>
          <small class="brand-slogan">Deals &amp; Món ngon</small>
        </div>
      </a>
    </div>

    <!-- CENTER: nav + search -->
    <div class="header-center">
      <nav class="main-nav" aria-label="Primary navigation">
        <ul class="nav-list">
          <li><a href="index.php">Trang chủ</a></li>
          <li><a href="menu.php">Thực đơn</a></li>
          <li><a href="deals.php">Khuyến mãi</a></li>
          <li><a href="news.php">Tin tức</a></li>
        </ul>
      </nav>

      <div class="search-wrap">
        <input id="header-search" class="search-input" type="search" placeholder="Tìm món,..." aria-label="Tìm kiếm">
        <button id="search-btn" class="search-btn" aria-label="Tìm">🔍</button>
        <ul id="search-suggestions" class="search-suggestions" role="listbox"></ul>
      </div>
    </div>

    <!-- RIGHT: actions -->
    <div class="header-right">

      <!-- notifications -->
      <div class="action-item dropdown" id="notifWrap">
        <button id="notifBtn" class="btn-ghost" aria-haspopup="true" aria-expanded="false" aria-label="Thông báo">
          🔔
          <?php if ($notifCount > 0): ?>
            <span class="badge" id="notifBadge"><?php echo $notifCount; ?></span>
          <?php endif; ?>
        </button>

        <div class="dropdown-panel" id="notifPanel" role="menu" aria-hidden="true">
          <div class="dropdown-head">Thông báo</div>
          <?php if (count($notifs) === 0): ?>
            <div class="dropdown-empty">Không có thông báo</div>
          <?php else: ?>
            <ul class="notif-list">
              <?php foreach ($notifs as $n): ?>
                <li class="notif-item <?php echo $n['is_read'] ? 'read' : 'unread'; ?>">
                  <div class="notif-title"><?php echo htmlspecialchars($n['title']); ?></div>
                  <div class="notif-time"><?php echo htmlspecialchars($n['created_at']); ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
            <a class="dropdown-foot" href="notifications.php">Xem tất cả</a>
          <?php endif; ?>
        </div>
      </div>

      <!-- cart -->
      <div class="action-item">
        <a href="/pages/cart.php" class="btn-ghost cart-btn" aria-label="Giỏ hàng">
          🛒
          <?php if ($cartCount > 0): ?>
            <span class="badge" id="cartBadge"><?php echo $cartCount; ?></span>
          <?php endif; ?>
        </a>
      </div>

      <!-- account -->
      <div class="action-item dropdown" id="accountWrap">
        <?php if ($userInfo): ?>
          <button id="accountBtn" class="account-btn" aria-haspopup="true" aria-expanded="false">
            <img class="avatar" src="<?php echo (!empty($userInfo['avatar']) && file_exists($userInfo['avatar'])) ? htmlspecialchars($userInfo['avatar']) : $avatarDefault; ?>" alt="avatar">
            <span class="username"><?php echo htmlspecialchars($userInfo['username']); ?></span>
          </button>

          <div class="dropdown-panel" id="accountPanel" role="menu" aria-hidden="true">
            <a class="dropdown-item" href="profile.php">Hồ sơ</a>
            <a class="dropdown-item" href="orders.php">Đơn hàng</a>
            <a class="dropdown-item" href="auth/logout.php">Đăng xuất</a>
          </div>
        <?php else: ?>
          <a href="../pages/login.php" class="btn btn-primary">Đăng nhập</a>
        <?php endif; ?>
      </div>

      <!-- language -->
      <div class="action-item">
        <button id="langToggle" class="btn-ghost" aria-label="Đổi ngôn ngữ">🇻🇳</button>
      </div>

      <!-- mobile menu toggle -->
      <div class="action-item mobile-only">
        <button id="mobileToggle" class="btn-ghost" aria-label="Mở menu">☰</button>
      </div>
    </div>
  </div>

  <!-- mobile slide menu -->
  <div id="mobileMenu" class="mobile-menu" aria-hidden="true">
    <div class="mobile-inner">
      <button id="mobileClose" class="mobile-close" aria-label="Đóng">✕</button>
      <nav class="mobile-nav">
        <a href="index.php">Trang chủ</a>
        <a href="menu.php">Thực đơn</a>
        <a href="deals.php">Khuyến mãi</a>
        <a href="news.php">Tin tức</a>
        <?php if ($userInfo): ?>
          <a href="profile.php">Hồ sơ</a>
          <a href="orders.php">Đơn hàng</a>
          <a href="auth/logout.php">Đăng xuất</a>
        <?php else: ?>
          <a href="auth/login.php">Đăng nhập</a>
          <a href="auth/register.php">Đăng ký</a>
        <?php endif; ?>
      </nav>
    </div>
  </div>
</header>

<script src="<?php echo htmlspecialchars($jsPath); ?>"></script>
</body>
</html>

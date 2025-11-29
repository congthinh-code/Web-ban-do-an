<?php
session_start();
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../functions/functions.php';

// Lấy id món từ query ?mamon= hoặc ?id=
$mamon = 0;
if (isset($_GET['mamon'])) {
    $mamon = (int)$_GET['mamon'];
} elseif (isset($_GET['id'])) {
    $mamon = (int)$_GET['id'];
}

if ($mamon <= 0) {
    // Không có id món → quay về khuyến mãi
    header("Location: deals.php");
    exit;
}

// Lấy thông tin món ăn
$sql = "SELECT Mamon, Tenmon, Giaban, Giagoc, Noidung, Anh 
        FROM Monan 
        WHERE Mamon = ? 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $mamon);
$stmt->execute();
$res = $stmt->get_result();
$product = $res->fetch_assoc();
$stmt->close();

if (!$product) {
    // Không tìm thấy món → quay về khuyến mãi
    header("Location: deals.php");
    exit;
}

$giaban = (float)$product['Giaban'];
$giagoc = (float)$product['Giagoc'];

// Tính % giảm
$discountPercent = 0;
if ($giagoc > 0 && $giaban < $giagoc) {
    $discountPercent = round(($giagoc - $giaban) / $giagoc * 100);
}

// Chuẩn hoá đường dẫn ảnh: trong DB đang là "assets/img/xxx"
$imgPath = '/' . ltrim($product['Anh'] ?? 'assets/img/default.jpg', '/');

// URL hiện tại (để return_url khi thêm giỏ hàng)
$currentUrl = $_SERVER['REQUEST_URI'] ?? '/pages/deals.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['Tenmon']); ?> - Khuyến mãi</title>
    <link rel="stylesheet" href="/assets/css/chitietmonan.css">
</head>
<body>
    <!-- Header -->
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="container">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="/index.php">Trang chủ</a> /
            <a href="/pages/deals.php">Khuyến mãi</a> /
            <span><?php echo htmlspecialchars($product['Tenmon']); ?></span>
        </div>

        <!-- Product Card -->
        <div class="product-card">
            <!-- Header -->
            <div class="header">
                <a href="/pages/deals.php">
                    <button class="back-btn">
                        <span>←</span>
                        <span>Quay lại khuyến mãi</span>
                    </button>
                </a>
                <div class="header-actions">
                    <button class="icon-btn" id="favoriteBtn">♥</button>
                </div>
            </div>

            <!-- Product Content -->
            <div class="product-content">
                <!-- Gallery -->
                <div class="gallery">
                    <div class="main-image">
                        <img id="mainImage"
                             src="<?php echo htmlspecialchars($imgPath); ?>"
                             alt="<?php echo htmlspecialchars($product['Tenmon']); ?>">
                        <?php if ($discountPercent > 0): ?>
                            <div class="discount-badge">-<?php echo $discountPercent; ?>%</div>
                        <?php endif; ?>
                    </div>

                    <div class="thumbnails">
                        <div class="thumbnail active" data-index="0">
                            <img src="<?php echo htmlspecialchars($imgPath); ?>"
                                 alt="Thumb">
                        </div>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="product-info">
                    <h1 class="product-title">
                        <?php echo htmlspecialchars($product['Tenmon']); ?>
                    </h1>
                    
                    <div class="rating">
                        <div class="stars">★★★★★</div>
                        <span class="rating-text">4.8 (234 đánh giá)</span>
                    </div>

                    <div class="price-section">
                        <span class="current-price">
                            <?php echo number_format($giaban, 0, ',', '.'); ?>₫
                        </span>

                        <?php if ($giaban < $giagoc): ?>
                            <span class="original-price">
                                <?php echo number_format($giagoc, 0, ',', '.'); ?>₫
                            </span>
                        <?php endif; ?>
                    </div>

                    <p class="description">
                        <?php echo htmlspecialchars($product['Noidung'] ?? ''); ?>
                    </p>

                    <div class="quantity-selector">
                        <div class="quantity-label">Số lượng:</div>
                        <div class="quantity-controls">
                            <button class="quantity-btn" id="decreaseBtn">−</button>
                            <span class="quantity-value" id="quantityValue">1</span>
                            <button class="quantity-btn" id="increaseBtn">+</button>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <!-- Thêm vào giỏ: mặc định +1, cho phép return về lại trang khuyến mãi/chi tiết -->
                        <a class="add-to-cart-btn"
                           href="/pages/cart.php?add=<?php echo (int)$product['Mamon']; ?>&return_url=<?php echo urlencode($currentUrl); ?>">
                            <span>🛒</span>
                            <span>Thêm vào giỏ hàng</span>
                        </a>
                    </div>

                    <div class="features">
                        <div class="feature">
                            <div class="feature-icon">🚚</div>
                            <div class="feature-text">Giao hàng nhanh 30 phút</div>
                        </div>
                        <div class="feature">
                            <div class="feature-icon">🛡️</div>
                            <div class="feature-text">Đảm bảo chất lượng</div>
                        </div>
                        <div class="feature">
                            <div class="feature-icon">♻️</div>
                            <div class="feature-text">Đổi trả trong 24h</div>
                        </div>
                        <div class="feature">
                            <div class="feature-icon">⭐</div>
                            <div class="feature-text">Cam kết 100% tươi</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nếu muốn thêm tab dinh dưỡng / bình luận sau này thì bỏ comment ra -->
            <!--
            <div class="tabs">
                <div class="tab-buttons">
                    <button class="tab-btn active" data-tab="nutrition">Thông tin dinh dưỡng</button>
                </div>

                <div class="tab-content active" id="nutrition">
                    ...
                </div>
            </div>
            -->
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>

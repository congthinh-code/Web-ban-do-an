<?php
// Load product details from DB for this page
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../functions/functions.php';

$mamon = isset($_GET['mamon']) ? intval($_GET['mamon']) : 0;
$product = null;
$images = [];
$discountPercent = null;
$originalPrice = null;
$currentPrice = 0;

if ($mamon > 0) {
    $sql = "SELECT Mamon, Tenmon, Giaban, Anh, Noidung, COALESCE(Giagoc, 0) AS Giagoc FROM Monan WHERE Mamon = ? LIMIT 1";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $mamon);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $product = $res->fetch_assoc();
            // parse images: support '|' delimited list or single path
            if (!empty($product['Anh'])) {
                if (strpos($product['Anh'], '|') !== false) {
                    $images = array_filter(array_map('trim', explode('|', $product['Anh'])));
                } else {
                    $images = [trim($product['Anh'])];
                }
            }
            // pricing
            $originalPrice = !empty($product['Giagoc']) ? floatval($product['Giagoc']) : 0;
            $currentPrice = floatval($product['Giaban']);
            if ($originalPrice > 0 && $originalPrice > $currentPrice) {
                $discountPercent = round((($originalPrice - $currentPrice) / $originalPrice) * 100);
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Sản Phẩm</title>
    <link rel="stylesheet" href="/assets/css/chitietmonan.css">
</head>
<body>
    <!-- Top Header -->
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <!--<div class="top-header">
        <div class="header-content">
            <div class="logo">
                <div class="logo-icon">🍜</div>
                <div class="logo-text">
                    <h1>Ăn Húp Hội</h1>
                    <p>Deals & Món ngon</p>
                </div>
            </div>
            
            <nav class="nav-menu">
                <a href="#" class="nav-link">Trang chủ</a>
                <a href="#" class="nav-link">Thực đơn</a>
                <a href="#" class="nav-link">Khuyến mãi</a>
                <a href="#" class="nav-link">Tin tức</a>
            </nav>

            <div class="search-box">
                <input type="text" placeholder="Tìm món...">
                <button>🔍</button>
            </div>

            <div class="header-actions">
                <button class="icon-btn">🔔</button>
                <button class="icon-btn">🛒</button>
                <button class="auth-btn">Đăng nhập</button>
                <button class="auth-btn">Đăng ký</button>
                <button class="lang-btn">VN</button>
            </div>
        </div>
    </div>-->

    <!-- Container -->
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="../index.php">Trang chủ</a> / <a href="#">Thực đơn</a> / <!--<span>Bánh Mì Phô Mai Bơ Tỏi</span>-->
        </div>

        <!-- Product Card -->
        <div class="product-card">
        <!-- Header -->
        <div class="header">
            <a href = '/index.php'>
                <button class="back-btn">
                    <span>←</span>
                    <span>Quay lại menu</span>
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
                    <?php if ($product && !empty($images)): ?>
                        <img id="mainImage" src="<?php echo htmlspecialchars(resolveImagePath($images[0])); ?>" alt="<?php echo htmlspecialchars($product['Tenmon']); ?>">
                    <?php else: ?>
                        <img id="mainImage" src="/assets/img/default-food.jpg" alt="Sản phẩm">
                    <?php endif; ?>
                    <?php if ($discountPercent !== null): ?>
                        <div class="discount-badge">-<?php echo $discountPercent; ?>%</div>
                    <?php endif; ?>
                </div>
                <div class="thumbnails">
                    <?php if (!empty($images)): ?>
                        <?php foreach ($images as $idx => $img): ?>
                            <div class="thumbnail <?php echo $idx === 0 ? 'active' : ''; ?>" data-index="<?php echo $idx; ?>">
                                <img src="<?php echo htmlspecialchars(resolveImagePath($img)); ?>" alt="Thumb <?php echo $idx+1; ?>">
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="thumbnail active" data-index="0">
                            <img src="/assets/img/default-food.jpg" alt="Thumb 1">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Info -->
            <div class="product-info">
                <h1 class="product-title"><?php echo $product ? htmlspecialchars($product['Tenmon']) : 'Sản phẩm'; ?></h1>
                
                <div class="rating">
                    <div class="stars">★★★★★</div>
                    <span class="rating-text">4.8 (234 đánh giá)</span>
                </div>

                <div class="price-section">
                    <span class="current-price"><?php echo $product ? number_format(floatval($product['Giaban']),0,',','.').'₫' : ''; ?></span>
                    <?php if (!empty($originalPrice) && $originalPrice > floatval($product['Giaban'])): ?>
                        <span class="original-price"><?php echo number_format($originalPrice,0,',','.'); ?>₫</span>
                    <?php endif; ?>
                </div>

                <p class="description">
                    <?php echo $product ? nl2br(htmlspecialchars($product['Noidung'])) : 'Mô tả sản phẩm chưa có.'; ?>
                </p>

                <!--<div class="size-selector">
                    <div class="size-label">Chọn kích cỡ:</div>
                    <div class="size-options">
                        <button class="size-btn" data-size="S">S</button>
                        <button class="size-btn active" data-size="M">M</button>
                        <button class="size-btn" data-size="L">L</button>
                    </div>
                </div>-->

                <div class="quantity-selector">
                    <div class="quantity-label">Số lượng:</div>
                    <div class="quantity-controls">
                        <button class="quantity-btn" id="decreaseBtn">−</button>
                        <span class="quantity-value" id="quantityValue">1</span>
                        <button class="quantity-btn" id="increaseBtn">+</button>
                    </div>
                </div>

                <div class="action-buttons">
                    <?php if ($product): ?>
                        <a class="add-to-cart-btn" href="/pages/cart.php?add=<?php echo intval($mamon); ?>&return_url=/pages/cart.php">
                            <span>🛒</span>
                            <span>Thêm vào giỏ hàng</span>
                        </a>
                    <?php else: ?>
                        <button class="add-to-cart-btn" disabled>
                            <span>🛒</span>
                            <span>Không có sản phẩm</span>
                        </button>
                    <?php endif; ?>
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

        <!-- Tabs -->
        <!--<div class="tabs">
            <div class="tab-buttons">
                <button class="tab-btn active" data-tab="nutrition">Thông tin dinh dưỡng</button>
            </div>

            <div class="tab-content active" id="nutrition">
                <div class="nutrition-grid">
                    <div class="nutrition-item">
                        <div class="nutrition-value">320</div>
                        <div class="nutrition-label">Calories (kcal)</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="nutrition-value">12g</div>
                        <div class="nutrition-label">Protein</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="nutrition-value">38g</div>
                        <div class="nutrition-label">Carbs</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="nutrition-value">14g</div>
                        <div class="nutrition-label">Fat</div>
                    </div>
                </div>
            </div>
        </div>
    </div>-->
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="/js/chitietmonan.js"></script>
</body>
</html>
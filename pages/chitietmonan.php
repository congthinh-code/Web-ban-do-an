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
    $sql = "SELECT Mamon, Tenmon, Giaban, Anh, Noidung, COALESCE(Giagoc, 0) AS Giagoc 
            FROM Monan 
            WHERE Mamon = ? 
            LIMIT 1";
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
            $currentPrice  = floatval($product['Giaban']);
            if ($originalPrice > 0 && $originalPrice > $currentPrice) {
                $discountPercent = round((($originalPrice - $currentPrice) / $originalPrice) * 100);
            }
        }
        $stmt->close();
    }
}

// URL hiện tại để return về sau khi thêm giỏ
$currentUrl = $_SERVER['REQUEST_URI'] ?? '/index.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> 
      <?php 
            //nếu là trang khuyến mãi thì trở về trang khuyến mãi
            if ($product && !empty($originalPrice) && $originalPrice > floatval($product['Giaban'])) {
              echo "Chi tiết món " . $product['Tenmon'] . " đang giảm giá";
            }
            else{
              echo "Chi tiết món " . $product['Tenmon'];
            }

      ?>
              
    </title>
    <link rel="stylesheet" href="/assets/css/chitietmonan.css">
</head>
<body>
    <!-- Header -->
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <!-- Container -->
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">

            <?php if ($product && !empty($originalPrice) && $originalPrice > floatval($product['Giaban'])): ?>
                <a href="../index.php">Trang chủ</a> / <a href="deals.php">Khuyến mãi</a> /
            <?php else: ?>
                <a href="../index.php">Trang chủ</a> / <a href="thucdon.php">Thực Đơn</a> /
            <?php endif; ?>


            <?php if ($product): ?>
              <span><?php echo htmlspecialchars($product['Tenmon']); ?></span>
            <?php endif; ?>

        </div>

        <!-- Product Card -->
        <div class="product-card">
        <!-- Header -->
        <div class="header">
            
            <a href="
            <?php 
            //nếu là trang khuyến mãi thì trở về trang khuyến mãi
            if ($product && !empty($originalPrice) && $originalPrice > floatval($product['Giaban'])) {
              echo "deals.php";
            }
            else{
              echo "index.php";
            }

            ?>">
                <button class="back-btn">
                    <span>←</span>
                    <span>Quay lại</span>
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
                        <img id="mainImage"
                             src="<?php echo htmlspecialchars(resolveImagePath($images[0])); ?>"
                             alt="<?php echo htmlspecialchars($product['Tenmon']); ?>">
                    <?php else: ?>
                        <img id="mainImage"
                             src="/assets/img/default-food.jpg"
                             alt="Sản phẩm">
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
                <h1 class="product-title">
                    <?php echo $product ? htmlspecialchars($product['Tenmon']) : 'Sản phẩm'; ?>
                </h1>
            <!--    
                <div class="rating">
                    <div class="stars">★★★★★</div>
                    <span class="rating-text">4.8 (234 đánh giá)</span>
                </div>
            -->
                <div class="price-section">
                    <span class="current-price">
                      <?php echo $product ? number_format(floatval($product['Giaban']),0,',','.').'₫' : ''; ?>
                    </span>
                    <?php if ($product && !empty($originalPrice) && $originalPrice > floatval($product['Giaban'])): ?>
                        <span class="original-price">
                          <?php echo number_format($originalPrice,0,',','.'); ?>₫
                        </span>
                    <?php endif; ?>
                </div>

                <p class="description">
                    <?php echo $product ? nl2br(htmlspecialchars($product['Noidung'])) : 'Mô tả sản phẩm chưa có.'; ?>
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
                    <?php if ($product): ?>
                        <!-- data-base-href để JS tự nối &qty=... -->
                        <a class="add-to-cart-btn"
                           href="/pages/cart.php?add=<?php echo intval($mamon); ?>&return_url=<?php echo urlencode($currentUrl); ?>"
                           data-base-href="/pages/cart.php?add=<?php echo intval($mamon); ?>&return_url=<?php echo urlencode($currentUrl); ?>">
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
                        <div class="feature-text">Đảm bảo chất lượng món ăn</div>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">♻️</div>
                        <div class="feature-text">Hỗ trợ xử lý sự cố đơn trong ngày</div>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">⭐</div>
                        <div class="feature-text">Cam kết 100% nguyên liệu tươi</div>
                    </div>
                </div>
            </div>
        </div>

        </div><!-- /product-card -->
    </div><!-- /container -->

    <!-- Toast -->
    <div id="toastAddedDeals" class="toast-added">
      Đã thêm vào giỏ hàng 🛒
    </div> 

    <?php include __DIR__ . '/../includes/footer.php'; ?>

   <script src="../../assets/js/chitietmonan.js"></script>
</body>
</html>

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
    <title>Chi Tiết Sản Phẩm</title>
    <link rel="stylesheet" href="/assets/css/detaildeals.css">
    <style>
        /* Toast báo thêm giỏ thành công */
        .toast-added {
          position: fixed;
          left: 50%;
          top: 20px;
          transform: translateX(-50%);
          background: #16a34a;
          color: #fff;
          padding: 10px 16px;
          border-radius: 999px;
          font-size: 14px;
          font-weight: 500;
          box-shadow: 0 4px 10px rgba(0,0,0,0.2);
          opacity: 0;
          pointer-events: none;
          transition: opacity .25s ease, transform .25s ease;
          z-index: 9999;
        }
        .toast-added.show {
          opacity: 1;
          transform: translate(-50%, 0);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <!-- Container -->
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="../index.php">Trang chủ</a> / <a href="#">Thực đơn</a> /
            <?php if ($product): ?>
              <span><?php echo htmlspecialchars($product['Tenmon']); ?></span>
            <?php endif; ?>
        </div>

        <!-- Product Card -->
        <div class="product-card">
        <!-- Header -->
        <div class="header">
            <a href="/index.php">
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
                
                <div class="rating">
                    <div class="stars">★★★★★</div>
                    <span class="rating-text">4.8 (234 đánh giá)</span>
                </div>

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

    <script>
    document.addEventListener('DOMContentLoaded', function () {
      const qtySpan  = document.getElementById('quantityValue');
      const btnMinus = document.getElementById('decreaseBtn');
      const btnPlus  = document.getElementById('increaseBtn');
      const addBtn   = document.querySelector('.add-to-cart-btn');
      const MIN_QTY = 1;
      const MAX_QTY = 99;

      function getQty() {
        let v = parseInt(qtySpan.textContent, 10);
        if (isNaN(v) || v < MIN_QTY) v = MIN_QTY;
        if (v > MAX_QTY) v = MAX_QTY;
        qtySpan.textContent = v;
        return v;
      }

      if (btnMinus) {
        btnMinus.addEventListener('click', function () {
          let v = getQty();
          if (v > MIN_QTY) {
            qtySpan.textContent = v - 1;
          }
        });
      }

      if (btnPlus) {
        btnPlus.addEventListener('click', function () {
          let v = getQty();
          if (v < MAX_QTY) {
            qtySpan.textContent = v + 1;
          }
        });
      }

      // Gallery đổi ảnh theo thumbnail
      const mainImage = document.getElementById('mainImage');
      const thumbs = document.querySelectorAll('.thumbnail');
      if (mainImage && thumbs.length > 0) {
        thumbs.forEach(thumb => {
          thumb.addEventListener('click', function () {
            thumbs.forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
            const img = thumb.querySelector('img');
            if (img) {
              mainImage.src = img.src;
            }
          });
        });
      }

      // Nút yêu thích
      const favBtn = document.getElementById('favoriteBtn');
      if (favBtn) {
        favBtn.addEventListener('click', function () {
          favBtn.classList.toggle('active');
        });
      }

      // Thêm giỏ: nối qty vào URL + hiện toast
      if (addBtn) {
        addBtn.addEventListener('click', function (e) {
          const baseHref = addBtn.getAttribute('data-base-href') || addBtn.getAttribute('href');
          if (!baseHref) return;

          const qty = getQty();
          const url = baseHref + '&qty=' + encodeURIComponent(qty);

          const toast = document.getElementById('toastAddedDeals');
          if (toast) {
            e.preventDefault();
            toast.classList.add('show');
            setTimeout(() => {
              toast.classList.remove('show');
              window.location.href = url;
            }, 700);
          } else {
            // fallback: chỉnh href rồi cho browser đi tiếp
            addBtn.setAttribute('href', url);
          }
        });
      }
    });
    </script>
</body>
</html>

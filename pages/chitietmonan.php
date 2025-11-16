<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Sản Phẩm - Bánh Mì Phô Mai</title>
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
            <a href="#">Trang chủ</a> / <a href="#">Thực đơn</a> / <span>Bánh Mì Phô Mai Bơ Tỏi</span>
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
                    <img id="mainImage" src="https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600" alt="Bánh Mì">
                    <div class="discount-badge">-31%</div>
                </div>
                <div class="thumbnails">
                    <div class="thumbnail active" data-index="0">
                        <img src="https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600" alt="Thumb 1">
                    </div>
                    <div class="thumbnail" data-index="1">
                        <img src="https://images.unsplash.com/photo-1608198399988-841b2d9e515b?w=600" alt="Thumb 2">
                    </div>
                    <div class="thumbnail" data-index="2">
                        <img src="https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=600" alt="Thumb 3">
                    </div>
                    <div class="thumbnail" data-index="3">
                        <img src="https://images.unsplash.com/photo-1586985289688-ca3cf47d3e6e?w=600" alt="Thumb 4">
                    </div>
                </div>
            </div>

            <!-- Product Info -->
            <div class="product-info">
                <h1 class="product-title">Bánh Mì Phô Mai Bơ Tỏi</h1>
                
                <div class="rating">
                    <div class="stars">★★★★★</div>
                    <span class="rating-text">4.8 (234 đánh giá)</span>
                </div>

                <div class="price-section">
                    <span class="current-price">45.000₫</span>
                    <span class="original-price">65.000₫</span>
                </div>

                <p class="description">
                    Bánh mì thơm giòn được phết bơ tỏi thơm lừng, phủ phô mai tan chảy béo ngậy. 
                    Được làm từ nguyên liệu tươi ngon, đảm bảo vệ sinh an toàn thực phẩm.
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
                    <button class="add-to-cart-btn">
                        <span>🛒</span>
                        <span>Thêm vào giỏ hàng</span>
                    </button>
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
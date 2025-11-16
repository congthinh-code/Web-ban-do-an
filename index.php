<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ Ăn húp hội</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="icon" type="image/jpg" sizes="16x16" href="../images/logo.jpg">
</head>
<body>
    <?php include 'includes/header.php'?>
    <div class="main">
        <div class="row bordered">
            <h1>Chào mừng bạn đến với Ăn húp hội</h1>
            <div class="video-container">
                <video autoplay muted loop>
                    <source src="assets/video/food.mp4" type="video/mp4">
                    Trình duyệt của bạn không hỗ trợ video.
                </video>
            </div>
        </div>
    </div>

    <div class="fooddishes">
        <h2>Món ăn nổi bật</h2>
        <div class="food-grid">
            <div class="food-item">
                <a href="/pages/chitietmonan.php" class="food-link">
                    <img src="assets/img/comga.jpg" alt="Món ăn 1">
                    <h3>Cơm gà</h3>
                    <p>30.000 VNĐ</p>
                </a>
            </div>
            <div class="food-item">
                <img src="assets/img/garan.jpg" alt="Món ăn 2">
                <h3>Gà rán</h3>
                <p>45.000 VNĐ</p>
            </div>
            <div class="food-item">
                <img src="assets/img/pho.jpg" alt="Món ăn 3">
                <h3>Phở</h3>
                <p>30.000 VNĐ</p>
            </div>
            <div class="food-item">
                <img src="assets/img/bunbohue.webp" alt="Món ăn 4">
                <h3>Bún bò huế</h3>
                <p>30.000 VNĐ</p>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'?>
</body>
</html>

<?php

session_start();
// Load product details from DB for this page
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../functions/functions.php';


$product = null;
$images = [];
$discountPercent = null;
$originalPrice = null;
$currentPrice = 0;

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}
$uid = (int)$_SESSION['user_id'];





//$sql = "SELECT * FROM Donhang WHERE MaKH = $uid"; 
$sql = "
SELECT 
    d.MaDH,
    d.TinhtrangDH,
    d.Ngaydat,
    d.Ngaygiao,
    m.Tenmon,
    SUM(c.Soluong * c.Dongia)       AS TongTien,
    SUM(c.Soluong)                  AS TongSoLuong,
    MIN(m.Anh)                      AS AnhDaiDien
FROM Donhang d
JOIN Chitietdonhang c ON d.MaDH = c.MaDH
JOIN Monan m ON c.Mamon = m.Mamon
WHERE d.MaKH = $uid
GROUP BY d.MaDH, d.TinhtrangDH, d.Ngaydat, d.Ngaygiao
ORDER BY d.Ngaydat DESC
";


// 1) nhaxe
$dhang = [];
$rs = $conn->query($sql);
if ($rs) {
  while ($r = $rs->fetch_assoc()) {
    $dhang[] = $r;
  }
  $rs->free();
}










?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Sản Phẩm</title>
    <link rel="stylesheet" href="/assets/css/orders.css">
</head>

<body>
    <!-- Header -->
    <div>
     <?php include __DIR__ . '/../includes/header.php'; ?>
    </div>
    <!-- Container -->
  
    <div class="select-oder-status">
        <a href ="?type=0"> Tất cả </a>
        <a href="?type=1"> Đang xử lý </a>
        <a href="?type=2"> Đang giao </a>
        <a href="?type=3"> Đã giao </a>
        <a href="?type=4" > Đã Huỷ </a>
    </div>


<?php $type = isset($_GET['type']) ? (int)$_GET['type'] : 0;
$hasOrder = false;
 ?>







<div class="list-oders">
<?php foreach ($dhang as $rdh): ?>

    <?php 
        $show = false;

        if ($type == 0 || $type > 4) $show = true;
        if ($type == 1 && $rdh['TinhtrangDH'] == "Đang xử lý") $show = true;
        if ($type == 2 && $rdh['TinhtrangDH'] == "Đang giao")   $show = true;
        if ($type == 3 && $rdh['TinhtrangDH'] == "Đã giao")     $show = true;
        if ($type == 4 && $rdh['TinhtrangDH'] == "Đã hủy")      $show = true; // sửa lại đúng ở đây

        if ($show) $hasOrder = true;
    ?>

    <?php if ($show): ?>
      <div class="oder">
          <div class="card-food"> 
              <div class="food">
                <div> 
                  <img class="img-food" src="<?php echo "../../". $rdh['AnhDaiDien']; ?>" >
                </div>

                <div class="food-text">
                  <div class="name-food">
                    <div>Đơn #<?php echo $rdh['MaDH'];?> </div>
                    <div><?php echo $rdh['Tenmon'] . "..."; ?></div>
                  </div>
                  <div>
                    <?php echo $rdh['TongSoLuong']; ?> món
                  </div>     
                </div>
              </div>

              <div class="oder-status">
                  <?php echo $rdh['TinhtrangDH']; ?>
              </div>
          </div>

          <div> 
              <div> Ngày Đặt: <?php echo $rdh['Ngaydat']; ?> </div>
              <?php if(!empty($rdh['Ngaygiao'])): ?>
                <div> Ngày Giao: <?php echo $rdh['Ngaygiao']; ?> </div>
              <?php endif; ?>
          </div>
          
          <div> Thanh toán: <span class="total"><?php echo number_format($rdh['TongTien']); ?>đ</span> </div>

          <div> 
            <a href="orderdetails.php?madh=<?php echo $rdh['MaDH']; ?>" class="detail-oder">Chi tiết</a>
          </div>
      </div>
    <?php endif; ?>

<?php endforeach; ?>

<?php if (!$hasOrder): ?>
    <div class="no-order">Không có đơn hàng</div>
<?php endif; ?>
</div>


<!--             d.TinhtrangDH,
            m.Tenmon,
            d.Ngaydat,
            d.Ngaygiao,
            c.Soluong,
            c.Dongia -->






<footer>
 <?php include __DIR__ . '/../includes/footer.php'; ?>
</footer>

</body>
</html>
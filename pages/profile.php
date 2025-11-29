<?php
session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../functions/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

$uid = (int)$_SESSION['user_id'];
$message = "";

// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hoten   = trim($_POST['Hoten'] ?? '');
    $email   = trim($_POST['Email'] ?? '');
    $phone   = trim($_POST['DienthoaiKH'] ?? '');
    $address = trim($_POST['DiachiKH'] ?? '');
    $dob     = trim($_POST['Ngaysinh'] ?? '');

    // Có thể thêm validate nữa (email hợp lệ, v.v.)
    $sqlUpdate = "
        UPDATE Khachhang
        SET Hoten = ?, Email = ?, DienthoaiKH = ?, DiachiKH = ?, Ngaysinh = ?
        WHERE MaKH = ?
    ";
    $stmt = $conn->prepare($sqlUpdate);
    $stmt->bind_param("sssssi", $hoten, $email, $phone, $address, $dob, $uid);

    if ($stmt->execute()) {
        $message = "Cập nhật hồ sơ thành công.";
    } else {
        $message = "Có lỗi khi cập nhật. Vui lòng thử lại.";
    }

    $stmt->close();
}

// Lấy thông tin user
$sqlUser = "
SELECT MaKH, Hoten, Taikhoan, Email, DienthoaiKH, DiachiKH, Ngaysinh
FROM Khachhang
WHERE MaKH = ?
LIMIT 1
";
$stmt2 = $conn->prepare($sqlUser);
$stmt2->bind_param("i", $uid);
$stmt2->execute();
$rsUser = $stmt2->get_result();
$user = $rsUser->fetch_assoc();
$stmt2->close();

if (!$user) {
    // Không thấy user? Logout luôn
    session_destroy();
    header("Location: /login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Hồ sơ cá nhân</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="/assets/css/profile.css">
</head>


<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <div class="container">
    <div class="card">
      <h1>Hồ sơ cá nhân</h1>
      <div class="subtext">
        Xem và cập nhật thông tin tài khoản của bạn.
      </div>
    </div>

    <div class="card">
      <?php if (!empty($message)): ?>
        <div class="message <?php echo (strpos($message, 'thành công') !== false) ? 'ok' : 'error'; ?>">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <form method="post" action="">
        <div class="form-group">
          <label>Mã khách hàng</label>
          <input type="text" value="<?php echo (int)$user['MaKH']; ?>" readonly>
        </div>

        <div class="form-group">
          <label>Tên đăng nhập</label>
          <input type="text" value="<?php echo htmlspecialchars($user['Taikhoan']); ?>" readonly>
        </div>

        <div class="form-group">
          <label>Họ tên</label>
          <input type="text" name="Hoten" required
                 value="<?php echo htmlspecialchars($user['Hoten'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <label>Email</label>
          <input type="email" name="Email"
                 value="<?php echo htmlspecialchars($user['Email'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <label>Số điện thoại</label>
          <input type="text" name="DienthoaiKH"
                 value="<?php echo htmlspecialchars($user['DienthoaiKH'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <label>Địa chỉ</label>
          <textarea name="DiachiKH" rows="3"><?php echo htmlspecialchars($user['DiachiKH'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
          <label>Ngày sinh</label>
          <input type="date" name="Ngaysinh"
                 value="<?php echo htmlspecialchars($user['Ngaysinh'] ?? ''); ?>">
        </div>

        <button type="submit" class="btn-submit">Lưu thay đổi</button>
      </form>
    </div>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

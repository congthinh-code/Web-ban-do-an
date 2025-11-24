<?php
session_start();
require_once __DIR__ . '/../database/db.php';

$errors = [];
$success = '';

// Nếu người dùng đã đăng nhập, chuyển hướng về trang chủ
if (!empty($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

// Xử lý đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '') $errors[] = 'Vui lòng nhập tên.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
    if (strlen($password) < 6) $errors[] = 'Mật khẩu phải ít nhất 6 ký tự.';

    if (empty($errors)) {
      // Kiểm tra email đã tồn tại trong Khachhang
      $sql = "SELECT MaKH FROM Khachhang WHERE Email = ? LIMIT 1";
      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
          $errors[] = 'Email này đã được đăng ký.';
        }
        $stmt->close();
      }
    }

    if (empty($errors)) {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      // Taikhoan: tạo tự động từ tên (loại bỏ khoảng trắng) nếu cần
      $taikhoan = substr(preg_replace('/\s+/', '', $name), 0, 50);
      $ins = "INSERT INTO Khachhang (Hoten, Taikhoan, Matkhau, Email) VALUES (?, ?, ?, ?)";
      if ($stmt = $conn->prepare($ins)) {
        $stmt->bind_param('ssss', $name, $taikhoan, $hash, $email);
        if ($stmt->execute()) {
          $uid = $stmt->insert_id;
          $_SESSION['user_id'] = $uid;
          $_SESSION['username'] = $name;
          // redirect to return_url or homepage
          $ret = $_GET['return_url'] ?? '/index.php';
          header('Location: ' . $ret);
          exit;
        } else {
          $errors[] = 'Đăng ký thất bại, vui lòng thử lại.';
        }
        $stmt->close();
      } else {
        $errors[] = 'Lỗi hệ thống (prepare).';
      }
    }
}

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
    if ($password === '') $errors[] = 'Vui lòng nhập mật khẩu.';

    if (empty($errors)) {
      // Tìm user theo Email hoặc Taikhoan
      $sql = "SELECT MaKH, Hoten, Matkhau FROM Khachhang WHERE Email = ? OR Taikhoan = ? LIMIT 1";
      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('ss', $email, $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
          $row = $res->fetch_assoc();
          $stored = $row['Matkhau'];
          $ok = false;
          if (is_string($stored) && strlen($stored) > 0 && $stored[0] === '$') {
            if (password_verify($password, $stored)) $ok = true;
          } else {
            // legacy plaintext password
            if ($password === $stored) {
              $ok = true;
              // upgrade to hashed password
              $newHash = password_hash($password, PASSWORD_DEFAULT);
              $upd = $conn->prepare("UPDATE Khachhang SET Matkhau = ? WHERE MaKH = ?");
              if ($upd) {
                $upd->bind_param('si', $newHash, $row['MaKH']);
                $upd->execute();
                $upd->close();
              }
            }
          }

          if ($ok) {
            $_SESSION['user_id'] = $row['MaKH'];
            $_SESSION['username'] = $row['Hoten'];
            $ret = $_GET['return_url'] ?? '/index.php';
            header('Location: ' . $ret);
            exit;
          } else {
            $errors[] = 'Email hoặc mật khẩu không đúng.';
          }
        } else {
          $errors[] = 'Email hoặc mật khẩu không đúng.';
        }
        $stmt->close();
      } else {
        $errors[] = 'Lỗi hệ thống (prepare).';
      }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/css/login.css">
  <title>Đăng nhập & Đăng ký</title>
</head>
<body>

  <div class="container" id="container">
    <!-- Form Đăng ký -->
    <div class="form-container sign-up-container">
      <form method="POST" action="?<?php echo isset($_GET['return_url']) ? 'return_url=' . urlencode($_GET['return_url']) : ''; ?>">
        <h1>Tạo tài khoản</h1>
        <?php if (!empty($errors) && isset($_POST['action']) && $_POST['action'] === 'register'): ?>
          <div class="error-list">
            <?php foreach ($errors as $e): ?>
              <div class="err"><?php echo htmlspecialchars($e); ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <input type="text" name="name" placeholder="Tên" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required />
        <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required />
        <input type="password" name="password" placeholder="Mật khẩu" required />
        <input type="hidden" name="action" value="register" />
        <button type="submit">Đăng ký</button>
      </form>
    </div>

    <!-- Form Đăng nhập -->
    <div class="form-container sign-in-container">
      <form method="POST" action="?<?php echo isset($_GET['return_url']) ? 'return_url=' . urlencode($_GET['return_url']) : ''; ?>">
        <h1>Đăng nhập</h1>
        <?php if (!empty($errors) && isset($_POST['action']) && $_POST['action'] === 'login'): ?>
          <div class="error-list">
            <?php foreach ($errors as $e): ?>
              <div class="err"><?php echo htmlspecialchars($e); ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required />
        <input type="password" name="password" placeholder="Mật khẩu" required />
        <a href="#">Quên mật khẩu?</a>
        <input type="hidden" name="action" value="login" />
        <button type="submit">Đăng nhập</button>
      </form>
    </div>

    <!-- Overlay -->
    <div class="overlay-container">
      <div class="overlay">
        <div class="overlay-panel overlay-left">
          <h1>Chào mừng trở lại!</h1>
          <p>Nếu bạn đã có tài khoản, vui lòng đăng nhập tại đây</p>
          <button class="ghost" id="signIn">Đăng nhập</button>
        </div>
        <div class="overlay-panel overlay-right">
          <h1>Xin chào!</h1>
          <p>Nhập thông tin cá nhân để bắt đầu hành trình cùng chúng tôi</p>
          <button class="ghost" id="signUp">Đăng ký</button>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
<script src="../assets/js/login.js"></script>
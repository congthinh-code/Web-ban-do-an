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

// Hàm tạo Taikhoan duy nhất dựa trên tên, kiểm tra trong DB
function generateUniqueUsername(mysqli $conn, string $name): string {
    // bỏ khoảng trắng, ký tự lạ
    $base = preg_replace('/[^A-Za-z0-9]+/', '', $name);
    if ($base === '') {
        $base = 'user';
    }
    $base = strtolower(substr($base, 0, 30)); // giới hạn độ dài

    $username = $base;
    $i = 1;

    $sql = "SELECT 1 FROM Khachhang WHERE Taikhoan = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        // nếu lỗi prepare thì trả đại base
        return $base;
    }

    while (true) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$res || $res->num_rows === 0) {
            // chưa tồn tại, dùng username này
            $stmt->close();
            return $username;
        }
        $i++;
        $username = substr($base, 0, 30 - strlen((string)$i)) . $i;
    }
}

// Xử lý đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
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
        } else {
            $errors[] = 'Lỗi hệ thống (prepare email).';
        }
    }

    if (empty($errors)) {
        // Tạo tên tài khoản duy nhất dựa vào DB
        $taikhoan = generateUniqueUsername($conn, $name);
        $hash     = password_hash($password, PASSWORD_DEFAULT);

        $ins = "INSERT INTO Khachhang (Hoten, Taikhoan, Matkhau, Email) VALUES (?, ?, ?, ?)";
        if ($stmt = $conn->prepare($ins)) {
            $stmt->bind_param('ssss', $name, $taikhoan, $hash, $email);
            if ($stmt->execute()) {
                $uid = $stmt->insert_id;
                $_SESSION['user_id']   = $uid;
                $_SESSION['username']  = $name;
                // redirect to return_url or homepage
                $ret = $_GET['return_url'] ?? '/index.php';
                header('Location: ' . $ret);
                exit;
            } else {
                $errors[] = 'Đăng ký thất bại, vui lòng thử lại.';
            }
            $stmt->close();
        } else {
            $errors[] = 'Lỗi hệ thống (prepare insert).';
        }
    }
}

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $emailOrUser = trim($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';

    if ($emailOrUser === '') $errors[] = 'Vui lòng nhập email hoặc tên đăng nhập.';
    if ($password === '')    $errors[] = 'Vui lòng nhập mật khẩu.';

    if (empty($errors)) {
        // Tìm user theo Email hoặc Taikhoan
        $sql = "SELECT MaKH, Hoten, Matkhau FROM Khachhang WHERE Email = ? OR Taikhoan = ? LIMIT 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('ss', $emailOrUser, $emailOrUser);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                $row    = $res->fetch_assoc();
                $stored = $row['Matkhau'];
                $ok     = false;

                // Nếu mật khẩu đã được hash (bắt đầu bằng $)
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
                    $_SESSION['user_id']  = $row['MaKH'];
                    $_SESSION['username'] = $row['Hoten'];
                    $ret = $_GET['return_url'] ?? '/index.php';
                    header('Location: ' . $ret);
                    exit;
                } else {
                    $errors[] = 'Email/Tên đăng nhập hoặc mật khẩu không đúng.';
                }
            } else {
                $errors[] = 'Email/Tên đăng nhập hoặc mật khẩu không đúng.';
            }
            $stmt->close();
        } else {
            $errors[] = 'Lỗi hệ thống (prepare login).';
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
  <title>Đăng nhập & Đăng ký - Ăn Húp Hội</title>
</head>

<body>

  <div class="auth-wrapper">
    <div class="auth-header">
      <div class="brand-mini">
        <div class="brand-logo-circle">🍜</div>
        <div class="brand-text">
          <span class="name">Ăn Húp Hội</span>
          <span class="slogan">Đăng nhập để đặt món nhanh hơn</span>
        </div>
      </div>
      <a href="/index.php" class="back-home-link">
        ← Về trang chủ
      </a>
    </div>

    <div class="container" id="container">
      <!-- Form Đăng ký -->
      <div class="form-container sign-up-container">
        <form method="POST" action="?<?php echo isset($_GET['return_url']) ? 'return_url=' . urlencode($_GET['return_url']) : ''; ?>">
          <h1>Tạo tài khoản</h1>
          <div class="form-subtitle">
            Chỉ mất vài giây để bắt đầu đặt đồ ăn với Ăn Húp Hội.
          </div>
          <?php if (!empty($errors) && isset($_POST['action']) && $_POST['action'] === 'register'): ?>
            <div class="error-list">
              <?php foreach ($errors as $e): ?>
                <div class="err"><?php echo htmlspecialchars($e); ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <input type="text" name="name" placeholder="Tên hiển thị" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required />
          <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required />
          <input type="password" name="password" placeholder="Mật khẩu (≥ 6 ký tự)" required />
          <input type="hidden" name="action" value="register" />
          <button type="submit">Đăng ký</button>
        </form>
      </div>

      <!-- Form Đăng nhập -->
      <div class="form-container sign-in-container">
        <form method="POST" action="?<?php echo isset($_GET['return_url']) ? 'return_url=' . urlencode($_GET['return_url']) : ''; ?>">
          <h1>Đăng nhập</h1>
          <div class="form-subtitle">
            Đăng nhập để xem lịch sử đơn hàng và đặt lại món yêu thích.
          </div>
          <?php if (!empty($errors) && isset($_POST['action']) && $_POST['action'] === 'login'): ?>
            <div class="error-list">
              <?php foreach ($errors as $e): ?>
                <div class="err"><?php echo htmlspecialchars($e); ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <input type="text" name="email" placeholder="Email hoặc tên đăng nhập" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required />
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
            <p>Đăng nhập để tiếp tục hành trình “ăn húp” của bạn.</p>
            <ul class="overlay-bullets">
              <li>Lưu lịch sử đơn hàng</li>
              <li>Đặt lại món chỉ với 1 chạm</li>
              <li>Nhận ưu đãi dành riêng cho bạn</li>
            </ul>
            <button class="ghost" id="signIn">Đăng nhập</button>
          </div>
          <div class="overlay-panel overlay-right">
            <h1>Xin chào!</h1>
            <p>Tạo tài khoản để không bỏ lỡ các deal món ngon.</p>
            <ul class="overlay-bullets">
              <li>Nhận thông báo khuyến mãi mới</li>
              <li>Lưu địa chỉ giao hàng yêu thích</li>
              <li>Thanh toán nhanh hơn cho những lần sau</li>
            </ul>
            <button class="ghost" id="signUp">Đăng ký</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="../assets/js/login.js"></script>
</body>
</html>

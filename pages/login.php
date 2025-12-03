<?php
session_start();
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/mailer.php'; // file chứa hàm sendOtpMail()

$errors  = [];
$success = [];

// Nếu người dùng đã đăng nhập, chuyển hướng về trang chủ
if (!empty($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

// ======================= HÀM PHỤ =======================

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

    $sql = "SELECT 1 FROM Users WHERE Taikhoan = ? LIMIT 1";
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

// Lấy action nhanh
$action = $_POST['action'] ?? '';

// ======================= XỬ LÝ FORM =======================

// ĐĂNG KÝ: B1 – NHẬP INFO & GỬI OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'register') {

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '') $errors[] = 'Vui lòng nhập tên.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
    if (strlen($password) < 6) $errors[] = 'Mật khẩu phải ít nhất 6 ký tự.';

    if (empty($errors)) {
        // Kiểm tra email đã tồn tại trong Users
        $sql = "SELECT UID FROM Users WHERE Email = ? LIMIT 1";
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
        // Tạo OTP & lưu session tạm
        $otp    = (string) random_int(100000, 999999);
        $hash   = password_hash($password, PASSWORD_DEFAULT);
        $tkTemp = generateUniqueUsername($conn, $name);

        $_SESSION['otp_register'] = [
            'name'       => $name,
            'email'      => $email,
            'password'   => $hash,
            'taikhoan'   => $tkTemp,
            'otp'        => $otp,
            'expires_at' => time() + 600, // 10 phút
            'last_sent'  => time()        // để đếm 60s resend
        ];

        if (sendOtpMail($email, $name, $otp)) {
            $success[] = 'Đã gửi mã OTP tới email. Vui lòng kiểm tra và nhập mã để hoàn tất đăng ký.';
        } else {
            $errors[] = 'Không gửi được email OTP, vui lòng thử lại sau.';
            unset($_SESSION['otp_register']);
        }
    }
}

// ĐĂNG KÝ: B2 – XÁC NHẬN OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'verify_register') {

    $otpInput = trim($_POST['otp'] ?? '');

    if (empty($_SESSION['otp_register'])) {
        $errors[] = 'Không tìm thấy phiên đăng ký, vui lòng đăng ký lại.';
    } else {
        $data = $_SESSION['otp_register'];

        if (time() > $data['expires_at']) {
            $errors[] = 'Mã OTP đã hết hạn, vui lòng đăng ký lại.';
            unset($_SESSION['otp_register']);
        } elseif ($otpInput !== $data['otp']) {
            $errors[] = 'Mã OTP không đúng.';
        } else {
            // OTP OK → insert DB
            $ins = "INSERT INTO Users (Hoten, Taikhoan, Matkhau, Email)
                    VALUES (?, ?, ?, ?)";
            if ($stmt = $conn->prepare($ins)) {
                $stmt->bind_param(
                    'ssss',
                    $data['name'],
                    $data['taikhoan'],
                    $data['password'],
                    $data['email']
                );
                if ($stmt->execute()) {
                    $_SESSION['user_id']  = $stmt->insert_id;
                    $_SESSION['username'] = $data['name'];

                    unset($_SESSION['otp_register']);

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
}

// RESEND OTP ĐĂNG KÝ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'resend_register_otp') {
    if (empty($_SESSION['otp_register'])) {
        $errors[] = 'Không tìm thấy phiên đăng ký, vui lòng đăng ký lại.';
    } else {
        $data =& $_SESSION['otp_register'];
        $now  = time();
        $last = $data['last_sent'] ?? $now;
        $diff = $now - $last;

        if ($diff < 60) {
            $errors[] = 'Vui lòng chờ thêm ' . (60 - $diff) . ' giây để gửi lại OTP.';
        } else {
            $otp = (string) random_int(100000, 999999);
            $data['otp']        = $otp;
            $data['expires_at'] = $now + 600;
            $data['last_sent']  = $now;

            if (sendOtpMail($data['email'], $data['name'], $otp)) {
                $success[] = 'Đã gửi lại mã OTP tới email của bạn.';
            } else {
                $errors[] = 'Không gửi được email OTP, vui lòng thử lại sau.';
            }
        }
    }
}

// HUỶ OTP ĐĂNG KÝ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'cancel_register_otp') {
    unset($_SESSION['otp_register']);
    $success[] = 'Đã huỷ xác thực OTP đăng ký. Bạn có thể chỉnh lại thông tin và đăng ký lại.';
}

// ĐĂNG NHẬP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {

    $emailOrUser = trim($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';

    if ($emailOrUser === '') $errors[] = 'Vui lòng nhập email hoặc tên đăng nhập.';
    if ($password === '')    $errors[] = 'Vui lòng nhập mật khẩu.';

    if (empty($errors)) {
        $sql = "SELECT *
                FROM Users
                WHERE Email = ? OR Taikhoan = ?
                LIMIT 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('ss', $emailOrUser, $emailOrUser);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                $row    = $res->fetch_assoc();
                $stored = $row['Matkhau'];
                $ok     = false;

                if (is_string($stored) && strlen($stored) > 0 && $stored[0] === '$') {
                    // password hash
                    if (password_verify($password, $stored)) $ok = true;
                } else {
                    // plaintext cũ
                    if ($password === $stored) {
                        $ok = true;
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $upd = $conn->prepare("UPDATE Users SET Matkhau = ? WHERE UID = ?");
                        if ($upd) {
                            $upd->bind_param('si', $newHash, $row['UID']);
                            $upd->execute();
                            $upd->close();
                        }
                    }
                }

                if ($ok) {
                    $_SESSION['user_id']  = $row['UID'];
                    $_SESSION['username'] = $row['Hoten'];
                    $_SESSION['role'] = $row['Role'];
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

// QUÊN MẬT KHẨU: B1 – GỬI OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'forgot') {

    $emailForgot = trim($_POST['email_forgot'] ?? '');

    if (!filter_var($emailForgot, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Vui lòng nhập email hợp lệ để khôi phục mật khẩu.';
    } else {
        $sql = "SELECT UID, Hoten FROM Users WHERE Email = ? LIMIT 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('s', $emailForgot);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                $row = $res->fetch_assoc();

                $otpForgot = (string) random_int(100000, 999999);
                $_SESSION['otp_forgot'] = [
                    'UID'       => $row['UID'],
                    'Hoten'      => $row['Hoten'],
                    'Email'      => $emailForgot,
                    'otp'        => $otpForgot,
                    'expires_at' => time() + 600,
                    'last_sent'  => time()
                ];

                if (sendOtpMail($emailForgot, $row['Hoten'], $otpForgot)) {
                    $success[] = 'Đã gửi mã OTP khôi phục mật khẩu tới email của bạn.';
                } else {
                    $errors[] = 'Không gửi được email OTP. Vui lòng thử lại sau.';
                    unset($_SESSION['otp_forgot']);
                }
            } else {
                $errors[] = 'Email này chưa được đăng ký.';
            }
            $stmt->close();
        } else {
            $errors[] = 'Lỗi hệ thống (prepare forgot).';
        }
    }
}

// QUÊN MẬT KHẨU: B2 – NHẬP OTP + MẬT KHẨU MỚI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'reset_password') {

    $otpInput = trim($_POST['otp_forgot'] ?? '');
    $newPass  = $_POST['new_password'] ?? '';
    $newPass2 = $_POST['new_password_confirm'] ?? '';

    if ($newPass === '' || strlen($newPass) < 6) {
        $errors[] = 'Mật khẩu mới phải từ 6 ký tự trở lên.';
    }
    if ($newPass !== $newPass2) {
        $errors[] = 'Xác nhận mật khẩu mới không trùng khớp.';
    }

    if (empty($_SESSION['otp_forgot'])) {
        $errors[] = 'Không tìm thấy yêu cầu khôi phục, vui lòng thử lại.';
    } else {
        $data = $_SESSION['otp_forgot'];

        if (time() > $data['expires_at']) {
            $errors[] = 'Mã OTP đã hết hạn, vui lòng yêu cầu lại.';
            unset($_SESSION['otp_forgot']);
        } elseif ($otpInput !== $data['otp']) {
            $errors[] = 'Mã OTP không đúng.';
        }
    }

    if (empty($errors) && !empty($_SESSION['otp_forgot'])) {
        $data    = $_SESSION['otp_forgot'];
        $newHash = password_hash($newPass, PASSWORD_DEFAULT);

        $upd = $conn->prepare("UPDATE Users SET Matkhau = ? WHERE UID = ?");
        if ($upd) {
            $upd->bind_param('si', $newHash, $data['UID']);
            if ($upd->execute()) {
                $success[] = 'Đặt lại mật khẩu thành công, bạn có thể đăng nhập bằng mật khẩu mới.';
                unset($_SESSION['otp_forgot']);
            } else {
                $errors[] = 'Không thể cập nhật mật khẩu, vui lòng thử lại.';
            }
            $upd->close();
        } else {
            $errors[] = 'Lỗi hệ thống (prepare reset).';
        }
    }
}

// RESEND OTP QUÊN MẬT KHẨU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'resend_forgot_otp') {
    if (empty($_SESSION['otp_forgot'])) {
        $errors[] = 'Không tìm thấy yêu cầu khôi phục, vui lòng thử lại.';
    } else {
        $data =& $_SESSION['otp_forgot'];
        $now  = time();
        $last = $data['last_sent'] ?? $now;
        $diff = $now - $last;

        if ($diff < 60) {
            $errors[] = 'Vui lòng chờ thêm ' . (60 - $diff) . ' giây để gửi lại OTP.';
        } else {
            $otp = (string) random_int(100000, 999999);
            $data['otp']        = $otp;
            $data['expires_at'] = $now + 600;
            $data['last_sent']  = $now;

            if (sendOtpMail($data['Email'], $data['Hoten'], $otp)) {
                $success[] = 'Đã gửi lại mã OTP khôi phục mật khẩu.';
            } else {
                $errors[] = 'Không gửi được email OTP, vui lòng thử lại sau.';
            }
        }
    }
}

// HUỶ OTP QUÊN MẬT KHẨU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'cancel_forgot_otp') {
    unset($_SESSION['otp_forgot']);
    $success[] = 'Đã huỷ xác thực OTP khôi phục mật khẩu.';
}

// ======================= XÁC ĐỊNH PANEL & THỜI GIAN RESEND =======================

$activePanel = 'login';
if (isset($_SESSION['otp_register']) || $action === 'register') {
    $activePanel = 'signup';
}

// thời gian còn lại để cho phép resend (đăng ký)
$registerResendRemaining = 0;
if (isset($_SESSION['otp_register'])) {
    $last    = $_SESSION['otp_register']['last_sent'] ?? time();
    $elapsed = time() - $last;
    $registerResendRemaining = max(0, 60 - $elapsed);
}

// thời gian còn lại để cho phép resend (quên mật khẩu)
$forgotResendRemaining = 0;
if (isset($_SESSION['otp_forgot'])) {
    $last    = $_SESSION['otp_forgot']['last_sent'] ?? time();
    $elapsed = time() - $last;
    $forgotResendRemaining = max(0, 60 - $elapsed);
}

$returnParam = isset($_GET['return_url']) ? 'return_url=' . urlencode($_GET['return_url']) : '';
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
      <a href="/index.php" class="back-home-link">← Về trang chủ</a>
    </div>

    <?php if (!empty($success)): ?>
      <div class="alert-success">
        <?php foreach ($success as $s): ?>
          <div><?php echo htmlspecialchars($s); ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="error-list global">
        <?php foreach ($errors as $e): ?>
          <div class="err"><?php echo htmlspecialchars($e); ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="container <?php echo ($activePanel === 'signup' ? 'right-panel-active' : ''); ?>" id="container">
      <!-- CỘT ĐĂNG KÝ -->
      <div class="form-container sign-up-container">
        <?php if (isset($_SESSION['otp_register'])): ?>
          <!-- Bước 2: Nhập OTP -->
          <form method="POST" action="?<?php echo $returnParam; ?>">
            <h1>Nhập mã OTP</h1>
            <p>Đã gửi tới: <strong><?php echo htmlspecialchars($_SESSION['otp_register']['email']); ?></strong></p>
            <input type="text" name="otp" placeholder="Mã OTP 6 số" required />
            
            <div class="btn-row">
              <button type="submit" name="action" value="verify_register" class="btn-confirm">
                Xác nhận & Hoàn tất
              </button>

              <!-- Nút gửi lại OTP (có đếm ngược) -->
              <button type="submit"
                      name="action"
                      value="resend_register_otp"
                      id="btnResendRegister"
                      class="btn-resend"
                      formnovalidate
                      data-remaining="<?php echo (int)$registerResendRemaining; ?>">
                Gửi lại OTP
              </button>
            </div>

            <!-- Dòng hiển thị đếm ngược -->
            <p class="otp-countdown">
              <span id="registerCountdownText"></span>
            </p>

            <!-- Huỷ: nút nhỏ dạng link, không validate OTP -->
            <button type="submit"
                    name="action"
                    value="cancel_register_otp"
                    class="btn-cancel"
                    formnovalidate>
              Huỷ, nhập lại thông tin
            </button>
          </form>

        <?php else: ?>
          <!-- Bước 1: Nhập thông tin -->
          <form method="POST" action="?<?php echo $returnParam; ?>">
            <h1>Tạo tài khoản</h1>
            <div class="form-subtitle">
              Chỉ mất vài giây để bắt đầu đặt đồ ăn với Ăn Húp Hội.
            </div>
            <input type="text" name="name" placeholder="Tên hiển thị"
                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required />
            <input type="email" name="email" placeholder="Email"
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required />
            <input type="password" name="password" placeholder="Mật khẩu (≥ 6 ký tự)" required />
            <input type="hidden" name="action" value="register" />
            <button type="submit">Gửi OTP</button>
          </form>
        <?php endif; ?>
      </div>

      <!-- CỘT ĐĂNG NHẬP + QUÊN MẬT KHẨU -->
      <div class="form-container sign-in-container">
        <?php if (isset($_SESSION['otp_forgot'])): ?>
          <!-- Reset mật khẩu: B2 -->
          <form method="POST">
            <h1>Đặt lại mật khẩu</h1>
            <p>OTP đã gửi đến: <strong><?php echo htmlspecialchars($_SESSION['otp_forgot']['Email']); ?></strong></p>
            <input type="text" name="otp_forgot" placeholder="Mã OTP 6 số" required />
            <input type="password" name="new_password" placeholder="Mật khẩu mới (≥ 6 ký tự)" required />
            <input type="password" name="new_password_confirm" placeholder="Nhập lại mật khẩu mới" required />
            
            <div class="btn-row">
              <button type="submit" name="action" value="reset_password" class="btn-confirm">
                Xác nhận
              </button>

              <!-- Nút gửi lại OTP (có đếm ngược) -->
              <button type="submit"
                      name="action"
                      value="resend_forgot_otp"
                      id="btnResendForgot"
                      class="btn-resend"
                      formnovalidate
                      data-remaining="<?php echo (int)$forgotResendRemaining; ?>">
                Gửi lại OTP
              </button>
            </div>

            <!-- Dòng hiển thị đếm ngược -->
            <p class="otp-countdown">
              <span id="forgotCountdownText"></span>
            </p>

            <!-- Huỷ: nút nhỏ dạng link -->
            <button type="submit"
                    name="action"
                    value="cancel_forgot_otp"
                    class="btn-cancel"
                    formnovalidate>
              Huỷ, không đặt lại nữa
            </button>
          </form>

        <?php else: ?>
          <!-- Form đăng nhập -->
          <form method="POST" action="?<?php echo $returnParam; ?>">
            <h1>Đăng nhập</h1>
            <div class="form-subtitle">
              Đăng nhập để xem lịch sử đơn hàng và đặt lại món yêu thích.
            </div>
            <input type="text" name="email" placeholder="Email hoặc tên đăng nhập"
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required />
            <input type="password" name="password" placeholder="Mật khẩu" required />
            <input type="hidden" name="action" value="login" />
            <button type="submit">Đăng nhập</button>
          </form>

          <hr class="divider" />

          <!-- Form quên mật khẩu -->
          <form method="POST">
            <h2>Quên mật khẩu?</h2>
            <p>Nhập email để nhận mã OTP đặt lại mật khẩu.</p>
            <input type="email" name="email_forgot" placeholder="Email đã đăng ký" required />
            <input type="hidden" name="action" value="forgot" />
            <button type="submit">Gửi OTP khôi phục</button>
          </form>
        <?php endif; ?>
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

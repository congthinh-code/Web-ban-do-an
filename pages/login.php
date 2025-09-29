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
      <form action="#">
        <h1>Tạo tài khoản</h1>
        <input type="text" placeholder="Tên" />
        <input type="email" placeholder="Email" />
        <input type="password" placeholder="Mật khẩu" />
        <button>Đăng ký</button>
      </form>
    </div>

    <!-- Form Đăng nhập -->
    <div class="form-container sign-in-container">
      <form action="#">
        <h1>Đăng nhập</h1>
        <input type="email" placeholder="Email" />
        <input type="password" placeholder="Mật khẩu" />
        <a href="#">Quên mật khẩu?</a>
        <button>Đăng nhập</button>
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
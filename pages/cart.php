<?php
session_start();
include_once '../database/db.php'; // phải tạo $conn (MySQLi)
require_once __DIR__ . '/../functions/functions.php';

// ----- Xử lý thêm sản phẩm vào giỏ từ ?add=ID&qty=?? -----
if (isset($_GET['add'])) {
    $prod_id = intval($_GET['add']);

    // Yêu cầu người dùng phải đăng nhập trước khi thêm vào giỏ
    if (empty($_SESSION['user_id'])) {
        // Chuyển hướng tới trang đăng nhập, giữ lại URL hiện tại để trả về sau khi đăng nhập
        $current = $_SERVER['REQUEST_URI'] ?? '/pages/cart.php';
        header("Location: /pages/login.php?return_url=" . urlencode($current));
        exit;
    }

    // Lấy số lượng từ query (nếu có), mặc định = 1
    $qty = isset($_GET['qty']) ? intval($_GET['qty']) : 1;
    if ($qty < 1) $qty = 1;
    if ($qty > 99) $qty = 99;

    // Lấy thông tin sản phẩm từ db
    $sql = "SELECT Mamon AS id, Tenmon AS name, Giaban AS price, Anh AS image 
            FROM Monan 
            WHERE Mamon = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $prod_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $product = $res->fetch_assoc();

            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            if (isset($_SESSION['cart'][$prod_id])) {
                // Nếu đã có thì cộng thêm số lượng
                $_SESSION['cart'][$prod_id]['qty'] += $qty;
            } else {
                // Nếu chưa có thì thêm mới với qty vừa chọn
                $_SESSION['cart'][$prod_id] = [
                    'id'    => $product['id'],
                    'name'  => $product['name'],
                    'price' => $product['price'],
                    'image' => $product['image'],
                    'qty'   => $qty
                ];
            }
        }
        $stmt->close();
    }

    // Decide where to redirect after adding
    $return_url = $_GET['return_url'] ?? '';
    // Basic safety: allow only local paths (no http scheme)
    if ($return_url && (stripos($return_url, 'http://') === 0 || stripos($return_url, 'https://') === 0)) {
        $return_url = '';
    }
    if (!$return_url) $return_url = 'cart.php';
    header("Location: " . $return_url);
    exit;
}

// ----- Xóa sản phẩm khỏi giỏ hàng -----
if (isset($_GET['remove_id'])) {
    $rid = intval($_GET['remove_id']);
    if (isset($_SESSION['cart'][$rid])) {
        unset($_SESSION['cart'][$rid]);
    }
    header("Location: cart.php");
    exit;
}

// ----- Tăng giảm số lượng qua link -----
if (isset($_GET['increase'])) {
    $id = intval($_GET['increase']);
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['qty'] += 1;
    }
    header("Location: cart.php");
    exit;
}

if (isset($_GET['decrease'])) {
    $id = intval($_GET['decrease']);
    if (isset($_SESSION['cart'][$id]) && $_SESSION['cart'][$id]['qty'] > 1) {
        $_SESSION['cart'][$id]['qty'] -= 1;
    }
    header("Location: cart.php");
    exit;
}

// ----- Cập nhật số lượng từ form -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['qty'] as $id => $q) {
        $id = intval($id);
        $q  = intval($q);
        if ($q < 1) $q = 1;
        if ($q > 99) $q = 99;
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['qty'] = $q;
        }
    }
    header("Location: cart.php");
    exit;
}

// ----- Tính tổng -----
$cartItems  = $_SESSION['cart'] ?? [];
$totalPrice = 0;
foreach ($cartItems as $item) {
    $totalPrice += $item['price'] * $item['qty'];
}

// ----- Lấy thông tin user giống header.php -----
$userInfo = null;
if (!empty($_SESSION['user_id']) && isset($conn)) {
    $uid = intval($_SESSION['user_id']);
    $sql = "SELECT MaKH AS id, Hoten AS username, Email AS email, '' AS avatar 
            FROM Khachhang 
            WHERE MaKH = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $userInfo = $res->fetch_assoc();
        }
        $stmt->close();
    }
}
?>

<?php include '../includes/header.php'; ?>
<link rel="stylesheet" href="../assets/css/cart.css">

<div class="cart-container">
    <h2>Giỏ hàng của bạn</h2>

    <?php if (empty($cartItems)) : ?>
        <p>Giỏ hàng đang trống!</p>
        <a href="menu.php" class="btn-link">Xem thực đơn</a>
    <?php else : ?>
        <form method="POST" action="cart.php">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Tạm tính</th>
                        <th>Xóa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td class="product-info">
                                <img src="<?php echo htmlspecialchars(resolveImagePath($item['image'] ?? '')); ?>" alt="">
                                <span><?php echo htmlspecialchars($item['name']); ?></span>
                            </td>
                            <td><?php echo number_format($item['price']); ?>₫</td>
                            <td class="qty-control">
                                <a href="cart.php?decrease=<?php echo $item['id']; ?>" class="qty-btn">-</a>
                                <input type="number"
                                       name="qty[<?php echo $item['id']; ?>]"
                                       value="<?php echo $item['qty']; ?>"
                                       min="1"
                                       max="99">
                                <a href="cart.php?increase=<?php echo $item['id']; ?>" class="qty-btn">+</a>
                            </td>
                            <td><?php echo number_format($item['price'] * $item['qty']); ?>₫</td>
                            <td><a href="cart.php?remove_id=<?php echo $item['id']; ?>" class="remove-link">Xóa</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-footer">
                <button type="submit" name="update_cart" class="btn-update">Cập nhật giỏ hàng</button>
                <span class="total-price">Tổng cộng: <?php echo number_format($totalPrice); ?>₫</span>
            </div>
        </form>

        <div class="checkout">
            <a href="checkout.php" class="btn-checkout">Thanh toán</a>
        </div>
    <?php endif; ?>
</div>

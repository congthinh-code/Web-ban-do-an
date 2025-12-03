<?php 
session_start();
require_once __DIR__ . '/../database/db.php';

// ---------------------------
// CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG
// ---------------------------
if (isset($_POST['update_status'])) {
    $maDH = intval($_POST['MaDH']);
    $tinhtrang = $conn->real_escape_string($_POST['TinhtrangDH']);
    $conn->query("UPDATE Donhang SET TinhtrangDH='$tinhtrang' WHERE MaDH=$maDH");
    header("Location: donhang.php");
    exit;
}

// Lấy danh sách đơn hàng
$sql = "
    SELECT Donhang.*, Users.Hoten 
    FROM Donhang
    LEFT JOIN Users ON Donhang.MaKH = Users.MaKH
    ORDER BY MaDH DESC
";
$dh = $conn->query($sql);
if (!$dh) die("Lỗi SQL: " . $conn->error);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<main class="admin-main">
    <h2>Quản lý đơn hàng</h2>

    <table class="table">
        <tr>
            <th>Mã</th>
            <th>Khách</th>
            <th>Ngày đặt</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
        </tr>

        <?php while ($d = $dh->fetch_assoc()): ?>
        <tr>
            <td><?= $d['MaDH']; ?></td>
            <td><?= $d['Hoten']; ?></td>
            <td><?= $d['Ngaydat']; ?></td>
            <td>
                <form method="POST" style="display:flex; gap:6px; align-items:center;">
                    <input type="hidden" name="MaDH" value="<?= $d['MaDH']; ?>">
                    <select name="TinhtrangDH" style="padding:6px 10px; border-radius:8px;">
                        <?php
                        $statuses = ['Đang xử lý','Đang giao','Đã giao','Đã hủy'];
                        foreach($statuses as $s): 
                        ?>
                            <option value="<?= $s; ?>" <?= $d['TinhtrangDH']==$s?'selected':''; ?>><?= $s; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="update_status" class="btn blue">Cập nhật</button>
                </form>
            </td>
            <td>
                <a href="donhang_view.php?id=<?= $d['MaDH']; ?>" class="btn">Xem chi tiết</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</main>

</body>
</html>

<?php 
session_start();
require_once __DIR__ . '/../database/db.php';

$users = $conn->query("SELECT * FROM Users ORDER BY MaKH DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quản lý người dùng</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<main class="admin-main">
    <h2>Quản lý người dùng</h2>

    <table class="table">
        <tr>
            <th>ID</th><th>Họ tên</th><th>Tài khoản</th><th>Email</th><th>SĐT</th><th>Role</th><th>Hành động</th>
        </tr>

        <?php while ($u = $users->fetch_assoc()): ?>
        <tr>
            <td><?= $u['MaKH']; ?></td>
            <td><?= $u['Hoten']; ?></td>
            <td><?= $u['Taikhoan']; ?></td>
            <td><?= $u['Email']; ?></td>
            <td><?= $u['DienthoaiKH']; ?></td>
            <td><?= $u['Role']; ?></td>
            <td>
                <a href="nguoidung_edit.php?id=<?= $u['MaKH']; ?>" class="btn blue">Sửa</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</main>

</body>
</html>

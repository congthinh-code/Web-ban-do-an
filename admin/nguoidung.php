<?php 
session_start();
require_once __DIR__ . '/../database/db.php';

$users = $conn->query("SELECT * FROM Users ORDER BY UID DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quản lý người dùng</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>

<main class="admin-main">
    <h2>Quản lý người dùng</h2>

    <table class="table">
        <tr>
            <th>ID</th><th>Họ tên</th><th>Tài khoản</th><th>Email</th><th>SĐT</th><th>Role</th><th>Hành động</th>
        </tr>

        <?php while ($u = $users->fetch_assoc()): ?>
        <tr>
            <td><?= $u['UID']; ?></td>
            <td><?= $u['Hoten']; ?></td>
            <td><?= $u['Taikhoan']; ?></td>
            <td><?= $u['Email']; ?></td>
            <td><?= $u['DienthoaiKH']; ?></td>
            <td><?= $u['Role']; ?></td>
            <td>
                <a href="nguoidung_edit.php?id=<?= $u['UID']; ?>" class="btn blue">Sửa</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</main>

</body>
</html>

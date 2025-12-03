<?php
session_start();
require_once __DIR__ . '/../database/db.php';

$sql = "
    SELECT Binhluan.*, Users.Hoten, Monan.Tenmon
    FROM Binhluan
    LEFT JOIN Users ON Binhluan.MaKH = Users.MaKH
    LEFT JOIN Monan ON Binhluan.Mamon = Monan.Mamon
    ORDER BY MaBL DESC
";

$bl = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quản lý bình luận</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<main class="admin-main">
    <h2>Quản lý bình luận</h2>

    <table class="table">
        <tr>
            <th>ID</th><th>Khách</th><th>Món</th><th>Nội dung</th><th>Ngày</th>
        </tr>

        <?php while ($b = $bl->fetch_assoc()): ?>
        <tr>
            <td><?= $b['MaBL']; ?></td>
            <td><?= $b['Hoten']; ?></td>
            <td><?= $b['Tenmon']; ?></td>
            <td><?= $b['Noidung']; ?></td>
            <td><?= $b['Ngaytao']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</main>

</body>
</html>

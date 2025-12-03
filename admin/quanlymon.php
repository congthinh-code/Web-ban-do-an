<?php
//require_once __DIR__ . '/check_admin.php';
//require_once __DIR__ . '/includes/header_admin.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php';

$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page-1)*$perPage;

$where = '';
$params = [];
if ($search !== '') {
    $where = " WHERE Tenmon LIKE ? OR Mamon LIKE ? ";
    $like = "%{$search}%";
}

$total = 0;
if ($where) {
    $stmt = $conn->prepare("SELECT COUNT(*) c FROM monan $where");
    $stmt->bind_param('ss',$like,$like);
    $stmt->execute(); $total = $stmt->get_result()->fetch_assoc()['c'] ?? 0;
} else {
    $res = $conn->query("SELECT COUNT(*) c FROM monan");
    $total = $res ? (int)$res->fetch_assoc()['c'] : 0;
}
$pages = max(1, ceil($total/$perPage));

if ($where) {
    $stmt = $conn->prepare("SELECT * FROM monan $where ORDER BY Mamon DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('ssii', $like, $like, $perPage, $offset); // note: PHP will coerce; works with mysqli
    $stmt->execute(); $res = $stmt->get_result();
    $items = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
} else {
    $stmt = $conn->prepare("SELECT * FROM monan ORDER BY Mamon DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('ii', $perPage, $offset);
    $stmt->execute(); $res = $stmt->get_result();
    $items = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}
?>
<div class="page">
  <h1>Quản lý món</h1>

  <div class="controls">
    <form method="get" class="search-form">
      <input type="text" name="q" placeholder="Tìm tên hoặc mã..." value="<?php echo htmlspecialchars($search); ?>">
      <button class="btn">Tìm</button>
      <a class="btn" href="/admin/themmon.php">Thêm mới</a>
    </form>
  </div>

  <table class="table">
    <thead>
      <tr><th>Mã</th><th>Ảnh</th><th>Tên</th><th>Giá</th><th>Hành động</th></tr>
    </thead>
    <tbody>
    <?php if ($items): foreach($items as $it): ?>
      <tr>
        <td><?php echo htmlspecialchars($it['Mamon']); ?></td>
        <td style="width:120px;"><img src="<?php echo htmlspecialchars(admin_img_url($it['Anh'] ?? '')); ?>" style="height:60px;object-fit:cover;border-radius:6px;"></td>
        <td><?php echo htmlspecialchars($it['Tenmon']); ?></td>
        <td><?php echo number_format($it['Giaban'] ?? 0,0,',','.'); ?> VNĐ</td>
        <td>
          <a class="btn" href="/admin/suamon.php?mamon=<?php echo urlencode($it['Mamon']); ?>">Sửa</a>
          <a class="btn danger" href="/admin/xoamon.php?mamon=<?php echo urlencode($it['Mamon']); ?>" onclick="return confirm('Xóa món này?')">Xóa</a>
        </td>
      </tr>
    <?php endforeach; else: ?>
      <tr><td colspan="5">Không có món.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>

  <div class="pagination">
    <?php for($p=1;$p<=$pages;$p++): ?>
      <a class="page-link <?php echo $p==$page?'active':''; ?>" href="?q=<?php echo urlencode($search); ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
    <?php endfor; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer_admin.php'; ?>

<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/../config/db.php';


// delete
if (($_GET['delete'] ?? '') !== '') {
    $id = (int)$_GET['delete'];
    $pdo->prepare('DELETE FROM feedback WHERE feedback_id=?')->execute([$id]);
    header('Location: feedback.php');
    exit;
}

// Approve feedback
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $pdo->prepare('UPDATE feedback SET state="Active" WHERE feedback_id=?')->execute([$id]);
    header('Location: feedback.php');
    exit;
}

// Deactivate feedback
if (isset($_GET['deactivate'])) {
    $id = (int)$_GET['deactivate'];
    $pdo->prepare('UPDATE feedback SET state="Inactive" WHERE feedback_id=?')->execute([$id]);
    header('Location: feedback.php');
    exit;
}


$q = trim($_GET['q'] ?? '');
$sql = "SELECT f.feedback_id, f.rating, f.comment, f.created_at, f.state,
        p.name AS product_name, c.name AS customer_name, c.email
        FROM feedback f
        JOIN products p ON p.product_id = f.product_id
        JOIN customers c ON c.customer_id = f.customer_id";
$params = [];
if ($q !== '') {
    $sql .= " WHERE p.name LIKE ? OR c.name LIKE ? OR c.email LIKE ? OR f.comment LIKE ? ORDER BY f.created_at DESC";
    $like = '%' . $q . '%';
    $params = [$like, $like, $like, $like];
}
$rows = $pdo->prepare($sql);
$rows->execute($params);
$data = $rows->fetchAll();
$pageTitle = 'Feedback';
?>
<a class="btn" href="index.php">← Dashboard</a>
<div class="card">
    <h2>Feedback</h2>
    <form method="get"><input name="q" placeholder="Search product/customer/comment" value="<?= htmlspecialchars($q) ?>"></form>
    <?php foreach ($data as $r): ?>
        <div class="card" style="width: 40%;">
            <div><strong><?= htmlspecialchars($r['product_name']) ?></strong> · ⭐ <?= (int)$r['rating'] ?> · <span class="small"><?= htmlspecialchars($r['created_at']) ?></span></div>
            <div class="small">By <?= htmlspecialchars($r['customer_name']) ?> (<?= htmlspecialchars($r['email']) ?>)</div>
            <?php if ($r['comment']): ?><p><?= nl2br(htmlspecialchars($r['comment'])) ?></p><?php endif; ?>
            <?php if ($r['state'] === 'Inactive'): ?>
                <a class="btn" href="feedback.php?approve=<?= (int)$r['feedback_id'] ?>">Approve</a>
            <?php else: ?>
                <a class="btn" href="feedback.php?deactivate=<?= (int)$r['feedback_id'] ?>">Deactivate</a>
            <?php endif; ?>
            <a class="btn" href="feedback.php?delete=<?= (int)$r['feedback_id'] ?>" onclick="return confirm('Delete this feedback?');">Delete</a>
        </div>
    <?php endforeach; ?>
    <?php if (!$data): ?><p class="small">No feedback found.</p><?php endif; ?>
</div>
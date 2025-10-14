<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/../config/db.php';

$adminId = (int)($_SESSION['fb_admin_auth'] ?? 0);
$pdo->prepare('SET @actor_id := ?')->execute([$adminId]);

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
    <form method="get" style="margin-bottom:10px;">
        <input name="q" placeholder="Search product / customer / comment"
            value="<?= htmlspecialchars($q) ?>" style="width:60%;">
        <button class="btn" type="submit">Search</button>
    </form>

    <table class="small" style="width:100%; border-collapse:collapse;">
        <tr>
            <th align="left">Product</th>
            <th align="left">Customer</th>
            <th align="left">Email</th>
            <th align="center">Rating</th>
            <th align="left">Comment</th>
            <th align="center">State</th>
            <th align="left">Date</th>
            <th align="center">Actions</th>
        </tr>

        <?php foreach ($data as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['product_name']) ?></td>
                <td><?= htmlspecialchars($r['customer_name']) ?></td>
                <td><?= htmlspecialchars($r['email']) ?></td>
                <td align="center">⭐ <?= (int)$r['rating'] ?></td>
                <td style="max-width:250px; white-space:pre-wrap;">
                    <?= htmlspecialchars(mb_strimwidth($r['comment'], 0, 150, '…')) ?>
                </td>
                <td align="center">
                    <span class="badge" style="background:<?= $r['state'] === 'Active' ? '#c8f7c5' : '#f9d0c4' ?>">
                        <?= htmlspecialchars($r['state']) ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($r['created_at']) ?></td>
                <td align="center" style="white-space:nowrap;">
                    <a class="btn" href="feedback_view.php?id=<?= (int)$r['feedback_id'] ?>">View</a>
                    <?php if ($r['state'] === 'Inactive'): ?>
                        <a class="btn" href="feedback.php?approve=<?= (int)$r['feedback_id'] ?>">Approve</a>
                    <?php else: ?>
                        <a class="btn" href="feedback.php?deactivate=<?= (int)$r['feedback_id'] ?>">Deactivate</a>
                    <?php endif; ?>
                    <a class="btn" href="feedback.php?delete=<?= (int)$r['feedback_id'] ?>"
                        onclick="return confirm('Delete this feedback?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if (!$data): ?>
            <tr>
                <td colspan="8" align="center" class="small">No feedback found.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>
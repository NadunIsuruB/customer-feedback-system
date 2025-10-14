<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/../config/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    exit('Feedback not found.');
}

$stmt = $pdo->prepare("
    SELECT f.feedback_id, f.rating, f.comment, f.state, f.created_at,
           p.name AS product_name, p.category, 
           c.name AS customer_name, c.email, c.phone
    FROM feedback f
    JOIN products p ON p.product_id = f.product_id
    JOIN customers c ON c.customer_id = f.customer_id
    WHERE f.feedback_id = ?
");
$stmt->execute([$id]);
$fb = $stmt->fetch();

if (!$fb) {
    http_response_code(404);
    exit('Feedback not found.');
}
?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Feedback #<?= $fb['feedback_id'] ?></title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>

<body>
    <div class="container">
        <a class="btn" href="feedback.php">← Back</a>

        <div class="card">
            <h2>Feedback Details</h2>
            <table class="small" style="width:100%;">
                <tr>
                    <th align="left">Product</th>
                    <td><?= htmlspecialchars($fb['product_name']) ?> (<?= htmlspecialchars($fb['category']) ?>)</td>
                </tr>
                <tr>
                    <th align="left">Customer</th>
                    <td><?= htmlspecialchars($fb['customer_name']) ?></td>
                </tr>
                <tr>
                    <th align="left">Email</th>
                    <td><?= htmlspecialchars($fb['email']) ?></td>
                </tr>
                <?php if (!empty($fb['phone'])): ?>
                    <tr>
                        <th align="left">Phone</th>
                        <td><?= htmlspecialchars($fb['phone']) ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <th align="left">Rating</th>
                    <td>⭐ <?= (int)$fb['rating'] ?></td>
                </tr>
                <tr>
                    <th align="left">State</th>
                    <td><?= htmlspecialchars($fb['state']) ?></td>
                </tr>
                <tr>
                    <th align="left">Date</th>
                    <td><?= htmlspecialchars($fb['created_at']) ?></td>
                </tr>
            </table>

            <h3 style="margin-top:16px;">Comment</h3>
            <?php if (trim($fb['comment']) === ''): ?>
                <p class="small">No comment provided.</p>
            <?php else: ?>
                <div style="white-space:pre-wrap; border:1px solid #ddd; border-radius:8px; padding:12px; background:#fafafa;">
                    <?= htmlspecialchars($fb['comment']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
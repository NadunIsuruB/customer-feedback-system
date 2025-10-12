<?php
// DEV: show errors (remove later)
ini_set('display_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/config/db.php';        // defines $pdo
require_once __DIR__ . '/config/csrf.php';   // defines csrf_field(), csrf_check()

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    exit('Not found');
}

// ----- load product + aggregates -----
$sql = "SELECT p.product_id, p.name, p.category, p.price_cents,
               ROUND(AVG(f.rating),1) AS avg_rating,
               COUNT(f.feedback_id)   AS reviews
        FROM products p
        LEFT JOIN feedback f ON f.product_id = p.product_id
        WHERE p.product_id = ?
        GROUP BY p.product_id";
$pstmt = $pdo->prepare($sql);
$pstmt->execute([$id]);
$product = $pstmt->fetch();
if (!$product) {
    http_response_code(404);
    exit('Not found');
}

// ----- latest feedback -----
$fb = $pdo->prepare(
    "SELECT f.*, c.name, c.email
     FROM feedback f
     JOIN customers c ON c.customer_id = f.customer_id
    WHERE f.product_id = ?
 ORDER BY f.created_at DESC
    LIMIT 50"
);
$fb->execute([$id]);
$feedback = $fb->fetchAll();
$pageTitle = 'Product';
?>
<a class="btn" href="index.php">← Back</a>

<div class="card">
    <h2><?= htmlspecialchars($product['name']) ?></h2>
    <div class="small">Category: <span class="badge"><?= htmlspecialchars($product['category']) ?></span></div>
    <p class="rating">⭐ <?= $product['avg_rating'] ?: '—' ?> (<?= (int)$product['reviews'] ?>)</p>
    <p><strong>LKR <?= number_format(((int)$product['price_cents']) / 100, 2) ?></strong></p>
</div>

<div class="card">
    <h3>Add your feedback</h3>
    <form method="post" action="submit-feedback.php">
        <?= csrf_field() ?>
        <input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
        <label>Name<input name="name" required></label>
        <label>Email<input name="email" type="email" required></label>
        <label>Phone (optional)<input name="phone"></label>
        <label>Rating
            <select name="rating" required>
                <option value="">Choose…</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </label>
        <label>Comment (optional)
            <textarea name="comment" rows="4" maxlength="2000"></textarea>
        </label>
        <button class="btn" type="submit">Submit</button>
    </form>
</div>

<div class="card">
    <h3>Recent feedback</h3>
    <?php if (!$feedback): ?>
        <p class="small">No feedback yet.</p>
        <?php else: foreach ($feedback as $f): ?>
            <div class="card">
                <div><strong><?= htmlspecialchars($f['name']) ?></strong> · ⭐ <?= (int)$f['rating'] ?> ·
                    <span class="small"><?= htmlspecialchars($f['created_at']) ?></span>
                </div>
                <?php if ($f['comment']): ?>
                    <p><?= nl2br(htmlspecialchars($f['comment'])) ?></p>
                <?php endif; ?>
            </div>
    <?php endforeach;
    endif; ?>
</div>
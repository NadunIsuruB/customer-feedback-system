<?php require __DIR__ . '/config/db.php';
?>

<h1>Products</h1>
<div class="grid cols-12">
    <?php
    $stmt = $pdo->query(
        "SELECT p.product_id, p.name, p.category, p.price_cents,
ROUND(AVG(f.rating),1) AS avg_rating, COUNT(f.feedback_id) AS reviews
FROM products p
LEFT JOIN feedback f ON f.product_id = p.product_id
AND f.state = 'Active'
GROUP BY p.product_id
ORDER BY p.created_at DESC"
    );
    foreach ($stmt as $row): ?>
        <div class="card" style="width: 30vw;">
            <h3><?= htmlspecialchars($row['name']) ?></h3>
            <div class="small">Category: <span class="badge"><?= htmlspecialchars($row['category']) ?></span></div>
            <p class="rating">⭐ <?= $row['avg_rating'] ?: '—' ?> (<?= $row['reviews'] ?>)</p>
            <p><strong>LKR <?= number_format($row['price_cents'] / 100, 2) ?></strong></p>
            <a class="btn" href="product.php?id=<?= (int)$row['product_id'] ?>">View</a>
        </div>
    <?php endforeach; ?>
</div>
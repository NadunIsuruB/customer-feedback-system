<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/../config/db.php';


// create/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['product_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $cat = trim($_POST['category'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    if ($name !== '' && $cat !== '' && $price >= 0) {
        $cents = (int)round($price * 100);
        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE products SET name=?, category=?, price_cents=? WHERE product_id=?');
            $stmt->execute([$name, $cat, $cents, $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO products (name,category,price_cents) VALUES (?,?,?)');
            $stmt->execute([$name, $cat, $cents]);
        }
    }
    header('Location: products.php');
    exit;
}


// delete
if (($_GET['delete'] ?? '') !== '') {
    $id = (int)$_GET['delete'];
    $pdo->prepare('DELETE FROM products WHERE product_id=?')->execute([$id]);
    header('Location: products.php');
    exit;
}


$products = $pdo->query('SELECT product_id,name,category,price_cents,created_at FROM products ORDER BY created_at DESC')->fetchAll();
$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare('SELECT * FROM products WHERE product_id=?');
    $s->execute([(int)$_GET['edit']]);
    $edit = $s->fetch();
}
$showForm = isset($_GET['add']) || $edit;
$pageTitle = 'Products';
?>
<a class="btn" href="index.php">← Dashboard</a>

<div class="card">
    <h2>Products</h2>
    <a class="btn" href="products.php?add=1" style="margin-top:8px;display:inline-block;">+ Add Product</a>

    <table class="small" style="width:100%;margin-top:10px;">
        <tr>
            <th align="left">Name</th>
            <th align="left">Category</th>
            <th align="right">Price</th>
            <th align="left">Created</th>
            <th></th>
        </tr>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= htmlspecialchars($p['category']) ?></td>
                <td align="right">LKR <?= number_format($p['price_cents'] / 100, 2) ?></td>
                <td><?= htmlspecialchars($p['created_at']) ?></td>
                <td>
                    <a class="btn" href="products.php?edit=<?= (int)$p['product_id'] ?>">Edit</a>
                    <a class="btn" href="products.php?delete=<?= (int)$p['product_id'] ?>" onclick="return confirm('Delete this product? Feedback will also be removed.');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php if ($showForm): ?>
    <div class="card">
        <h3><?= $edit ? 'Edit Product' : 'Add Product' ?></h3>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= (int)($edit['product_id'] ?? 0) ?>">
            <label>Name<input name="name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required></label>
            <label>Category<input name="category" value="<?= htmlspecialchars($edit['category'] ?? '') ?>" required></label>
            <label>Price (LKR)<input name="price" type="number" step="0.01" min="0" value="<?= isset($edit) ? number_format($edit['price_cents'] / 100, 2, '.', '') : '' ?>" required></label>

            <button class="btn" type="submit">Save</button>
            <a class="btn" href="products.php" style="background:#eee;">Cancel</a>
        </form>
    </div>
<?php endif; ?>
<?php
session_start();
require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/csrf.php';
require __DIR__ . '/../config/admin.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $u = trim($_POST['user'] ?? '');
    $p = $_POST['pass'] ?? '';
    $stmt = $pdo->prepare('SELECT user_id, password_hash FROM users WHERE username=?');
    $stmt->execute([$u]);
    $row = $stmt->fetch();
    if ($row && password_verify($p, $row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION[$ADMIN_SESSION_KEY] = (int)$row['user_id'];
        header('Location: index.php');
        exit;
    }
    $err = 'Invalid credentials';
}
$pageTitle = 'Login';
?>
<div class="card">
    <h2>Admin Login</h2>
    <?php if (!empty($err)): ?><p class="small" style="color:#b00;"><?= htmlspecialchars($err) ?></p><?php endif; ?>
    <form method="post">
        <?= csrf_field() ?>
        <label>Username<input name="user" required></label>
        <label>Password<input name="pass" type="password" required></label>
        <button class="btn" type="submit">Login</button>
    </form>
</div>
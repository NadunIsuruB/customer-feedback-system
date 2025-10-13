<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/csrf.php';
require __DIR__ . '/../config/admin.php';
$uid = (int)$_SESSION[$ADMIN_SESSION_KEY];
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $cur = $_POST['current'] ?? '';
    $nw1 = $_POST['new1'] ?? '';
    $nw2 = $_POST['new2'] ?? '';
    if ($nw1 !== $nw2) {
        $msg = 'New passwords do not match';
    } else {
        $s = $pdo->prepare('SELECT password_hash FROM users WHERE user_id=?');
        $s->execute([$uid]);
        $row = $s->fetch();
        if (!$row || !password_verify($cur, $row['password_hash'])) {
            $msg = 'Current password wrong';
        } else {
            $hash = password_hash($nw1, PASSWORD_DEFAULT);
            $u = $pdo->prepare('UPDATE users SET password_hash=? WHERE user_id=?');
            $u->execute([$hash, $uid]);
            $msg = 'Password updated';
        }
    }
}
$pageTitle = 'Account';
?>
<a class="btn" href="index.php">← Dashboard</a>
<div class="card" style="max-width:400px;">
    <h2>Change Password</h2>
    <?php if ($msg): ?><p class="small"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
    <form method="post">
        <?= csrf_field() ?>
        <label>Current password<input name="current" type="password" required></label>
        <label>New password<input name="new1" type="password" required></label>
        <label>Repeat new password<input name="new2" type="password" required></label>
        <button class="btn" type="submit">Update</button>
    </form>
</div>
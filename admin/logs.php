<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/../config/db.php';

$etype = $_GET['type']   ?? '';
$action = $_GET['action'] ?? '';
$q     = trim($_GET['q'] ?? '');

$sql = "SELECT log_id, entity_type, entity_id, action, details, actor_id, created_at
          FROM audit_log WHERE 1=1";
$params = [];

if ($etype !== '') {
    $sql .= " AND entity_type = ?";
    $params[] = $etype;
}
if ($action !== '') {
    $sql .= " AND action = ?";
    $params[] = $action;
}
if ($q !== '') {
    $sql .= " AND details LIKE ?";
    $params[] = '%' . $q . '%';
}

$sql .= " ORDER BY created_at DESC, log_id DESC LIMIT 500";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

$pageTitle = 'Audit Log';
?>
<a class="btn" href="index.php">← Dashboard</a>
<div class="card">
    <h2>Audit Log</h2>
    <form method="get" style="display:flex; gap:8px; align-items:flex-end; margin:8px 0;">
        <label>Type
            <select name="type">
                <option value="">All</option>
                <option value="product" <?= $etype === 'product' ? 'selected' : '' ?>>Product</option>
                <option value="feedback" <?= $etype === 'feedback' ? 'selected' : '' ?>>Feedback</option>
            </select>
        </label>
        <label>Action
            <select name="action">
                <option value="">All</option>
                <?php foreach (['create', 'update', 'delete', 'approve', 'deactivate'] as $a): ?>
                    <option value="<?= $a ?>" <?= $action === $a ? 'selected' : '' ?>><?= ucfirst($a) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Search details
            <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder='name / rating / "from"'>
        </label>
        <button class="btn" type="submit">Filter</button>
        <a class="btn" href="logs.php">Clear</a>
    </form>

    <table class="small" style="width:100%;">
        <tr>
            <th align="left">When</th>
            <th align="left">Type</th>
            <th align="left">Entity</th>
            <th align="left">Action</th>
            <th align="left">Details</th>
            <th align="left">Actor</th>
        </tr>
        <?php foreach ($logs as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td><?= htmlspecialchars($row['entity_type']) ?></td>
                <td>#<?= (int)$row['entity_id'] ?></td>
                <td><?= htmlspecialchars($row['action']) ?></td>
                <td><code style="white-space:pre-wrap;"><?= htmlspecialchars($row['details'] ?? '') ?></code></td>
                <td><?= $row['actor_id'] !== null ? (int)$row['actor_id'] : '—' ?></td>
            </tr>
        <?php endforeach;
        if (!$logs): ?>
            <tr>
                <td colspan="6" class="small">No logs found.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>
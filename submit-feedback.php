<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}
require __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/csrf.php';
csrf_check();


$product_id = (int)($_POST['product_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');


if ($product_id <= 0 || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $rating < 1 || $rating > 5) {
    http_response_code(400);
    exit('Invalid data');
}


$pdo->beginTransaction();
try {
    // upsert customer by email
    $sel = $pdo->prepare('SELECT customer_id FROM customers WHERE email=?');
    $sel->execute([$email]);
    $cid = $sel->fetchColumn();


    if (!$cid) {
        $ins = $pdo->prepare('INSERT INTO customers (name,email,phone) VALUES (?,?,?)');
        $ins->execute([$name, $email, $phone ?: null]);
        $cid = (int)$pdo->lastInsertId();
    } else {
        // keep latest name/phone fresh (optional)
        $upd = $pdo->prepare('UPDATE customers SET name=?, phone=? WHERE customer_id=?');
        $upd->execute([$name, $phone ?: null, $cid]);
    }


    // insert feedback
    $fb = $pdo->prepare('INSERT INTO feedback (customer_id,product_id,rating,comment) VALUES (?,?,?,?)');
    $fb->execute([$cid, $product_id, $rating, $comment ?: null]);


    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    exit('Failed');
}


header('Location: product.php?id=' . $product_id);

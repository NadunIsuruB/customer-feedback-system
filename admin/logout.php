<?php
session_start();
require __DIR__ . '/../config/admin.php';
unset($_SESSION[$ADMIN_SESSION_KEY]);
header('Location: login.php');

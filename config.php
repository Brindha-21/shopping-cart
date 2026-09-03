<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbname = 'shopping_cart_db';
$username = 'root';
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("<div style='font-family:sans-serif; padding:20px; background:#ffebee; color:#c62828; border-radius:8px; margin:20px; max-width: 600px;'>
        <h2 style='margin-top:0;'>Database Connection Error</h2>
        <p>Could not connect to MySQL database <strong>$dbname</strong>.</p>
        <p><strong>Error details:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
        <p>Please make sure MySQL service is running and you have imported <code>database.sql</code> in phpMyAdmin / MySQL.</p>
    </div>");
}
function getCartItemCount() {
    $count = 0;
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $qty) {
            $count += (int)$qty;
        }
    }
    return $count;
}
?>

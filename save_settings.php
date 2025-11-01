<?php
session_start();
require 'vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$PDO = new PDO('mysql:dbname=php_users;host=localhost;charset=utf8mb4', 'root', '');
$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$bg_color = $_POST['bg_color'] ?? '#ffffff';
$text_color = $_POST['text_color'] ?? '#000000';

$stmt = $PDO->prepare("UPDATE users SET bg_color = ?, text_color = ? WHERE id = ?");
$stmt->execute([$bg_color, $text_color, $_SESSION['user_id']]);


setcookie('bg_color', $bg_color, time() + 30*24*3600, '/');
setcookie('text_color', $text_color, time() + 30*24*3600, '/');

header('Location: index.php');
exit;
?>

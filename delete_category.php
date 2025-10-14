<?php
require_once 'config.php';

$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($category_id <= 0) {
    die("Invalid category ID");
}

try {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->execute([$category_id]);

    header("Location: category.php?msg=deleted");
    exit;
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

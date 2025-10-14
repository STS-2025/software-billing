<?php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Invoice ID");
}

$pi_id = intval($_GET['id']);

try {
    $pdo->beginTransaction();
    $pdo->prepare("DELETE FROM purchase_invoice_items WHERE pi_id=?")->execute([$pi_id]);
    $pdo->prepare("DELETE FROM purchase_invoices WHERE pi_id=?")->execute([$pi_id]);
    $pdo->commit();
    header("Location: purchase_invoice_list.php?msg=deleted");
} catch (Exception $e) {
    $pdo->rollBack();
    die("Error deleting invoice: " . $e->getMessage());
}

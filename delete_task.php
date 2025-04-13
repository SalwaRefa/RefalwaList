<?php
require 'db.php';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}

header("Location: tasks.php");
exit;
?>

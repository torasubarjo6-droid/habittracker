<?php
require_once __DIR__ . '/../includes/session.php';
requireLogin();

$habitId = (int)($_GET['id'] ?? 0);

// Verifikasi habit milik user ini sebelum hapus
$stmt = $pdo->prepare("SELECT nama_habit FROM habits WHERE id = ? AND user_id = ?");
$stmt->execute([$habitId, $_SESSION['user_id']]);
$habit = $stmt->fetch();

if ($habit) {
    $stmt = $pdo->prepare("DELETE FROM habits WHERE id = ? AND user_id = ?");
    $stmt->execute([$habitId, $_SESSION['user_id']]);
    $_SESSION['flash'] = "Habit '{$habit['nama_habit']}' berhasil dihapus.";
}

header('Location: ' . BASE_URL . '/habits/index.php');
exit;

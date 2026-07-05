<?php
require_once __DIR__ . '/../includes/session.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$habitId = (int)($_POST['habit_id'] ?? 0);
$today   = date('Y-m-d');

// Verifikasi habit milik user ini
$stmt = $pdo->prepare("SELECT id FROM habits WHERE id = ? AND user_id = ? AND is_active = 1");
$stmt->execute([$habitId, $_SESSION['user_id']]);
if (!$stmt->fetch()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

// Cek apakah log hari ini sudah ada
$stmt = $pdo->prepare("SELECT id FROM habit_logs WHERE habit_id = ? AND tanggal = ?");
$stmt->execute([$habitId, $today]);
$existing = $stmt->fetch();

if ($existing) {
    // Sudah ada → hapus (uncheck)
    $stmt = $pdo->prepare("DELETE FROM habit_logs WHERE id = ?");
    $stmt->execute([$existing['id']]);
} else {
    // Belum ada → buat baru (check)
    $stmt = $pdo->prepare("INSERT INTO habit_logs (habit_id, tanggal, status) VALUES (?, ?, 1)");
    $stmt->execute([$habitId, $today]);
}

header('Location: ' . BASE_URL . '/dashboard.php');
exit;

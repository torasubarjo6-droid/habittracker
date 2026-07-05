<?php
require_once __DIR__ . '/../includes/session.php';
requireLogin();

$habitId = (int)($_GET['habit_id'] ?? 0);

// Verifikasi habit milik user ini
$stmt = $pdo->prepare("SELECT * FROM habits WHERE id = ? AND user_id = ?");
$stmt->execute([$habitId, $_SESSION['user_id']]);
$habit = $stmt->fetch();

if (!$habit) {
    header('Location: ' . BASE_URL . '/habits/index.php');
    exit;
}

$pageTitle = 'Riwayat: ' . $habit['nama_habit'] . ' - HabitTrack';

// Ambil log 30 hari terakhir
$days = [];
for ($i = 29; $i >= 0; $i--) {
    $days[date('Y-m-d', strtotime("-{$i} days"))] = null;
}

$stmt = $pdo->prepare("SELECT tanggal, status FROM habit_logs WHERE habit_id = ? AND tanggal >= ?");
$stmt->execute([$habitId, array_key_first($days)]);
foreach ($stmt->fetchAll() as $log) {
    $tgl = substr($log['tanggal'], 0, 10);
    if (array_key_exists($tgl, $days)) {
        $days[$tgl] = (bool)$log['status'];
    }
}

$totalDone   = count(array_filter($days, function ($v) {
    return $v === true;
}));
$streak      = getStreak($pdo, $habitId);

require_once __DIR__ . '/../includes/header.php';
?>
?>

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="<?= BASE_URL ?>/habits/index.php"
           class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600 transition mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
        <div class="flex items-center gap-3">
            <div class="w-4 h-4 rounded-full"
                 style="background-color: <?= htmlspecialchars($habit['warna']) ?>"></div>
            <h2 class="text-2xl font-bold text-gray-800">
                <?= htmlspecialchars($habit['nama_habit']) ?>
            </h2>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= kategoriClass($habit['kategori']) ?>">
                <?= htmlspecialchars($habit['kategori']) ?>
            </span>
        </div>
    </div>

    <!-- Statistik -->
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
            <div class="text-3xl font-bold text-green-500"><?= $totalDone ?></div>
            <div class="text-xs text-gray-500 mt-1">Selesai (30 hari)</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
            <div class="text-3xl font-bold text-amber-500"><?= $streak ?></div>
            <div class="text-xs text-gray-500 mt-1">Streak Aktif</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
            <div class="text-3xl font-bold text-indigo-600">
                <?= $totalDone > 0 ? round($totalDone / 30 * 100) : 0 ?>%
            </div>
            <div class="text-xs text-gray-500 mt-1">Konsistensi</div>
        </div>
    </div>

    <!-- Grid 30 hari -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-500 mb-4">30 Hari Terakhir</h3>
        <div class="grid grid-cols-10 gap-2">
            <?php foreach ($days as $date => $status): ?>
            <div class="flex flex-col items-center gap-1" title="<?= $date ?>">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center
                            <?= $status === true
                                ? 'bg-green-400'
                                : ($status === false
                                    ? 'bg-red-100'
                                    : 'bg-gray-100') ?>">
                    <?php if ($status === true): ?>
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <?php elseif ($status === false): ?>
                    <svg class="w-3 h-3 text-red-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    <?php endif; ?>
                </div>
                <span class="text-xs text-gray-400"><?= date('j', strtotime($date)) ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Legend -->
        <div class="flex items-center gap-4 mt-5 pt-4 border-t border-gray-100 text-xs text-gray-500">
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded bg-green-400"></div> Selesai
            </div>
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded bg-red-100"></div> Terlewat
            </div>
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded bg-gray-100"></div> Belum ada data
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

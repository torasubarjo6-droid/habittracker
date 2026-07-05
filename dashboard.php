<?php
require_once __DIR__ . '/includes/session.php';
requireLogin();

$pageTitle = 'Dashboard - HabitTrack';
$today     = date('Y-m-d');
$userId    = $_SESSION['user_id'];

// Ambil semua habit aktif milik user
$stmt = $pdo->prepare("SELECT * FROM habits WHERE user_id = ? AND is_active = 1 ORDER BY created_at ASC");
$stmt->execute([$userId]);
$habits = $stmt->fetchAll();

// Susun data lengkap per habit
$habitData = [];
foreach ($habits as $habit) {
    // Status hari ini
    $stmt = $pdo->prepare("SELECT id, status FROM habit_logs WHERE habit_id = ? AND tanggal = ?");
    $stmt->execute([$habit['id'], $today]);
    $todayLog = $stmt->fetch();

    $habitData[] = [
        'habit'  => $habit,
        'today'  => $todayLog,
        'week'   => getWeekHistory($pdo, $habit['id']),
        'streak' => getStreak($pdo, $habit['id']),
    ];
}

// Ringkasan statistik
$totalHabits = count($habits);
$doneToday   = count(array_filter($habitData, function ($h) {
    return $h['today'] && $h['today']['status'];
}));
$maxStreak   = $habitData ? max(array_column($habitData, 'streak')) : 0;

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pageTitle = 'Dashboard - HabitTrack';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Greeting -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-2">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            Selamat datang, <?= htmlspecialchars($_SESSION['name']) ?>!
        </h2>
        <p class="text-gray-500 text-sm mt-0.5">
            <?= date('l, d F Y') ?>
        </p>
    </div>
    <a href="<?= BASE_URL ?>/habits/create.php"
       class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Habit
    </a>
</div>

<!-- Flash message -->
<?php if ($flash): ?>
<div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg mb-5">
    <?= htmlspecialchars($flash) ?>
</div>
<?php endif; ?>

<!-- Stats cards -->
<div class="grid grid-cols-3 gap-4 mb-8">
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
        <div class="text-3xl font-bold text-indigo-600"><?= $totalHabits ?></div>
        <div class="text-xs text-gray-500 mt-1">Total Habit</div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
        <div class="text-3xl font-bold text-green-500"><?= $doneToday ?>/<?= $totalHabits ?></div>
        <div class="text-xs text-gray-500 mt-1">Selesai Hari Ini</div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
        <div class="text-3xl font-bold text-amber-500"><?= $maxStreak ?></div>
        <div class="text-xs text-gray-500 mt-1">Streak Terpanjang</div>
    </div>
</div>

<!-- Habit list -->
<?php if (empty($habitData)): ?>
<div class="bg-white rounded-xl p-10 text-center shadow-sm border border-gray-100">
    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <p class="text-gray-400 text-sm">Belum ada habit. Yuk tambah habit pertamamu!</p>
    <a href="<?= BASE_URL ?>/habits/create.php"
       class="inline-block mt-4 bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
        Tambah Habit
    </a>
</div>

<?php else: ?>
<h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Habit Hari Ini</h3>
<div class="flex flex-col gap-4">
    <?php foreach ($habitData as $hd):
        $habit    = $hd['habit'];
        $todayLog = $hd['today'];
        $week     = $hd['week'];
        $streak   = $hd['streak'];
        $isDone   = $todayLog && $todayLog['status'];
        $days     = ['S','S','R','K','J','S','M']; // label hari (Senin-Minggu)
        $weekKeys = array_keys($week);
    ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5
                <?= $isDone ? 'border-l-4' : '' ?>"
         style="<?= $isDone ? 'border-left-color:' . htmlspecialchars($habit['warna']) : '' ?>">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">

            <!-- Kiri: info habit -->
            <div class="flex items-center gap-3 flex-1">
                <div class="w-10 h-10 rounded-xl flex-shrink-0"
                     style="background-color: <?= htmlspecialchars($habit['warna']) ?>22">
                    <div class="w-full h-full flex items-center justify-center">
                        <div class="w-4 h-4 rounded-full"
                             style="background-color: <?= htmlspecialchars($habit['warna']) ?>"></div>
                    </div>
                </div>
                <div>
                    <div class="font-semibold text-gray-800 text-sm"><?= htmlspecialchars($habit['nama_habit']) ?></div>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= kategoriClass($habit['kategori']) ?>">
                        <?= htmlspecialchars($habit['kategori']) ?>
                    </span>
                </div>
            </div>

            <!-- Tengah: 7-day dots -->
            <div class="flex items-center gap-1.5">
                <?php foreach ($week as $date => $status): ?>
                <div class="flex flex-col items-center gap-1">
                    <div class="w-6 h-6 rounded-full <?= $status === true ? 'bg-green-400' : ($status === false ? 'bg-red-200' : 'bg-gray-100') ?> 
                                flex items-center justify-center">
                        <?php if ($status === true): ?>
                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php endif; ?>
                    </div>
                    <span class="text-xs text-gray-400"><?= date('j', strtotime($date)) ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Kanan: streak + toggle -->
            <div class="flex items-center gap-3">
                <div class="text-center">
                    <div class="text-lg font-bold text-amber-500"><?= $streak ?></div>
                    <div class="text-xs text-gray-400">streak</div>
                </div>
                <form method="POST" action="<?= BASE_URL ?>/logs/toggle.php">
                    <input type="hidden" name="habit_id" value="<?= $habit['id'] ?>">
                    <button type="submit"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition
                                   <?= $isDone
                                       ? 'bg-green-100 text-green-700 hover:bg-red-50 hover:text-red-500'
                                       : 'bg-indigo-600 text-white hover:bg-indigo-700' ?>">
                        <?= $isDone ? 'Selesai' : 'Tandai Selesai' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

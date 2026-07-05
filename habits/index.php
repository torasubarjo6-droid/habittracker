<?php
require_once __DIR__ . '/../includes/session.php';
requireLogin();

$pageTitle = 'Kelola Habit - HabitTrack';
$userId    = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM habits WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$habits = $stmt->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Kelola Habit</h2>
        <p class="text-gray-500 text-sm mt-0.5">Tambah, edit, atau hapus habit kamu</p>
    </div>
    <a href="<?= BASE_URL ?>/habits/create.php"
       class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Habit
    </a>
</div>

<?php if ($flash): ?>
<div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg mb-5">
    <?= htmlspecialchars($flash) ?>
</div>
<?php endif; ?>

<?php if (empty($habits)): ?>
<div class="bg-white rounded-xl p-10 text-center shadow-sm border border-gray-100">
    <p class="text-gray-400 text-sm">Belum ada habit. Tambahkan habit pertamamu!</p>
    <a href="<?= BASE_URL ?>/habits/create.php"
       class="inline-block mt-4 bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
        Tambah Habit
    </a>
</div>

<?php else: ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-gray-500 font-medium">Habit</th>
                <th class="text-left px-5 py-3 text-gray-500 font-medium">Kategori</th>
                <th class="text-left px-5 py-3 text-gray-500 font-medium">Streak</th>
                <th class="text-left px-5 py-3 text-gray-500 font-medium">Dibuat</th>
                <th class="text-center px-5 py-3 text-gray-500 font-medium">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php foreach ($habits as $habit):
                $streak = getStreak($pdo, $habit['id']);
            ?>
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full flex-shrink-0"
                             style="background-color: <?= htmlspecialchars($habit['warna']) ?>"></div>
                        <span class="font-medium text-gray-800">
                            <?= htmlspecialchars($habit['nama_habit']) ?>
                        </span>
                    </div>
                </td>
                <td class="px-5 py-4">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= kategoriClass($habit['kategori']) ?>">
                        <?= htmlspecialchars($habit['kategori']) ?>
                    </span>
                </td>
                <td class="px-5 py-4">
                    <span class="font-semibold text-amber-500"><?= $streak ?></span>
                    <span class="text-gray-400 text-xs ml-1">hari</span>
                </td>
                <td class="px-5 py-4 text-gray-400">
                    <?= date('d M Y', strtotime($habit['created_at'])) ?>
                </td>
                <td class="px-5 py-4">
                    <div class="flex items-center justify-center gap-2">
                        <a href="<?= BASE_URL ?>/logs/history.php?habit_id=<?= $habit['id'] ?>"
                           class="text-xs text-gray-500 hover:text-indigo-600 border border-gray-200 px-2.5 py-1 rounded-lg hover:border-indigo-300 transition">
                            Riwayat
                        </a>
                        <a href="<?= BASE_URL ?>/habits/edit.php?id=<?= $habit['id'] ?>"
                           class="text-xs text-indigo-600 border border-indigo-200 px-2.5 py-1 rounded-lg hover:bg-indigo-50 transition">
                            Edit
                        </a>
                        <a href="<?= BASE_URL ?>/habits/delete.php?id=<?= $habit['id'] ?>"
                           class="text-xs text-red-500 border border-red-200 px-2.5 py-1 rounded-lg hover:bg-red-50 transition"
                           onclick="return confirm('Yakin hapus habit ini? Semua log akan ikut terhapus.')">
                            Hapus
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

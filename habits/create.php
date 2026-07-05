<?php
require_once __DIR__ . '/../includes/session.php';
requireLogin();

$pageTitle = 'Tambah Habit - HabitTrack';
$error     = '';

$kategoriList = ['Kesehatan', 'Belajar', 'Olahraga', 'Produktivitas', 'Lainnya'];
$warnaList    = [
    '#6366f1' => 'Indigo',
    '#10b981' => 'Hijau',
    '#0ea5e9' => 'Biru',
    '#f59e0b' => 'Amber',
    '#ef4444' => 'Merah',
    '#8b5cf6' => 'Ungu',
    '#ec4899' => 'Pink',
    '#14b8a6' => 'Teal',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama_habit'] ?? '');
    $kategori = $_POST['kategori']        ?? '';
    $warna    = $_POST['warna']           ?? '#6366f1';

    if (!$nama) {
        $error = 'Nama habit wajib diisi.';
    } elseif (!in_array($kategori, $kategoriList)) {
        $error = 'Pilih kategori yang valid.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO habits (user_id, nama_habit, kategori, warna) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $nama, $kategori, $warna]);
        $_SESSION['flash'] = "Habit '{$nama}' berhasil ditambahkan!";
        header('Location: ' . BASE_URL . '/habits/index.php');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-lg mx-auto">
    <div class="mb-6">
        <a href="<?= BASE_URL ?>/habits/index.php"
           class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600 transition mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
        <h2 class="text-2xl font-bold text-gray-800">Tambah Habit Baru</h2>
    </div>

    <?php if ($error): ?>
    <div class="bg-red-50 border border-red-200 text-red-600 text-sm px-4 py-3 rounded-lg mb-5">
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST">
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Habit</label>
                <input type="text" name="nama_habit"
                       value="<?= htmlspecialchars($_POST['nama_habit'] ?? '') ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                       placeholder="Contoh: Olahraga 30 Menit" required autofocus>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select name="kategori"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($kategoriList as $kat): ?>
                    <option value="<?= $kat ?>"
                            <?= (($_POST['kategori'] ?? '') === $kat) ? 'selected' : '' ?>>
                        <?= $kat ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Warna</label>
                <div class="flex flex-wrap gap-3">
                    <?php foreach ($warnaList as $hex => $label):
                        $checked = (($_POST['warna'] ?? '#6366f1') === $hex) ? 'checked' : '';
                    ?>
                    <label class="cursor-pointer">
                        <input type="radio" name="warna" value="<?= $hex ?>" <?= $checked ?> class="sr-only">
                        <div class="w-8 h-8 rounded-full border-4 border-transparent hover:scale-110 transition"
                             style="background-color: <?= $hex ?>;"
                             title="<?= $label ?>"
                             onclick="this.parentElement.querySelector('input').checked = true; highlightColor('<?= $hex ?>')">
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 bg-indigo-600 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Simpan Habit
                </button>
                <a href="<?= BASE_URL ?>/habits/index.php"
                   class="px-5 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Highlight warna yang dipilih
function highlightColor(hex) {
    document.querySelectorAll('[name="warna"]').forEach(r => {
        r.nextElementSibling.style.borderColor = r.value === hex ? hex : 'transparent';
    });
}
// Set default highlight
document.addEventListener('DOMContentLoaded', () => {
    const checked = document.querySelector('[name="warna"]:checked');
    if (checked) highlightColor(checked.value);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

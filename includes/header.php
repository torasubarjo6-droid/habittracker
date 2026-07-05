<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'HabitTrack') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">

<nav class="bg-indigo-600 shadow-md">
    <div class="max-w-5xl mx-auto px-4 py-3 flex justify-between items-center">
        <a href="<?= BASE_URL ?>/dashboard.php" class="text-white font-bold text-xl tracking-tight">
            HabitTrack
        </a>
        <div class="flex items-center gap-4">
            <a href="<?= BASE_URL ?>/dashboard.php"
               class="text-sm text-indigo-100 hover:text-white transition">Dashboard</a>
            <a href="<?= BASE_URL ?>/habits/index.php"
               class="text-sm text-indigo-100 hover:text-white transition">Kelola Habit</a>
            <span class="text-indigo-400">|</span>
            <span class="text-sm text-indigo-200">
                <?= htmlspecialchars($_SESSION['name'] ?? '') ?>
            </span>
            <a href="<?= BASE_URL ?>/auth/logout.php"
               class="text-sm bg-white text-indigo-600 px-3 py-1 rounded-full font-medium hover:bg-indigo-50 transition">
                Keluar
            </a>
        </div>
    </div>
</nav>

<main class="max-w-5xl mx-auto px-4 py-8">

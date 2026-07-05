<?php
require_once __DIR__ . '/../config/db.php';

class DbSessionHandler implements SessionHandlerInterface
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function open($path, $name)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        $stmt = $this->pdo->prepare("SELECT data FROM sessions WHERE session_id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $row['data'] : '';
    }

    public function write($id, $data)
    {
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $stmt = $this->pdo->prepare("
            INSERT INTO sessions (session_id, user_id, data)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), data = VALUES(data)
        ");
        return $stmt->execute([$id, $userId, $data]);
    }

    public function destroy($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE session_id = ?");
        return $stmt->execute([$id]);
    }

    public function gc($max_lifetime)
    {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE last_active < DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->execute([$max_lifetime]);
        return $stmt->rowCount();
    }
}

$handler = new DbSessionHandler($pdo);
session_set_save_handler($handler, true);
session_start();

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

function requireGuest(): void
{
    if (isLoggedIn()) {
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
}

// Hitung streak aktif untuk sebuah habit
function getStreak(PDO $pdo, int $habitId): int
{
    $stmt = $pdo->prepare("SELECT tanggal FROM habit_logs WHERE habit_id = ? AND status = 1 ORDER BY tanggal DESC");
    $stmt->execute([$habitId]);
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($dates)) return 0;

    $streak    = 0;
    $checkDate = new DateTime('today');

    foreach ($dates as $date) {
        if ($date === $checkDate->format('Y-m-d')) {
            $streak++;
            $checkDate->modify('-1 day');
        } else {
            break;
        }
    }
    return $streak;
}

// Ambil status 7 hari terakhir untuk sebuah habit
// Ambil status 7 hari terakhir untuk sebuah habit
function getWeekHistory(PDO $pdo, int $habitId): array
{
    $week = [];
    for ($i = 6; $i >= 0; $i--) {
        $week[date('Y-m-d', strtotime("-{$i} days"))] = null;
    }

    $stmt = $pdo->prepare("SELECT tanggal, status FROM habit_logs WHERE habit_id = ? AND tanggal >= ?");
    $stmt->execute([$habitId, array_key_first($week)]);
    foreach ($stmt->fetchAll() as $log) {
        $tgl = substr($log['tanggal'], 0, 10);
        if (array_key_exists($tgl, $week)) {
            $week[$tgl] = (bool)$log['status'];
        }
    }
    return $week;
}

// Badge warna kategori
function kategoriClass(string $kat): string
{
    switch ($kat) {
        case 'Kesehatan':
            return 'bg-green-100 text-green-700';
        case 'Belajar':
            return 'bg-indigo-100 text-indigo-700';
        case 'Olahraga':
            return 'bg-blue-100 text-blue-700';
        case 'Produktivitas':
            return 'bg-amber-100 text-amber-700';
        default:
            return 'bg-gray-100 text-gray-600';
    }
}
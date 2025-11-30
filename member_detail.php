<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: members.php');
    exit();
}

$member_id = $_GET['id'];
$db = new Database();
$conn = $db->getConnection();

// Get Member Details
$stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
$stmt->execute([$member_id]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    header('Location: members.php');
    exit();
}

// Get Win History
$stmt = $conn->prepare("
    SELECT w.*, a.period_name, a.amount 
    FROM winners w 
    JOIN arisan_periods a ON w.arisan_id = a.id 
    WHERE w.member_id = ? 
    ORDER BY w.draw_date DESC
");
$stmt->execute([$member_id]);
$wins = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get Payment History
$stmt = $conn->prepare("
    SELECT p.*, a.period_name 
    FROM payments p 
    JOIN arisan_periods a ON p.arisan_id = a.id 
    WHERE p.member_id = ? 
    ORDER BY p.payment_date DESC
");
$stmt->execute([$member_id]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate Stats
$total_wins_amount = array_sum(array_column($wins, 'amount'));
$total_paid_amount = array_sum(array_column($payments, 'amount'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Anggota - <?php echo htmlspecialchars($member['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-gray-900 via-black to-gray-900 text-gray-100 min-h-screen font-['Inter']">
    
    <?php include 'includes/sidebar.php'; ?>

    <div class="ml-64 p-8">
        <!-- Header -->
        <div class="mb-8 flex items-center gap-4">
            <a href="members.php" class="bg-gray-800 hover:bg-gray-700 p-3 rounded-xl transition-all">
                <i class="fas fa-arrow-left text-white"></i>
            </a>
            <div>
                <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-400 to-pink-600 bg-clip-text text-transparent mb-1">
                    Detail Anggota
                </h1>
                <p class="text-gray-400">Informasi lengkap anggota arisan</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Card -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50">
                    <div class="text-center mb-6">
                        <div class="inline-block bg-gradient-to-br from-purple-600 to-pink-600 p-6 rounded-full mb-4">
                            <i class="fas fa-user text-4xl text-white"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($member['name']); ?></h2>
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold mt-2 <?php echo $member['status'] == 'active' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'; ?>">
                            <?php echo $member['status'] == 'active' ? 'Aktif' : 'Tidak Aktif'; ?>
                        </span>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 text-gray-300">
                            <div class="w-8 h-8 rounded-lg bg-gray-700/50 flex items-center justify-center">
                                <i class="fas fa-phone text-purple-400 text-sm"></i>
                            </div>
                            <span><?php echo htmlspecialchars($member['phone']); ?></span>
                        </div>
                        <div class="flex items-center gap-3 text-gray-300">
                            <div class="w-8 h-8 rounded-lg bg-gray-700/50 flex items-center justify-center">
                                <i class="fas fa-map-marker-alt text-purple-400 text-sm"></i>
                            </div>
                            <span><?php echo htmlspecialchars($member['address']); ?></span>
                        </div>
                        <div class="flex items-center gap-3 text-gray-300">
                            <div class="w-8 h-8 rounded-lg bg-gray-700/50 flex items-center justify-center">
                                <i class="fas fa-calendar text-purple-400 text-sm"></i>
                            </div>
                            <span>Bergabung: <?php echo date('d M Y', strtotime($member['created_at'])); ?></span>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-700/50 grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <p class="text-xs text-gray-400 mb-1">Total Setor</p>
                            <p class="text-lg font-bold text-green-400">
                                Rp <?php echo number_format($total_paid_amount, 0, ',', '.'); ?>
                            </p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-400 mb-1">Total Dapat</p>
                            <p class="text-lg font-bold text-yellow-400">
                                Rp <?php echo number_format($total_wins_amount, 0, ',', '.'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Tabs -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Win History -->
                <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50">
                    <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-trophy text-yellow-500"></i> Riwayat Kemenangan
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="text-xs text-gray-400 uppercase bg-gray-700/30">
                                <tr>
                                    <th class="px-4 py-3 rounded-l-lg">Periode</th>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3 rounded-r-lg text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/30">
                                <?php if (empty($wins)): ?>
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">Belum pernah menang</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($wins as $win): ?>
                                        <tr class="text-sm">
                                            <td class="px-4 py-3 text-white"><?php echo htmlspecialchars($win['period_name']); ?></td>
                                            <td class="px-4 py-3 text-gray-400"><?php echo date('d/m/Y', strtotime($win['draw_date'])); ?></td>
                                            <td class="px-4 py-3 text-yellow-400 font-bold text-right">
                                                Rp <?php echo number_format($win['amount'], 0, ',', '.'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment History -->
                <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50">
                    <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-history text-blue-400"></i> Riwayat Pembayaran Terakhir
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="text-xs text-gray-400 uppercase bg-gray-700/30">
                                <tr>
                                    <th class="px-4 py-3 rounded-l-lg">Periode</th>
                                    <th class="px-4 py-3">Tanggal Bayar</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 rounded-r-lg text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/30">
                                <?php if (empty($payments)): ?>
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">Belum ada data pembayaran</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($payments, 0, 10) as $payment): ?>
                                        <tr class="text-sm">
                                            <td class="px-4 py-3 text-white"><?php echo htmlspecialchars($payment['period_name']); ?></td>
                                            <td class="px-4 py-3 text-gray-400"><?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-400">Lunas</span>
                                            </td>
                                            <td class="px-4 py-3 text-white font-medium text-right">
                                                Rp <?php echo number_format($payment['amount'], 0, ',', '.'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($payments) > 10): ?>
                        <div class="mt-4 text-center">
                            <a href="#" class="text-sm text-purple-400 hover:text-purple-300">Lihat semua pembayaran</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>

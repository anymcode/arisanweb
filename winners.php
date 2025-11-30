<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Handle delete all
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_all'])) {
    $stmt = $conn->query("DELETE FROM winners");
    $success = "Semua riwayat pemenang berhasil dihapus!";
}

// Get all winners with details
$stmt = $conn->query("
    SELECT w.*, m.name as member_name, m.phone, a.period_name, a.amount 
    FROM winners w 
    JOIN members m ON w.member_id = m.id 
    JOIN arisan_periods a ON w.arisan_id = a.id 
    ORDER BY w.draw_date DESC
");
$winners = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pemenang - Sistem Arisan Digital</title>
    <meta name="description" content="Riwayat pemenang arisan">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-gray-900 via-black to-gray-900 text-gray-100 min-h-screen font-['Inter']">
    
    <?php include 'includes/sidebar.php'; ?>

    <div class="md:ml-64 p-4 md:p-8 pt-20 md:pt-8">
        <!-- Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-purple-400 to-pink-600 bg-clip-text text-transparent mb-2">
                    <i class="fas fa-trophy"></i> Daftar Pemenang
                </h1>
                <p class="text-gray-400">Riwayat pemenang undian arisan</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <?php if (!empty($winners)): ?>
                    <button onclick="confirmDeleteAll()" class="bg-red-600/20 hover:bg-red-600/30 text-red-400 border border-red-600/30 px-6 py-3 rounded-xl font-semibold transition-all hover:shadow-lg hover:shadow-red-500/20">
                        <i class="fas fa-trash-alt mr-2"></i>Hapus Semua
                    </button>
                <?php endif; ?>
                <a href="print_winners.php" target="_blank" class="bg-gray-800 hover:bg-gray-700 text-white px-6 py-3 rounded-xl font-semibold transition-all border border-gray-700 hover:border-gray-600 flex items-center gap-2">
                    <i class="fas fa-print"></i> Cetak Laporan
                </a>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="bg-green-500/10 border border-green-500/50 rounded-xl p-4 mb-6 alert-auto-hide">
                <p class="text-green-400 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Winners Table -->
        <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl border border-gray-700/50 overflow-hidden">
            <div class="p-6 border-b border-gray-700/50">
                <div class="flex items-center gap-4">
                    <i class="fas fa-search text-gray-400"></i>
                    <input 
                        type="text" 
                        id="searchInput"
                        placeholder="Cari pemenang..." 
                        class="flex-1 bg-transparent border-none outline-none text-white placeholder-gray-500"
                    >
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full custom-table" id="winnersTable">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">No</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Pemenang</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Periode Arisan</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Jumlah</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Tanggal Undian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/30">
                        <?php if (empty($winners)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                    <i class="fas fa-trophy text-5xl mb-4 opacity-20"></i>
                                    <p>Belum ada pemenang</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($winners as $index => $winner): ?>
                                <tr class="hover:bg-gray-700/20 transition-colors">
                                    <td class="px-6 py-4 text-gray-300"><?php echo $index + 1; ?></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="bg-gradient-to-br from-yellow-500 to-orange-500 p-3 rounded-lg">
                                                <i class="fas fa-trophy text-white"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-white"><?php echo htmlspecialchars($winner['member_name']); ?></h4>
                                                <p class="text-sm text-gray-400"><?php echo htmlspecialchars($winner['phone']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-300"><?php echo htmlspecialchars($winner['period_name']); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="text-green-400 font-semibold">
                                            Rp <?php echo number_format($winner['amount'], 0, ',', '.'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-300"><?php echo date('d/m/Y H:i', strtotime($winner['draw_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Statistics -->
        <?php if (!empty($winners)): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                <div class="bg-gradient-to-br from-purple-900/40 to-purple-800/20 backdrop-blur-xl rounded-2xl p-6 border border-purple-500/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm mb-1">Total Pemenang</p>
                            <h3 class="text-3xl font-bold text-white"><?php echo count($winners); ?></h3>
                        </div>
                        <div class="bg-purple-500/20 p-4 rounded-xl">
                            <i class="fas fa-trophy text-purple-400 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-900/40 to-green-800/20 backdrop-blur-xl rounded-2xl p-6 border border-green-500/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm mb-1">Total Hadiah</p>
                            <h3 class="text-2xl font-bold text-white">
                                Rp <?php echo number_format(array_sum(array_column($winners, 'amount')), 0, ',', '.'); ?>
                            </h3>
                        </div>
                        <div class="bg-green-500/20 p-4 rounded-xl">
                            <i class="fas fa-money-bill-wave text-green-400 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-900/40 to-blue-800/20 backdrop-blur-xl rounded-2xl p-6 border border-blue-500/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm mb-1">Pemenang Terbaru</p>
                            <h3 class="text-lg font-bold text-white"><?php echo htmlspecialchars($winners[0]['member_name']); ?></h3>
                        </div>
                        <div class="bg-blue-500/20 p-4 rounded-xl">
                            <i class="fas fa-star text-blue-400 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Delete All Form -->
    <form id="deleteAllForm" method="POST" action="" class="hidden">
        <input type="hidden" name="delete_all" value="1">
    </form>

    <script src="assets/js/main.js"></script>
    <script>
        searchTable('searchInput', 'winnersTable');

        function confirmDeleteAll() {
            confirmDialog('APAKAH ANDA YAKIN? Tindakan ini akan menghapus SEMUA riwayat pemenang secara permanen!', () => {
                if(confirm('Yakin 100%? Data pemenang akan hilang!')) {
                    document.getElementById('deleteAllForm').submit();
                }
            });
        }
    </script>
</body>
</html>

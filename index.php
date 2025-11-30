<?php
session_start();
require_once 'config/database.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Get statistics
$db = new Database();
$conn = $db->getConnection();

// Count total members
$stmt = $conn->query("SELECT COUNT(*) as total FROM members WHERE status = 'active'");
$totalMembers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Count active arisan
$stmt = $conn->query("SELECT COUNT(*) as total FROM arisan_periods WHERE status = 'active'");
$activeArisan = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get recent winners
$stmt = $conn->query("SELECT w.*, m.name as member_name, a.period_name 
                      FROM winners w 
                      JOIN members m ON w.member_id = m.id 
                      JOIN arisan_periods a ON w.arisan_id = a.id 
                      ORDER BY w.draw_date DESC LIMIT 5");
$recentWinners = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get active arisan periods
$stmt = $conn->query("SELECT * FROM arisan_periods WHERE status = 'active' ORDER BY created_at DESC");
$activeArisanList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Arisan Digital - Dashboard</title>
    <meta name="description" content="Sistem manajemen arisan digital modern dengan fitur undian otomatis">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-gray-900 via-black to-gray-900 text-gray-100 min-h-screen font-['Inter']">
    
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="md:ml-64 p-4 md:p-8 pt-20 md:pt-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-purple-400 to-pink-600 bg-clip-text text-transparent mb-2">
                Dashboard
            </h1>
            <p class="text-gray-400">Selamat datang kembali, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Members Card -->
            <div class="bg-gradient-to-br from-purple-900/40 to-purple-800/20 backdrop-blur-xl rounded-2xl p-6 border border-purple-500/20 hover:border-purple-500/40 transition-all duration-300 hover:shadow-lg hover:shadow-purple-500/20 hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Total Anggota</p>
                        <h3 class="text-3xl font-bold text-white"><?php echo $totalMembers; ?></h3>
                    </div>
                    <div class="bg-purple-500/20 p-4 rounded-xl">
                        <i class="fas fa-users text-purple-400 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Active Arisan Card -->
            <div class="bg-gradient-to-br from-blue-900/40 to-blue-800/20 backdrop-blur-xl rounded-2xl p-6 border border-blue-500/20 hover:border-blue-500/40 transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/20 hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Arisan Aktif</p>
                        <h3 class="text-3xl font-bold text-white"><?php echo $activeArisan; ?></h3>
                    </div>
                    <div class="bg-blue-500/20 p-4 rounded-xl">
                        <i class="fas fa-coins text-blue-400 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Winners Card -->
            <div class="bg-gradient-to-br from-pink-900/40 to-pink-800/20 backdrop-blur-xl rounded-2xl p-6 border border-pink-500/20 hover:border-pink-500/40 transition-all duration-300 hover:shadow-lg hover:shadow-pink-500/20 hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Total Pemenang</p>
                        <h3 class="text-3xl font-bold text-white"><?php echo count($recentWinners); ?></h3>
                    </div>
                    <div class="bg-pink-500/20 p-4 rounded-xl">
                        <i class="fas fa-trophy text-pink-400 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Arisan & Recent Winners -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Active Arisan List -->
            <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-white">Arisan Aktif</h2>
                    <a href="arisan.php" class="text-purple-400 hover:text-purple-300 transition-colors">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="space-y-4">
                    <?php if (empty($activeArisanList)): ?>
                        <p class="text-gray-400 text-center py-8">Belum ada arisan aktif</p>
                    <?php else: ?>
                        <?php foreach ($activeArisanList as $arisan): ?>
                            <div class="bg-gray-900/50 rounded-xl p-4 border border-gray-700/30 hover:border-purple-500/30 transition-all">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="font-semibold text-white mb-1"><?php echo htmlspecialchars($arisan['period_name']); ?></h3>
                                        <p class="text-sm text-gray-400">Rp <?php echo number_format($arisan['amount'], 0, ',', '.'); ?> / bulan</p>
                                    </div>
                                    <a href="draw.php?id=<?php echo $arisan['id']; ?>" class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                                        Undi
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Winners -->
            <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-white">Pemenang Terbaru</h2>
                    <a href="winners.php" class="text-purple-400 hover:text-purple-300 transition-colors">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="space-y-4">
                    <?php if (empty($recentWinners)): ?>
                        <p class="text-gray-400 text-center py-8">Belum ada pemenang</p>
                    <?php else: ?>
                        <?php foreach ($recentWinners as $winner): ?>
                            <div class="bg-gray-900/50 rounded-xl p-4 border border-gray-700/30 hover:border-pink-500/30 transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="bg-gradient-to-br from-yellow-500 to-orange-500 p-3 rounded-full">
                                        <i class="fas fa-trophy text-white"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-white"><?php echo htmlspecialchars($winner['member_name']); ?></h3>
                                        <p class="text-sm text-gray-400"><?php echo htmlspecialchars($winner['period_name']); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-400"><?php echo date('d/m/Y', strtotime($winner['draw_date'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>

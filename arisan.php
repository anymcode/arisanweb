<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $stmt = $conn->prepare("INSERT INTO arisan_periods (period_name, amount, duration_months, status) VALUES (?, ?, ?, 'active')");
            $stmt->execute([$_POST['period_name'], $_POST['amount'], $_POST['duration_months']]);
            $success = "Arisan berhasil ditambahkan!";
        } elseif ($_POST['action'] == 'edit') {
            $stmt = $conn->prepare("UPDATE arisan_periods SET period_name = ?, amount = ?, duration_months = ? WHERE id = ?");
            $stmt->execute([$_POST['period_name'], $_POST['amount'], $_POST['duration_months'], $_POST['id']]);
            $success = "Arisan berhasil diupdate!";
        }
    } elseif (isset($_POST['delete_all'])) {
        // DELETE FROM arisan_periods will cascade to payments and winners
        $stmt = $conn->query("DELETE FROM arisan_periods");
        $success = "Semua periode arisan (dan data terkait) berhasil dihapus!";
    }
}

if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("UPDATE arisan_periods SET status = 'inactive' WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $success = "Arisan berhasil dihapus!";
}

if (isset($_GET['close'])) {
    $stmt = $conn->prepare("UPDATE arisan_periods SET status = 'completed' WHERE id = ?");
    $stmt->execute([$_GET['close']]);
    $success = "Arisan berhasil ditutup!";
}

// Get all arisan periods with winner count
$stmt = $conn->query("
    SELECT a.*, 
    (SELECT COUNT(*) FROM winners w WHERE w.arisan_id = a.id) as winner_count,
    (SELECT COUNT(*) FROM members m WHERE m.status = 'active') as total_members
    FROM arisan_periods a 
    WHERE a.status != 'inactive' 
    ORDER BY a.created_at DESC
");
$arisanPeriods = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get arisan for edit
$editArisan = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM arisan_periods WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editArisan = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Arisan - Sistem Arisan Digital</title>
    <meta name="description" content="Kelola periode arisan">
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
                    <i class="fas fa-box-open"></i> Data Arisan
                </h1>
                <p class="text-gray-400">Kelola periode dan data arisan</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <?php if (!empty($arisanPeriods)): ?>
                    <button onclick="confirmDeleteAll()" class="bg-red-600/20 hover:bg-red-600/30 text-red-400 border border-red-600/30 px-6 py-3 rounded-xl font-semibold transition-all hover:shadow-lg hover:shadow-red-500/20">
                        <i class="fas fa-trash-alt mr-2"></i>Hapus Semua
                    </button>
                <?php endif; ?>
                <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 px-6 py-3 rounded-xl font-semibold transition-all hover:shadow-lg hover:shadow-purple-500/50">
                    <i class="fas fa-plus mr-2"></i>Buat Arisan Baru
                </button>
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

        <!-- Arisan Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($arisanPeriods as $arisan): ?>
                <?php 
                    // Calculate progress
                    // Assuming total potential winners is based on duration or total active members
                    // Let's use duration as the target cycle length
                    $progress = ($arisan['winner_count'] / $arisan['duration_months']) * 100;
                    $progress = min($progress, 100); // Max 100%
                ?>
                <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50 hover:border-purple-500/30 transition-all hover:shadow-lg hover:shadow-purple-500/20">
                    <div class="flex items-start justify-between mb-4">
                        <div class="bg-gradient-to-br from-purple-600 to-pink-600 p-3 rounded-xl">
                            <i class="fas fa-coins text-white text-xl"></i>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $arisan['status'] == 'active' ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400'; ?>">
                            <?php echo $arisan['status'] == 'active' ? 'Aktif' : 'Selesai'; ?>
                        </span>
                    </div>
                    
                    <h3 class="text-xl font-bold text-white mb-2"><?php echo htmlspecialchars($arisan['period_name']); ?></h3>
                    
                    <!-- Progress Bar -->
                    <div class="mb-4">
                        <div class="flex justify-between text-xs text-gray-400 mb-1">
                            <span>Progres Putaran</span>
                            <span><?php echo $arisan['winner_count']; ?> / <?php echo $arisan['duration_months']; ?> Pemenang</span>
                        </div>
                        <div class="w-full bg-gray-700 rounded-full h-2.5">
                            <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-2.5 rounded-full transition-all duration-500" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                    </div>

                    <div class="space-y-2 mb-4">
                        <p class="text-gray-400 text-sm flex items-center gap-2">
                            <i class="fas fa-money-bill-wave text-purple-400"></i>
                            <span>Rp <?php echo number_format($arisan['amount'], 0, ',', '.'); ?> / bulan</span>
                        </p>
                        <p class="text-gray-400 text-sm flex items-center gap-2">
                            <i class="fas fa-calendar text-purple-400"></i>
                            <span>Durasi: <?php echo $arisan['duration_months']; ?> Bulan</span>
                        </p>
                    </div>
                    
                    <div class="flex gap-2">
                        <?php if ($arisan['status'] == 'active'): ?>
                            <a href="draw.php?id=<?php echo $arisan['id']; ?>" class="flex-1 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white text-center px-4 py-2 rounded-lg text-sm font-medium transition-all">
                                <i class="fas fa-dice mr-1"></i>Undi
                            </a>
                            <a href="?edit=<?php echo $arisan['id']; ?>" class="bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 px-3 py-2 rounded-lg transition-all">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="handleDelete('?close=<?php echo $arisan['id']; ?>', 'Tutup arisan ini?')" class="bg-yellow-600/20 hover:bg-yellow-600/30 text-yellow-400 px-3 py-2 rounded-lg transition-all">
                                <i class="fas fa-check"></i>
                            </button>
                        <?php endif; ?>
                        <button onclick="handleDelete('?delete=<?php echo $arisan['id']; ?>', 'Hapus arisan ini?')" class="bg-red-600/20 hover:bg-red-600/30 text-red-400 px-3 py-2 rounded-lg transition-all">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="addModal" class="<?php echo $editArisan ? '' : 'hidden'; ?> fixed inset-0 modal-overlay z-50 flex items-center justify-center">
        <div class="bg-gray-800 rounded-2xl p-8 max-w-md w-full mx-4 border border-gray-700 animate-fadeIn">
            <h2 class="text-2xl font-bold text-white mb-6">
                <?php echo $editArisan ? 'Edit Arisan' : 'Buat Arisan Baru'; ?>
            </h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $editArisan ? 'edit' : 'add'; ?>">
                <?php if ($editArisan): ?>
                    <input type="hidden" name="id" value="<?php echo $editArisan['id']; ?>">
                <?php endif; ?>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Nama Periode</label>
                        <input type="text" name="period_name" required value="<?php echo $editArisan['period_name'] ?? ''; ?>" placeholder="Contoh: Arisan Januari 2024" class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Jumlah per Bulan (Rp)</label>
                        <input type="number" name="amount" required value="<?php echo $editArisan['amount'] ?? ''; ?>" placeholder="100000" class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Durasi (Bulan)</label>
                        <input type="number" name="duration_months" required value="<?php echo $editArisan['duration_months'] ?? ''; ?>" placeholder="12" class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="window.location.href='arisan.php'" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white px-4 py-3 rounded-xl transition-all">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-4 py-3 rounded-xl transition-all">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete All Form -->
    <form id="deleteAllForm" method="POST" action="" class="hidden">
        <input type="hidden" name="delete_all" value="1">
    </form>

    <script src="assets/js/main.js"></script>
    <script>
        function confirmDeleteAll() {
            confirmDialog('PERINGATAN KERAS! Menghapus semua arisan juga akan MENGHAPUS SEMUA riwayat pembayaran dan kemenangan. Data tidak bisa dikembalikan!', () => {
                if(confirm('Yakin 100%? Ini akan mereset hampir seluruh data aplikasi!')) {
                    document.getElementById('deleteAllForm').submit();
                }
            });
        }
    </script>
</body>
</html>

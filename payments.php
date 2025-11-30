<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Handle payment record
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_payment'])) {
        $member_id = $_POST['member_id'];
        $arisan_id = $_POST['arisan_id'];
        $amount = $_POST['amount'];
        $payment_date = $_POST['payment_date'];

        // 1. CEK DOBEL BAYAR (Duplicate Check)
        // Cek apakah member ini sudah bayar di bulan & tahun yang sama untuk arisan ini
        $stmt = $conn->prepare("
            SELECT id FROM payments 
            WHERE member_id = ? 
            AND arisan_id = ? 
            AND MONTH(payment_date) = MONTH(?) 
            AND YEAR(payment_date) = YEAR(?)
        ");
        $stmt->execute([$member_id, $arisan_id, $payment_date, $payment_date]);
        
        if ($stmt->rowCount() > 0) {
            $error = "GAGAL! Anggota ini SUDAH BAYAR untuk bulan tersebut. Cek kembali tanggal atau nama anggota.";
        } else {
            $stmt = $conn->prepare("INSERT INTO payments (member_id, arisan_id, amount, payment_date, status) VALUES (?, ?, ?, ?, 'paid')");
            $stmt->execute([$member_id, $arisan_id, $amount, $payment_date]);
            $success = "Pembayaran berhasil dicatat!";
        }

    } elseif (isset($_POST['delete_all'])) {
        // Double check for safety
        $stmt = $conn->query("TRUNCATE TABLE payments");
        $success = "Semua riwayat pembayaran berhasil dihapus!";
    }
}

// Handle Individual Delete
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM payments WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $success = "Data pembayaran berhasil dihapus!";
}

// Get all arisans for filter (Active & Completed)
$stmt = $conn->query("SELECT * FROM arisan_periods ORDER BY status ASC, created_at DESC");
$allArisans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Filter
$filter_arisan = $_GET['filter_arisan'] ?? '';
$whereClause = "";
$params = [];

if ($filter_arisan != '') {
    $whereClause = "WHERE p.arisan_id = ?";
    $params[] = $filter_arisan;
}

// Get payments based on filter
$sql = "
    SELECT p.*, m.name as member_name, m.phone, a.period_name, a.status as arisan_status 
    FROM payments p 
    JOIN members m ON p.member_id = m.id 
    JOIN arisan_periods a ON p.arisan_id = a.id 
    $whereClause
    ORDER BY p.payment_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get active arisan for dropdown (Input Form)
$stmt = $conn->query("SELECT * FROM arisan_periods WHERE status = 'active'");
$activeArisan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get active members for dropdown
$stmt = $conn->query("SELECT * FROM members WHERE status = 'active' ORDER BY name ASC");
$activeMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Sistem Arisan Digital</title>
    <meta name="description" content="Kelola pembayaran arisan">
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
                    <i class="fas fa-money-bill-wave"></i> Pembayaran
                </h1>
                <p class="text-gray-400">Kelola pembayaran arisan</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <?php if (!empty($payments)): ?>
                    <button onclick="confirmDeleteAll()" class="bg-red-600/20 hover:bg-red-600/30 text-red-400 border border-red-600/30 px-6 py-3 rounded-xl font-semibold transition-all hover:shadow-lg hover:shadow-red-500/20">
                        <i class="fas fa-trash-alt mr-2"></i>Hapus Semua
                    </button>
                <?php endif; ?>
                <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 px-6 py-3 rounded-xl font-semibold transition-all hover:shadow-lg hover:shadow-purple-500/50">
                    <i class="fas fa-plus mr-2"></i>Catat Pembayaran
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

        <?php if (isset($error)): ?>
            <div class="bg-red-500/10 border border-red-500/50 rounded-xl p-4 mb-6">
                <p class="text-red-400 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl p-4 border border-gray-700/50 mb-6">
            <form method="GET" action="" class="flex items-center gap-4">
                <div class="flex-1">
                    <label class="text-sm text-gray-400 mb-1 block">Filter Arisan:</label>
                    <div class="relative">
                        <select name="filter_arisan" onchange="this.form.submit()" class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-purple-500 appearance-none">
                            <option value="">Semua Arisan</option>
                            <optgroup label="Arisan Aktif">
                                <?php foreach ($allArisans as $arisan): ?>
                                    <?php if ($arisan['status'] == 'active'): ?>
                                        <option value="<?php echo $arisan['id']; ?>" <?php echo $filter_arisan == $arisan['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($arisan['period_name']); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="Arisan Selesai">
                                <?php foreach ($allArisans as $arisan): ?>
                                    <?php if ($arisan['status'] != 'active'): ?>
                                        <option value="<?php echo $arisan['id']; ?>" <?php echo $filter_arisan == $arisan['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($arisan['period_name']); ?> (Selesai)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                        <div class="absolute right-4 top-3 pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                <div class="flex-1">
                    <label class="text-sm text-gray-400 mb-1 block">Cari Anggota:</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            id="searchInput"
                            placeholder="Ketik nama..." 
                            class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-purple-500 pl-10"
                        >
                        <i class="fas fa-search absolute left-3 top-3.5 text-gray-500"></i>
                    </div>
                </div>
            </form>
        </div>

        <!-- Payments Table -->
        <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl border border-gray-700/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full custom-table" id="paymentsTable">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">No</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Nama Anggota</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Periode Arisan</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Jumlah</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Tanggal Bayar</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-300">Status</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-300">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/30">
                        <?php if (empty($payments)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                    <i class="fas fa-money-bill-wave text-5xl mb-4 opacity-20"></i>
                                    <p>Belum ada data pembayaran</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($payments as $index => $payment): ?>
                                <tr class="hover:bg-gray-700/20 transition-colors">
                                    <td class="px-6 py-4 text-gray-300"><?php echo $index + 1; ?></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="bg-gray-700 p-2 rounded-lg">
                                                <i class="fas fa-user text-gray-400 text-sm"></i>
                                            </div>
                                            <div>
                                                <span class="font-medium text-white block"><?php echo htmlspecialchars($payment['member_name']); ?></span>
                                                <span class="text-xs text-gray-500"><?php echo htmlspecialchars($payment['phone']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-gray-300"><?php echo htmlspecialchars($payment['period_name']); ?></span>
                                        <?php if ($payment['arisan_status'] != 'active'): ?>
                                            <span class="text-xs bg-gray-600 text-gray-300 px-2 py-0.5 rounded ml-2">Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-green-400 font-semibold">
                                            Rp <?php echo number_format($payment['amount'], 0, ',', '.'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-300"><?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-400">
                                            Lunas
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button onclick="handleDelete('?delete=<?php echo $payment['id']; ?>', 'Hapus pembayaran ini?')" class="text-red-400 hover:text-red-300 transition-colors" title="Hapus Pembayaran Ini">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Payment Modal -->
    <div id="addModal" class="hidden fixed inset-0 modal-overlay z-50 flex items-center justify-center">
        <div class="bg-gray-800 rounded-2xl p-8 max-w-md w-full mx-4 border border-gray-700 animate-fadeIn">
            <h2 class="text-2xl font-bold text-white mb-6">Catat Pembayaran</h2>
            <form method="POST" action="" onsubmit="return confirmPayment(this)">
                <input type="hidden" name="add_payment" value="1">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Pilih Anggota</label>
                        <select name="member_id" id="memberSelect" required class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500">
                            <option value="">-- Pilih Anggota --</option>
                            <?php foreach ($activeMembers as $member): ?>
                                <option value="<?php echo $member['id']; ?>" data-name="<?php echo htmlspecialchars($member['name']); ?>">
                                    <?php echo htmlspecialchars($member['name']); ?> (<?php echo htmlspecialchars($member['phone']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Pilih Arisan</label>
                        <select name="arisan_id" id="arisanSelect" required onchange="updateAmount()" class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500">
                            <option value="">-- Pilih Arisan --</option>
                            <?php foreach ($activeArisan as $arisan): ?>
                                <option value="<?php echo $arisan['id']; ?>" data-amount="<?php echo $arisan['amount']; ?>" data-name="<?php echo htmlspecialchars($arisan['period_name']); ?>">
                                    <?php echo htmlspecialchars($arisan['period_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Jumlah (Otomatis)</label>
                        <input type="number" name="amount" id="amountInput" readonly required class="w-full bg-gray-900/20 border border-gray-700 rounded-xl px-4 py-3 text-gray-400 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Tanggal Bayar</label>
                        <input type="date" name="payment_date" required value="<?php echo date('Y-m-d'); ?>" class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500">
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white px-4 py-3 rounded-xl transition-all">
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
        searchTable('searchInput', 'paymentsTable');
        
        function updateAmount() {
            const select = document.getElementById('arisanSelect');
            const amount = select.options[select.selectedIndex].getAttribute('data-amount');
            document.getElementById('amountInput').value = amount || '';
        }

        function confirmPayment(form) {
            const memberSelect = document.getElementById('memberSelect');
            const arisanSelect = document.getElementById('arisanSelect');
            const amount = document.getElementById('amountInput').value;
            
            const memberName = memberSelect.options[memberSelect.selectedIndex].getAttribute('data-name');
            const arisanName = arisanSelect.options[arisanSelect.selectedIndex].getAttribute('data-name');
            
            // Format currency for display
            const formattedAmount = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);

            return confirm(`KONFIRMASI PEMBAYARAN:\n\nNama: ${memberName}\nArisan: ${arisanName}\nJumlah: ${formattedAmount}\n\nApakah data ini sudah benar?`);
        }

        function confirmDeleteAll() {
            confirmDialog('APAKAH ANDA YAKIN? Tindakan ini akan menghapus SEMUA riwayat pembayaran secara permanen dan tidak dapat dikembalikan!', () => {
                // Second confirmation for safety
                if(confirm('Yakin 100%? Data akan hilang selamanya!')) {
                    document.getElementById('deleteAllForm').submit();
                }
            });
        }
    </script>
</body>
</html>

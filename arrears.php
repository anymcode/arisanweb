<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Get Active Arisan Periods
$stmt = $conn->query("SELECT * FROM arisan_periods WHERE status = 'active'");
$arisanPeriods = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Default Filter
$selected_arisan = $_GET['arisan_id'] ?? ($arisanPeriods[0]['id'] ?? null);
$selected_month = $_GET['month'] ?? date('m');
$selected_year = $_GET['year'] ?? date('Y');

$arrears = [];

if ($selected_arisan) {
    // Logic: Get members who are active BUT NOT in payments table for selected month/year/arisan
    $sql = "
        SELECT m.* 
        FROM members m 
        WHERE m.status = 'active' 
        AND m.id NOT IN (
            SELECT p.member_id 
            FROM payments p 
            WHERE p.arisan_id = ? 
            AND MONTH(p.payment_date) = ? 
            AND YEAR(p.payment_date) = ?
        )
        ORDER BY m.name ASC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$selected_arisan, $selected_month, $selected_year]);
    $arrears = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Helper for month names
$months = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Tunggakan - Sistem Arisan Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-gray-900 via-black to-gray-900 text-gray-100 min-h-screen font-['Inter']">
    
    <?php include 'includes/sidebar.php'; ?>

    <div class="md:ml-64 p-4 md:p-8 pt-20 md:pt-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-purple-400 to-pink-600 bg-clip-text text-transparent mb-2">
                <i class="fas fa-exclamation-circle"></i> Cek Tunggakan
            </h1>
            <p class="text-gray-400">Cek anggota yang belum membayar iuran</p>
        </div>

        <!-- Filter Card -->
        <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50 mb-8">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Periode Arisan</label>
                    <select name="arisan_id" class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500">
                        <?php foreach ($arisanPeriods as $arisan): ?>
                            <option value="<?php echo $arisan['id']; ?>" <?php echo $selected_arisan == $arisan['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($arisan['period_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Bulan</label>
                    <select name="month" class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500">
                        <?php foreach ($months as $num => $name): ?>
                            <option value="<?php echo $num; ?>" <?php echo $selected_month == $num ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Tahun</label>
                    <select name="year" class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500">
                        <?php for ($y = date('Y'); $y >= 2023; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo $selected_year == $y ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="md:col-span-4 flex justify-end">
                    <button type="submit" class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-6 py-3 rounded-xl font-semibold transition-all shadow-lg shadow-purple-500/30">
                        <i class="fas fa-filter mr-2"></i> Tampilkan Data
                    </button>
                </div>
            </form>
        </div>

        <!-- Results -->
        <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl border border-gray-700/50 overflow-hidden">
            <div class="p-6 border-b border-gray-700/50 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white">
                    Daftar Tunggakan: <?php echo $months[$selected_month] . ' ' . $selected_year; ?>
                </h3>
                <span class="bg-red-500/20 text-red-400 px-3 py-1 rounded-full text-sm font-semibold">
                    <?php echo count($arrears); ?> Anggota Belum Bayar
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full custom-table">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">No</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Nama Anggota</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Telepon</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/30">
                        <?php if (empty($arrears)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                    <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                                    <p class="text-lg">Luar biasa! Semua anggota sudah lunas.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($arrears as $index => $member): ?>
                                <tr class="hover:bg-gray-700/20 transition-colors">
                                    <td class="px-6 py-4 text-gray-300"><?php echo $index + 1; ?></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="bg-gray-700 p-2 rounded-lg">
                                                <i class="fas fa-user text-gray-400 text-sm"></i>
                                            </div>
                                            <a href="member_detail.php?id=<?php echo $member['id']; ?>" class="font-medium text-white hover:text-purple-400 transition-colors">
                                                <?php echo htmlspecialchars($member['name']); ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-300"><?php echo htmlspecialchars($member['phone']); ?></td>
                                    <td class="px-6 py-4">
                                        <a href="https://wa.me/<?php echo preg_replace('/^0/', '62', $member['phone']); ?>?text=Halo%20<?php echo urlencode($member['name']); ?>%2C%20mohon%20segera%20melakukan%20pembayaran%20arisan%20untuk%20bulan%20<?php echo $months[$selected_month]; ?>.%20Terima%20kasih." target="_blank" class="bg-green-600/20 hover:bg-green-600/30 text-green-400 px-4 py-2 rounded-lg transition-all text-sm font-medium flex items-center gap-2 w-fit">
                                            <i class="fab fa-whatsapp"></i> Tagih WA
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>

<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Get arisan periods
$stmt = $conn->query("SELECT * FROM arisan_periods WHERE status = 'active' ORDER BY created_at DESC");
$arisanPeriods = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selectedArisan = null;
$eligibleMembers = [];

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM arisan_periods WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $selectedArisan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($selectedArisan) {
        // Get members who haven't won AND have paid this month
        $stmt = $conn->prepare("
            SELECT m.* FROM members m 
            WHERE m.status = 'active' 
            AND m.id NOT IN (
                SELECT member_id FROM winners WHERE arisan_id = ?
            )
            AND m.id IN (
                SELECT member_id FROM payments 
                WHERE arisan_id = ? 
                AND MONTH(payment_date) = MONTH(CURRENT_DATE()) 
                AND YEAR(payment_date) = YEAR(CURRENT_DATE())
            )
        ");
        $stmt->execute([$selectedArisan['id'], $selectedArisan['id']]);
        $eligibleMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Handle draw
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['draw'])) {
    $arisanId = $_POST['arisan_id'];
    $memberId = $_POST['member_id'];
    
    $stmt = $conn->prepare("INSERT INTO winners (arisan_id, member_id, draw_date) VALUES (?, ?, NOW())");
    $stmt->execute([$arisanId, $memberId]);
    
    $success = true;
    
    // Refresh eligible members
    $stmt = $conn->prepare("
        SELECT m.* FROM members m 
        WHERE m.status = 'active' 
        AND m.id NOT IN (
            SELECT member_id FROM winners WHERE arisan_id = ?
        )
        AND m.id IN (
            SELECT member_id FROM payments 
            WHERE arisan_id = ? 
            AND MONTH(payment_date) = MONTH(CURRENT_DATE()) 
            AND YEAR(payment_date) = YEAR(CURRENT_DATE())
        )
    ");
    $stmt->execute([$arisanId, $arisanId]);
    $eligibleMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Undian Arisan - Sistem Arisan Digital</title>
    <meta name="description" content="Sistem undian arisan otomatis">
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
                <i class="fas fa-dice"></i> Undian Arisan
            </h1>
            <p class="text-gray-400">Sistem undian otomatis untuk menentukan pemenang</p>
        </div>

        <!-- Select Arisan -->
        <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50 mb-8">
            <label class="block text-sm font-medium text-gray-300 mb-3">Pilih Periode Arisan</label>
            <select onchange="window.location.href='draw.php?id=' + this.value" class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                <option value="">-- Pilih Arisan --</option>
                <?php foreach ($arisanPeriods as $arisan): ?>
                    <option value="<?php echo $arisan['id']; ?>" <?php echo ($selectedArisan && $selectedArisan['id'] == $arisan['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($arisan['period_name']); ?> - Rp <?php echo number_format($arisan['amount'], 0, ',', '.'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if ($selectedArisan): ?>
            <!-- Info Alert -->
            <div class="bg-blue-500/10 border border-blue-500/50 rounded-xl p-4 mb-6 flex items-start gap-3">
                <i class="fas fa-info-circle text-blue-400 mt-1"></i>
                <div>
                    <h4 class="font-bold text-blue-400">Aturan Undian</h4>
                    <p class="text-gray-300 text-sm">Hanya anggota yang <strong>sudah membayar iuran bulan ini</strong> dan <strong>belum pernah menang</strong> yang akan diikutkan dalam undian.</p>
                </div>
            </div>

            <?php if (empty($eligibleMembers)): ?>
                <div class="bg-yellow-500/10 border border-yellow-500/50 rounded-2xl p-8 text-center">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-5xl mb-4"></i>
                    <h3 class="text-2xl font-bold text-white mb-2">Tidak Ada Peserta Memenuhi Syarat</h3>
                    <p class="text-gray-400">Pastikan anggota sudah membayar iuran untuk bulan ini dan belum pernah menang sebelumnya.</p>
                    <a href="payments.php" class="inline-block mt-4 text-purple-400 hover:text-purple-300 underline">Ke Menu Pembayaran</a>
                </div>
            <?php else: ?>
                <!-- Lottery UI (Restored Version) -->
                <div class="flex flex-col items-center justify-center py-8">
                    <div class="relative mb-8">
                        <div class="w-64 h-64 rounded-full border-4 border-purple-500 relative flex items-center justify-center bg-gray-800 shadow-[0_0_50px_rgba(168,85,247,0.4)] overflow-hidden" id="wheel">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <i class="fas fa-gift text-6xl text-purple-500 opacity-20"></i>
                            </div>
                            <div class="z-10 text-2xl font-bold text-white text-center px-4" id="wheelText">
                                ???
                            </div>
                        </div>
                        <!-- Pointer -->
                        <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 text-yellow-500 text-4xl z-20">
                            <i class="fas fa-caret-down"></i>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-white mb-2" id="winnerText">Siap Mengundi?</h3>
                        <p class="text-gray-400 mb-6">Klik tombol di bawah untuk mengacak pemenang!</p>
                        
                        <form id="drawForm" method="POST" action="" class="hidden">
                            <input type="hidden" name="draw" value="1">
                            <input type="hidden" name="arisan_id" value="<?php echo $selectedArisan['id']; ?>">
                            <input type="hidden" name="member_id" id="winnerInput">
                        </form>

                        <button onclick="startDraw()" id="drawButton" class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-8 py-4 rounded-full font-bold text-xl shadow-lg shadow-purple-500/50 transform hover:scale-105 transition-all">
                            <i class="fas fa-random mr-2"></i> KOCOK SEKARANG!
                        </button>
                    </div>
                </div>

                <!-- Eligible Members List -->
                <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50 mt-8">
                    <h3 class="text-xl font-bold text-white mb-4">Peserta yang Memenuhi Syarat (<?php echo count($eligibleMembers); ?>)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($eligibleMembers as $member): ?>
                            <div class="bg-gray-900/50 rounded-xl p-4 border border-gray-700/30 hover:border-purple-500/30 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="bg-gradient-to-br from-purple-600 to-pink-600 p-3 rounded-lg">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-white"><?php echo htmlspecialchars($member['name']); ?></h4>
                                        <p class="text-sm text-gray-400"><?php echo htmlspecialchars($member['phone']); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl p-12 text-center border border-gray-700/50">
                <i class="fas fa-arrow-up text-4xl text-gray-600 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-400">Pilih Arisan Terlebih Dahulu</h3>
            </div>
        <?php endif; ?>
    </div>
    
    <div id="confettiContainer"></div>

    <script src="assets/js/main.js"></script>
    <script>
        const members = <?php echo json_encode($eligibleMembers ?? []); ?>;
        const wheel = document.getElementById('wheel');
        const wheelText = document.getElementById('wheelText');
        const winnerText = document.getElementById('winnerText');
        const drawButton = document.getElementById('drawButton');
        let isSpinning = false;

        function startDraw() {
            if (isSpinning || members.length === 0) return;
            
            isSpinning = true;
            drawButton.disabled = true;
            drawButton.classList.add('opacity-50', 'cursor-not-allowed');
            
            // Add spin class for CSS animation
            wheel.classList.add('lottery-spin');
            winnerText.innerText = "Mengacak...";
            
            // Shuffle text animation
            let counter = 0;
            const shuffleInterval = setInterval(() => {
                const randomMember = members[Math.floor(Math.random() * members.length)];
                wheelText.innerText = randomMember.name;
                counter++;
            }, 100);

            setTimeout(() => {
                clearInterval(shuffleInterval);
                wheel.classList.remove('lottery-spin');
                
                // Select Winner
                const winnerIndex = Math.floor(Math.random() * members.length);
                const winner = members[winnerIndex];
                
                wheelText.innerText = winner.name;
                winnerText.innerText = `Pemenang: ${winner.name}!`;
                winnerText.classList.add('text-yellow-400', 'animate-bounce');
                
                // Show Confetti
                if (typeof createConfetti === 'function') {
                    createConfetti();
                }
                
                setTimeout(() => {
                    confirmDialog(`SELAMAT! Pemenangnya adalah:\n\n🎉 ${winner.name} 🎉\n\nSimpan hasil ini?`, () => {
                        document.getElementById('winnerInput').value = winner.id;
                        document.getElementById('drawForm').submit();
                    });
                    
                    isSpinning = false;
                    drawButton.disabled = false;
                    drawButton.classList.remove('opacity-50', 'cursor-not-allowed');
                }, 1000);
                
            }, 3000);
        }
        
        <?php if (isset($success)): ?>
            showToast('Pemenang berhasil disimpan!', 'success');
        <?php endif; ?>
    </script>
</body>
</html>

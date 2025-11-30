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
            $stmt = $conn->prepare("INSERT INTO members (name, phone, address, status) VALUES (?, ?, ?, 'active')");
            $stmt->execute([$_POST['name'], $_POST['phone'], $_POST['address']]);
            $success = "Anggota berhasil ditambahkan!";
        } elseif ($_POST['action'] == 'edit') {
            $stmt = $conn->prepare("UPDATE members SET name = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->execute([$_POST['name'], $_POST['phone'], $_POST['address'], $_POST['id']]);
            $success = "Anggota berhasil diupdate!";
        }
    } elseif (isset($_POST['delete_all'])) {
        // DELETE FROM members will cascade to payments and winners
        $stmt = $conn->query("DELETE FROM members");
        $success = "Semua anggota (dan data terkait) berhasil dihapus!";
    }
}

if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("UPDATE members SET status = 'inactive' WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $success = "Anggota berhasil dihapus!";
}

// Get all members
$stmt = $conn->query("SELECT * FROM members WHERE status = 'active' ORDER BY created_at DESC");
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get member for edit
$editMember = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editMember = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Anggota - Sistem Arisan Digital</title>
    <meta name="description" content="Kelola data anggota arisan">
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
                    <i class="fas fa-users"></i> Data Anggota
                </h1>
                <p class="text-gray-400">Kelola data anggota arisan</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <?php if (!empty($members)): ?>
                    <button onclick="confirmDeleteAll()" class="bg-red-600/20 hover:bg-red-600/30 text-red-400 border border-red-600/30 px-6 py-3 rounded-xl font-semibold transition-all hover:shadow-lg hover:shadow-red-500/20">
                        <i class="fas fa-trash-alt mr-2"></i>Hapus Semua
                    </button>
                <?php endif; ?>
                <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 px-6 py-3 rounded-xl font-semibold transition-all hover:shadow-lg hover:shadow-purple-500/50">
                    <i class="fas fa-plus mr-2"></i>Tambah Anggota
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

        <!-- Members Table -->
        <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl border border-gray-700/50 overflow-hidden">
            <div class="p-6 border-b border-gray-700/50">
                <div class="flex items-center gap-4">
                    <i class="fas fa-search text-gray-400"></i>
                    <input 
                        type="text" 
                        id="searchInput"
                        placeholder="Cari anggota..." 
                        class="flex-1 bg-transparent border-none outline-none text-white placeholder-gray-500"
                    >
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full custom-table" id="membersTable">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">No</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Nama</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Telepon</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Alamat</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Terdaftar</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-300">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/30">
                        <?php foreach ($members as $index => $member): ?>
                            <tr class="hover:bg-gray-700/20 transition-colors">
                                <td class="px-6 py-4 text-gray-300"><?php echo $index + 1; ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="bg-gradient-to-br from-purple-600 to-pink-600 p-2 rounded-lg">
                                            <i class="fas fa-user text-white text-sm"></i>
                                        </div>
                                        <span class="font-medium text-white"><?php echo htmlspecialchars($member['name']); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-300"><?php echo htmlspecialchars($member['phone']); ?></td>
                                <td class="px-6 py-4 text-gray-300"><?php echo htmlspecialchars($member['address']); ?></td>
                                <td class="px-6 py-4 text-gray-300"><?php echo date('d/m/Y', strtotime($member['created_at'])); ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="member_detail.php?id=<?php echo $member['id']; ?>" class="bg-purple-600/20 hover:bg-purple-600/30 text-purple-400 px-3 py-2 rounded-lg transition-all" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?edit=<?php echo $member['id']; ?>" class="bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 px-3 py-2 rounded-lg transition-all" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="handleDelete('?delete=<?php echo $member['id']; ?>', 'Hapus anggota <?php echo htmlspecialchars($member['name']); ?>?')" class="bg-red-600/20 hover:bg-red-600/30 text-red-400 px-3 py-2 rounded-lg transition-all" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="addModal" class="<?php echo $editMember ? '' : 'hidden'; ?> fixed inset-0 modal-overlay z-50 flex items-center justify-center">
        <div class="bg-gray-800 rounded-2xl p-8 max-w-md w-full mx-4 border border-gray-700 animate-fadeIn">
            <h2 class="text-2xl font-bold text-white mb-6">
                <?php echo $editMember ? 'Edit Anggota' : 'Tambah Anggota'; ?>
            </h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $editMember ? 'edit' : 'add'; ?>">
                <?php if ($editMember): ?>
                    <input type="hidden" name="id" value="<?php echo $editMember['id']; ?>">
                <?php endif; ?>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Nama Lengkap</label>
                        <input type="text" name="name" required value="<?php echo $editMember['name'] ?? ''; ?>" class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Nomor Telepon</label>
                        <input type="text" name="phone" required value="<?php echo $editMember['phone'] ?? ''; ?>" class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Alamat</label>
                        <textarea name="address" required rows="3" class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20"><?php echo $editMember['address'] ?? ''; ?></textarea>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="window.location.href='members.php'" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white px-4 py-3 rounded-xl transition-all">
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
        searchTable('searchInput', 'membersTable');

        function confirmDeleteAll() {
            confirmDialog('PERINGATAN KERAS! Menghapus semua anggota juga akan MENGHAPUS SEMUA riwayat pembayaran dan kemenangan mereka. Data tidak bisa dikembalikan!', () => {
                if(confirm('Yakin 100%? Ini akan mereset hampir seluruh data aplikasi!')) {
                    document.getElementById('deleteAllForm').submit();
                }
            });
        }
    </script>
</body>
</html>

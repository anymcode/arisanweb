<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$success = '';
$error = '';

// Get current admin data
$stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Update profile info
    if (!empty($name) && !empty($username)) {
        $stmt = $conn->prepare("UPDATE admin SET name = ?, username = ? WHERE id = ?");
        if ($stmt->execute([$name, $username, $_SESSION['admin_id']])) {
            $_SESSION['admin_name'] = $name;
            $success = "Profil berhasil diperbarui!";
            
            // Refresh data
            $admin['name'] = $name;
            $admin['username'] = $username;
        } else {
            $error = "Gagal memperbarui profil.";
        }
    }
    
    // Update password if provided
    if (!empty($new_password)) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashed_password, $_SESSION['admin_id']])) {
                $success = "Password berhasil diubah!";
            } else {
                $error = "Gagal mengubah password.";
            }
        } else {
            $error = "Konfirmasi password tidak cocok!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - Sistem Arisan Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-gray-900 via-black to-gray-900 text-gray-100 min-h-screen font-['Inter']">
    
    <?php include 'includes/sidebar.php'; ?>

    <div class="ml-64 p-8">
        <div class="mb-8">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-400 to-pink-600 bg-clip-text text-transparent mb-2">
                <i class="fas fa-user-cog"></i> Pengaturan Profil
            </h1>
            <p class="text-gray-400">Kelola akun administrator</p>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-500/10 border border-green-500/50 rounded-xl p-4 mb-6 alert-auto-hide">
                <p class="text-green-400 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/50 rounded-xl p-4 mb-6 alert-auto-hide">
                <p class="text-red-400 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Profile Form -->
            <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl p-8 border border-gray-700/50">
                <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <i class="fas fa-id-card text-purple-400"></i> Informasi Akun
                </h2>
                
                <form method="POST" action="" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Nama Lengkap</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                    </div>

                    <div class="pt-4 border-t border-gray-700/50">
                        <h3 class="text-lg font-semibold text-white mb-4">Ganti Password</h3>
                        <p class="text-sm text-gray-400 mb-4">Kosongkan jika tidak ingin mengubah password</p>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Password Baru</label>
                                <input type="password" name="new_password" class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Konfirmasi Password</label>
                                <input type="password" name="confirm_password" class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold py-3 rounded-xl transition-all hover:shadow-lg hover:shadow-purple-500/50">
                        <i class="fas fa-save mr-2"></i>Simpan Perubahan
                    </button>
                </form>
            </div>

            <!-- Info Card -->
            <div class="space-y-6">
                <div class="bg-gradient-to-br from-purple-900/40 to-pink-900/40 backdrop-blur-xl rounded-2xl p-8 border border-purple-500/20">
                    <div class="text-center mb-6">
                        <div class="inline-block bg-gradient-to-br from-purple-600 to-pink-600 p-4 rounded-full mb-4">
                            <i class="fas fa-user-shield text-4xl text-white"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($admin['name']); ?></h3>
                        <p class="text-purple-300">Administrator</p>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-900/50 rounded-xl border border-gray-700/30">
                            <span class="text-gray-400">Terdaftar Sejak</span>
                            <span class="text-white font-medium"><?php echo date('d F Y', strtotime($admin['created_at'])); ?></span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-900/50 rounded-xl border border-gray-700/30">
                            <span class="text-gray-400">Status</span>
                            <span class="text-green-400 font-medium flex items-center gap-2">
                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                Aktif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>

<!-- Mobile Menu Button -->
<button id="mobile-menu-btn" class="md:hidden fixed top-4 left-4 z-50 bg-gray-800 text-white p-3 rounded-xl shadow-lg border border-gray-700">
    <i class="fas fa-bars text-xl"></i>
</button>

<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 hidden backdrop-blur-sm transition-opacity"></div>

<!-- Sidebar -->
<div id="sidebar" class="fixed top-0 left-0 h-screen w-64 bg-gray-900/95 backdrop-blur-xl border-r border-gray-800 z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="p-6">
        <div class="flex items-center gap-3 mb-8">
            <div class="bg-gradient-to-br from-purple-600 to-pink-600 p-2 rounded-lg">
                <i class="fas fa-gem text-white text-xl"></i>
            </div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-purple-400 to-pink-600 bg-clip-text text-transparent">
                ArisanApp
            </h1>
        </div>

        <nav class="space-y-2">
            <a href="index.php" class="flex items-center gap-3 text-gray-400 hover:text-white hover:bg-gray-800 px-4 py-3 rounded-xl transition-all <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-gray-800 text-white border border-gray-700 shadow-lg shadow-purple-500/10' : ''; ?>">
                <i class="fas fa-home w-6"></i>
                <span>Dashboard</span>
            </a>
            <a href="members.php" class="flex items-center gap-3 text-gray-400 hover:text-white hover:bg-gray-800 px-4 py-3 rounded-xl transition-all <?php echo basename($_SERVER['PHP_SELF']) == 'members.php' ? 'bg-gray-800 text-white border border-gray-700 shadow-lg shadow-purple-500/10' : ''; ?>">
                <i class="fas fa-users w-6"></i>
                <span>Anggota</span>
            </a>
            <a href="arisan.php" class="flex items-center gap-3 text-gray-400 hover:text-white hover:bg-gray-800 px-4 py-3 rounded-xl transition-all <?php echo basename($_SERVER['PHP_SELF']) == 'arisan.php' ? 'bg-gray-800 text-white border border-gray-700 shadow-lg shadow-purple-500/10' : ''; ?>">
                <i class="fas fa-box-open w-6"></i>
                <span>Data Arisan</span>
            </a>
            <a href="draw.php" class="flex items-center gap-3 text-gray-400 hover:text-white hover:bg-gray-800 px-4 py-3 rounded-xl transition-all <?php echo basename($_SERVER['PHP_SELF']) == 'draw.php' ? 'bg-gray-800 text-white border border-gray-700 shadow-lg shadow-purple-500/10' : ''; ?>">
                <i class="fas fa-dice w-6"></i>
                <span>Kocok Undian</span>
            </a>
            <a href="winners.php" class="flex items-center gap-3 text-gray-400 hover:text-white hover:bg-gray-800 px-4 py-3 rounded-xl transition-all <?php echo basename($_SERVER['PHP_SELF']) == 'winners.php' ? 'bg-gray-800 text-white border border-gray-700 shadow-lg shadow-purple-500/10' : ''; ?>">
                <i class="fas fa-trophy w-6"></i>
                <span>Pemenang</span>
            </a>
            <a href="payments.php" class="flex items-center gap-3 text-gray-400 hover:text-white hover:bg-gray-800 px-4 py-3 rounded-xl transition-all <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'bg-gray-800 text-white border border-gray-700 shadow-lg shadow-purple-500/10' : ''; ?>">
                <i class="fas fa-money-bill-wave w-6"></i>
                <span>Pembayaran</span>
            </a>
            <a href="arrears.php" class="flex items-center gap-3 text-gray-400 hover:text-white hover:bg-gray-800 px-4 py-3 rounded-xl transition-all <?php echo basename($_SERVER['PHP_SELF']) == 'arrears.php' ? 'bg-gray-800 text-white border border-gray-700 shadow-lg shadow-purple-500/10' : ''; ?>">
                <i class="fas fa-exclamation-circle w-6"></i>
                <span>Cek Tunggakan</span>
            </a>
        </nav>
    </div>

    <div class="absolute bottom-0 w-full p-6 border-t border-gray-800">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-600 flex items-center justify-center">
                <i class="fas fa-user-shield text-white"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-white"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></p>
                <p class="text-xs text-gray-400">Administrator</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <a href="profile.php" class="text-center py-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-xs text-gray-300 transition-colors">
                Profile
            </a>
            <a href="logout.php" class="text-center py-2 rounded-lg bg-red-900/20 hover:bg-red-900/40 text-xs text-red-400 transition-colors">
                Logout
            </a>
        </div>
    </div>
</div>

<script>
    // Mobile Menu Logic
    const menuBtn = document.getElementById('mobile-menu-btn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    if (menuBtn && sidebar && overlay) {
        function toggleSidebar() {
            const isHidden = sidebar.classList.contains('-translate-x-full');
            if (isHidden) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        menuBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);
        
        // Close sidebar when clicking a link (on mobile)
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) { // md breakpoint
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                }
            });
        });
    }
</script>

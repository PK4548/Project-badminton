<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm no-print">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-trophy-fill me-2"></i>Badminton Booking System
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item me-3">
                    <span class="nav-link text-white active">
                        สวัสดี, <?php echo $_SESSION['user_name']; ?> 
                        <span class="badge bg-light text-primary ms-1"><?php echo $_SESSION['user_role']; ?></span>
                    </span>
                </li>

                <li class="nav-item me-2">
                    <button class="btn btn-link nav-link theme-toggle-btn p-0" id="theme-toggle" type="button">
                        <i class="bi bi-sun-fill d-none" id="theme-icon-light"></i>
                        <i class="bi bi-moon-stars-fill" id="theme-icon-dark"></i>
                    </button>
                </li>

                <li class="nav-item">
                    <a href="logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                        <i class="bi bi-box-arrow-right me-1"></i> ออกจากระบบ
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    /* สไตล์สำหรับปุ่มสลับโหมด */
    #theme-toggle { cursor: pointer; font-size: 1.3rem; transition: 0.3s; border: none; text-decoration: none; }
    #theme-toggle:hover { transform: scale(1.1); }
    #theme-icon-light { color: #ffca28 !important; }
    #theme-icon-dark { color: #ffffff !important; }
    .navbar-brand:hover { opacity: 0.9; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const themeToggle = document.getElementById('theme-toggle');
        const iconLight = document.getElementById('theme-icon-light');
        const iconDark = document.getElementById('theme-icon-dark');
        const htmlElement = document.documentElement;

        // 1. ฟังก์ชันอัปเดตหน้าตาปุ่มตามธีมปัจจุบัน
        function updateUI(theme) {
            htmlElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);
            
            if (theme === 'dark') {
                iconLight.classList.remove('d-none');
                iconDark.classList.add('d-none');
            } else {
                iconLight.classList.add('d-none');
                iconDark.classList.remove('d-none');
            }
        }

        // 2. โหลดค่าเริ่มต้นจาก localStorage (ถ้าไม่มีให้เป็น light)
        const savedTheme = localStorage.getItem('theme') || 'light';
        updateUI(savedTheme);

        // 3. เหตุการณ์เมื่อกดปุ่มสลับโหมด
        themeToggle.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            updateUI(newTheme);
        });
    });
</script>
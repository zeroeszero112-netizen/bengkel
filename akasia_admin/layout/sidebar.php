        <nav class="sidebar-nav">
            <a href="index.php?page=dashboard" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>

            <div class="nav-group">
                <div class="nav-group-title">
                    <span>Kelola Reservasi</span>
                </div>
                <div class="nav-submenu">
                    <a href="index.php?page=status_servis&stage=menunggu_konfirmasi" class="nav-item nav-subitem <?= ($currentPage === 'status_servis' && (($_GET['stage'] ?? 'menunggu_konfirmasi') === 'menunggu_konfirmasi')) || in_array($currentPage, ['reservasi', 'detail_reservasi'], true) ? 'active' : '' ?>">
                        <i class="bi bi-hourglass-split"></i>
                        <span>Menunggu Konfirmasi</span>
                    </a>
                    <a href="index.php?page=status_servis&stage=dikonfirmasi" class="nav-item nav-subitem <?= $currentPage === 'status_servis' && (($_GET['stage'] ?? 'dikonfirmasi') === 'dikonfirmasi') ? 'active' : '' ?>">
                        <i class="bi bi-patch-check"></i>
                        <span>Dikonfirmasi</span>
                    </a>
                    <a href="index.php?page=status_servis&stage=menunggu_antrean" class="nav-item nav-subitem <?= $currentPage === 'status_servis' && (($_GET['stage'] ?? '') === 'menunggu_antrean') ? 'active' : '' ?>">
                        <i class="bi bi-people"></i>
                        <span>Menunggu Antrean</span>
                    </a>
                    <a href="index.php?page=status_servis&stage=diproses" class="nav-item nav-subitem <?= $currentPage === 'status_servis' && (($_GET['stage'] ?? '') === 'diproses') ? 'active' : '' ?>">
                        <i class="bi bi-wrench-adjustable-circle"></i>
                        <span>Diproses</span>
                    </a>
                    <a href="index.php?page=status_servis&stage=pending" class="nav-item nav-subitem <?= $currentPage === 'status_servis' && (($_GET['stage'] ?? '') === 'pending') ? 'active' : '' ?>">
                        <i class="bi bi-clock-history"></i>
                        <span>Pending Kehadiran</span>
                    </a>
                    <a href="index.php?page=status_servis&stage=selesai" class="nav-item nav-subitem <?= $currentPage === 'status_servis' && (($_GET['stage'] ?? '') === 'selesai') ? 'active' : '' ?>">
                        <i class="bi bi-check-circle"></i>
                        <span>Selesai</span>
                    </a>
                </div>
            </div>

            <div class="nav-group">
                <div class="nav-group-title">
                    <span>Data Master</span>
                </div>
                <div class="nav-submenu">
                    <a href="index.php?page=jenis_layanan" class="nav-item nav-subitem <?= $currentPage === 'jenis_layanan' ? 'active' : '' ?>">
                        <i class="bi bi-card-checklist"></i>
                        <span>Jenis Layanan Servis</span>
                    </a>
                    <a href="index.php?page=kegiatan_servis" class="nav-item nav-subitem <?= $currentPage === 'kegiatan_servis' ? 'active' : '' ?>">
                        <i class="bi bi-list-task"></i>
                        <span>Kegiatan Servis</span>
                    </a>
                    <a href="index.php?page=mekanik" class="nav-item nav-subitem <?= $currentPage === 'mekanik' ? 'active' : '' ?>">
                        <i class="bi bi-person-gear"></i>
                        <span>Mekanik</span>
                    </a>
                    <a href="index.php?page=template_notifikasi" class="nav-item nav-subitem <?= $currentPage === 'template_notifikasi' ? 'active' : '' ?>">
                        <i class="bi bi-chat-left-text"></i>
                        <span>Template WhatsApp</span>
                    </a>
                </div>
            </div>

            <div class="nav-group">
                <div class="nav-group-title">
                    <span>Pengaturan</span>
                </div>
                <div class="nav-submenu">
                    <a href="index.php?page=pengaturan" class="nav-item nav-subitem <?= $currentPage === 'pengaturan' ? 'active' : '' ?>">
                        <i class="bi bi-sliders"></i>
                        <span>Profil Bengkel</span>
                    </a>
                </div>
            </div>

            <a href="logout.php" class="nav-item nav-logout">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </nav>
    </aside>

    <main class="content-area">
        <header class="topbar">
            <div>
                <h1 class="page-title"><?= htmlspecialchars($pageTitle) ?></h1>
                <div class="page-subtitle">Sistem administrasi bengkel Akasia Motor</div>
            </div>
            <div class="topbar-user">
                <div class="avatar-circle"><?= htmlspecialchars(strtoupper(substr($adminUser['nama'] ?? 'A', 0, 1))) ?></div>
                <div>
                    <div class="user-name"><?= htmlspecialchars($adminUser['nama'] ?? 'Admin') ?></div>
                    <div class="user-role"><?= htmlspecialchars($adminUser['email'] ?? 'Administrator') ?></div>
                </div>
            </div>
        </header>
        <section class="page-body">
            <?php if ($flash): ?>
                <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show shadow-sm" role="alert">
                    <?= htmlspecialchars($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

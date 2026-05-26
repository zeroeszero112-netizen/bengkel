<?php
$stages = getReservasiStages();
$counts = [];
foreach (array_keys($stages) as $stageKey) {
    $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM reservasi WHERE status = :status");
    $countStmt->execute([':status' => $stageKey]);
    $counts[$stageKey] = (int) ($countStmt->fetch()['total'] ?? 0);
}

$mekanikStmt = $pdo->query("SELECT nama, no_hp, status FROM mekanik ORDER BY FIELD(status, 'tersedia', 'sibuk', 'libur'), nama ASC");
$mekanikList = $mekanikStmt->fetchAll();

// Antrean saat ini - yang sedang diproses
$antreanStmt = $pdo->query("
    SELECT r.id, r.no_antrian, u.nama AS pelanggan, jl.nama AS layanan, m.nama AS mekanik
    FROM reservasi r
    INNER JOIN users u ON u.id = r.user_id
    INNER JOIN jenis_layanan jl ON jl.id = r.jenis_layanan_id
    LEFT JOIN mekanik m ON m.id = r.mekanik_id
    WHERE r.status = 'diproses'
    ORDER BY r.updated_at DESC
    LIMIT 5
");
$antreanList = $antreanStmt->fetchAll();

// Reservasi menunggu konfirmasi
$menungguKonfirmasiStmt = $pdo->query("
    SELECT r.id, r.no_antrian, u.nama AS pelanggan, jl.nama AS layanan, r.tanggal_servis
    FROM reservasi r
    INNER JOIN users u ON u.id = r.user_id
    INNER JOIN jenis_layanan jl ON jl.id = r.jenis_layanan_id
    WHERE r.status = 'menunggu_konfirmasi'
    ORDER BY r.created_at DESC
    LIMIT 5
");
$menungguKonfirmasiList = $menungguKonfirmasiStmt->fetchAll();

// Mekanik tersedia
$mekanikTersediaStmt = $pdo->query("
    SELECT nama, no_hp
    FROM mekanik
    WHERE status = 'tersedia'
    ORDER BY nama ASC
    LIMIT 5
");
$mekanikTersediaList = $mekanikTersediaStmt->fetchAll();

// Aktivitas terbaru (perubahan status) - link ke riwayat_servis
$aktivitasStmt = $pdo->query("
    SELECT r.no_antrian, u.nama AS pelanggan, r.status, r.updated_at, r.id AS reservasi_id
    FROM reservasi r
    INNER JOIN users u ON u.id = r.user_id
    ORDER BY r.updated_at DESC
    LIMIT 5
");
$aktivitasList = $aktivitasStmt->fetchAll();

$todayStmt = $pdo->query("
    SELECT r.no_antrian, u.nama AS pelanggan, jl.nama AS layanan
    FROM reservasi r
    INNER JOIN users u ON u.id = r.user_id
    INNER JOIN jenis_layanan jl ON jl.id = r.jenis_layanan_id
    WHERE r.tanggal_servis = CURDATE()
    ORDER BY r.no_antrian ASC
    LIMIT 1
");
$highlight = $todayStmt->fetch();
?>

<div class="status-grid">
    <?php foreach ($stages as $stageKey => $meta): ?>
        <?php $targetUrl = 'index.php?page=status_servis&stage=' . urlencode($stageKey); ?>
        <a href="<?= htmlspecialchars($targetUrl) ?>" class="status-card text-decoration-none">
            <div class="status-card-label"><?= htmlspecialchars($meta['label']) ?></div>
            <div class="status-card-value"><?= $counts[$stageKey] ?? 0 ?></div>
        </a>
    <?php endforeach; ?>
</div>

<!-- Bagian 1: Antrean Saat Ini dan Menunggu Konfirmasi -->
<div class="row g-4 mb-4">
    <!-- Antrean Saat Ini -->
    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="section-title d-flex justify-content-between align-items-center">
                    <span>Antrean Saat Ini</span>
                </div>
                <?php if (empty($antreanList)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-6"></i>
                        <p class="mt-2">Tidak ada antrean yang sedang diproses.</p>
                    </div>
                <?php else: ?>
                    <div class="process-list">
                        <?php foreach ($antreanList as $item): ?>
                            <div class="process-item">
                                <div class="process-meta">
                                    <span class="process-code"><?= htmlspecialchars($item['no_antrian'] ?? '-') ?></span>
                                    <div class="process-line"><strong><?= htmlspecialchars($item['pelanggan'] ?? '-') ?></strong></div>
                                    <div class="process-line text-muted"><?= htmlspecialchars($item['layanan'] ?? '-') ?></div>
                                    <div class="process-line">
                                        <small>Mekanik: </small>
                                        <?php if ($item['mekanik']): ?>
                                            <strong><?= htmlspecialchars($item['mekanik']) ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="process-actions">
                                    <a href="index.php?page=detail_reservasi&id=<?= (int) $item['id'] ?>" class="btn btn-primary">
                                        <i class="bi bi-play-circle"></i>
                                        Proses
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Reservasi Menunggu Konfirmasi -->
    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="section-title d-flex justify-content-between align-items-center">
                    <span>Menunggu Konfirmasi</span>
                    <a href="index.php?page=status_servis&stage=menunggu_konfirmasi" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-list-ul"></i>
                        Lihat Semua
                    </a>
                </div>
                <?php if (empty($menungguKonfirmasiList)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-6"></i>
                        <p class="mt-2">Tidak ada reservasi menunggu konfirmasi.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Antrian</th>
                                    <th>Pelanggan</th>
                                    <th>Layanan</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($menungguKonfirmasiList as $item): ?>
                                    <tr>
                                        <td><span class="process-code"><?= htmlspecialchars($item['no_antrian'] ?? '-') ?></span></td>
                                        <td><strong><?= htmlspecialchars($item['pelanggan'] ?? '-') ?></strong></td>
                                        <td><?= htmlspecialchars($item['layanan'] ?? '-') ?></td>
                                        <td><?= formatDateIndonesia($item['tanggal_servis'] ?? null) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Bagian 2: Mekanik Tersedia dan Aktivitas Terbaru -->
<div class="row g-4">
    <!-- Mekanik Tersedia -->
    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="section-title">Mekanik Tersedia</div>
                <?php if (empty($mekanikTersediaList)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-person-x display-6"></i>
                        <p class="mt-2">Tidak ada mekanik tersedia saat ini.</p>
                    </div>
                <?php else: ?>
                    <div class="detail-list">
                        <?php foreach ($mekanikTersediaList as $mekanik): ?>
                            <div class="detail-row">
                                <span>
                                    <strong class="d-block text-start"><?= htmlspecialchars($mekanik['nama']) ?></strong>
                                    <small class="text-secondary"><?= htmlspecialchars(displayPhone($mekanik['no_hp'])) ?></small>
                                </span>
                                <strong><span class="badge badge-soft-success">Tersedia</span></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Aktivitas Terbaru -->
    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="section-title d-flex justify-content-between align-items-center">
                    <span>Aktivitas Terbaru</span>
                    <a href="index.php?page=riwayat_servis" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-clock-history"></i>
                        Lihat Semua
                    </a>
                </div>
                <?php if (empty($aktivitasList)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-activity display-6"></i>
                        <p class="mt-2">Belum ada aktivitas terbaru.</p>
                    </div>
                <?php else: ?>
                    <div class="detail-list">
                        <?php foreach ($aktivitasList as $aktivitas): ?>
                            <a href="index.php?page=riwayat_servis" class="text-decoration-none">
                                <div class="detail-row">
                                    <span>
                                        <strong class="d-block text-start"><?= htmlspecialchars($aktivitas['no_antrian'] ?? '-') ?> - <?= htmlspecialchars($aktivitas['pelanggan'] ?? '-') ?></strong>
                                        <small class="text-secondary"><?= formatDateTimeIndonesia($aktivitas['updated_at'] ?? null) ?></small>
                                    </span>
                                    <span class="badge <?= getStatusBadgeClass($aktivitas['status'] ?? '') ?>"><?= htmlspecialchars(getStageMeta($aktivitas['status'] ?? '')['label']) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

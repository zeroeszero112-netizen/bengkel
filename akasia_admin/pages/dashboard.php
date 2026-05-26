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

<div class="row g-4">
    <div class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="section-title">Fokus Hari Ini</div>
                <div class="helper-box">
                    <div class="detail-row"><span>Antrean</span><strong><?= htmlspecialchars($highlight['no_antrian'] ?? '-') ?></strong></div>
                    <div class="detail-row"><span>Pelanggan</span><strong><?= htmlspecialchars($highlight['pelanggan'] ?? 'Belum ada data') ?></strong></div>
                    <div class="detail-row"><span>Layanan</span><strong><?= htmlspecialchars($highlight['layanan'] ?? '-') ?></strong></div>
                </div>
                <div class="mt-3 text-secondary">Dashboard ini mengikuti alur status final. Setiap menu di sidebar langsung merepresentasikan satu status reservasi.</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="section-title">Ringkasan Proses</div>
                <div class="detail-list">
                    <div class="detail-row"><span>Reservasi Hari Ini</span><strong><?= array_sum($counts) ?></strong></div>
                    <div class="detail-row"><span>Siap Diproses</span><strong><?= $counts['menunggu_antrean'] ?? 0 ?></strong></div>
                    <div class="detail-row"><span>Sedang Dikerjakan</span><strong><?= $counts['diproses'] ?? 0 ?></strong></div>
                    <div class="detail-row"><span>Pending Kehadiran</span><strong><?= $counts['pending'] ?? 0 ?></strong></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="section-title">Status Mekanik</div>
                <?php foreach ($mekanikList as $mekanik): ?>
                    <div class="detail-row">
                        <span>
                            <strong class="d-block text-start"><?= htmlspecialchars($mekanik['nama']) ?></strong>
                            <small class="text-secondary"><?= htmlspecialchars(displayPhone($mekanik['no_hp'])) ?></small>
                        </span>
                        <strong><span class="badge <?= getMekanikBadgeClass($mekanik['status']) ?>"><?= htmlspecialchars($mekanik['status']) ?></span></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

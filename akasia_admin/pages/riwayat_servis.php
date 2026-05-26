<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();

$currentPage = 'riwayat_servis';
$pageTitle = 'Riwayat Servis';
$adminUser = currentAdmin();
$flash = getFlash();

// Pagination
$perPage = 15;
$currentPageNum = max(1, (int) ($_GET['p'] ?? 1));
$offset = ($currentPageNum - 1) * $perPage;

// Get all reservations ordered by latest
$countStmt = $pdo->query("SELECT COUNT(*) AS total FROM reservasi");
$totalRecords = (int) ($countStmt->fetch()['total'] ?? 0);
$totalPages = (int) ceil($totalRecords / $perPage);

$stmt = $pdo->prepare("
    SELECT 
        r.*,
        u.nama AS pelanggan,
        u.no_whatsapp,
        jl.nama AS jenis_layanan,
        m.nama AS mekanik_nama,
        ss.nama AS status_nama
    FROM reservasi r
    LEFT JOIN users u ON u.id = r.user_id
    LEFT JOIN jenis_layanan jl ON jl.id = r.jenis_layanan_id
    LEFT JOIN mekanik m ON m.id = r.mekanik_id
    LEFT JOIN status_servis ss ON ss.kode = r.status
    ORDER BY r.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$reservasiList = $stmt->fetchAll();

$stages = getReservasiStages();

include __DIR__ . '/../layout/header.php';
?>

<div class="toolbar">
    <div class="toolbar-group">
        <span class="stage-chip active">
            <i class="bi bi-clock-history"></i>
            <span>Semua Riwayat Servis</span>
        </span>
    </div>
    <div class="toolbar-group">
        <a href="index.php?page=status_servis&stage=menunggu_konfirmasi" class="btn btn-outline-secondary">
            <i class="bi bi-hourglass-split"></i>
            Menunggu Konfirmasi
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($reservasiList)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox display-4 text-muted"></i>
                <p class="mt-3 text-muted">Belum ada data reservasi.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No. Antrian</th>
                            <th>Pelanggan</th>
                            <th>Layanan</th>
                            <th>Tanggal Servis</th>
                            <th>Mekanik</th>
                            <th>Status</th>
                            <th>Kehadiran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservasiList as $r): ?>
                            <tr>
                                <td>
                                    <span class="process-code"><?= htmlspecialchars($r['no_antrian'] ?? '-') ?></span>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($r['pelanggan'] ?? '-') ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars(displayPhone($r['no_whatsapp'] ?? '')) ?></small>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($r['jenis_layanan'] ?? '-') ?></td>
                                <td><?= formatDateTimeIndonesia($r['tanggal_servis'] ?? null) ?></td>
                                <td>
                                    <?php if ($r['mekanik_nama']): ?>
                                        <span class="badge badge-soft-success"><?= htmlspecialchars($r['mekanik_nama']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= getStatusBadgeClass($r['status'] ?? '') ?>"><?= htmlspecialchars(getStageMeta($r['status'] ?? '')['label']) ?></span>
                                </td>
                                <td>
                                    <?php if ($r['kehadiran']): ?>
                                        <span class="badge <?= getKehadiranBadgeClass($r['kehadiran']) ?>"><?= htmlspecialchars($r['kehadiran']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="index.php?page=detail_reservasi&id=<?= (int) $r['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <?= renderPagination($currentPageNum, $totalPages, ['page' => 'riwayat_servis']) ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

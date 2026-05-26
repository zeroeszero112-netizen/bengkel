<?php
$pengaturanStmt = $pdo->query("
    SELECT id, nama_bengkel, alamat, no_whatsapp, jam_buka, jam_tutup, hari_operasional
    FROM pengaturan
    ORDER BY id ASC
    LIMIT 1
");
$pengaturanBengkel = $pengaturanStmt->fetch();

if (!$pengaturanBengkel) {
    echo '<div class="alert alert-warning shadow-sm">Data pengaturan belum tersedia.</div>';
    return;
}
?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <form method="post" action="index.php?page=pengaturan">
                    <input type="hidden" name="action" value="update_pengaturan">
                    <input type="hidden" name="id" value="<?= (int) $pengaturanBengkel['id'] ?>">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nama Bengkel</label>
                            <input type="text" name="nama_bengkel" class="form-control" value="<?= htmlspecialchars($pengaturanBengkel['nama_bengkel']) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="4"><?= htmlspecialchars($pengaturanBengkel['alamat']) ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor WhatsApp</label>
                            <input type="text" name="no_whatsapp" class="form-control" value="<?= htmlspecialchars($pengaturanBengkel['no_whatsapp']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Jam Buka</label>
                            <input type="time" name="jam_buka" class="form-control" value="<?= htmlspecialchars(substr((string) $pengaturanBengkel['jam_buka'], 0, 5)) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Jam Tutup</label>
                            <input type="time" name="jam_tutup" class="form-control" value="<?= htmlspecialchars(substr((string) $pengaturanBengkel['jam_tutup'], 0, 5)) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Hari Operasional</label>
                            <input type="text" name="hari_operasional" class="form-control" value="<?= htmlspecialchars($pengaturanBengkel['hari_operasional']) ?>">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-12 col-xl-4">
                <div class="setting-box">
                    <div class="section-title">Informasi Sistem</div>
                    <div class="detail-list">
                        <div><span>Aplikasi</span><strong>Admin Akasia Motor</strong></div>
                        <div><span>Arsitektur</span><strong>PHP Native Monolith</strong></div>
                        <div><span>Frontend</span><strong>Bootstrap 5</strong></div>
                        <div><span>Database</span><strong>MySQL</strong></div>
                        <div><span>WhatsApp</span><strong>Placeholder Log</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

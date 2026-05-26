<?php
$mekanikStmt = $pdo->query("SELECT id, nama, no_hp, status FROM mekanik ORDER BY nama ASC");
$mekanikList = $mekanikStmt->fetchAll();
$editId = (int) ($_GET['edit'] ?? 0);
$showForm = ($_GET['form'] ?? '') === 'tambah' || $editId > 0;
$mekanikEdit = [
    'id' => 0,
    'nama' => '',
    'no_hp' => '',
    'status' => 'tersedia'
];

if ($editId > 0) {
    $editStmt = $pdo->prepare("SELECT id, nama, no_hp, status FROM mekanik WHERE id = :id LIMIT 1");
    $editStmt->execute([':id' => $editId]);
    $mekanikEditData = $editStmt->fetch();
    if ($mekanikEditData) {
        $mekanikEdit = $mekanikEditData;
    }
}
?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="toolbar">
            <div class="toolbar-group">
                <a href="index.php?page=mekanik&form=tambah" class="btn btn-primary">Tambah Mekanik</a>
            </div>
        </div>

        <?php if ($showForm): ?>
            <div class="card border form-panel mb-4">
                <div class="card-body">
                    <div class="section-title mb-3"><?= $mekanikEdit['id'] ? 'Ubah Mekanik' : 'Tambah Mekanik' ?></div>
                    <form method="post" action="index.php?page=mekanik">
                        <input type="hidden" name="action" value="save_mekanik">
                        <input type="hidden" name="id" value="<?= (int) $mekanikEdit['id'] ?>">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Nama Mekanik</label>
                                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($mekanikEdit['nama']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">No. HP</label>
                                <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($mekanikEdit['no_hp'] ?: '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status_mekanik" class="form-select">
                                    <?php foreach (['tersedia', 'sibuk', 'libur'] as $statusOption): ?>
                                        <option value="<?= htmlspecialchars($statusOption) ?>" <?= $mekanikEdit['status'] === $statusOption ? 'selected' : '' ?>><?= htmlspecialchars($statusOption) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="index.php?page=mekanik" class="btn btn-outline-secondary">Batal</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php foreach ($mekanikList as $mekanik): ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card mechanic-card border">
                        <div class="card-body">
                            <div class="mechanic-avatar"><?= strtoupper(substr($mekanik['nama'], 0, 1)) ?></div>
                            <h5 class="mb-1"><?= htmlspecialchars($mekanik['nama']) ?></h5>
                            <div class="text-secondary mb-3"><?= htmlspecialchars($mekanik['no_hp'] ?: '-') ?></div>
                            <div class="mb-3">
                                <span class="badge <?= getMekanikBadgeClass($mekanik['status']) ?>"><?= htmlspecialchars($mekanik['status']) ?></span>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="index.php?page=mekanik&edit=<?= (int) $mekanik['id'] ?>" class="btn btn-outline-primary btn-sm w-100">Edit</a>
                                <form method="post" action="index.php?page=mekanik" class="w-100">
                                    <input type="hidden" name="action" value="delete_mekanik">
                                    <input type="hidden" name="id" value="<?= (int) $mekanik['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Hapus data mekanik ini?');">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (!$mekanikList): ?>
                <div class="col-12">
                    <div class="text-center text-secondary py-5">Data mekanik belum tersedia.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

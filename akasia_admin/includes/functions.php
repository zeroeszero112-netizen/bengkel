<?php

function redirectTo(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function formatDateIndonesia(?string $value): string
{
    if (!$value) {
        return '-';
    }

    $timestamp = strtotime($value);
    if (!$timestamp) {
        return $value;
    }

    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    return date('d', $timestamp) . ' ' . ($months[(int) date('n', $timestamp)] ?? date('m', $timestamp)) . ' ' . date('Y', $timestamp);
}

function formatDateTimeIndonesia(?string $value): string
{
    if (!$value) {
        return '-';
    }

    $timestamp = strtotime($value);
    if (!$timestamp) {
        return $value;
    }

    return formatDateIndonesia($value) . ' ' . date('H:i', $timestamp);
}

function getReservasiStages(): array
{
    return [
        'menunggu_konfirmasi' => ['label' => 'Menunggu Konfirmasi', 'icon' => 'bi-hourglass-split', 'badge' => 'warning'],
        'dikonfirmasi' => ['label' => 'Dikonfirmasi', 'icon' => 'bi-patch-check', 'badge' => 'primary'],
        'menunggu_antrean' => ['label' => 'Menunggu Antrean', 'icon' => 'bi-people', 'badge' => 'info'],
        'diproses' => ['label' => 'Diproses', 'icon' => 'bi-wrench-adjustable-circle', 'badge' => 'warning'],
        'pending' => ['label' => 'Pending Kehadiran', 'icon' => 'bi-clock-history', 'badge' => 'secondary'],
        'selesai' => ['label' => 'Selesai', 'icon' => 'bi-check-circle', 'badge' => 'success'],
    ];
}

function getStageMeta(string $status): array
{
    $stages = getReservasiStages();

    return $stages[$status] ?? ['label' => ucfirst(str_replace('_', ' ', $status)), 'icon' => 'bi-circle', 'badge' => 'dark'];
}

function getStatusBadgeClass(string $status): string
{
    return match ($status) {
        'menunggu_konfirmasi' => 'text-bg-warning',
        'dikonfirmasi' => 'text-bg-primary',
        'menunggu_antrean' => 'text-bg-info',
        'diproses' => 'text-bg-warning',
        'pending' => 'text-bg-secondary',
        'selesai' => 'text-bg-success',
        'dibatalkan' => 'text-bg-danger',
        default => 'text-bg-dark',
    };
}

function getKehadiranBadgeClass(string $status): string
{
    return $status === 'Hadir' ? 'text-bg-success' : 'text-bg-secondary';
}

function getMekanikBadgeClass(string $status): string
{
    return match ($status) {
        'sibuk' => 'text-bg-warning',
        'libur' => 'text-bg-secondary',
        default => 'text-bg-success',
    };
}

function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin']) && is_array($_SESSION['admin']);
}

function requireAdminAuth(): void
{
    if (!isAdminLoggedIn()) {
        redirectTo('index.php?page=login');
    }
}

function currentAdmin(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function normalizePhone(?string $phone): string
{
    $phone = trim((string) $phone);
    if ($phone === '') {
        return '';
    }

    return preg_replace('/\D+/', '', $phone);
}

function displayPhone(?string $phone): string
{
    $phone = normalizePhone($phone);
    if ($phone === '') {
        return '-';
    }

    if (str_starts_with($phone, '62')) {
        return $phone;
    }

    if (str_starts_with($phone, '0')) {
        return '62' . substr($phone, 1);
    }

    return $phone;
}

function isValidPhone(?string $phone): bool
{
    $phone = normalizePhone($phone);

    if ($phone === '') {
        return true;
    }

    return preg_match('/^(62|0)[0-9]{9,14}$/', $phone) === 1;
}

function isValidDateValue(?string $date): bool
{
    if (!$date) {
        return false;
    }

    $parsed = DateTime::createFromFormat('Y-m-d', $date);

    return $parsed && $parsed->format('Y-m-d') === $date;
}

function generatePelangganEmail(PDO $pdo, string $nama, string $whatsapp): string
{
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '.', $nama), '.'));
    if ($slug === '') {
        $slug = 'pelanggan';
    }

    $phonePart = preg_replace('/\D+/', '', $whatsapp);
    if ($phonePart === '') {
        $phonePart = (string) time();
    }

    $base = $slug . '.' . $phonePart;
    $email = $base . '@walkin.akasia.local';
    $counter = 1;

    while (true) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);

        if (!$stmt->fetch()) {
            return $email;
        }

        $counter++;
        $email = $base . $counter . '@walkin.akasia.local';
    }
}

function generateQueueNumber(PDO $pdo, string $tanggalServis): string
{
    $stmt = $pdo->prepare("
        INSERT INTO antrean_harian (tanggal_servis, nomor_terakhir)
        VALUES (:tanggal_servis, 1)
        ON DUPLICATE KEY UPDATE nomor_terakhir = nomor_terakhir + 1
    ");
    $stmt->execute([':tanggal_servis' => $tanggalServis]);

    $fetchStmt = $pdo->prepare("SELECT nomor_terakhir FROM antrean_harian WHERE tanggal_servis = :tanggal_servis LIMIT 1");
    $fetchStmt->execute([':tanggal_servis' => $tanggalServis]);
    $row = $fetchStmt->fetch();

    return 'A' . str_pad((string) ((int) ($row['nomor_terakhir'] ?? 1)), 3, '0', STR_PAD_LEFT);
}

function buildPageUrl(array $params): string
{
    return 'index.php?' . http_build_query(array_filter($params, static fn ($value) => $value !== null && $value !== ''));
}

function renderPagination(int $currentPage, int $totalPages, array $baseParams): string
{
    if ($totalPages <= 1) {
        return '';
    }

    $html = '<nav class="pagination-wrap" aria-label="Pagination"><ul class="pagination mb-0">';

    $prevParams = $baseParams;
    $prevParams['p'] = max(1, $currentPage - 1);
    $html .= '<li class="page-item' . ($currentPage <= 1 ? ' disabled' : '') . '">';
    $html .= '<a class="page-link" href="' . htmlspecialchars(buildPageUrl($prevParams)) . '"><i class="bi bi-chevron-left"></i></a></li>';

    for ($page = 1; $page <= $totalPages; $page++) {
        $pageParams = $baseParams;
        $pageParams['p'] = $page;
        $html .= '<li class="page-item' . ($page === $currentPage ? ' active' : '') . '">';
        $html .= '<a class="page-link" href="' . htmlspecialchars(buildPageUrl($pageParams)) . '">' . $page . '</a></li>';
    }

    $nextParams = $baseParams;
    $nextParams['p'] = min($totalPages, $currentPage + 1);
    $html .= '<li class="page-item' . ($currentPage >= $totalPages ? ' disabled' : '') . '">';
    $html .= '<a class="page-link" href="' . htmlspecialchars(buildPageUrl($nextParams)) . '"><i class="bi bi-chevron-right"></i></a></li>';
    $html .= '</ul></nav>';

    return $html;
}

function getWhatsAppTemplate(PDO $pdo, string $kode): ?string
{
    $stmt = $pdo->prepare("SELECT isi_pesan FROM template_whatsapp WHERE kode_template = :kode AND is_active = 1 LIMIT 1");
    $stmt->execute([':kode' => $kode]);
    $row = $stmt->fetch();

    return $row['isi_pesan'] ?? null;
}

function renderTemplateMessage(string $template, array $data): string
{
    foreach ($data as $key => $value) {
        $template = str_replace('{' . $key . '}', (string) $value, $template);
    }

    return $template;
}

function sendWhatsApp(string $phone, string $message): array
{
    $token = defined('FONNTE_API_TOKEN') ? FONNTE_API_TOKEN : '';

    if ($token === '' || $token === 'TOKEN_FONNTE_ANDA') {
        return ['success' => false, 'reason' => 'Token Fonnte belum dikonfigurasi.'];
    }

    $phone = displayPhone($phone);

    if ($phone === '-') {
        return ['success' => false, 'reason' => 'Nomor WhatsApp kosong.'];
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Authorization: ' . $token],
        CURLOPT_POSTFIELDS => [
            'target' => $phone,
            'message' => $message,
            'countryCode' => '62',
        ],
        CURLOPT_TIMEOUT => 15,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError !== '') {
        return ['success' => false, 'reason' => 'Curl error: ' . $curlError];
    }

    $data = json_decode((string) $response, true);

    if ($httpCode === 200 && isset($data['status']) && $data['status'] === true) {
        return ['success' => true, 'reason' => 'Pesan berhasil dikirim.'];
    }

    return ['success' => false, 'reason' => $data['reason'] ?? ('HTTP ' . $httpCode)];
}

function sendAndLogWhatsApp(PDO $pdo, int $reservasiId, string $phone, string $message, string $jenis): bool
{
    $result = sendWhatsApp($phone, $message);
    $status = $result['success'] ? 'Terkirim' : 'Gagal';

    $stmt = $pdo->prepare("
        INSERT INTO notifikasi_wa (reservasi_id, no_tujuan, pesan, jenis, status)
        VALUES (:reservasi_id, :no_tujuan, :pesan, :jenis, :status)
    ");
    $stmt->execute([
        ':reservasi_id' => $reservasiId,
        ':no_tujuan' => displayPhone($phone),
        ':pesan' => $message,
        ':jenis' => $jenis,
        ':status' => $status,
    ]);

    return $result['success'];
}

function buildAndSendTemplate(PDO $pdo, array $reservasi, string $kodeTemplate, string $jenisLog, array $data): bool
{
    $template = getWhatsAppTemplate($pdo, $kodeTemplate);
    if (!$template) {
        return false;
    }

    $message = renderTemplateMessage($template, $data);

    return sendAndLogWhatsApp($pdo, (int) $reservasi['id'], (string) ($reservasi['no_whatsapp'] ?? ''), $message, $jenisLog);
}

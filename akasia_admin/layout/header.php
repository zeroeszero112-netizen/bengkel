<?php
$currentPage = $page ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Akasia Motor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="admin-shell">
    <aside class="sidebar">
        <div class="brand-box">
            <div class="brand-mark">A</div>
            <div>
                <div class="brand-title">AKASIA MOTOR</div>
                <div class="brand-subtitle">Admin Panel</div>
            </div>
        </div>


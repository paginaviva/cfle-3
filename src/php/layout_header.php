<?php
// src/php/layout_header.php
if (!defined('APP_TITLE')) {
    define('APP_TITLE', 'Gestor de Fichas');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_TITLE; ?></title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>
    <header class="app-header">
        <div class="header-left">
            <!-- Logo corporativo (Referencia visual 3) -->
            <img src="https://srrhhmx.s-ul.eu/P6Za8iMR" alt="Logo" style="height: 40px; width: auto;">
            <h1 class="app-title"><?php echo APP_TITLE; ?></h1>
        </div>
        <div class="header-right">
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                <span style="margin-right: 1rem; align-self: center;">Hola, <?php echo htmlspecialchars($_SESSION['user'] ?? ''); ?></span>
                <a href="carga_pdf.php?nuevo=1" class="btn btn-primary">Nuevo Proceso</a>
                <a href="logout.php" class="btn btn-outline">Cerrar Sesión</a>
            <?php endif; ?>
        </div>
    </header>
    <main class="main-container">

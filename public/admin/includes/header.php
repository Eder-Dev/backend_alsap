<?php
declare(strict_types=1);

$pageTitle = $pageTitle ?? 'Painel administrativo';
$activePage = $activePage ?? '';
$publicPath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/admin/index.php')), '/\\');
$apiBase = ($publicPath === '' ? '' : $publicPath) . '/alsap';

function adminEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= adminEscape($pageTitle) ?> - ALSAP</title>
    <link rel="stylesheet" href="./css/index.css">
    <link rel="stylesheet" href="./css/acervo.css">
    <link rel="stylesheet" href="./css/admin.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="./js/admin-shell.js" defer></script>
    <script src="./js/admin.js?v=<?= (string) filemtime(__DIR__ . '/../js/admin.js') ?>" defer></script>
</head>
<body>
    <div class="sidebar">
        <div class="logo-details">
            <div class="logo_name">Alsap</div>
            <button class="icon-button sidebar-toggle" id="btn" type="button" aria-label="Abrir ou fechar menu">
                <i class="bx bx-menu" aria-hidden="true"></i>
            </button>
        </div>
        <ul class="nav-list">
            <li>
                <a href="dashboard.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>" <?= $activePage === 'dashboard' ? 'aria-current="page"' : '' ?>>
                    <i class="bx bx-home-alt-2" aria-hidden="true"></i>
                    <span class="links_name">Dashboard</span>
                </a>
                <span class="tooltip">Dashboard</span>
            </li>
            <li>
                <a href="questoes.php" class="<?= $activePage === 'questoes' ? 'active' : '' ?>" <?= $activePage === 'questoes' ? 'aria-current="page"' : '' ?>>
                    <i class="bx bx-help-circle" aria-hidden="true"></i>
                    <span class="links_name">Questões</span>
                </a>
                <span class="tooltip">Questões</span>
            </li>
            <li>
                <a href="respostas.php" class="<?= $activePage === 'respostas' ? 'active' : '' ?>" <?= $activePage === 'respostas' ? 'aria-current="page"' : '' ?>>
                    <i class="bx bx-message-rounded-detail" aria-hidden="true"></i>
                    <span class="links_name">Respostas</span>
                </a>
                <span class="tooltip">Respostas</span>
            </li>
            <li>
                <a href="audios.php" class="<?= $activePage === 'audios' ? 'active' : '' ?>" <?= $activePage === 'audios' ? 'aria-current="page"' : '' ?>>
                    <i class="bx bx-music" aria-hidden="true"></i>
                    <span class="links_name">Áudios</span>
                </a>
                <span class="tooltip">Áudios</span>
            </li>
            <li>
                <a href="localizacoes.php" class="<?= $activePage === 'localizacoes' ? 'active' : '' ?>" <?= $activePage === 'localizacoes' ? 'aria-current="page"' : '' ?>>
                    <i class="bx bx-map" aria-hidden="true"></i>
                    <span class="links_name">Localizações</span>
                </a>
                <span class="tooltip">Localizações</span>
            </li>
            <li class="profile">
                <div class="profile-details">
                    <i class="bx bx-user-circle profile-avatar" aria-hidden="true"></i>
                    <div class="name_job">
                        <div class="name">ALSAP</div>
                        <div class="job">Gestão do acervo</div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    <section class="home-section" id="content">

<?php
declare(strict_types=1);

$pageTitle = 'Áudios';
$activePage = 'audios';
require __DIR__ . '/includes/header.php';
?>
<main class="admin-page" data-admin-resource="audios" data-api-base="<?= adminEscape($apiBase) ?>">
    <div class="admin-page-header">
        <div>
            <h1>Áudios</h1>
            <p>Gerencie os metadados e os endereços dos áudios do acervo.</p>
        </div>
    </div>
    <div class="admin-toolbar">
        <label class="admin-search">
            <i class="bx bx-search" aria-hidden="true"></i>
            <span class="visually-hidden">Pesquisar áudios</span>
            <input id="adminSearch" type="search" placeholder="Pesquisar áudios..." autocomplete="off">
        </label>
        <span id="adminResultCount" class="admin-result-count" aria-live="polite"></span>
    </div>
    <div id="adminMessage" class="admin-message" role="status" aria-live="polite"></div>
    <div id="adminLoading" class="admin-loading" role="status">Carregando áudios...</div>
    <div class="admin-table-wrap">
        <table class="admin-table" id="adminTable">
            <thead></thead>
            <tbody></tbody>
        </table>
    </div>
    <button id="createDocumentBtn" class="create-document-btn" type="button" aria-label="Cadastrar novo áudio">
        <i class="bx bx-plus" aria-hidden="true"></i>
        <span>Novo áudio</span>
    </button>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>

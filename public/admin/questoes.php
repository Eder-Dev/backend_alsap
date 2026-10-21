<?php
declare(strict_types=1);

$pageTitle = 'Questões';
$activePage = 'questoes';
require __DIR__ . '/includes/header.php';
?>
<main class="admin-page" data-admin-resource="questoes" data-api-base="<?= adminEscape($apiBase) ?>">
    <div class="admin-page-header">
        <div>
            <h1>Questões</h1>
            <p>Gerencie as questões cadastradas no acervo.</p>
        </div>
    </div>
    <div class="admin-toolbar">
        <label class="admin-search">
            <i class="bx bx-search" aria-hidden="true"></i>
            <span class="visually-hidden">Pesquisar questões</span>
            <input id="adminSearch" type="search" placeholder="Pesquisar questões..." autocomplete="off">
        </label>
        <span id="adminResultCount" class="admin-result-count" aria-live="polite"></span>
    </div>
    <div id="adminMessage" class="admin-message" role="status" aria-live="polite"></div>
    <div id="adminLoading" class="admin-loading" role="status">Carregando questões...</div>
    <div class="admin-table-wrap">
        <table class="admin-table" id="adminTable">
            <thead></thead>
            <tbody></tbody>
        </table>
    </div>
    <button id="createDocumentBtn" class="create-document-btn" type="button" aria-label="Criar nova questão">
        <i class="bx bx-plus" aria-hidden="true"></i>
        <span>Nova questão</span>
    </button>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>

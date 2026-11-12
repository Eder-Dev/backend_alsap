<?php
declare(strict_types=1);

$pageTitle = 'Respostas';
$activePage = 'respostas';
require __DIR__ . '/includes/header.php';
?>
<main class="admin-page" data-admin-resource="respostas" data-api-base="<?= adminEscape($apiBase) ?>">
    <div class="admin-page-header">
        <div>
            <h1>Respostas</h1>
            <p>Gerencie respostas, questões e localizações associadas.</p>
        </div>
    </div>
    <div class="admin-toolbar">
        <label class="admin-search">
            <i class="bx bx-search" aria-hidden="true"></i>
            <span class="visually-hidden">Pesquisar respostas</span>
            <input id="adminSearch" type="search" placeholder="Pesquisar respostas..." autocomplete="off">
        </label>
        <span id="adminResultCount" class="admin-result-count" aria-live="polite"></span>
        <button id="collectiveResponseBtn" class="admin-action" type="button" disabled>
            <i class="bx bx-group" aria-hidden="true"></i>
            Resposta Coletiva
        </button>
    </div>
    <div id="adminMessage" class="admin-message" role="status" aria-live="polite"></div>
    <div id="adminLoading" class="admin-loading" role="status">Carregando respostas...</div>
    <div class="admin-table-wrap">
        <table class="admin-table" id="adminTable">
            <thead></thead>
            <tbody></tbody>
        </table>
    </div>
    <button id="createDocumentBtn" class="create-document-btn" type="button" aria-label="Criar nova resposta">
        <i class="bx bx-plus" aria-hidden="true"></i>
        <span>Nova resposta</span>
    </button>
    <div id="collectiveResponseModal" class="modal admin-modal" aria-hidden="true">
        <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="collectiveResponseTitle">
            <div class="modal-header">
                <h2 id="collectiveResponseTitle">Resposta Coletiva</h2>
                <button class="modal-close" type="button" data-collective-close aria-label="Fechar">×</button>
            </div>
            <form id="collectiveResponseForm">
                <div class="modal-body">
                    <label class="modal-field" for="collectiveQuestion">
                        <strong>Pergunta</strong>
                        <select id="collectiveQuestion" name="questao_id" required>
                            <option value="">Selecione uma pergunta...</option>
                        </select>
                    </label>
                    <p id="collectiveResponseEstimate" class="admin-modal-note" aria-live="polite">Selecione uma pergunta para ver quantas respostas serão criadas.</p>
                    <div id="collectiveResponseMessage" class="admin-message" role="status" aria-live="polite"></div>
                </div>
                <div class="modal-footer">
                    <button class="admin-cancel" type="button" data-collective-close>Cancelar</button>
                    <button class="btn-view" type="submit" id="collectiveResponseSubmit">Criar respostas</button>
                </div>
            </form>
        </div>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>

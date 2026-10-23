<?php
declare(strict_types=1);

$pageTitle = 'Editar resposta';
$activePage = 'respostas';
require __DIR__ . '/includes/header.php';
$responseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$responseId = is_int($responseId) && $responseId > 0 ? (string) $responseId : '';
?>
<main class="admin-page resposta-editor" data-resposta-editor data-api-base="<?= adminEscape($apiBase) ?>" data-edit-id="<?= adminEscape($responseId) ?>">
    <div class="admin-page-header">
        <div>
            <h1>Editar resposta</h1>
            <p>Edite a questão, a localização e os áudios associados à resposta.</p>
        </div>
    </div>
    <div id="adminMessage" class="admin-message" role="status" aria-live="polite"></div>
    <div id="respostaLoading" class="admin-loading" role="status">Carregando resposta...</div>
    <div id="respostaForm" hidden>
        <div class="resposta-editor-grid">
            <section class="resposta-editor-section">
                <h2>Questão</h2>
                <label class="resposta-editor-field" for="respostaQuestao">
                    <span>Selecione a questão</span>
                    <select id="respostaQuestao" name="questao_id" required></select>
                </label>
                <button class="admin-action" type="button" data-toggle-form="questao">+ Criar nova questão</button>
                <form class="resposta-quick-form" id="novaQuestaoForm" hidden>
                    <label class="resposta-editor-field" for="novaQuestaoDescricao">
                        <span>Descrição da questão</span>
                        <input id="novaQuestaoDescricao" name="descricao" type="text" maxlength="255" required>
                    </label>
                    <fieldset class="resposta-radio-group">
                        <legend>Tipo</legend>
                        <label><input name="tipo" type="radio" value="QFF" required checked> Questões Fonética Fonológica (QFF)</label>
                        <label><input name="tipo" type="radio" value="QSL" required> Questões Semântico Lexical (QSL)</label>
                    </fieldset>
                    <button class="admin-action" type="submit">Criar e selecionar questão</button>
                </form>
            </section>
            <section class="resposta-editor-section">
                <h2>Localização</h2>
                <label class="resposta-editor-field" for="respostaEndereco">
                    <span>Selecione a localização</span>
                    <select id="respostaEndereco" name="endereco_id" required></select>
                </label>
                <button class="admin-action" type="button" data-toggle-form="endereco">+ Criar nova localização</button>
                <form class="resposta-quick-form" id="novoEnderecoForm" hidden>
                    <label class="resposta-editor-field" for="novoEnderecoNome">
                        <span>Nome</span>
                        <input id="novoEnderecoNome" name="nome" type="text" maxlength="255" required>
                    </label>
                    <label class="resposta-editor-field" for="novoEnderecoCep">
                        <span>CEP</span>
                        <input id="novoEnderecoCep" name="cep" type="text" maxlength="255">
                    </label>
                    <div class="resposta-editor-grid">
                        <label class="resposta-editor-field" for="novoEnderecoLatitude">
                            <span>Latitude</span>
                            <input id="novoEnderecoLatitude" name="latitude" type="number" step="any">
                        </label>
                        <label class="resposta-editor-field" for="novoEnderecoLongitude">
                            <span>Longitude</span>
                            <input id="novoEnderecoLongitude" name="longitude" type="number" step="any">
                        </label>
                    </div>
                    <button class="admin-action" type="submit">Criar e selecionar localização</button>
                </form>
            </section>
        </div>
        <div class="resposta-editor-save">
            <button class="btn-view" id="saveResposta" type="button">Salvar questão e localização</button>
        </div>
    </div>
    <section class="resposta-editor-section resposta-audios" id="respostaAudios" hidden>
        <h2>Áudio</h2>
        <div id="audioList" class="resposta-audio-list"></div>
        <button class="admin-action" id="showAudioForm" type="button">+ Acrescentar áudio</button>
        <form class="resposta-quick-form" id="novoAudioForm" hidden>
            <label class="resposta-editor-field" for="novoAudioNome">
                <span>Nome</span>
                <input id="novoAudioNome" name="nome" type="text" maxlength="255">
            </label>
            <label class="resposta-editor-field" for="novoAudioTranscricao">
                <span>Transcrição</span>
                <textarea id="novoAudioTranscricao" name="transcricao" maxlength="255"></textarea>
            </label>
            <label class="resposta-editor-field" for="novoAudioUrl">
                <span>URL do áudio</span>
                <input id="novoAudioUrl" name="audio_url" type="url" maxlength="255" required>
            </label>
            <label class="resposta-editor-field" for="novoAudioTipo">
                <span>Tipo (número)</span>
                <input id="novoAudioTipo" name="tipo" type="number" value="0" required>
            </label>
            <button class="admin-action" type="submit">Adicionar áudio</button>
        </form>
    </section>
</main>
<script src="./js/resposta-editor.js" defer></script>
<?php require __DIR__ . '/includes/footer.php'; ?>

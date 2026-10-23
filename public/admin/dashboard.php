<?php
declare(strict_types=1);

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<main class="admin-page dashboard-page" data-dashboard data-api-base="<?= adminEscape($apiBase) ?>">
    <div class="admin-page-header">
        <div>
            <h1>Dashboard</h1>
            <p>Visão geral do acervo de questões, respostas, áudios e localizações.</p>
        </div>
        <span class="dashboard-updated" id="dashboardUpdated" aria-live="polite"></span>
    </div>
    <div class="admin-message" id="dashboardMessage" role="status" aria-live="polite"></div>
    <div class="admin-loading" id="dashboardLoading" role="status">Carregando dados do acervo...</div>
    <div class="dashboard-content" id="dashboardContent" hidden>
        <section class="dashboard-stats" aria-label="Indicadores do acervo">
            <a class="dashboard-stat" href="questoes.php">
                <span class="dashboard-stat-icon"><i class="bx bx-help-circle" aria-hidden="true"></i></span>
                <span class="dashboard-stat-label">Questões</span>
                <strong id="statQuestions">0</strong>
                <span class="dashboard-stat-detail" id="statQuestionTypes">QFF: 0 · QSL: 0</span>
            </a>
            <a class="dashboard-stat" href="respostas.php">
                <span class="dashboard-stat-icon"><i class="bx bx-message-rounded-detail" aria-hidden="true"></i></span>
                <span class="dashboard-stat-label">Respostas</span>
                <strong id="statResponses">0</strong>
                <span class="dashboard-stat-detail" id="statAverage">Média de 0 por questão</span>
            </a>
            <a class="dashboard-stat" href="audios.php">
                <span class="dashboard-stat-icon"><i class="bx bx-music" aria-hidden="true"></i></span>
                <span class="dashboard-stat-label">Áudios</span>
                <strong id="statAudios">0</strong>
                <span class="dashboard-stat-detail" id="statAudioLinks">0 associados a respostas</span>
            </a>
            <a class="dashboard-stat" href="localizacoes.php">
                <span class="dashboard-stat-icon"><i class="bx bx-map" aria-hidden="true"></i></span>
                <span class="dashboard-stat-label">Localizações</span>
                <strong id="statAddresses">0</strong>
                <span class="dashboard-stat-detail">Locais cadastrados no acervo</span>
            </a>
        </section>

        <div class="dashboard-panels">
            <section class="dashboard-panel">
                <div class="dashboard-panel-heading">
                    <div>
                        <h2>Questões por tipo</h2>
                        <p>Distribuição das questões cadastradas.</p>
                    </div>
                </div>
                <div id="questionTypeChart" class="dashboard-bar-chart" aria-label="Distribuição de questões por tipo"></div>
            </section>
            <section class="dashboard-panel">
                <div class="dashboard-panel-heading">
                    <div>
                        <h2>Respostas por localização</h2>
                        <p>Quantidade de respostas associadas a cada local.</p>
                    </div>
                </div>
                <div id="locationChart" class="dashboard-bar-chart" aria-label="Respostas por localização"></div>
            </section>
        </div>

        <div class="dashboard-panels dashboard-lists">
            <section class="dashboard-panel">
                <div class="dashboard-panel-heading">
                    <div>
                        <h2>Questões com mais respostas</h2>
                        <p>Questões com maior participação no acervo.</p>
                    </div>
                    <a href="questoes.php">Ver questões</a>
                </div>
                <div class="dashboard-list" id="topQuestions"></div>
            </section>
            <section class="dashboard-panel">
                <div class="dashboard-panel-heading">
                    <div>
                        <h2>Respostas recentes</h2>
                        <p>Últimos registros pelo identificador.</p>
                    </div>
                    <a href="respostas.php">Ver respostas</a>
                </div>
                <div class="dashboard-list" id="recentResponses"></div>
            </section>
        </div>
    </div>
</main>
<script src="./js/dashboard.js" defer></script>
<?php require __DIR__ . '/includes/footer.php'; ?>

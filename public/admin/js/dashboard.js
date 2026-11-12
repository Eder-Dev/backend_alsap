(() => {
    const root = document.querySelector('[data-dashboard]');
    if (!root) return;

    const apiBase = root.dataset.apiBase.replace(/\/+$/, '');
    const loading = document.getElementById('dashboardLoading');
    const content = document.getElementById('dashboardContent');
    const message = document.getElementById('dashboardMessage');
    const updated = document.getElementById('dashboardUpdated');
    const importButton = document.getElementById('importSqlBtn');
    const importFile = document.getElementById('importSqlFile');
    const formatButton = document.getElementById('formatDatabaseBtn');

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>'"]/g, (character) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#039;',
            '"': '&quot;'
        }[character]));
    }

    async function request(path) {
        let response;
        try {
            response = await fetch(`${apiBase}/${path}`, {
                headers: { Accept: 'application/json' },
                cache: 'no-store'
            });
        } catch {
            throw new Error('Não foi possível conectar à API ALSAP.');
        }
        if (!response.ok) {
            throw new Error(`Não foi possível carregar os dados do dashboard (HTTP ${response.status}).`);
        }
        let data;
        try {
            data = await response.json();
        } catch {
            throw new Error('A API retornou dados inválidos para o dashboard.');
        }
        if (!Array.isArray(data)) throw new Error('A API retornou dados em formato inesperado.');
        return data;
    }

    function setStat(id, value) {
        document.getElementById(id).textContent = String(value);
    }

    function chartMarkup(items, maxValue, emptyText) {
        if (items.length === 0 || maxValue === 0) return `<p class="dashboard-empty">${emptyText}</p>`;
        return items.map((item) => {
            const width = item.value === 0 ? 0 : Math.max(4, Math.round((item.value / maxValue) * 100));
            return `<div class="dashboard-bar-row">
                <div class="dashboard-bar-label"><span title="${escapeHtml(item.label)}">${escapeHtml(item.label)}</span><strong>${item.value}</strong></div>
                <div class="dashboard-bar-track" role="img" aria-label="${escapeHtml(item.label)}: ${item.value}">
                    <span class="dashboard-bar-fill" style="width:${width}%"></span>
                </div>
            </div>`;
        }).join('');
    }

    function renderQuestions(questions) {
        const answers = questions.reduce((total, question) => total + (Array.isArray(question.respostas) ? question.respostas.length : 0), 0);
        const qff = questions.filter((question) => String(question.tipo || '').toUpperCase() === 'QFF').length;
        const qsl = questions.filter((question) => String(question.tipo || '').toUpperCase() === 'QSL').length;
        setStat('statQuestions', questions.length);
        setStat('statQuestionTypes', `QFF: ${qff} · QSL: ${qsl}`);
        setStat('statAverage', `Média de ${questions.length ? (answers / questions.length).toFixed(1).replace('.', ',') : '0'} por questão`);

        const topQuestions = [...questions]
            .sort((a, b) => (b.respostas?.length || 0) - (a.respostas?.length || 0) || Number(b.id) - Number(a.id))
            .slice(0, 5);
        document.getElementById('topQuestions').innerHTML = topQuestions.length
            ? topQuestions.map((question) => `<a class="dashboard-list-item" href="questoes.php">
                <span><strong>${escapeHtml(question.descricao || `Questão #${question.id}`)}</strong><small>${escapeHtml(question.tipo || 'Tipo não informado')} · ID ${escapeHtml(question.id)}</small></span>
                <span class="dashboard-list-count">${Array.isArray(question.respostas) ? question.respostas.length : 0} ${Array.isArray(question.respostas) && question.respostas.length === 1 ? 'resposta' : 'respostas'}</span>
            </a>`).join('')
            : '<p class="dashboard-empty">Nenhuma questão cadastrada.</p>';

        const typeCounts = [
            { label: 'QFF · Fonética Fonológica', value: qff },
            { label: 'QSL · Semântico Lexical', value: qsl },
            { label: 'Outros tipos', value: questions.length - qff - qsl }
        ];
        document.getElementById('questionTypeChart').innerHTML = chartMarkup(typeCounts, questions.length, 'Nenhuma questão cadastrada.');
        return answers;
    }

    function renderLocations(addresses, responses) {
        const counts = new Map(addresses.map((address) => [Number(address.id), 0]));
        responses.forEach((response) => {
            const addressId = Number(response.endereco?.id);
            if (counts.has(addressId)) counts.set(addressId, counts.get(addressId) + 1);
        });
        const items = addresses
            .map((address) => ({ label: address.nome || `Localização #${address.id}`, value: counts.get(Number(address.id)) || 0 }))
            .sort((a, b) => b.value - a.value || a.label.localeCompare(b.label, 'pt-BR'));
        document.getElementById('locationChart').innerHTML = chartMarkup(items, Math.max(0, ...items.map((item) => item.value)), 'Nenhuma localização cadastrada.');
    }

    function renderRecentResponses(responses, questions) {
        const questionByResponse = new Map();
        questions.forEach((question) => (question.respostas || []).forEach((response) => {
            questionByResponse.set(Number(response.id), question);
        }));
        const recent = [...responses].sort((a, b) => Number(b.id) - Number(a.id)).slice(0, 5);
        document.getElementById('recentResponses').innerHTML = recent.length
            ? recent.map((response) => {
                const question = questionByResponse.get(Number(response.id));
                return `<a class="dashboard-list-item" href="editResposta.php?id=${encodeURIComponent(response.id)}">
                    <span><strong>Resposta #${escapeHtml(response.id)}</strong><small>${escapeHtml(question?.descricao || 'Questão não identificada')}</small></span>
                    <span class="dashboard-list-count">${escapeHtml(response.endereco?.nome || 'Sem localização')}</span>
                </a>`;
            }).join('')
            : '<p class="dashboard-empty">Nenhuma resposta cadastrada.</p>';
    }

    async function load() {
        try {
            const [questions, responses, audios, addresses] = await Promise.all([
                request('Questao/all'),
                request('Respostas/all'),
                request('Audio/all'),
                request('Endereco/all')
            ]);
            const responseAudioCount = responses.reduce((count, response) =>
                count + (Array.isArray(response.audios) ? response.audios.length : 0), 0);
            setStat('statResponses', responses.length);
            setStat('statAudios', audios.length);
            setStat('statAudioLinks', `${responseAudioCount} associados a respostas`);
            setStat('statAddresses', addresses.length);
            renderQuestions(questions);
            renderLocations(addresses, responses);
            renderRecentResponses(responses, questions);
            updated.textContent = `Atualizado às ${new Intl.DateTimeFormat('pt-BR', { hour: '2-digit', minute: '2-digit' }).format(new Date())}`;
            content.hidden = false;
        } catch (error) {
            message.textContent = error.message;
            message.className = 'admin-message visible error';
        } finally {
            loading.hidden = true;
        }
    }

    importButton.addEventListener('click', () => importFile.click());
    importFile.addEventListener('change', async () => {
        const file = importFile.files[0];
        if (!file) return;
        if (!file.name.toLowerCase().endsWith('.sql')) {
            message.textContent = 'Selecione um arquivo com extensão .sql.';
            message.className = 'admin-message visible error';
            importFile.value = '';
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            message.textContent = 'O arquivo SQL deve ter no máximo 5 MB.';
            message.className = 'admin-message visible error';
            importFile.value = '';
            return;
        }
        if (!window.confirm('Importar este arquivo? Só serão aceitos comandos INSERT INTO ... VALUES com valores literais nas tabelas do acervo.')) {
            importFile.value = '';
            return;
        }

        importButton.disabled = true;
        formatButton.disabled = true;
        importButton.textContent = 'Importando...';
        try {
            const formData = new FormData();
            formData.append('csrfToken', root.dataset.csrfToken);
            formData.append('sqlFile', file);
            const response = await fetch(`${apiBase}/Database/import`, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: formData,
                cache: 'no-store'
            });
            let result;
            try {
                result = await response.json();
            } catch {
                throw new Error('A API retornou uma resposta inválida ao importar o arquivo SQL.');
            }
            if (!response.ok) {
                throw new Error(result.message || `Não foi possível importar o arquivo SQL (HTTP ${response.status}).`);
            }

            message.textContent = result.message || 'Arquivo SQL importado com sucesso.';
            message.className = 'admin-message visible success';
            await load();
        } catch (error) {
            message.textContent = error.message || 'Não foi possível importar o arquivo SQL.';
            message.className = 'admin-message visible error';
        } finally {
            importFile.value = '';
            importButton.disabled = false;
            formatButton.disabled = false;
            importButton.innerHTML = '<i class="bx bx-upload" aria-hidden="true"></i> Importar arquivo SQL';
        }
    });

    formatButton.addEventListener('click', async () => {
        if (!window.confirm('Esta ação apagará todos os registros do acervo e não pode ser desfeita. Deseja continuar?')) return;
        if (window.prompt('Para confirmar, digite FORMATAR:') !== 'FORMATAR') return;

        formatButton.disabled = true;
        importButton.disabled = true;
        formatButton.textContent = 'Formatando...';
        try {
            const response = await fetch(`${apiBase}/Database/format`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ csrfToken: root.dataset.csrfToken }),
                cache: 'no-store'
            });
            let result;
            try {
                result = await response.json();
            } catch {
                throw new Error('A API retornou uma resposta inválida ao formatar o banco de dados.');
            }
            if (!response.ok) {
                throw new Error(result.message || `Não foi possível formatar o banco de dados (HTTP ${response.status}).`);
            }

            message.textContent = result.message || 'Registros do acervo removidos com sucesso.';
            message.className = 'admin-message visible success';
            await load();
        } catch (error) {
            message.textContent = error.message || 'Não foi possível formatar o banco de dados.';
            message.className = 'admin-message visible error';
        } finally {
            formatButton.disabled = false;
            importButton.disabled = false;
            formatButton.innerHTML = '<i class="bx bx-trash" aria-hidden="true"></i> Formatar Banco de Dados';
        }
    });

    load();
})();

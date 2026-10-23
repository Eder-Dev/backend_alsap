(() => {
    const root = document.querySelector('[data-dashboard]');
    if (!root) return;

    const apiBase = root.dataset.apiBase.replace(/\/+$/, '');
    const loading = document.getElementById('dashboardLoading');
    const content = document.getElementById('dashboardContent');
    const message = document.getElementById('dashboardMessage');
    const updated = document.getElementById('dashboardUpdated');

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

    load();
})();

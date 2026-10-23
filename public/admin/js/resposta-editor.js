(() => {
    const root = document.querySelector('[data-resposta-editor]');
    if (!root) return;

    const apiBase = root.dataset.apiBase.replace(/\/+$/, '');
    const responseId = Number(root.dataset.editId);
    const loading = document.getElementById('respostaLoading');
    const message = document.getElementById('adminMessage');
    const editor = document.getElementById('respostaForm');
    const questionSelect = document.getElementById('respostaQuestao');
    const addressSelect = document.getElementById('respostaEndereco');
    const audioSection = document.getElementById('respostaAudios');
    const audioList = document.getElementById('audioList');
    let response;
    let questions = [];
    let addresses = [];

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>'"]/g, (character) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#039;',
            '"': '&quot;'
        }[character]));
    }

    function showMessage(text, type = 'error') {
        message.textContent = text;
        message.className = `admin-message visible ${type}`;
    }

    function clearMessage() {
        message.textContent = '';
        message.className = 'admin-message';
    }

    async function request(path, options = {}) {
        let result;
        try {
            result = await fetch(`${apiBase}/${path}`, {
                ...options,
                headers: {
                    Accept: 'application/json',
                    ...(options.body ? { 'Content-Type': 'application/json' } : {}),
                    ...options.headers
                },
                cache: 'no-store'
            });
        } catch {
            throw new Error('Não foi possível conectar à API ALSAP.');
        }

        if (!result.ok) {
            if (result.status === 404) throw new Error('O registro solicitado não foi encontrado.');
            if (result.status === 400) throw new Error('A API rejeitou os dados. Revise os campos e tente novamente.');
            if (result.status >= 500) throw new Error('A API encontrou um erro interno. Verifique os dados e tente novamente.');
            throw new Error(`Não foi possível concluir a operação (HTTP ${result.status}).`);
        }
        if (result.status === 204) return null;
        try {
            return await result.json();
        } catch {
            throw new Error('A API retornou uma resposta inválida.');
        }
    }

    function fillSelect(select, records, labelFor, selectedId) {
        select.innerHTML = `<option value="">Selecione...</option>${records.map((record) =>
            `<option value="${escapeHtml(record.id)}" ${Number(record.id) === Number(selectedId) ? 'selected' : ''}>${escapeHtml(labelFor(record))}</option>`
        ).join('')}`;
    }

    function renderAudios() {
        const audios = Array.isArray(response.audios) ? response.audios : [];
        audioList.innerHTML = audios.length
            ? audios.map((audio) => {
                const source = String(audio.audio_url || '').trim();
                let player = '';
                if (source) {
                    try {
                        const url = new URL(source, window.location.href);
                        if (['http:', 'https:'].includes(url.protocol)) {
                            player = `<audio controls preload="none" src="${escapeHtml(url.href)}">Seu navegador não suporta áudio.</audio>`;
                        }
                    } catch {
                        player = '';
                    }
                }
                return `<article class="resposta-audio-card">
                    <div>
                        <h3>${escapeHtml(audio.nome || `Áudio #${audio.id}`)}</h3>
                        <p><strong>Transcrição:</strong> ${escapeHtml(audio.transcricao || 'Não informada')}</p>
                        <p><strong>Tipo:</strong> ${escapeHtml(audio.tipo)}</p>
                        ${player || '<p>Reprodução indisponível</p>'}
                    </div>
                </article>`;
            }).join('')
            : '<p class="resposta-audio-empty">Esta resposta ainda não possui áudios associados.</p>';
    }

    async function loadData() {
        clearMessage();
        loading.hidden = false;
        editor.hidden = true;
        audioSection.hidden = true;
        if (!Number.isSafeInteger(responseId) || responseId < 1) {
            throw new Error('ID de resposta inválido. Verifique o endereço da página.');
        }
        const [loadedResponse, loadedQuestions, loadedAddresses] = await Promise.all([
            request(`Respostas/${responseId}`),
            request('Questao/all'),
            request('Endereco/all')
        ]);
        if (!loadedResponse || Number(loadedResponse.id) !== responseId
            || !Array.isArray(loadedQuestions) || !Array.isArray(loadedAddresses)) {
            throw new Error('A API retornou dados em formato inesperado.');
        }
        response = loadedResponse;
        questions = loadedQuestions;
        addresses = loadedAddresses;

        const selectedQuestion = questions.find((question) =>
            Array.isArray(question.respostas) && question.respostas.some((item) => Number(item.id) === responseId)
        );
        fillSelect(questionSelect, questions, (question) => `${question.descricao || 'Questão'} (${question.tipo || 'sem tipo'})`, selectedQuestion?.id);
        fillSelect(addressSelect, addresses, (address) => address.nome || `Localização #${address.id}`, response.endereco?.id);
        renderAudios();
        editor.hidden = false;
        audioSection.hidden = false;
    }

    function setBusy(form, busy, submitText) {
        const button = form.querySelector('[type="submit"]');
        if (!button) return;
        if (busy) {
            button.dataset.originalText = button.textContent;
            button.textContent = submitText;
        } else {
            button.textContent = button.dataset.originalText || button.textContent;
        }
        button.disabled = busy;
    }

    document.querySelectorAll('[data-toggle-form]').forEach((button) => {
        button.addEventListener('click', () => {
            const form = button.dataset.toggleForm === 'questao'
                ? document.getElementById('novaQuestaoForm')
                : document.getElementById('novoEnderecoForm');
            form.hidden = !form.hidden;
            if (!form.hidden) form.querySelector('input')?.focus();
        });
    });

    document.getElementById('saveResposta').addEventListener('click', async () => {
        clearMessage();
        if (!questionSelect.reportValidity() || !addressSelect.reportValidity()) return;
        const button = document.getElementById('saveResposta');
        button.disabled = true;
        try {
            await request(`Respostas/update/${responseId}`, {
                method: 'PUT',
                body: JSON.stringify({
                    questao_id: Number(questionSelect.value),
                    endereco_id: Number(addressSelect.value)
                })
            });
            showMessage('Questão e localização atualizadas com sucesso.', 'success');
        } catch (error) {
            showMessage(error.message);
        } finally {
            button.disabled = false;
        }
    });

    document.getElementById('novaQuestaoForm').addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        if (!form.reportValidity()) return;
        setBusy(form, true, 'Criando...');
        clearMessage();
        try {
            const data = new FormData(form);
            const createdId = await request('Questao/create', {
                method: 'POST',
                body: JSON.stringify({
                    descricao: String(data.get('descricao')).trim(),
                    tipo: data.get('tipo')
                })
            });
            await loadData();
            const questionId = Number(typeof createdId === 'object' ? createdId?.id : createdId);
            if (Number.isSafeInteger(questionId) && questionId > 0) questionSelect.value = String(questionId);
            document.getElementById('novaQuestaoForm').hidden = true;
            form.reset();
            showMessage('Questão criada. Salve as alterações para associá-la à resposta.', 'success');
        } catch (error) {
            showMessage(error.message);
        } finally {
            setBusy(form, false);
        }
    });

    document.getElementById('novoEnderecoForm').addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        if (!form.reportValidity()) return;
        setBusy(form, true, 'Criando...');
        clearMessage();
        try {
            const data = new FormData(form);
            const valueOrNull = (name) => String(data.get(name) || '').trim() || null;
            const createdId = await request('Endereco/create', {
                method: 'POST',
                body: JSON.stringify({
                    nome: String(data.get('nome')).trim(),
                    cep: valueOrNull('cep'),
                    latitude: valueOrNull('latitude'),
                    longitude: valueOrNull('longitude')
                })
            });
            await loadData();
            const addressId = Number(typeof createdId === 'object' ? createdId?.id : createdId);
            if (Number.isSafeInteger(addressId) && addressId > 0) addressSelect.value = String(addressId);
            document.getElementById('novoEnderecoForm').hidden = true;
            form.reset();
            showMessage('Localização criada. Salve as alterações para associá-la à resposta.', 'success');
        } catch (error) {
            showMessage(error.message);
        } finally {
            setBusy(form, false);
        }
    });

    document.getElementById('showAudioForm').addEventListener('click', () => {
        const form = document.getElementById('novoAudioForm');
        form.hidden = !form.hidden;
        if (!form.hidden) form.querySelector('input')?.focus();
    });

    document.getElementById('novoAudioForm').addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        if (!form.reportValidity()) return;
        setBusy(form, true, 'Adicionando...');
        clearMessage();
        try {
            const data = new FormData(form);
            await request('Audio/create', {
                method: 'POST',
                body: JSON.stringify({
                    nome: String(data.get('nome') || '').trim() || null,
                    transcricao: String(data.get('transcricao') || '').trim() || null,
                    audio_url: String(data.get('audio_url')).trim(),
                    tipo: Number(data.get('tipo')),
                    resposta_id: responseId,
                    narrativa_id: null
                })
            });
            form.reset();
            document.getElementById('novoAudioTipo').value = '0';
            form.hidden = true;
            await loadData();
            showMessage('Áudio associado à resposta com sucesso.', 'success');
        } catch (error) {
            showMessage(error.message);
        } finally {
            setBusy(form, false);
        }
    });

    loadData().catch((error) => showMessage(error.message)).finally(() => {
        loading.hidden = true;
    });
})();

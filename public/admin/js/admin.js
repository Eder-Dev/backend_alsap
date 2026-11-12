(() => {
    const root = document.querySelector('[data-admin-resource]');
    if (!root) return;

    const configs = {
        questoes: {
            singular: 'questão',
            plural: 'questões',
            gender: 'f',
            endpoint: 'Questao',
            listPath: 'all',
            fields: [
                { name: 'descricao', label: 'Descrição', type: 'text', maxLength: 255 },
                {
                    name: 'tipo',
                    label: 'Tipo',
                    type: 'radio',
                    required: true,
                    defaultValue: 'QFF',
                    options: [
                        { value: 'QFF', label: 'Questões Fonética Fonológica' },
                        { value: 'QSL', label: 'Questões Semântico Lexical' }
                    ]
                }
            ],
            columns: [
                { label: 'ID', value: (record) => record.id },
                { label: 'Questão', value: (record) => record.descricao },
                { label: 'Tipo', value: (record) => record.tipo },
                { label: 'Respostas', value: (record) => Array.isArray(record.respostas) ? record.respostas.length : 0 }
            ]
        },
        respostas: {
            singular: 'resposta',
            plural: 'respostas',
            gender: 'f',
            endpoint: 'Respostas',
            listPath: 'all',
            related: ['Endereco/all', 'Questao/all'],
            fields: [
                { name: 'endereco_id', label: 'Localização', type: 'select', source: 'enderecos', required: true },
                { name: 'questao_id', label: 'Questão', type: 'select', source: 'questoes', required: true }
            ],
            columns: [
                { label: 'ID', value: (record) => record.id },
                { label: 'Localização', value: (record, state) => record.endereco?.nome || `ID ${record.endereco?.id ?? '—'}` },
                { label: 'Questão', value: (record, state) => state.questionByResponse.get(Number(record.id))?.descricao || 'Não identificada pela API' },
                { label: 'Áudios', value: (record) => Array.isArray(record.audios) ? record.audios.length : 0 }
            ]
        },
        audios: {
            singular: 'áudio',
            plural: 'áudios',
            gender: 'm',
            endpoint: 'Audio',
            listPath: 'all',
            related: ['Respostas/all', 'Narrativa/all', 'Questao/all'],
            fields: [
                { name: 'nome', label: 'Nome', type: 'text', maxLength: 255 },
                { name: 'transcricao', label: 'Transcrição', type: 'textarea', maxLength: 255 },
                { name: 'audio_url', label: 'URL do áudio', type: 'text', maxLength: 255 },
                { name: 'tipo', label: 'Tipo (número)', type: 'number', required: true, defaultValue: 0 },
                { name: 'resposta_id', label: 'Resposta associada (opcional)', type: 'select', source: 'respostas' },
                { name: 'narrativa_id', label: 'Narrativa associada (opcional)', type: 'select', source: 'narrativas' }
            ],
            columns: [
                { label: 'ID', value: (record) => record.id },
                { label: 'Nome', value: (record) => record.nome },
                { label: 'Tipo', value: (record) => record.tipo },
                { label: 'Transcrição', value: (record) => record.transcricao },
                { label: 'Reprodução', value: (record) => audioMarkup(record.audio_url), html: true },
                { label: 'Associação', value: (record, state) => audioAssociations(record, state) }
            ]
        },
        localizacoes: {
            singular: 'localização',
            plural: 'localizações',
            gender: 'f',
            endpoint: 'Endereco',
            listPath: 'all',
            fields: [
                { name: 'nome', label: 'Nome', type: 'text', maxLength: 255 },
                { name: 'cep', label: 'CEP', type: 'text', maxLength: 255 },
                { name: 'latitude', label: 'Latitude', type: 'number', step: 'any' },
                { name: 'longitude', label: 'Longitude', type: 'number', step: 'any' }
            ],
            columns: [
                { label: 'ID', value: (record) => record.id },
                { label: 'Nome', value: (record) => record.nome },
                { label: 'CEP', value: (record) => record.cep },
                { label: 'Latitude', value: (record) => record.latitude },
                { label: 'Longitude', value: (record) => record.longitude }
            ]
        }
    };

    const config = configs[root.dataset.adminResource];
    if (!config) return;

    const apiBase = root.dataset.apiBase.replace(/\/+$/, '');
    const table = document.getElementById('adminTable');
    const tableHead = table.querySelector('thead');
    const tableBody = table.querySelector('tbody');
    const searchInput = document.getElementById('adminSearch');
    const resultCount = document.getElementById('adminResultCount');
    const loading = document.getElementById('adminLoading');
    const message = document.getElementById('adminMessage');
    const createButton = document.getElementById('createDocumentBtn');
    const importAudiosButton = document.getElementById('importAudiosBtn');
    const importAudiosFile = document.getElementById('importAudiosFile');
    const collectiveResponseButton = document.getElementById('collectiveResponseBtn');
    const collectiveResponseModal = document.getElementById('collectiveResponseModal');
    const collectiveResponseForm = document.getElementById('collectiveResponseForm');
    const collectiveQuestionSelect = document.getElementById('collectiveQuestion');
    const collectiveResponseEstimate = document.getElementById('collectiveResponseEstimate');
    const collectiveResponseMessage = document.getElementById('collectiveResponseMessage');
    const collectiveResponseSubmit = document.getElementById('collectiveResponseSubmit');
    const modal = document.createElement('div');
    modal.className = 'modal admin-modal';
    modal.id = 'adminModal';
    modal.setAttribute('aria-hidden', 'true');
    modal.innerHTML = `
        <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="adminModalTitle">
            <div class="modal-header">
                <h2 id="adminModalTitle"></h2>
                <button class="modal-close" type="button" data-close-modal aria-label="Fechar">×</button>
            </div>
            <form id="adminForm">
                <div class="modal-body" id="adminFormFields"></div>
                <div id="adminModalMessage" class="admin-modal-note" role="status" aria-live="polite"></div>
                <div class="modal-footer">
                    <button class="admin-cancel" type="button" data-close-modal>Cancelar</button>
                    <button class="btn-view" type="submit" id="adminSave">Salvar</button>
                </div>
            </form>
        </div>`;
    document.body.append(modal);

    const form = modal.querySelector('#adminForm');
    const formFields = modal.querySelector('#adminFormFields');
    const modalTitle = modal.querySelector('#adminModalTitle');
    const modalMessage = modal.querySelector('#adminModalMessage');
    const saveButton = modal.querySelector('#adminSave');
    const state = {
        records: [],
        related: {},
        questionByResponse: new Map(),
        responseByAudio: new Map(),
        narrativeByAudio: new Map(),
        mode: 'create',
        selectedId: null,
        loaded: false
    };

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

    function parseCsv(text) {
        const rows = [];
        let row = [];
        let field = '';
        let insideQuotes = false;
        let afterQuote = false;
        let line = 1;

        const finishRow = () => {
            row.push(field);
            if (row.some((value) => value.trim() !== '')) rows.push(row);
            row = [];
            field = '';
            afterQuote = false;
        };

        for (let index = 0; index < text.length; index += 1) {
            const character = text[index];
            if (insideQuotes) {
                if (character === '"') {
                    if (text[index + 1] === '"') {
                        field += '"';
                        index += 1;
                    } else {
                        insideQuotes = false;
                        afterQuote = true;
                    }
                } else {
                    field += character;
                    if (character === '\n') line += 1;
                }
                continue;
            }

            if (afterQuote && character !== ',' && character !== '\r' && character !== '\n') {
                throw new Error(`CSV inválido na linha ${line}: conteúdo após o fechamento de aspas.`);
            }
            if (character === '"' && field === '') {
                insideQuotes = true;
            } else if (character === ',') {
                row.push(field);
                field = '';
                afterQuote = false;
            } else if (character === '\r' || character === '\n') {
                finishRow();
                if (character === '\r' && text[index + 1] === '\n') index += 1;
                line += 1;
            } else if (character === '"') {
                throw new Error(`CSV inválido na linha ${line}: aspas em posição inesperada.`);
            } else {
                field += character;
            }
        }

        if (insideQuotes) throw new Error(`CSV inválido na linha ${line}: aspas sem fechamento.`);
        if (field !== '' || row.length > 0 || afterQuote) finishRow();
        return rows;
    }

    function audioImportRows(csvText) {
        const rows = parseCsv(csvText.replace(/^\uFEFF/, ''));
        const headers = ['nome', 'transcricao', 'resposta_id', 'audio_url', 'tipo'];
        if (!rows.length || rows[0].map((value) => value.trim().toLocaleLowerCase('pt-BR')).join(',') !== headers.join(',')) {
            throw new Error(`O cabeçalho do CSV deve ser: ${headers.join(',')}.`);
        }

        const records = [];
        const errors = [];
        rows.slice(1).forEach((values, index) => {
            const csvRow = index + 2;
            if (values.length !== headers.length) {
                errors.push(`Registro ${csvRow}: esperado 5 colunas, encontrado ${values.length}.`);
                return;
            }

            const [nome, transcricao, respostaId, audioUrl, tipo] = values.map((value) => value.trim());
            if (!/^-?\d+$/.test(tipo) || !Number.isSafeInteger(Number(tipo))) {
                errors.push(`Registro ${csvRow}: tipo deve ser um número inteiro.`);
                return;
            }
            if (respostaId !== '' && (!/^\d+$/.test(respostaId) || !Number.isSafeInteger(Number(respostaId)) || Number(respostaId) < 1)) {
                errors.push(`Registro ${csvRow}: resposta_id deve ser um identificador positivo ou vazio.`);
                return;
            }

            records.push({
                rowNumber: csvRow,
                payload: {
                    nome: nome || null,
                    transcricao: transcricao || null,
                    resposta_id: respostaId === '' ? null : Number(respostaId),
                    audio_url: audioUrl || null,
                    tipo: Number(tipo),
                    narrativa_id: null
                }
            });
        });

        if (!records.length) {
            const details = errors.length ? ` ${errors.slice(0, 5).join(' ')}` : '';
            throw new Error(`Nenhum registro válido para importar.${details}`);
        }
        return { records, errors };
    }

    function apiFailure(status, action) {
        if (status === 400) return 'A API rejeitou os dados. Revise os campos e tente novamente.';
        if (status === 404) return 'O registro solicitado não foi encontrado. Atualize a lista e tente novamente.';
        if (status === 405) return 'Esta operação não está disponível para o endpoint da API.';
        if (status === 409) return 'A operação conflita com outros dados cadastrados.';
        if (status >= 500) return 'A API encontrou um erro interno. Verifique os vínculos e tente novamente.';
        return `Não foi possível ${action} ${config.singular}.`;
    }

    async function request(path, options = {}) {
        let response;
        try {
            response = await fetch(`${apiBase}/${path}`, {
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

        if (!response.ok) {
            throw new Error(apiFailure(response.status, 'concluir a operação em'));
        }
        if (response.status === 204) return null;
        if (response.status === 200 || response.status === 201) {
            try {
                return await response.json();
            } catch {
                throw new Error('A API retornou uma resposta inválida.');
            }
        }
        return null;
    }

    function buildIndexes() {
        state.questionByResponse = new Map();
        state.responseByAudio = new Map();
        state.narrativeByAudio = new Map();

        (state.related.questoes || []).forEach((question) => {
            (question.respostas || []).forEach((response) => {
                state.questionByResponse.set(Number(response.id), question);
            });
        });
        (state.related.respostas || []).forEach((response) => {
            (response.audios || []).forEach((audio) => {
                state.responseByAudio.set(Number(audio.id), response);
            });
        });
        (state.related.narrativas || []).forEach((narrative) => {
            (narrative.audios || []).forEach((audio) => {
                state.narrativeByAudio.set(Number(audio.id), narrative);
            });
        });
    }

    async function loadData() {
        clearMessage();
        loading.hidden = false;
        table.hidden = true;
        try {
            const relatedPaths = config.related || [];
            const [records, ...relatedRecords] = await Promise.all([
                request(`${config.endpoint}/${config.listPath}`),
                ...relatedPaths.map((path) => request(path))
            ]);
            if (!Array.isArray(records) || relatedRecords.some((list) => !Array.isArray(list))) {
                throw new Error('A API retornou dados em formato inesperado.');
            }

            state.records = records;
            state.related = {};
            relatedPaths.forEach((path, index) => {
                const name = path.startsWith('Endereco/') ? 'enderecos'
                    : path.startsWith('Questao/') ? 'questoes'
                        : path.startsWith('Respostas/') ? 'respostas'
                            : 'narrativas';
                state.related[name] = relatedRecords[index];
            });
            buildIndexes();
            renderTable();
            state.loaded = true;
            if (importAudiosButton) importAudiosButton.disabled = false;
            if (collectiveResponseButton) {
                populateCollectiveQuestions();
                collectiveResponseButton.disabled = !state.related.enderecos?.length || !state.related.questoes?.length;
            }
            table.hidden = false;
            const editId = Number(root.dataset.editId);
            if (config.endpoint === 'Respostas' && Number.isSafeInteger(editId) && editId > 0 && !state.initialEditHandled) {
                state.initialEditHandled = true;
                await openRecord('edit', editId);
            }
            return true;
        } catch (error) {
            state.loaded = false;
            if (collectiveResponseButton) collectiveResponseButton.disabled = true;
            tableBody.innerHTML = `<tr><td colspan="${config.columns.length + 1}" class="admin-empty">${escapeHtml(error.message || 'Não foi possível carregar os registros.')}</td></tr>`;
            if (importAudiosButton) importAudiosButton.disabled = true;
            table.hidden = false;
            resultCount.textContent = '';
            showMessage(error.message || 'Não foi possível carregar os registros.');
            return false;
        } finally {
            loading.hidden = true;
        }
    }

    function audioSource(value) {
        const source = String(value || '').trim();
        if (!source) return '';
        try {
            const url = new URL(source, window.location.href);
            return ['http:', 'https:'].includes(url.protocol) ? url.href : '';
        } catch {
            return '';
        }
    }

    function audioMarkup(value) {
        const source = audioSource(value);
        return source
            ? `<audio class="admin-audio" controls preload="none" src="${escapeHtml(source)}">Seu navegador não suporta áudio.</audio>`
            : 'Indisponível';
    }

    function audioAssociations(record, currentState) {
        const response = state.responseByAudio.get(Number(record.id));
        const narrative = state.narrativeByAudio.get(Number(record.id));
        const labels = [];
        if (response) {
            const responseId = Number(response.id);
            const question = currentState.questionByResponse.get(responseId)
                || (currentState.related.questoes || []).find((item) =>
                    (item.respostas || []).some((relatedResponse) => Number(relatedResponse.id) === responseId)
                );
            labels.push(question?.descricao || 'Questão não identificada');
        }
        if (narrative) labels.push(`Narrativa #${narrative.id}`);
        return labels.join(' · ') || 'Sem associação identificada';
    }

    function cellValue(column, record) {
        return column.value(record, state);
    }

    function renderTable() {
        const query = searchInput.value.trim().toLocaleLowerCase('pt-BR');
        const records = state.records.filter((record) => {
            if (!query) return true;
            const rowText = [
                JSON.stringify(record),
                ...config.columns.map((column) => cellValue(column, record))
            ].join(' ').toLocaleLowerCase('pt-BR');
            return rowText.includes(query);
        });

        tableHead.innerHTML = `<tr>${config.columns.map((column) => `<th scope="col">${escapeHtml(column.label)}</th>`).join('')}<th scope="col">Ações</th></tr>`;
        resultCount.textContent = `${records.length} de ${state.records.length} ${config.plural}`;

        if (records.length === 0) {
            const text = state.records.length
                ? 'Nenhum registro corresponde à pesquisa.'
                : `${config.gender === 'f' ? 'Nenhuma' : 'Nenhum'} ${config.singular} cadastrada${config.gender === 'f' ? '' : 'o'}.`;
            tableBody.innerHTML = `<tr><td colspan="${config.columns.length + 1}" class="admin-empty">${text}</td></tr>`;
            return;
        }

        tableBody.innerHTML = records.map((record) => {
            const id = Number(record.id);
            const cells = config.columns.map((column) => {
                const value = cellValue(column, record);
                return `<td>${column.html ? value : escapeHtml(value)}</td>`;
            }).join('');
            return `<tr>
                ${cells}
                <td>
                    <div class="admin-actions">
                        ${config.endpoint === 'Respostas' ? `<a class="admin-action" href="editResposta.php?id=${id}" aria-label="Abrir edição da resposta #${id}"><i class="bx bx-right-arrow-alt" aria-hidden="true"></i></a>` : ''}
                        <button class="admin-action" type="button" data-action="view" data-id="${id}"><i class="bx bx-show" aria-hidden="true"></i> Ver</button>
                        <button class="admin-action" type="button" data-action="edit" data-id="${id}"><i class="bx bx-edit" aria-hidden="true"></i> Editar</button>
                        <button class="admin-action delete" type="button" data-action="delete" data-id="${id}"><i class="bx bx-trash" aria-hidden="true"></i> Excluir</button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    function populateCollectiveQuestions() {
        if (!collectiveQuestionSelect) return;
        const questions = state.related.questoes || [];
        collectiveQuestionSelect.innerHTML = `<option value="">Selecione uma pergunta...</option>${questions.map((question) =>
            `<option value="${escapeHtml(question.id)}">${escapeHtml(question.descricao || `Pergunta #${question.id}`)} (#${escapeHtml(question.id)})</option>`
        ).join('')}`;
        updateCollectiveEstimate();
    }

    function collectiveCounts(questionId) {
        const existingAddressIds = new Set();
        state.records.forEach((response) => {
            const question = state.questionByResponse.get(Number(response.id));
            const addressId = Number(response.endereco?.id);
            if (Number(question?.id) === questionId && Number.isSafeInteger(addressId)) {
                existingAddressIds.add(addressId);
            }
        });
        const addresses = state.related.enderecos || [];
        return {
            addresses,
            existing: addresses.filter((address) => existingAddressIds.has(Number(address.id))).length,
            pending: addresses.filter((address) => !existingAddressIds.has(Number(address.id)))
        };
    }

    function updateCollectiveEstimate() {
        if (!collectiveResponseEstimate || !collectiveQuestionSelect) return;
        const questionId = Number(collectiveQuestionSelect.value);
        if (!Number.isSafeInteger(questionId) || questionId < 1) {
            collectiveResponseEstimate.textContent = 'Selecione uma pergunta para ver quantas respostas serão criadas.';
            if (collectiveResponseSubmit) collectiveResponseSubmit.disabled = true;
            return;
        }
        const { addresses, existing, pending } = collectiveCounts(questionId);
        collectiveResponseEstimate.textContent = `Serão criadas ${pending.length} resposta(s) para os municípios cadastrados; ${existing} município(s) com resposta existente serão ignorados.`;
        if (collectiveResponseSubmit) collectiveResponseSubmit.disabled = addresses.length === 0 || pending.length === 0;
    }

    function optionsFor(field) {
        if (field.source === 'enderecos') {
            return (state.related.enderecos || []).map((record) => ({
                id: record.id,
                label: `${record.nome || 'Localização'} (#${record.id})`
            }));
        }
        if (field.source === 'questoes') {
            return (state.related.questoes || []).map((record) => ({
                id: record.id,
                label: `${record.descricao || 'Questão'} (#${record.id})`
            }));
        }
        if (field.source === 'respostas') {
            return (state.related.respostas || []).map((record) => ({
                id: record.id,
                label: `Resposta #${record.id}${record.endereco?.nome ? ` — ${record.endereco.nome}` : ''}`
            }));
        }
        if (field.source === 'narrativas') {
            return (state.related.narrativas || []).map((record) => ({
                id: record.id,
                label: `${record.titulo || record.nome || 'Narrativa'} (#${record.id})`
            }));
        }
        return [];
    }

    function valueForField(field, record) {
        if (!record) return field.defaultValue ?? '';
        if (field.name === 'endereco_id') return record.endereco?.id ?? '';
        if (field.name === 'questao_id') return state.questionByResponse.get(Number(record.id))?.id ?? '';
        if (field.name === 'resposta_id') return state.responseByAudio.get(Number(record.id))?.id ?? '';
        if (field.name === 'narrativa_id') return state.narrativeByAudio.get(Number(record.id))?.id ?? '';
        return record[field.name] ?? field.defaultValue ?? '';
    }

    function renderField(field, record, readOnly) {
        const value = valueForField(field, record);
        const valueString = String(value);
        const id = `adminField-${field.name}`;
        let input;
        if (field.type === 'radio') {
            input = `<fieldset class="admin-radio-group">
                <legend>${escapeHtml(field.label)}</legend>
                ${field.options.map((option, index) => {
                    const optionId = `${id}-${index}`;
                    return `<label for="${optionId}">
                        <input id="${optionId}" name="${field.name}" type="radio" value="${escapeHtml(option.value)}" ${field.required ? 'required' : ''} ${valueString === option.value ? 'checked' : ''} ${readOnly ? 'disabled' : ''}>
                        <span>${escapeHtml(option.label)}</span>
                    </label>`;
                }).join('')}
            </fieldset>`;
            return `<div class="modal-field">${input}</div>`;
        }
        if (field.type === 'select') {
            const options = optionsFor(field);
            input = `<select id="${id}" name="${field.name}" ${field.required ? 'required' : ''} ${readOnly ? 'disabled' : ''}>
                <option value="">${field.required ? 'Selecione...' : 'Sem nova associação'}</option>
                ${options.map((option) => `<option value="${escapeHtml(option.id)}" ${String(option.id) === valueString ? 'selected' : ''}>${escapeHtml(option.label)}</option>`).join('')}
            </select>`;
        } else if (field.type === 'textarea') {
            input = `<textarea id="${id}" name="${field.name}" ${field.maxLength ? `maxlength="${field.maxLength}"` : ''} ${readOnly ? 'disabled' : ''}>${escapeHtml(valueString)}</textarea>`;
        } else {
            const inputType = field.type === 'number' ? 'number' : 'text';
            input = `<input id="${id}" name="${field.name}" type="${inputType}" value="${escapeHtml(valueString)}" ${field.maxLength ? `maxlength="${field.maxLength}"` : ''} ${field.step ? `step="${field.step}"` : ''} ${field.required ? 'required' : ''} ${readOnly ? 'disabled' : ''}>`;
        }
        return `<label class="modal-field" for="${id}">
            <strong>${escapeHtml(field.label)}</strong>
            ${input}
        </label>`;
    }

    function setModal(mode, record = null) {
        state.mode = mode;
        state.selectedId = record ? Number(record.id) : null;
        const readOnly = mode === 'view';
        const title = mode === 'create' ? `Nova ${config.singular}` : mode === 'edit' ? `Editar ${config.singular}` : `Visualizar ${config.singular}`;
        modalTitle.textContent = title;
        modalMessage.textContent = '';
        formFields.innerHTML = `${record ? `<p class="admin-modal-note">ID: ${escapeHtml(record.id)}</p>` : ''}
            ${config.fields.map((field) => renderField(field, record, readOnly)).join('')}
            ${config.endpoint === 'Audio' && mode !== 'create' ? '<p class="admin-modal-note">A API atual não remove associações quando o campo fica vazio. Em cada atualização, só é possível alterar uma associação por vez.</p>' : ''}`;
        saveButton.hidden = readOnly;
        modal.querySelectorAll('[data-close-modal]').forEach((button) => {
            button.textContent = button.classList.contains('modal-close') ? '×' : readOnly ? 'Fechar' : 'Cancelar';
        });
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        if (!readOnly) {
            const firstField = formFields.querySelector('input, select, textarea');
            firstField?.focus();
        }
    }

    function closeModal() {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        form.reset();
        modalMessage.textContent = '';
        state.selectedId = null;
    }

    function recordPayload() {
        const payload = {};
        config.fields.forEach((field) => {
            const input = form.elements.namedItem(field.name);
            const value = input.value.trim();
            if (field.type === 'number') {
                payload[field.name] = value === '' ? (field.required ? field.defaultValue ?? 0 : null) : Number(value);
            } else if (field.type === 'select') {
                payload[field.name] = value === '' ? null : Number(value);
            } else {
                payload[field.name] = value === '' ? null : value;
            }
        });
        if (config.endpoint === 'Audio' && state.mode === 'edit' && state.selectedId !== null) {
            const originalResponseId = state.responseByAudio.get(state.selectedId)?.id ?? null;
            const originalNarrativeId = state.narrativeByAudio.get(state.selectedId)?.id ?? null;
            const responseChanged = payload.resposta_id !== originalResponseId;
            const narrativeChanged = payload.narrativa_id !== originalNarrativeId;

            if (responseChanged && narrativeChanged && payload.resposta_id !== null && payload.narrativa_id !== null) {
                throw new Error('A API permite alterar apenas uma associação de áudio por operação. Salve uma associação por vez.');
            }
            if (narrativeChanged && !responseChanged) payload.resposta_id = null;
            if (responseChanged && !narrativeChanged) payload.narrativa_id = null;
        }
        return payload;
    }

    async function openRecord(mode, id) {
        clearMessage();
        try {
            const record = await request(`${config.endpoint}/${id}`);
            if (!record || typeof record !== 'object' || Number(record.id) !== Number(id)) {
                throw new Error('A API retornou um registro em formato inesperado.');
            }
            setModal(mode, record);
        } catch (error) {
            showMessage(error.message || `Não foi possível consultar ${config.singular}.`);
        }
    }

    createButton.addEventListener('click', () => {
        clearMessage();
        setModal('create');
    });
    if (collectiveResponseButton && collectiveResponseModal && collectiveResponseForm) {
        const closeCollectiveModal = () => {
            collectiveResponseModal.classList.remove('active');
            collectiveResponseModal.setAttribute('aria-hidden', 'true');
            if (!modal.classList.contains('active')) document.body.style.overflow = '';
        };

        collectiveResponseButton.addEventListener('click', () => {
            collectiveResponseMessage.textContent = '';
            collectiveResponseMessage.className = 'admin-message';
            updateCollectiveEstimate();
            collectiveResponseModal.classList.add('active');
            collectiveResponseModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            collectiveQuestionSelect.focus();
        });
        collectiveQuestionSelect.addEventListener('change', updateCollectiveEstimate);
        collectiveResponseModal.querySelectorAll('[data-collective-close]').forEach((button) => {
            button.addEventListener('click', closeCollectiveModal);
        });
        collectiveResponseModal.addEventListener('click', (event) => {
            if (event.target === collectiveResponseModal) closeCollectiveModal();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && collectiveResponseModal.classList.contains('active')) {
                closeCollectiveModal();
            }
        });
        collectiveResponseForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!collectiveResponseForm.reportValidity()) return;

            const questionId = Number(collectiveQuestionSelect.value);
            const { pending } = collectiveCounts(questionId);
            if (!pending.length) {
                updateCollectiveEstimate();
                return;
            }

            collectiveResponseSubmit.disabled = true;
            collectiveResponseSubmit.textContent = 'Criando respostas...';
            collectiveResponseModal.querySelectorAll('[data-collective-close]').forEach((button) => {
                button.disabled = true;
            });
            const failures = [];
            let created = 0;
            try {
                for (let index = 0; index < pending.length; index += 1) {
                    const address = pending[index];
                    collectiveResponseMessage.textContent = `Criando resposta ${index + 1} de ${pending.length}...`;
                    collectiveResponseMessage.className = 'admin-message visible info';
                    try {
                        await request(`${config.endpoint}/create`, {
                            method: 'POST',
                            body: JSON.stringify({
                                endereco_id: Number(address.id),
                                questao_id: questionId
                            })
                        });
                        created += 1;
                    } catch (error) {
                        failures.push(`${address.nome || `Município #${address.id}`}: ${error.message || 'falha ao criar resposta.'}`);
                    }
                }

                const refreshed = created > 0 ? await loadData() : true;
                const summary = `Resposta coletiva concluída: ${created} de ${pending.length} resposta(s) criada(s).`
                    + (failures.length ? ` Falhas: ${failures.slice(0, 5).join(' ')}` : '')
                    + (!refreshed ? ' Não foi possível atualizar a lista; recarregue a página.' : '');
                closeCollectiveModal();
                showMessage(summary, failures.length || !refreshed ? 'error' : 'success');
            } catch (error) {
                collectiveResponseMessage.textContent = error.message || 'Não foi possível criar as respostas coletivas.';
                collectiveResponseMessage.className = 'admin-message visible error';
            } finally {
                collectiveResponseSubmit.textContent = 'Criar respostas';
                collectiveResponseModal.querySelectorAll('[data-collective-close]').forEach((button) => {
                    button.disabled = false;
                });
                updateCollectiveEstimate();
            }
        });
    }
    if (importAudiosButton && importAudiosFile) {
        importAudiosButton.addEventListener('click', () => importAudiosFile.click());
        importAudiosFile.addEventListener('change', async () => {
            const file = importAudiosFile.files[0];
            if (!file) return;

            importAudiosButton.disabled = true;
            importAudiosFile.disabled = true;
            try {
                const { records, errors } = audioImportRows(await file.text());
                const invalidCount = errors.length;
                const confirmation = `Importar ${records.length} novos áudios${invalidCount ? ` e ignorar ${invalidCount} registro(s) inválido(s)` : ''}?\n\nA importação cria novos registros; importar o mesmo arquivo novamente pode gerar duplicatas.`;
                if (!window.confirm(confirmation)) return;

                const failures = [...errors];
                let imported = 0;
                for (let index = 0; index < records.length; index += 1) {
                    message.textContent = `Importando áudio ${index + 1} de ${records.length}...`;
                    message.className = 'admin-message visible info';
                    try {
                        await request(`${config.endpoint}/create`, {
                            method: 'POST',
                            body: JSON.stringify(records[index].payload)
                        });
                        imported += 1;
                    } catch (error) {
                        failures.push(`Registro ${records[index].rowNumber}: ${error.message || 'falha ao cadastrar.'}`);
                    }
                }

                const refreshed = imported > 0 ? await loadData() : true;
                const summary = `Importação concluída: ${imported} de ${records.length} áudio(s) importado(s).`
                    + (failures.length ? ` ${failures.length} registro(s) não foram importados. ${failures.slice(0, 5).join(' ')}` : '')
                    + (!refreshed ? ' Não foi possível atualizar a lista; recarregue a página.' : '');
                showMessage(summary, failures.length || !refreshed ? 'error' : 'success');
            } catch (error) {
                showMessage(error.message || 'Não foi possível importar o arquivo CSV.');
            } finally {
                importAudiosFile.value = '';
                importAudiosFile.disabled = false;
                importAudiosButton.disabled = !state.loaded;
            }
        });
    }
    searchInput.addEventListener('input', renderTable);
    modal.querySelectorAll('[data-close-modal]').forEach((button) => button.addEventListener('click', closeModal));
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('active')) closeModal();
    });

    tableBody.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-action]');
        if (!button) return;

        const id = Number(button.dataset.id);
        if (!Number.isSafeInteger(id) || id < 1) return;
        const action = button.dataset.action;
        if (action === 'view' || action === 'edit') {
            await openRecord(action, id);
            return;
        }
        if (action !== 'delete') return;

        if (!window.confirm(`Deseja excluir ${config.singular} #${id}?`)) return;
        button.disabled = true;
        button.textContent = 'Excluindo...';
        try {
            await request(`${config.endpoint}/delete/${id}`, { method: 'DELETE' });
            const refreshed = await loadData();
            if (refreshed) {
                showMessage(`${config.singular[0].toLocaleUpperCase('pt-BR')}${config.singular.slice(1)} ${config.gender === 'f' ? 'excluída' : 'excluído'} com sucesso.`, 'success');
            }
        } catch (error) {
            showMessage(error.message || `Não foi possível excluir ${config.singular}.`);
        } finally {
            button.disabled = false;
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (state.mode === 'view' || !form.reportValidity()) return;

        let payload;
        try {
            payload = recordPayload();
        } catch (error) {
            modalMessage.textContent = error.message;
            modalMessage.className = 'admin-modal-note admin-message error visible';
            return;
        }

        saveButton.disabled = true;
        saveButton.textContent = 'Salvando...';
        modal.querySelectorAll('.admin-cancel, .modal-close').forEach((button) => {
            button.disabled = true;
        });

        const action = state.mode;
        const id = state.selectedId;
        const path = action === 'create' ? `${config.endpoint}/create` : `${config.endpoint}/update/${id}`;
        try {
            await request(path, {
                method: action === 'create' ? 'POST' : 'PUT',
                body: JSON.stringify(payload)
            });
            closeModal();
            const refreshed = await loadData();
            if (refreshed) {
                const verb = action === 'create'
                    ? (config.gender === 'f' ? 'cadastrada' : 'cadastrado')
                    : (config.gender === 'f' ? 'atualizada' : 'atualizado');
                showMessage(`${config.singular[0].toLocaleUpperCase('pt-BR')}${config.singular.slice(1)} ${verb} com sucesso.`, 'success');
            }
        } catch (error) {
            modalMessage.textContent = error.message || `Não foi possível salvar ${config.singular}.`;
            modalMessage.className = 'admin-modal-note admin-message error visible';
        } finally {
            saveButton.disabled = false;
            saveButton.textContent = 'Salvar';
            modal.querySelectorAll('.admin-cancel, .modal-close').forEach((button) => {
                button.disabled = false;
            });
        }
    });

    loadData();
})();
